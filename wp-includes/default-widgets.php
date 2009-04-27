<?php

/**
 * Default Widgets
 *
 * @package WordPress
 * @subpackage Widgets
 */

/**
 * Pages widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Pages extends WP_Widget {

	function WP_Widget_Pages() {
		$widget_ops = array('classname' => 'widget_pages', 'description' => __( "Your blog's WordPress Pages") );
		$this->WP_Widget('pages', __('Pages'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = empty( $instance['title'] ) ? __( 'Pages' ) : apply_filters('widget_title', $instance['title']);
		$sortby = empty( $instance['sortby'] ) ? 'menu_order' : $instance['sortby'];
		$exclude = empty( $instance['exclude'] ) ? '' : $instance['exclude'];

		if ( $sortby == 'menu_order' )
			$sortby = 'menu_order, post_title';

		$out = wp_list_pages( array('title_li' => '', 'echo' => 0, 'sort_column' => $sortby, 'exclude' => $exclude) );

		if ( !empty( $out ) ) {
			echo $before_widget;
			echo $before_title . $title . $after_title;
		?>
		<ul>
			<?php echo $out; ?>
		</ul>
		<?php
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( in_array( $new_instance['sortby'], array( 'post_title', 'menu_order', 'ID' ) ) ) {
			$instance['sortby'] = $new_instance['sortby'];
		} else {
			$instance['sortby'] = 'menu_order';
		}

		$instance['exclude'] = strip_tags( $new_instance['exclude'] );

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'sortby' => 'post_title', 'title' => '', 'exclude' => '') );
		$title = attribute_escape( $instance['title'] );
		$exclude = attribute_escape( $instance['exclude'] );
	?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
		<p>
			<label for="<?php echo $this->get_field_id('sortby'); ?>"><?php _e( 'Sort by:' ); ?>
				<select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>" class="widefat">
					<option value="post_title"<?php selected( $instance['sortby'], 'post_title' ); ?>><?php _e('Page title'); ?></option>
					<option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>><?php _e('Page order'); ?></option>
					<option value="ID"<?php selected( $instance['sortby'], 'ID' ); ?>><?php _e( 'Page ID' ); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php _e( 'Exclude:' ); ?> <input type="text" value="<?php echo $exclude; ?>" name="<?php echo $this->get_field_name('exclude'); ?>" id="<?php echo $this->get_field_id('exclude'); ?>" class="widefat" /></label>
			<br />
			<small><?php _e( 'Page IDs, separated by commas.' ); ?></small>
		</p>
<?php
	}

}

/**
 * Links widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Links extends WP_Widget {

	function WP_Widget_Links() {
		$widget_ops = array('description' => __( "Your blogroll" ) );
		$this->WP_Widget('links', __('Links'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args, EXTR_SKIP);

		$show_description = isset($instance['description']) ? $instance['description'] : false;
		$show_name = isset($instance['name']) ? $instance['name'] : false;
		$show_rating = isset($instance['rating']) ? $instance['rating'] : false;
		$show_images = isset($instance['images']) ? $instance['images'] : true;
		$category = isset($instance['category']) ? $instance['category'] : false;

		if ( is_admin() && !$category ) {
			// Display All Links widget as such in the widgets screen
			echo $before_widget . $before_title. __('All Links') . $after_title . $after_widget;
			return;
		}

		$before_widget = preg_replace('/id="[^"]*"/','id="%id"', $before_widget);
		wp_list_bookmarks(apply_filters('widget_links_args', array(
			'title_before' => $before_title, 'title_after' => $after_title,
			'category_before' => $before_widget, 'category_after' => $after_widget,
			'show_images' => $show_images, 'show_description' => $show_description,
			'show_name' => $show_name, 'show_rating' => $show_rating,
			'category' => $category, 'class' => 'linkcat widget'
		)));
	}

	function update( $new_instance, $old_instance ) {
		$new_instance = (array) $new_instance;
		$instance = array( 'images' => 0, 'name' => 0, 'description' => 0, 'rating' => 0);
		foreach ( $instance as $field => $val ) {
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		}
		$instance['category'] = intval($new_instance['category']);

		return $instance;
	}

	function form( $instance ) {

		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'images' => true, 'name' => true, 'description' => false, 'rating' => false, 'category' => false ) );
		$link_cats = get_terms( 'link_category');
?>
		<p>
		<label for="<?php echo $this->get_field_id('category'); ?>">
		<select class="widefat" id="<?php echo $this->get_field_id('category'); ?>" name="<?php echo $this->get_field_name('category'); ?>">
		<option value=""><?php _e('All Links'); ?></option>
		<?php
		foreach ( $link_cats as $link_cat ) {
			echo '<option value="' . intval($link_cat->term_id) . '"'
				. ( $link_cat->term_id == $instance['category'] ? ' selected="selected"' : '' )
				. '>' . $link_cat->name . "</option>\n";
		}
		?>
		</select></label></p>
		<p>
		<label for="<?php echo $this->get_field_id('images'); ?>">
		<input class="checkbox" type="checkbox" <?php checked($instance['images'], true) ?> id="<?php echo $this->get_field_id('images'); ?>" name="<?php echo $this->get_field_name('images'); ?>" /> <?php _e('Show Link Image'); ?></label><br />
		<label for="<?php echo $this->get_field_id('name'); ?>">
		<input class="checkbox" type="checkbox" <?php checked($instance['name'], true) ?> id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" /> <?php _e('Show Link Name'); ?></label><br />
		<label for="<?php echo $this->get_field_id('description'); ?>">
		<input class="checkbox" type="checkbox" <?php checked($instance['description'], true) ?> id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>" /> <?php _e('Show Link Description'); ?></label><br />
		<label for="<?php echo $this->get_field_id('rating'); ?>">
		<input class="checkbox" type="checkbox" <?php checked($instance['rating'], true) ?> id="<?php echo $this->get_field_id('rating'); ?>" name="<?php echo $this->get_field_name('rating'); ?>" /> <?php _e('Show Link Rating'); ?></label>
		</p>
<?php
	}
}

/**
 * Search widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Search extends WP_Widget {

	function WP_Widget_Search() {
		$widget_ops = array('classname' => 'widget_search', 'description' => __( "A search form for your blog") );
		$this->WP_Widget('search', __('Search'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		echo $before_widget;

		// Use current theme search form if it exists
		get_search_form();

		echo $after_widget;
	}
}

/**
 * Archives widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Archives extends WP_Widget {

	function WP_Widget_Archives() {
		$widget_ops = array('classname' => 'widget_archive', 'description' => __( "A monthly archive of your blog's posts") );
		$this->WP_Widget('archives', __('Archives'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$c = $instance['count'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';
		$title = empty($instance['title']) ? __('Archives') : apply_filters('widget_title', $instance['title']);

		echo $before_widget;
		echo $before_title . $title . $after_title;

		if ( $d ) {
?>
		<select name="archive-dropdown" onchange='document.location.href=this.options[this.selectedIndex].value;'> <option value=""><?php echo attribute_escape(__('Select Month')); ?></option> <?php wp_get_archives("type=monthly&format=option&show_post_count=$c"); ?> </select>
<?php
		} else {
?>
		<ul>
		<?php wp_get_archives("type=monthly&show_post_count=$c"); ?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => 0, 'dropdown' => '') );
		$title = strip_tags($instance['title']);
		$count = $instance['count'] ? 'checked="checked"' : '';
		$dropdown = $instance['dropdown'] ? 'checked="checked"' : '';
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>"><input class="checkbox" type="checkbox" <?php echo $count; ?> id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" /> <?php _e('Show post counts'); ?></label>
			<br />
			<label for="<?php echo $this->get_field_id('dropdown'); ?>"><input class="checkbox" type="checkbox" <?php echo $dropdown; ?> id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>" /> <?php _e('Display as a drop down'); ?></label>
		</p>
<?php
	}
}

/**
 * Meta widget class
 *
 * Displays log in/out, RSS feed links, etc.
 *
 * @since 2.8.0
 */
