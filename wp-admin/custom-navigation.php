<?php
/**
 * WordPress Administration for Navigation Menus
 * Interface functions
 *
 * @author Jeffikus <pearce.jp@gmail.com>
 * @version 1.1.0
 *
 * @package WordPress
 * @subpackage Administration
 */

require_once('admin.php');

if ( ! current_user_can('switch_themes') )
	wp_die( __( 'Cheatin&#8217; uh?' ));

wp_admin_css( 'custom-navigation' );
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'jquery-ui-droppable' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'jquery-ui-dialog' );
wp_enqueue_script( 'custom-navigation-dynamic-functions' );
wp_enqueue_script( 'custom-navigation-default-items' );
wp_enqueue_script( 'jquery-autocomplete' );
wp_enqueue_script( 'custom-navigation-php-functions' );

require_once('admin-header.php');
require_once (ABSPATH . WPINC . '/custom-navigation.php');

function wp_reset_nav_menu() {
	wp_custom_navigation_setup(true);

	return true;
}

$messagesdiv = '';
$menu_id_in_edit = 0;
$updated = false;

// Check which menu is selected and if menu is in edit already
if ( isset( $_GET['edit_menu'] ) ) {
	$menu_selected_id = (int) $_GET['edit_menu'];
	$updated = true;
} elseif ( isset( $_POST[ 'menu_id_in_edit' ] ) ) {
	$menu_selected_id = (int) $_POST[ 'menu_id_in_edit' ];
} else {
	$menu_selected_id = 0;
}

if ( isset( $_POST[ 'delete_menu' ] ) && $menu_selected_id > 0 ) {
	wp_delete_nav_menu( $menu_selected_id );
	$menu_selected_id = 0;
	$updated = true;
}

// Default Menu to show
$custom_menus = wp_get_nav_menus();
if ( ! $menu_selected_id && ! empty( $custom_menus ) )
	$menu_selected_id = $custom_menus[0]->term_id;

$menu_title = '';
if ( $menu_selected_id > 0 ) {
	foreach ( $custom_menus as $menu ) {
		if ( $menu->term_id == $menu_selected_id ) {
			$menu_title = $menu->name;
			break;
		}
	}
}

if ( isset( $_POST['set_wp_menu'] ) ) {
	// @todo validate set_wp_menu
	update_option( 'wp_custom_nav_menu', $_POST['enable_wp_menu'] );
	$updated = true;
	$messagesdiv = '<div id="message" class="updated fade below-h2"><p>' . __('Menu has been updated!') . '</p></div>';
}

if ( isset( $_POST['licount'] ) )
	$postCounter = $_POST['licount'];
else
	$postCounter = 0;

// Create a new menu. Menus are stored as terms in the 'menu' taxonomy.
if ( isset( $_POST['add_menu'] ) && ! $updated ) {
	$insert_menu_name = $_POST['add_menu_name'];

	if ( $insert_menu_name != '' ) {
		$menu = wp_create_nav_menu($insert_menu_name);
		if ( is_wp_error($menu) ) {
			$messagesdiv = '<div id="message" class="error fade below-h2"><p>' . esc_html( $menu->get_error_message() ) . '</p></div>';
		} else {
			$custom_menus[$menu->term_id] = $menu;
			$menu_selected_id = $menu->term_id;
			$menu_id_in_edit = $menu_selected_id;
			$messagesdiv = '<div id="message" class="updated fade below-h2"><p>' . esc_html( sprintf( __('"%s" menu has been created!'), $menu->name ) ) . '</p></div>';
			$postCounter = 0;
		}
	} else {
		$messagesdiv = '<div id="message" class="error fade below-h2"><p>' . __('Please enter a valid menu name.') . '</p></div>';
	}
	$updated = true;
}

