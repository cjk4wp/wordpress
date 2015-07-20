/* global JSON, _wpCustomizePreviewNavMenusExports */

wp.customize.menusPreview = ( function( $, api ) {
	'use strict';
	var self;

	self = {
		renderQueryVar: null,
		renderNonceValue: null,
		renderNoncePostKey: null,
		previewCustomizeNonce: null,
		requestUri: '/',
		theme: {
			active: false,
			stylesheet: ''
		},
		navMenuInstanceArgs: {},
		refreshDebounceDelay: 200
	};

	api.bind( 'preview-ready', function() {
		api.preview.bind( 'active', function() {
			self.init();
		} );
	} );

	/**
	 * Bootstrap functionality.
	 */
	self.init = function() {
		var self = this, initializedSettings = {};

		if ( 'undefined' !== typeof _wpCustomizePreviewNavMenusExports ) {
			$.extend( self, _wpCustomizePreviewNavMenusExports );
		}

		api.each( function( setting, id ) {
			setting.id = id;
			initializedSettings[ setting.id ] = true;
			self.bindListener( setting );
		} );

		api.preview.bind( 'setting', function( args ) {
			var id, value, setting;
			args = args.slice();
			id = args.shift();
			value = args.shift();

			setting = api( id );
			if ( ! setting ) {
				// Currently customize-preview.js is not creating settings for dynamically-created settings in the pane, so we have to do it.
				setting = api.create( id, value ); // @todo This should be in core
			}
			if ( ! setting.id ) {
				// Currently customize-preview.js doesn't set the id property for each setting, like customize-controls.js does.
				setting.id = id;
			}

			if ( ! initializedSettings[ setting.id ] ) {
				initializedSettings[ setting.id ] = true;
				if ( self.bindListener( setting ) ) {
					setting.callbacks.fireWith( setting, [ setting(), null ] );
				}
			}
		} );
	};

	/**
	 *
	 * @param {wp.customize.Value} setting
	 * @returns {boolean} Whether the setting was bound.
	 */
	self.bindListener = function( setting ) {
		var matches, themeLocation;

		matches = setting.id.match( /^nav_menu\[(-?\d+)]$/ );
		if ( matches ) {
			setting.navMenuId = parseInt( matches[1], 10 );
			setting.bind( self.onChangeNavMenuSetting );
			return true;
		}

		matches = setting.id.match( /^nav_menu_item\[(-?\d+)]$/ );
		if ( matches ) {
			setting.navMenuItemId = parseInt( matches[1], 10 );
			setting.bind( self.onChangeNavMenuItemSetting );
			return true;
		}

		matches = setting.id.match( /^nav_menu_locations\[(.+?)]/ );
		if ( matches ) {
			themeLocation = matches[1];
			setting.bind( function() {
				self.refreshMenuLocation( themeLocation );
			} );
			return true;
		}

		return false;
	};

	/**
	 * Handle changing of a nav_menu setting.
	 *
	 * @this {wp.customize.Setting}
	 */
	self.onChangeNavMenuSetting = function() {
		var setting = this;
		if ( ! setting.navMenuId ) {
			throw new Error( 'Expected navMenuId property to be set.' );
		}
		self.refreshMenu( setting.navMenuId );
	};

	/**
	 * Handle changing of a nav_menu_item setting.
	 *
	 * @this {wp.customize.Setting}
	 * @param {object} to
	 * @param {object} from
	 */
	self.onChangeNavMenuItemSetting = function( to, from ) {
		if ( from && from.nav_menu_term_id && ( ! to || from.nav_menu_term_id !== to.nav_menu_term_id ) ) {
			self.refreshMenu( from.nav_menu_term_id );
		}
		if ( to && to.nav_menu_term_id ) {
			self.refreshMenu( to.nav_menu_term_id );
		}
	};

	/**
	 * Update a given menu rendered in the preview.
	 *
	 * @param {int} menuId
	 */
	self.refreshMenu = function( menuId ) {
		var self = this, assignedLocations = [];

		api.each(function( setting, id ) {
			var matches = id.match( /^nav_menu_locations\[(.+?)]/ );
			if ( matches && menuId === setting() ) {
				assignedLocations.push( matches[1] );
			}
		});

		_.each( self.navMenuInstanceArgs, function( navMenuArgs, instanceNumber ) {
			if ( menuId === navMenuArgs.menu || -1 !== _.indexOf( assignedLocations, navMenuArgs.theme_location ) ) {
				self.refreshMenuInstanceDebounced( instanceNumber );
			}
		} );
	};

	/**
	 * Refresh the menu(s) associated with a given nav menu location.
	 *
	 * @param {string} location
	 */
	self.refreshMenuLocation = function( location ) {
		var foundInstance = false;
		_.each( self.navMenuInstanceArgs, function( navMenuArgs, instanceNumber ) {
			if ( location === navMenuArgs.theme_location ) {
				self.refreshMenuInstanceDebounced( instanceNumber );
				foundInstance = true;
			}
		} );
		if ( ! foundInstance ) {
			api.preview.send( 'refresh' );
		}
	};

	/**
	 * Update a specific instance of a given menu on the page.
	 *
	 * @param {int} instanceNumber
	 */
	self.refreshMenuInstance = function( instanceNumber ) {
		var self = this, data, menuId, customized, container, request, wpNavArgs, instance, containerInstanceClassName;

		if ( ! self.navMenuInstanceArgs[ instanceNumber ] ) {
			throw new Error( 'unknown_instance_number' );
		}
		instance = self.navMenuInstanceArgs[ instanceNumber ];

		containerInstanceClassName = 'partial-refreshable-nav-menu-' + String( instanceNumber );
		container = $( '.' + containerInstanceClassName );

		if ( _.isNumber( instance.menu ) ) {
			menuId = instance.menu;
		} else if ( instance.theme_location && api.has( 'nav_menu_locations[' + instance.theme_location + ']' ) ) {
			menuId = api( 'nav_menu_locations[' + instance.theme_location + ']' ).get();
		}

		if ( ! menuId || ! instance.can_partial_refresh || 0 === container.length ) {
			api.preview.send( 'refresh' );
			return;
		}
		menuId = parseInt( menuId, 10 );

		data = {
			nonce: self.previewCustomizeNonce, // for Customize Preview
			wp_customize: 'on'
		};
		if ( ! self.theme.active ) {
			data.theme = self.theme.stylesheet;
		}
		data[ self.renderQueryVar ] = '1';

		// Gather settings to send in partial refresh request.
		customized = {};
		api.each( function( setting, id ) {
			var value = setting.get(), shouldSend = false;
			// @todo Core should propagate the dirty state into the Preview as well so we can use that here.

			// Send setting if it is a nav_menu_locations[] setting.
			shouldSend = shouldSend || /^nav_menu_locations\[/.test( id );

			// Send setting if it is the setting for this menu.
			shouldSend = shouldSend || id === 'nav_menu[' + String( menuId ) + ']';

			// Send setting if it is one that is associated with this menu, or it is deleted.
			shouldSend = shouldSend || ( /^nav_menu_item\[/.test( id ) && ( false === value || menuId === value.nav_menu_term_id ) );

			if ( shouldSend ) {
				customized[ id ] = value;
			}
		} );
		data.customized = JSON.stringify( customized );
		data[ self.renderNoncePostKey ] = self.renderNonceValue;

		wpNavArgs = $.extend( {}, instance );
		data.wp_nav_menu_args_hash = wpNavArgs.args_hash;
		delete wpNavArgs.args_hash;
		data.wp_nav_menu_args = JSON.stringify( wpNavArgs );

		container.addClass( 'customize-partial-refreshing' );

		request = wp.ajax.send( null, {
			data: data,
			url: self.requestUri
		} );
		request.done( function( data ) {
			// If the menu is now not visible, refresh since the page layout may have changed.
			if ( false === data ) {
				api.preview.send( 'refresh' );
				return;
			}

			var eventParam, previousContainer = container;
			container = $( data );
			container.addClass( containerInstanceClassName );
			container.addClass( 'partial-refreshable-nav-menu customize-partial-refreshing' );
			previousContainer.replaceWith( container );
			eventParam = {
				instanceNumber: instanceNumber,
				wpNavArgs: wpNavArgs,
				oldContainer: previousContainer,
				newContainer: container
			};
			container.removeClass( 'customize-partial-refreshing' );
			$( document ).trigger( 'customize-preview-menu-refreshed', [ eventParam ] );
		} );
	};

	self.currentRefreshMenuInstanceDebouncedCalls = {};

	self.refreshMenuInstanceDebounced = function( instanceNumber ) {
		if ( self.currentRefreshMenuInstanceDebouncedCalls[ instanceNumber ] ) {
			clearTimeout( self.currentRefreshMenuInstanceDebouncedCalls[ instanceNumber ] );
		}
		self.currentRefreshMenuInstanceDebouncedCalls[ instanceNumber ] = setTimeout(
			function() {
				self.refreshMenuInstance( instanceNumber );
			},
			self.refreshDebounceDelay
		);
	};

	return self;

}( jQuery, wp.customize ) );