class WP_Widget_Meta extends WP_Widget {

	function WP_Widget_Meta() {
		$widget_ops = array('classname' => 'widget_meta', 'description' => __( "Log in/out, admin, feed and WordPress links") );
		$this->WP_Widget('meta', __('Meta'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = empty($instance['title']) ? __('Meta') : apply_filters('widget_title', $instance['title']);

		echo $before_widget;
		echo $before_title . $title . $after_title;
?>
			<ul>
			<?php wp_register(); ?>
			<li><?php wp_loginout(); ?></li>
			<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php echo attribute_escape(__('Syndicate this site using RSS 2.0')); ?>"><?php _e('Entries <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php echo attribute_escape(__('The latest comments to all posts in RSS')); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>
			<li><a href="http://wordpress.org/" title="<?php echo attribute_escape(__('Powered by WordPress, state-of-the-art semantic personal publishing platform.')); ?>">WordPress.org</a></li>
			<?php wp_meta(); ?>
			</ul>
<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
<?php
	}
}

/**
 * Calendar widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Calendar extends WP_Widget {

	function WP_Widget_Calendar() {
		$widget_ops = array('classname' => 'widget_calendar', 'description' => __( "A calendar of your blog's posts") );
		$this->WP_Widget('calendar', __('Calendar'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
		echo $before_widget . $before_title . $title . $after_title;
		echo '<div id="calendar_wrap">';
		get_calendar();
		echo '</div>';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		</label></p>
<?php
	}
}

/**
 * Text widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Text extends WP_Widget {

	function WP_Widget_Text() {
		$widget_ops = array('classname' => 'widget_text', 'description' => __('Arbitrary text or HTML'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('text', __('Text'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$text = apply_filters( 'widget_text', $instance['text'] );
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
			<div class="textwidget"><?php echo $instance['filter'] ? wpautop($text) : $text; ?></div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = wp_filter_post_kses( $new_instance['text'] );
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = strip_tags($instance['title']);
		$text = format_to_edit($instance['text']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
		</label></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><label for="<?php echo $this->get_field_id('filter'); ?>">
		<input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked($instance['filter']); ?> />&nbsp;<?php _e('Automatically add paragraphs.') ?>
		</label></p>
<?php
	}
}

/**
 * Categories widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Categories extends WP_Widget {

	function WP_Widget_Categories() {
		$widget_ops = array( 'classname' => 'widget_categories', 'description' => __( "A list or dropdown of categories" ) );
		$this->WP_Widget('categories', __('Categories'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = empty( $instance['title'] ) ? __( 'Categories' ) : apply_filters('widget_title', $instance['title']);
		$c = $instance['count'] ? '1' : '0';
		$h = $instance['hierarchical'] ? '1' : '0';
		$d = $instance['dropdown'] ? '1' : '0';

		echo $before_widget;
		echo $before_title . $title . $after_title;

		$cat_args = array('orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h);

		if ( $d ) {
			$cat_args['show_option_none'] = __('Select Category');
			wp_dropdown_categories($cat_args);
?>

<script type='text/javascript'>
/* <![CDATA[ */
	var dropdown = document.getElementById("cat");
	function onCatChange() {
		if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo get_option('home'); ?>/?cat="+dropdown.options[dropdown.selectedIndex].value;
		}
	}
	dropdown.onchange = onCatChange;
/* ]]> */
</script>

<?php
		} else {
?>
		<ul>
<?php
		$cat_args['title_li'] = '';
		wp_list_categories($cat_args);
?>
		</ul>
<?php
		}

		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = $new_instance['count'] ? 1 : 0;
		$instance['hierarchical'] = $new_instance['hierarchical'] ? 1 : 0;
		$instance['dropdown'] = $new_instance['dropdown'] ? 1 : 0;

		return $instance;
	}

	function form( $instance ) {
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = attribute_escape( $instance['title'] );
		$count = (bool) $instance['count'];
		$hierarchical = (bool) $instance['hierarchical'];
		$dropdown = (bool) $instance['dropdown'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e( 'Title:' ); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</label></p>

		<p><label for="<?php echo $this->get_field_id('dropdown'); ?>">
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('dropdown'); ?>" name="<?php echo $this->get_field_name('dropdown'); ?>"<?php checked( $dropdown ); ?> />
		<?php _e( 'Show as dropdown' ); ?>
		</label><br />

		<label for="<?php echo $this->get_field_id('count'); ?>">
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>"<?php checked( $count ); ?> />
		<?php _e( 'Show post counts' ); ?>
		</label><br />

		<label for="<?php echo $this->get_field_id('hierarchical'); ?>">
		<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('hierarchical'); ?>" name="<?php echo $this->get_field_name('hierarchical'); ?>"<?php checked( $hierarchical ); ?> />
		<?php _e( 'Show hierarchy' ); ?>
		</label></p>
<?php
	}

}