if ( isset($_POST['reset_wp_menu']) && ! $updated ) {
	$success = wp_reset_nav_menu();
	if ( $success ) {
		// DISPLAY SUCCESS MESSAGE IF Menu Reset Correctly
		$messagesdiv = '<div id="message" class="updated fade below-h2"><p>' . __('The menu has been reset.') . '</p></div>';
		// GET reset menu id
		$custom_menus = array();
		$menu_selected_id = 0;
	} else {
		// DISPLAY SUCCESS MESSAGE IF Menu Reset Correctly
		$messagesdiv = '<div id="message" class="error fade below-h2"><p>' . __('The menu could not be reset. Please try again.') . '</p></div>';
	}
} elseif ( $postCounter > 0 && $menu_selected_id > 0 && ! $updated ) {
	$menu_items = wp_get_nav_menu_items( $menu_selected_id, array('orderby' => 'ID', 'output' => ARRAY_A, 'output_key' => 'ID') );
	$parent_menu_ids = array();

	// Loop through all POST variables
	for ( $k = 1; $k <= $postCounter; $k++ ) {
		if (isset($_POST['dbid'.$k])) { $db_id = $_POST['dbid'.$k]; } else { $db_id = 0; }
		if (isset($_POST['postmenu'.$k])) { $object_id = $_POST['postmenu'.$k]; } else { $object_id = 0; }
		if (isset($_POST['parent'.$k])) { $parent_id = $_POST['parent'.$k]; } else { $parent_id = 0; }
		if (isset($_POST['title'.$k])) { $custom_title = $_POST['title'.$k]; } else { $custom_title = ''; }
		if (isset($_POST['linkurl'.$k])) { $custom_linkurl = $_POST['linkurl'.$k]; } else { $custom_linkurl = ''; }
		if (isset($_POST['description'.$k])) { $custom_description = $_POST['description'.$k]; } else { $custom_description = ''; }
		// doesn't seem to be used by UI
		if (isset($_POST['icon'.$k])) { $icon = $_POST['icon'.$k]; } else { $icon = 0; }
		if (isset($_POST['position'.$k])) { $position = $_POST['position'.$k]; } else { $position = 0; }
		if (isset($_POST['linktype'.$k])) { $linktype = $_POST['linktype'.$k]; } else { $linktype = 'custom'; }
		if (isset($_POST['anchortitle'.$k])) { $custom_anchor_title = $_POST['anchortitle'.$k]; } else { $custom_anchor_title = $custom_title; }
		if (isset($_POST['newwindow'.$k])) { $new_window = $_POST['newwindow'.$k]; } else { $new_window = 0; }

		$post = array( 'post_status' => 'publish', 'post_type' => 'nav_menu_item', 'post_author' => $user_ID,
			'ping_status' => 0, 'post_parent' => 0, 'menu_order' => $position,
			'guid' => $custom_linkurl, 'post_excerpt' => $custom_anchor_title, 'tax_input' => array( 'nav_menu' => $menu_title ),
			'post_content' => $custom_description, 'post_title' => $custom_title );
		if ( $new_window )
			$post['post_content_filtered'] = '_blank';
		else
			$post['post_content_filtered'] = '';
		if ( $parent_id > 0 && isset( $parent_menu_ids[ $parent_id ] ) )
			$post[ 'post_parent' ] = $parent_menu_ids[ $parent_id ];

		// New menu item
		if ( $db_id == 0 ) {
			$db_id = wp_insert_post( $post );
		} elseif ( isset( $menu_items[$db_id] ) ) {
			$post['ID'] = $db_id;
			wp_update_post( $post );
			unset( $menu_items[$db_id] );
		}
		$parent_menu_ids[ $k ] = $db_id;

		update_post_meta($db_id, 'menu_type', $linktype);
		update_post_meta($db_id, 'object_id', $object_id);
	}
	if ( !empty( $menu_items ) ) {
		foreach ( array_keys( $menu_items ) as $menu_id ) {
			wp_delete_post( $menu_id );
		}
	}
	// DISPLAY SUCCESS MESSAGE IF POST CORRECT
	$messagesdiv = '<div id="message" class="updated fade below-h2"><p>' . __('The menu has been updated.') . '</p></div>';
}

?>
<div class="wrap">
<?php screen_icon(); ?>
<h2 class="maintitle"><?php esc_html_e('Menus') ?></h2>
	<div class="hide-if-js error"><p><?php _e('You do not have JavaScript enabled in your browser. Please enable it to access the Menus functionality.'); ?></p></div>
	<div class="hide-if-no-js">
	<div id="pages-left">
		<ul class="subsubsub">
