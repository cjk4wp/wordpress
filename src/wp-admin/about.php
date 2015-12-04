<?php
/**
 * About This Version administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

wp_enqueue_style( 'wp-mediaelement' );
wp_enqueue_script( 'wp-mediaelement' );
wp_localize_script( 'mediaelement', '_wpmejsSettings', array(
	'pluginPath' => includes_url( 'js/mediaelement/', 'relative' ),
	'pauseOtherPlayers' => ''
) );

if ( current_user_can( 'install_plugins' ) ) {
	add_thickbox();
	wp_enqueue_script( 'plugin-install' );
}


wp_oembed_add_host_js();

$title = __( 'About' );

list( $display_version ) = explode( '-', $wp_version );

include( ABSPATH . 'wp-admin/admin-header.php' );
?>
	<div class="wrap about-wrap">
		<h1><?php printf( __( 'Welcome to WordPress&nbsp;%s' ), $display_version ); ?></h1>

		<div class="about-text"><?php printf( __( 'Thank you for updating! WordPress %s makes it even easier to format your content and customize your site.' ), $display_version ); ?></div>
		<div class="wp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

		<h2 class="nav-tab-wrapper">
			<a href="about.php" class="nav-tab nav-tab-active"><?php _e( 'What&#8217;s New' ); ?></a>
			<a href="credits.php" class="nav-tab"><?php _e( 'Credits' ); ?></a>
			<a href="freedoms.php" class="nav-tab"><?php _e( 'Freedoms' ); ?></a>
		</h2>

		<div class="headline-feature feature-section one-col">
			<h2><?php echo ( 'Twenty Sixteen' ); ?></h2>
			<div class="media-container">
				<img src="https://cldup.com/K6k4JfS2nW.png" alt="" />
			</div>
			<div class="two-col">
				<div class="col">
					<h3><?php echo ( 'Introducing Twenty Sixteen' ); ?></h3>
					<p><?php echo ( 'Our newest default theme, Twenty Sixteen, is a modern take on a classic blog design.' ); ?></p>
					<p><?php echo ( 'Twenty Sixteen was built with a mobile-first approach, ensuring your site will look great on any device. A harmonious fluid grid, fun color schemes, flexible mast head, and more make this a great addition to the default theme family.' ); ?></p>
					<div class="horizontal-image">
						<div class="content">
							<img class="feature-image horizontal-screen" src="https://cldup.com/J-zxmMqkXs.png" alt="" />
						</div>
					</div>
				</div>
				<div class="col feature-image">
					<img class="vertical-screen" src="https://cldup.com/M8i_6X5LUL.png" alt="" />
				</div>
			</div>
		</div>

		<hr />

		<div class="feature-section two-col">
			<div class="col">
				<div class="media-container">
					<img src="https://cldup.com/av6MH44-Au.png" alt="" />
				</div>
			</div>
			<div class="col">
				<h3><?php echo ( 'Responsive images' ); ?></h3>
				<p><?php echo ( 'WordPress now takes a smarter approach to displaying device-appropriate image sizes, ensuring a perfect fit every time. You don&#8217;t need to do anything to your theme, it just works.' ); ?></p>
			</div>
		</div>

		<hr />

		<div class="feature-section two-col">
			<div class="col">
				<div class="embed-container">
					<?php echo wp_oembed_get( 'https://make.wordpress.org/core/2015/10/28/new-embeds-feature-in-wordpress-4-4/' ); ?>
				</div>
				<h3><?php echo ( 'Embeddable WordPress content' ); ?></h3>
				<p><?php echo ( 'Now you can embed WordPress content inside of other WordPress content. Simply drop a post URL into the editor and see an instant embed preview, complete with title, excerpt, comment and sharing links, even your site icon.' ); ?></p>
			</div>
			<div class="col">
				<div class="embed-container">
					<?php echo wp_oembed_get( '' ); ?>
				</div>
				<h3><?php echo ( 'More third-party embeds' ); ?></h3>
				<p><?php echo ( 'If the addition of post embeds weren’t enough, WordPress 4.4 also adds support for five new oEmbed providers: Cloudup, ReverbNation, Reddit&nbsp;Comments, VideoPress, and Speaker&nbsp;Deck.' ); ?></p>
			</div>
		</div>

		<hr />

		<div class="changelog">
			<h3><?php _e( 'Under the Hood' ); ?></h3>

			<div class="feature-section under-the-hood one-col">
				<div class="col">
					<h4><?php echo ( 'REST API infrastructure' ); ?></h4>
					<div class="two-col-text">
						<p><?php echo ( 'WordPress 4.4 marks the beginning of a new era with integration of infrastructure for the new REST API. The REST API serves to provide developers with a robust path forward for building and extending RESTful APIs on top of WordPress.' ); ?></p>
						<p><?php
							if ( current_user_can( 'install_plugins' ) ) {
								$url_args = array(
									'tab'       => 'plugin-information',
									'plugin'    => 'rest-api',
									'TB_iframe' => true,
									'width'     => 600,
									'height'    => 550
								);

								$plugin_link = '<a href="' . esc_url( add_query_arg( $url_args, network_admin_url( 'plugin-install.php' ) ) ) . '" class="thickbox">WordPress REST API</a>';
							} else {
								$plugin_link = '<a href="https://wordpress.org/plugins/rest-api">WordPress REST API</a>';
							}

							/* translators: WordPress REST API plugin link */
							printf( ( 'Infrastructure is the first part of a multi-stage rollout for the REST API, which also targets inclusion of core endpoints in an upcoming release. To get a sneak peek of the core endpoints, and for more on extending the REST API, check out the official %s plugin.' ), $plugin_link );
						?></p>
					</div>
				</div>
			</div>

			<div class="feature-section under-the-hood three-col">
				<div class="col">
					<h4><?php echo ( 'Term meta' ); ?></h4>
					<p><?php
						/* translators: 1: add_term_meta() docs link, 2: get_term_meta() docs link, 3: update_term_meta() docs link */
						printf( ( 'Terms now support metadata, just like posts. See %1$s, %2$s, and %3$s for more information.' ),
							'<a href="https://developer.wordpress.org/reference/functions/add_term_meta"><code>add_term_meta()</code></a>',
							'<a href="https://developer.wordpress.org/reference/functions/get_term_meta"><code>get_term_meta()</code></a>',
							'<a href="https://developer.wordpress.org/reference/functions/update_term_meta"><code>update_term_meta()</code></a>'
				         );
					?></p>
				</div>
				<div class="col">
					<h4><?php echo ( 'Comment query improvements' ); ?></h4>
					<p><?php
						/* translators: WP_Comment_Query class name */
						printf( ( 'Comment queries now have cache handling to improve performance. New arguments in %s make crafting robust comment queries simpler.' ), '<code>WP_Comment_Query</code>' );
					?></p>
				</div>
				<div class="col">
					<h4><?php echo ( 'Term, comment, and network objects' ); ?></h4>
					<p><?php
						/* translators: 1: WP_Term class name, WP_Comment class name, WP_Network class name */
						printf( ( 'New %1$s, %2$s, and %3$s objects make interacting with terms, comments, and networks more predictable and intuitive in code.' ),
							'<code>WP_Term</code>',
							'<code>WP_Comment</code>',
							'<code>WP_Network</code>'
						);
					?></p>
				</div>
			</div>

			<div class="return-to-dashboard">
				<?php if ( current_user_can( 'update_core' ) && isset( $_GET['updated'] ) ) : ?>
					<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>">
						<?php is_multisite() ? _e( 'Return to Updates' ) : _e( 'Return to Dashboard &rarr; Updates' ); ?>
					</a> |
				<?php endif; ?>
				<a href="<?php echo esc_url( self_admin_url() ); ?>"><?php is_blog_admin() ? _e( 'Go to Dashboard &rarr; Home' ) : _e( 'Go to Dashboard' ); ?></a>
			</div>

		</div>
	</div>