/**
 * Recent_Posts widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Recent_Posts extends WP_Widget {

	function WP_Widget_Recent_Posts() {
		$widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent posts on your blog") );
		$this->WP_Widget('recent-posts', __('Recent Posts'), $widget_ops);
		$this->alt_option_name = 'widget_recent_entries';

		add_action( 'save_post', array(&$this, 'flush_widget_cache') );
		add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
		add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	}

	function widget($args, $instance) {
		$cache = wp_cache_get('widget_recent_posts', 'widget');

		if ( !is_array($cache) )
			$cache = array();

		if ( isset($cache[$args['widget_id']]) )
			return $cache[$args['widget_id']];

		ob_start();
		extract($args);

		$title = empty($instance['title']) ? __('Recent Posts') : apply_filters('widget_title', $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		$r = new WP_Query(array('showposts' => $number, 'what_to_show' => 'posts', 'nopaging' => 0, 'post_status' => 'publish', 'caller_get_posts' => 1));
		if ($r->have_posts()) :
?>
		<?php echo $before_widget; ?>
		<?php echo $before_title . $title . $after_title; ?>
		<ul>
		<?php  while ($r->have_posts()) : $r->the_post(); ?>
		<li><a href="<?php the_permalink() ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?> </a></li>
		<?php endwhile; ?>
		</ul>
		<?php echo $after_widget; ?>
<?php
			wp_reset_query();  // Restore global post data stomped by the_post().
		endif;

		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('widget_recent_posts', $cache, 'widget');
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_entries']) )
			delete_option('widget_recent_entries');

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete('widget_recent_posts', 'widget');
	}

	function form( $instance ) {
		$title = attribute_escape($instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>">
		<?php _e('Number of posts to show:'); ?>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" /></label>
		<br /><small><?php _e('(at most 15)'); ?></small></p>
<?php
	}
}

/**
 * Recent_Comments widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Recent_Comments extends WP_Widget {

	function WP_Widget_Recent_Comments() {
		$widget_ops = array('classname' => 'widget_recent_comments', 'description' => __( 'The most recent comments' ) );
		$this->WP_Widget('recent-comments', __('Recent Comments'), $widget_ops);
		$this->alt_option_name = 'widget_recent_comments';

		if ( is_active_widget(false, false, $this->id_base) )
			add_action( 'wp_head', array(&$this, 'recent_comments_style') );

		add_action( 'comment_post', array(&$this, 'flush_widget_cache') );
		add_action( 'wp_set_comment_status', array(&$this, 'flush_widget_cache') );
	}

	function recent_comments_style() { ?>
	<style type="text/css">.recentcomments a{display:inline !important;padding:0 !important;margin:0 !important;}</style>
<?php
	}

	function flush_widget_cache() {
		wp_cache_delete('recent_comments', 'widget');
	}

	function widget( $args, $instance ) {
		global $wpdb, $comments, $comment;

		extract($args, EXTR_SKIP);
		$title = empty($instance['title']) ? __('Recent Comments') : apply_filters('widget_title', $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 5;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;

		if ( !$comments = wp_cache_get( 'recent_comments', 'widget' ) ) {
			$comments = $wpdb->get_results("SELECT * FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT 15");
			wp_cache_add( 'recent_comments', $comments, 'widget' );
		}

		$comments = array_slice( (array) $comments, 0, $number );
?>
		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul id="recentcomments"><?php
			if ( $comments ) : foreach ( (array) $comments as $comment) :
			echo  '<li class="recentcomments">' . /* translators: comments widget: 1: comment author, 2: post link */ sprintf(_x('%1$s on %2$s', 'widgets'), get_comment_author_link(), '<a href="' . clean_url( get_comment_link($comment->comment_ID) ) . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			endforeach; endif;?></ul>
		<?php echo $after_widget; ?>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['widget_recent_comments']) )
			delete_option('widget_recent_comments');

		return $instance;
	}

	function form( $instance ) {
		$title = attribute_escape($instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 5;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">
		<?php _e('Title:'); ?>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('number'); ?>">
		<?php _e('Number of comments to show:'); ?>
		<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" /></label>
		<br /><small><?php _e('(at most 15)'); ?></small></p>
<?php
	}
}