<?php		if ( ! empty( $custom_menus ) ) {
				foreach ( $custom_menus as $menu ) {
					$sep = end($custom_menus) == $menu ? '' : ' | ';
					// $menu_term = get_term( $menu, 'nav_menu' );
					if ( ( $menu_id_in_edit == $menu->term_id ) || ( $menu_selected_id == $menu->term_id ) ) { ?>
						<li><?php echo esc_html( $menu->name ) . $sep; ?></li>
<?php				} else { ?>
			<li><a href='custom-navigation.php?edit_menu=<?php echo esc_attr($menu->term_id); ?>'><?php echo esc_html( $menu->name ); ?></a><?php echo $sep; ?></li>
<?php				}
				}
			} else { ?>
			<li><?php _e( 'Default' ); ?></li>
<?php		} ?>
		</ul>
		<div class="clear"></div>
		<div class="inside">
		<?php
			// CHECK if custom menu has been enabled
			$enabled_menu = get_option('wp_custom_nav_menu');
			$checked = strtolower($enabled_menu);

			if ( $checked != 'true' )
				echo '<div id="message-enabled" class="error fade below-h2"><p><strong>' . __('Menu editing has not been Enabled yet. Please enable it in order to use it -------->') . '</strong></p></div>';
		?>
		<?php echo $messagesdiv; ?>
		<form onsubmit="updatepostdata()" action="custom-navigation.php" method="post"  enctype="multipart/form-data">

		<input type="hidden" name="licount" id="licount" value="0" />
		<input type="hidden" name="menu_id_in_edit" id="menu_id_in_edit" value="<?php echo esc_attr($menu_selected_id); ?>" />

		<div class="sidebar-name">

			<div class="sidebar-name-arrow">
				<br/>
			</div>
			<h3><?php echo esc_html($menu_title); ?></h3>

		</div>

		<div id="nav-container">
			<ul id="custom-nav">

		<?php
		//DISPLAY existing menu
		if ( $menu_selected_id > 0 ) {
			// SET output type
			$output_type = "backend";
			// MAIN OUTPUT FUNCTION
			wp_custom_navigation_output( 'type='.$output_type.'&name='.$menu_title.'&id='.$menu_selected_id );
		}
		?>

			</ul>
		</div><!-- /#nav-container -->

		<p class="submit">

		<script type="text/javascript">
			updatepostdata();
		</script>
		<!-- <input id="delete_menu" name="delete_menu" type="submit" value="<?php esc_attr_e('Delete This Menu'); ?>" /> -->
		<input id="save_bottom" name="save_bottom" type="submit" value="<?php esc_attr_e('Save All Changes'); ?>" /></p>
		</div><!-- /.inside -->
	</div>

	<div id="menu-right">
		<div class="widgets-holder-wrap">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"></div>
				<h3><?php esc_html_e('Setup Menus'); ?></h3>
			</div>
			<div class="widget-holder">

				<?php
				// Setup custom menu
				$enabled_menu = get_option('wp_custom_nav_menu');
				$checked = strtolower($enabled_menu);
				?>

				<span >
					<label><?php _e('Enable'); ?></label><input type="radio" name="enable_wp_menu" value="true" <?php if ($checked=='true') { echo 'checked="checked"'; } ?> />
					<label><?php _e('Disable'); ?></label><input type="radio" name="enable_wp_menu" value="false" <?php if ($checked=='true') { } else { echo 'checked="checked"'; } ?> />
				</span><!-- /.checkboxes -->

				<input id="set_wp_menu" type="submit" value="<?php esc_attr_e('Set Menu'); ?>" name="set_wp_menu" class="button" /><br />

				<span>
					<label><?php _e('Reset Menu to Default'); ?></label>
					<input id="reset_wp_menu" type="submit" value="Reset" name="reset_wp_menu" class="button" onclick="return confirm('<?php _e('Are you sure you want to reset the menu to its default settings?'); ?>');" />
				</span>
				<br /><br />
				<span>
				<input id="add_menu_name" name="add_menu_name" type="text" value=""  />
				<input id="add_menu" type="submit" value="<?php esc_attr_e('Add Menu'); ?>" name="add_menu" class="button" />
				</span>
			</div>
		</div><!-- /.widgets-holder-wrap -->
		<?php $advanced_option_descriptions = get_option('wp_settings_custom_nav_advanced_options'); ?>
		<div class="widgets-holder-wrap" style="display:none;">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"></div>
				<h3><?php esc_html_e('Top Level Menu Descriptions'); ?></h3>
			</div>
			<div class="widget-holder">
				<span><?php _e('Display Descriptions in Top Level Menu?'); ?></span>

				<?php
				// UPDATE and DISPLAY Menu Description Option
				if ( isset($_POST['menu-descriptions']) ) {
					if ( !isset($_POST['switch_menu']) ) {
						$menu_options_to_edit = $_POST['menu_id_in_edit'];
						update_option('wp_settings_custom_nav_'.$menu_options_to_edit.'_descriptions',$_POST['menu-descriptions']);
					}
				}

				if ( $menu_id_in_edit > 0 )
					$checkedraw = get_option('wp_settings_custom_nav_'.$menu_id_in_edit.'_descriptions');
				else
					$checkedraw = get_option('wp_settings_custom_nav_'.$menu_selected_id.'_descriptions');

				$checked = strtolower($checkedraw);

				if ( $advanced_option_descriptions == 'no' )
					$checked = 'no';
				?>

				<span class="checkboxes">
					<label><?php _e('Yes'); ?></label><input type="radio" name="menu-descriptions" value="yes" <?php if ($checked=='yes') { echo 'checked="checked"'; } ?> />
					<label><?php _e('No'); ?></label><input type="radio" name="menu-descriptions" value="no" <?php if ($checked=='yes') { } else { echo 'checked="checked"'; } ?> />
				</span><!-- /.checkboxes -->
				</form>
				<div class="fix"></div>
			</div>
		</div><!-- /.widgets-holder-wrap -->

		<div class="widgets-holder-wrap">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"></div>
				<h3><?php esc_html_e('Add an Existing Page'); ?></h3>
			</div>
			<div class="widget-holder">

				<?php
				$pages_args = array(
				'child_of' => 0,
				'sort_order' => 'ASC',
				'sort_column' => 'post_title',
				'hierarchical' => 1,
				'exclude' => '',
				'include' => '',
				'meta_key' => '',
				'meta_value' => '',
				'authors' => '',
				'parent' => -1,
				'exclude_tree' => '',
				'number' => '',
				'offset' => 0 );

				//GET all pages
				$pages_array = get_pages($pages_args);
				$page_name = '';
				//CHECK if pages exist
				if ( $pages_array ) {
					foreach ( $pages_array as $post ) {
						//Add page name to
						$page_name .= htmlentities($post->post_title).'|';
					}
				} else {
					$page_name = __('No pages available');
				}
				?>

				<script>
					jQuery(document).ready(function(){

						//GET PHP pages
						var dataposts = "<?php echo esc_js($page_name); ?>".split("|");

						//Set autocomplete
						jQuery("#page-search").autocomplete(dataposts);

						//Handle autocomplete result
						jQuery("#page-search").result(function(event, data, formatted) {
							jQuery('#existing-pages').css('display','block');
							jQuery("#existing-pages dt:contains('" + data + "')").css("display", "block");

							jQuery('#show-pages').hide();
							jQuery('#hide-pages').show();

						});
						jQuery('#existing-pages').css('display','none');
					});
				</script>

				<input type="text" onfocus="jQuery('#page-search').attr('value','');" id="page-search" value="<?php esc_attr_e('Search Pages'); ?>" />

				<a id="show-pages" style="cursor:pointer;" onclick="jQuery('#existing-pages').css('display','block');jQuery('#page-search').attr('value','');jQuery('#existing-pages dt').css('display','block');jQuery('#show-pages').hide();jQuery('#hide-pages').show();">View All</a>
				<a id="hide-pages" style="cursor:pointer;" onclick="jQuery('#existing-pages').css('display','none');jQuery('#page-search').attr('value','Search Pages');jQuery('#existing-pages dt').css('display','none');jQuery('#show-pages').show();jQuery('#hide-pages').hide();">Hide All</a>

				<script type="text/javascript">
					jQuery('#hide-pages').hide();
				</script>

				<ul id="existing-pages" class="list">
					<?php
						$intCounter = 0;
						//Get default Pages
						$intCounter = wp_custom_nav_get_pages($intCounter,'default');
					?>
				</ul>

				<div class="fix"></div>

			</div>
		</div><!-- /.widgets-holder-wrap -->

		<div class="widgets-holder-wrap">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"></div>
				<h3><?php esc_html_e('Add an Existing Category'); ?></h3>
			</div>
			<div class="widget-holder">

				<?php
				// Custom GET categories query
				// @todo Use API
				$categories = $wpdb->get_results("SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = 'category' ORDER BY term_id ASC");
				$cat_name = '';
				//CHECK for results
				if ( $categories ) {
					foreach ( $categories as $category ) {
						$cat_id = $category->term_id;

						$cat_args = array(
							'orderby' => 'name',
							'include' => $cat_id,
							'hierarchical' => 1,
							'order' => 'ASC'
						);

						$category_names=get_categories($cat_args);

						if ( isset($category_names[0]->name) ) {
							// Add category name to data string
							$cat_name .= htmlentities($category_names[0]->name).'|';
						}
					}
				} else {
					$cat_name = __('No categories available');
				}
				?>

				<script>
					jQuery(document).ready(function(){

						//GET PHP categories
						var datacats = "<?php echo esc_js($cat_name); ?>".split("|");

						//Set autocomplete
						jQuery("#cat-search").autocomplete(datacats);

						//Handle autocomplete result
						jQuery("#cat-search").result(function(event, data, formatted) {
							jQuery('#existing-categories').css('display','block');
							jQuery("#existing-categories dt:contains('" + data + "')").css("display", "block");

							jQuery('#show-cats').hide();
							jQuery('#hide-cats').show();

						});
						jQuery('#existing-categories').css('display','none');

					});
				</script>

				<input type="text" onfocus="jQuery('#cat-search').attr('value','');" id="cat-search" value="<?php esc_attr_e('Search Categories'); ?>" />

				<a id="show-cats" style="cursor:pointer;" onclick="jQuery('#existing-categories').css('display','block');jQuery('#cat-search').attr('value','');jQuery('#existing-categories dt').css('display','block');jQuery('#show-cats').hide();jQuery('#hide-cats').show();">View All</a>
				<a id="hide-cats" style="cursor:pointer;" onclick="jQuery('#existing-categories').css('display','none');jQuery('#cat-search').attr('value','Search Categories');jQuery('#existing-categories dt').css('display','none');jQuery('#show-cats').show();jQuery('#hide-cats').hide();">Hide All</a>

				<script type="text/javascript">
					jQuery('#hide-cats').hide();
				</script>

				<ul id="existing-categories" class="list">
					<?php
						// Get default Categories
						$intCounter = wp_custom_nav_get_categories($intCounter, 'default');
					?>
				</ul>

				<div class="fix"></div>

			</div>
		</div><!-- /.widgets-holder-wrap -->

		<div class="widgets-holder-wrap">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"></div>
				<h3><?php esc_html_e('Add a Custom Url'); ?></h3>
			</div>
			<div class="widget-holder">
				<input id="custom_menu_item_url" type="text" value="http://"  />
				<label><?php _e('URL'); ?></label><br />
				<?php $templatedir = get_bloginfo('url'); ?>
				<input type="hidden" id="templatedir" value="<?php echo esc_attr($templatedir); ?>" />
				<input id="custom_menu_item_name" type="text" value="Menu Item" onfocus="jQuery('#custom_menu_item_name').attr('value','');"  />
				<label><?php _e('Menu Text'); ?></label><br />
				<input id="custom_menu_item_description" type="text" value="<?php esc_attr_e('A description'); ?>" <?php if ($advanced_option_descriptions == 'no') { ?>style="display:none;"<?php } ?> onfocus="jQuery('#custom_menu_item_description').attr('value','');" />
				<label <?php if ($advanced_option_descriptions == 'no') { ?>style="display:none;"<?php } ?> ><?php _e('Description'); ?></label>
				<a class="addtomenu" onclick="appendToList('<?php echo $templatedir; ?>','Custom','','','','0','');jQuery('#custom_menu_item_name').attr('value','Menu Item');jQuery('#custom_menu_item_description').attr('value','A description');"><?php _e('Add to menu'); ?></a>
				<div class="fix"></div>
			</div>
		</div><!-- /.widgets-holder-wrap -->
	</div><!-- /.hide-if-no-js -->
	</div>
</div>

<div id="dialog-confirm" style="display:none;" title="<?php esc_attr_e('Edit Menu Item'); ?>">
	</label><input id="edittitle" type="text" name="edittitle" value="" /><label class="editlabel" for="edittitle"><?php _e('Menu Title'); ?></label><br />
	<input id="editlink" type="text" name="editlink" value="" /><label class="editlabel" for="editlink"><?php _e('URL'); ?></label><br />
	<input id="editanchortitle" type="text" name="editanchortitle" value="" /><label class="editlabel" for="editanchortitle"><?php _e('Link Title'); ?></label><br />
	<select id="editnewwindow" name="editnewwindow">
		<option value="1"><?php _e('Yes'); ?></option>
		<option value="0"><?php _e('No'); ?></option>
	</select><label class="editlabel" for="editnewwindow"><?php _e('Open Link in a new window'); ?></label>
	<input id="editdescription" type="text" name="editdescription" value="" <?php if ($advanced_option_descriptions == 'no') { ?>style="display:none;"<?php } ?> /><label class="editlabel" for="editdescription" <?php if ($advanced_option_descriptions == 'no') { ?>style="display:none;"<?php } ?> ><?php _e('Description'); ?></label><br />
</div>

<?php

include("admin-footer.php");