<?php

include( ABSPATH . 'wp-admin/admin-footer.php' );

// These are strings we may use to describe maintenance/security releases, where we aim for no new strings.
return;

__( 'Maintenance Release' );
__( 'Maintenance Releases' );

__( 'Security Release' );
__( 'Security Releases' );

__( 'Maintenance and Security Release' );
__( 'Maintenance and Security Releases' );

/* translators: %s: WordPress version number */
__( '<strong>Version %s</strong> addressed one security issue.' );
/* translators: %s: WordPress version number */
__( '<strong>Version %s</strong> addressed some security issues.' );

/* translators: 1: WordPress version number, 2: plural number of bugs. */
_n_noop( '<strong>Version %1$s</strong> addressed %2$s bug.',
         '<strong>Version %1$s</strong> addressed %2$s bugs.' );

/* translators: 1: WordPress version number, 2: plural number of bugs. Singular security issue. */
_n_noop( '<strong>Version %1$s</strong> addressed a security issue and fixed %2$s bug.',
         '<strong>Version %1$s</strong> addressed a security issue and fixed %2$s bugs.' );

/* translators: 1: WordPress version number, 2: plural number of bugs. More than one security issue. */
_n_noop( '<strong>Version %1$s</strong> addressed some security issues and fixed %2$s bug.',
         '<strong>Version %1$s</strong> addressed some security issues and fixed %2$s bugs.' );

/* translators: %s: Codex URL */
__( 'For more information, see <a href="%s">the release notes</a>.' );