/**
 * RSS widget class
 *
 * @since 2.8.0
 */
class WP_Widget_RSS extends WP_Widget {

	function WP_Widget_RSS() {
		$widget_ops = array( 'description' => __('Entries from any RSS or Atom feed') );
		$control_ops = array( 'width' => 400, 'height' => 200 );
		$this->WP_Widget( 'rss', __('RSS'), $widget_ops, $control_ops );
	}

	function widget($args, $instance) {

		if ( isset($instance['error']) && $instance['error'] )
			return;

		extract($args, EXTR_SKIP);

		$url = $instance['url'];
		while ( stristr($url, 'http') != $url )
			$url = substr($url, 1);

		if ( empty($url) )
			return;

		$rss = fetch_feed($url);
		$title = $instance['title'];
		$desc = '';
		$link = '';

		if ( ! is_wp_error($rss) ) {
			$desc = attribute_escape(strip_tags(@html_entity_decode($rss->get_description(), ENT_QUOTES, get_option('blog_charset'))));
			if ( empty($title) )
				$title = htmlentities(strip_tags($rss->get_title()));
			$link = clean_url(strip_tags($rss->get_permalink()));
			while ( stristr($link, 'http') != $link )
				$link = substr($link, 1);
		}

		if ( empty($title) )
			$title = empty($desc) ? __('Unknown Feed') : $desc;

		$title = apply_filters('widget_title', $title );
		$url = clean_url(strip_tags($url));
		$icon = includes_url('images/rss.png');
		$title = "<a class='rsswidget' href='$url' title='" . attribute_escape(__('Syndicate this content')) ."'><img style='background:orange;color:white;border:none;' width='14' height='14' src='$icon' alt='RSS' /></a> <a class='rsswidget' href='$link' title='$desc'>$title</a>";

		echo $before_widget;
		echo $before_title . $title . $after_title;
		wp_widget_rss_output( $rss, $instance );
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$testurl = $new_instance['url'] != $old_instance['url'];
		return wp_widget_rss_process( $new_instance, $testurl );
	}

	function form($instance) {

		if ( empty($instance) )
			$instance = array( 'number' => '__i__', 'title' => '', 'url' => '', 'items' => 10, 'error' => false, 'show_summary' => 0, 'show_author' => 0, 'show_date' => 0 );
		else
			$instance['number'] = $this->number;

		wp_widget_rss_form( $instance );
	}
}

/**
 * Display the RSS entries in a list.
 *
 * @since 2.5.0
 *
 * @param string|array|object $rss RSS url.
 * @param array $args Widget arguments.
 */
function wp_widget_rss_output( $rss, $args = array() ) {
	if ( is_string( $rss ) ) {
		$rss = fetch_feed($rss);
	} elseif ( is_array($rss) && isset($rss['url']) ) {
		$args = $rss;
		$rss = fetch_feed($rss['url']);
	} elseif ( !is_object($rss) ) {
		return;
	}

	if ( is_wp_error($rss) ) {
		if ( is_admin() || current_user_can('manage_options') )
			echo '<p>' . sprintf( __('<strong>RSS Error</strong>: %s'), $rss->get_error_message() ) . '</p>';

		return;
	}

	$default_args = array( 'show_author' => 0, 'show_date' => 0, 'show_summary' => 0 );
	$args = wp_parse_args( $args, $default_args );
	extract( $args, EXTR_SKIP );

	$items = (int) $items;
	if ( $items < 1 || 20 < $items )
		$items = 10;
	$show_summary  = (int) $show_summary;
	$show_author   = (int) $show_author;
	$show_date     = (int) $show_date;

	if ( !$rss->get_item_quantity() ) {
		echo '<ul><li>' . __( 'An error has occurred; the feed is probably down. Try again later.' ) . '</li></ul>';
		return;
	}

	echo '<ul>';
	foreach ( $rss->get_items(0, $items) as $item ) {
		$link = $item->get_link();
		while ( stristr($link, 'http') != $link )
			$link = substr($link, 1);
		$link = clean_url(strip_tags($link));
		$title = attribute_escape(strip_tags($item->get_title()));
		if ( empty($title) )
			$title = __('Untitled');

		$desc = str_replace(array("\n", "\r"), ' ', attribute_escape(strip_tags(@html_entity_decode($item->get_description(), ENT_QUOTES, get_option('blog_charset')))));
		$desc = wp_html_excerpt( $desc, 360 ) . ' [&hellip;]';
		$desc = wp_specialchars( $desc );

		if ( $show_summary ) {
			$summary = "<div class='rssSummary'>$desc</div>";
		} else {
			$summary = '';
		}

		$date = '';
		if ( $show_date ) {
			$date = $item->get_date();

			if ( $date ) {
				if ( $date_stamp = strtotime( $date ) )
					$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
				else
					$date = '';
			}
		}

		$author = '';
		if ( $show_author ) {
			$author = $item->get_author();
			$author = $author->get_name();
			$author = ' <cite>' . wp_specialchars( strip_tags( $author ) ) . '</cite>';
		}

		if ( $link == '' ) {
			echo "<li>$title{$date}{$summary}{$author}</li>";
		} else {
			echo "<li><a class='rsswidget' href='$link' title='$desc'>$title</a>{$date}{$summary}{$author}</li>";
		}
	}
	echo '</ul>';
}



/**
 * Display RSS widget options form.
 *
 * The options for what fields are displayed for the RSS form are all booleans
 * and are as follows: 'url', 'title', 'items', 'show_summary', 'show_author',
 * 'show_date'.
 *
 * @since 2.5.0
 *
 * @param array|string $args Values for input fields.
 * @param array $inputs Override default display options.
 */
function wp_widget_rss_form( $args, $inputs = null ) {

	$default_inputs = array( 'url' => true, 'title' => true, 'items' => true, 'show_summary' => true, 'show_author' => true, 'show_date' => true );
	$inputs = wp_parse_args( $inputs, $default_inputs );
	extract( $args );
	extract( $inputs, EXTR_SKIP);

	$number = attribute_escape( $number );
	$title  = attribute_escape( $title );
	$url    = attribute_escape( $url );
	$items  = (int) $items;
	if ( $items < 1 || 20 < $items )
		$items  = 10;
	$show_summary   = (int) $show_summary;
	$show_author    = (int) $show_author;
	$show_date      = (int) $show_date;

	if ( !empty($error) )
		echo '<p class="widget-error"><strong>' . sprintf( __('RSS Error: %s'), $error) . '</strong></p>';

	if ( $inputs['url'] ) :
?>
	<p><label for="rss-url-<?php echo $number; ?>">
	<?php _e('Enter the RSS feed URL here:'); ?>
	<input class="widefat" id="rss-url-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][url]" type="text" value="<?php echo $url; ?>" />
	</label></p>
<?php endif; if ( $inputs['title'] ) : ?>
	<p><label for="rss-title-<?php echo $number; ?>">
	<?php _e('Give the feed a title (optional):'); ?>
	<input class="widefat" id="rss-title-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" />
	</label></p>
<?php endif; if ( $inputs['items'] ) : ?>
	<p><label for="rss-items-<?php echo $number; ?>">
	<?php _e('How many items would you like to display?'); ?>
	<select id="rss-items-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][items]">
<?php
		for ( $i = 1; $i <= 20; ++$i )
			echo "<option value='$i' " . ( $items == $i ? "selected='selected'" : '' ) . ">$i</option>";
?>
	</select>
	</label></p>
<?php endif; if ( $inputs['show_summary'] ) : ?>
	<p><label for="rss-show-summary-<?php echo $number; ?>">
	<input id="rss-show-summary-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][show_summary]" type="checkbox" value="1" <?php if ( $show_summary ) echo 'checked="checked"'; ?>/>
	<?php _e('Display item content?'); ?>
	</label></p>
<?php endif; if ( $inputs['show_author'] ) : ?>
	<p><label for="rss-show-author-<?php echo $number; ?>">
	<input id="rss-show-author-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][show_author]" type="checkbox" value="1" <?php if ( $show_author ) echo 'checked="checked"'; ?>/>
	<?php _e('Display item author if available?'); ?>
	</label></p>
<?php endif; if ( $inputs['show_date'] ) : ?>
	<p><label for="rss-show-date-<?php echo $number; ?>">
	<input id="rss-show-date-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][show_date]" type="checkbox" value="1" <?php if ( $show_date ) echo 'checked="checked"'; ?>/>
	<?php _e('Display item date?'); ?>
	</label></p>
<?php
	endif;
	foreach ( array_keys($default_inputs) as $input ) :
		if ( 'hidden' === $inputs[$input] ) :
			$id = str_replace( '_', '-', $input );
?>
	<input type="hidden" id="rss-<?php echo $id; ?>-<?php echo $number; ?>" name="widget-rss[<?php echo $number; ?>][<?php echo $input; ?>]" value="<?php echo $$input; ?>" />
<?php
		endif;
	endforeach;
}

/**
 * Process RSS feed widget data and optionally retrieve feed items.
 *
 * The feed widget can not have more than 20 items or it will reset back to the
 * default, which is 10.
 *
 * The resulting array has the feed title, feed url, feed link (from channel),
 * feed items, error (if any), and whether to show summary, author, and date.
 * All respectively in the order of the array elements.
 *
 * @since 2.5.0
 *
 * @param array $widget_rss RSS widget feed data. Expects unescaped data.
 * @param bool $check_feed Optional, default is true. Whether to check feed for errors.
 * @return array
 */
function wp_widget_rss_process( $widget_rss, $check_feed = true ) {
	$items = (int) $widget_rss['items'];
	if ( $items < 1 || 20 < $items )
		$items = 10;
	$url           = sanitize_url(strip_tags( $widget_rss['url'] ));
	$title         = trim(strip_tags( $widget_rss['title'] ));
	$show_summary  = (int) $widget_rss['show_summary'];
	$show_author   = (int) $widget_rss['show_author'];
	$show_date     = (int) $widget_rss['show_date'];

	if ( $check_feed ) {
		$rss = fetch_feed($url);
		$error = false;
		$link = '';
		if ( is_wp_error($rss) ) {
			$error = $rss->get_error_message();
		} else {
			$link = clean_url(strip_tags($rss->get_permalink()));
			while ( stristr($link, 'http') != $link )
				$link = substr($link, 1);
		}
	}

	return compact( 'title', 'url', 'link', 'items', 'error', 'show_summary', 'show_author', 'show_date' );
}

/**
 * Tag cloud widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Tag_Cloud extends WP_Widget {

	function WP_Widget_Tag_Cloud() {
		$widget_ops = array( 'description' => __( "Your most used tags in cloud format") );
		$this->WP_Widget('tag_cloud', __('Tag Cloud'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = empty($instance['title']) ? __('Tags') : apply_filters('widget_title', $instance['title']);

		echo $before_widget;
		echo $before_title . $title . $after_title;
		wp_tag_cloud();
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		return $instance;
	}

	function form( $instance ) {
?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>">
	<?php _e('Title:') ?>
	<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo attribute_escape( $instance['title'] ); ?>" />
	</label></p>
<?php
	}
}

/**
 * Register all of the default WordPress widgets on startup.
 *
 * Calls 'widgets_init' action after all of the WordPress widgets have been
 * registered.
 *
 * @since 2.2.0
 */
function wp_widgets_init() {
	if ( !is_blog_installed() )
		return;

	register_widget('WP_Widget_Pages');

	register_widget('WP_Widget_Calendar');

	register_widget('WP_Widget_Archives');

	register_widget('WP_Widget_Links');

	register_widget('WP_Widget_Meta');

	register_widget('WP_Widget_Search');

	register_widget('WP_Widget_Text');

	register_widget('WP_Widget_Categories');

	register_widget('WP_Widget_Recent_Posts');

	register_widget('WP_Widget_Recent_Comments');

	register_widget('WP_Widget_RSS');

	register_widget('WP_Widget_Tag_Cloud');

	do_action('widgets_init');
}

add_action('init', 'wp_widgets_init', 1);
