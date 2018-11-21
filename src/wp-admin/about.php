<?php
/**
 * About This Version administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once( dirname( __FILE__ ) . '/admin.php' );

wp_enqueue_script( 'underscore' );

/* translators: Page title of the About WordPress page in the admin. */
$title = _x( 'About', 'page title' );

list( $display_version ) = explode( '-', get_bloginfo( 'version' ) );

wp_enqueue_style( 'wp-block-library' );

include( ABSPATH . 'wp-admin/admin-header.php' );
?>
	<div class="wrap about-wrap full-width-layout">
		<h1><?php printf( __( 'Welcome to WordPress&nbsp;%s' ), $display_version ); ?></h1>

		<p class="about-text"><?php printf( __( 'Thank you for updating to the latest version! WordPress %s introduces a robust new content creation experience.' ), $display_version ); ?></p>
		<p class="about-text">
			<a href="#classic-editor"><?php _e( 'Learn how to keep using the old editor.' ); ?></a>
		</p>
		<div class="wp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

		<h2 class="nav-tab-wrapper wp-clearfix">
			<a href="about.php" class="nav-tab nav-tab-active"><?php _e( 'What&#8217;s New' ); ?></a>
			<a href="credits.php" class="nav-tab"><?php _e( 'Credits' ); ?></a>
			<a href="freedoms.php" class="nav-tab"><?php _e( 'Freedoms' ); ?></a>
			<a href="freedoms.php?privacy-notice" class="nav-tab"><?php _e( 'Privacy' ); ?></a>
		</h2>

		<div class="feature-section one-col">
			<div class="col">
				<h2><?php _e( 'Say Hello to the New Editor' ); ?></h2>
			</div>
		</div>

		<div class="full-width">
			<img src="https://wordpress.org/gutenberg/files/2018/11/gutenberg-sketch.png" alt="">
		</div>

		<div class="feature-section one-col">
			<div class="col">
				<p><?php _e( 'You&#8217;ve successfully upgraded to WordPress 5.0! We’ve made some big changes to the editor. Our new block-based editor is the first step toward an exciting new future with a streamlined editing experience across your site. You’ll have more flexibility with how content is displayed, whether you are building your first site, revamping your blog, or write code for a living.' ); ?></p>
			</div>
		</div>

		<div class="feature-section four-col">
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Plugin-1-1.gif" alt=""/>
					<figcaption><?php _e( 'Do more with fewer plugins.' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Layout-3.gif" alt=""/>
					<figcaption><?php _e( 'Create modern, multimedia-heavy layouts.' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Devices-1-1.gif" alt=""/>
					<figcaption><?php _e( 'Work across all screen sizes and devices.' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Visual-1.gif" alt=""/>
					<figcaption><?php _e( 'Trust that your editor looks like your website.' ); ?></figcaption>
				</figure>
			</div>
		</div>

		<div class="feature-section one-col">
			<div class="col">
				<h2><?php _e( 'Building with Blocks' ); ?></h2>
				<p><?php _e( 'The new block-based editor won&#8217;t change the way any of your content looks to your visitors. What it will do is let you insert any type of multimedia in a snap and rearrange to your heart&#8217;s content. Each piece of content will be in its own block; a distinct wrapper for easy maneuvering. If you&#8217;re more of an HTML and CSS sort of person, then the blocks won&#8217;t stand in your way. WordPress is here to simplify the process, not the outcome.' ); ?></p>
				<video controls autoplay loop>
					<source src="https://wordpress.org/gutenberg/files/2018/11/add-block.mp4" type="video/mp4">
					<source src="https://wordpress.org/gutenberg/files/2018/11/add-block.webm" type="video/webm">
					<p><?php printf( __('Your browser doesn&#8217;t support HTML5 video. Here is a %1$slink to the video%2$s instead.'), '<a href="https://wordpress.org/gutenberg/files/2018/11/add-block.mp4">', '</a>'); ?></p>
				</video>
				<p><?php _e( 'We have tons of blocks available by default, and more get added by the community every day. Here are a few of the blocks to help you get started:' ); ?></p>
			</div>
		</div>

		<div class="feature-section eight-col">
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon.png" alt=""/>
					<figcaption><?php _e( 'Paragraph' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Heading.png" alt=""/>
					<figcaption><?php _e( 'Heading' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Subheading.png" alt=""/>
					<figcaption><?php _e( 'Preformatted' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Quote.png" alt=""/>
					<figcaption><?php _e( 'Quote' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Image.png" alt=""/>
					<figcaption><?php _e( 'Image' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Gallery.png" alt=""/>
					<figcaption><?php _e( 'Gallery' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Cover-Image.png" alt=""/>
					<figcaption><?php _e( 'Cover Image' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Video.png" alt=""/>
					<figcaption><?php _e( 'Video' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Audio.png" alt=""/>
					<figcaption><?php _e( 'Audio' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Column.png" alt=""/>
					<figcaption><?php _e( 'Columns' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-File.png" alt=""/>
					<figcaption><?php _e( 'File' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Code.png" alt=""/>
					<figcaption><?php _e( 'Code' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-List.png" alt=""/>
					<figcaption><?php _e( 'List' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Button.png" alt=""/>
					<figcaption><?php _e( 'Button' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-Embeds.png" alt=""/>
					<figcaption><?php _e( 'Embeds' ); ?></figcaption>
				</figure>
			</div>
			<div class="col">
				<figure>
					<img src="https://wordpress.org/gutenberg/files/2018/07/Block-Icon-More.png" alt=""/>
					<figcaption><?php _e( 'More' ); ?></figcaption>
				</figure>
			</div>
		</div>

		<div class="feature-section one-col">
			<div class="col">
				<h2><?php _e( 'Freedom to Build, Freedom to Write' ); ?></h2>
				<p><?php _e( 'This new editing experience provides a more consistent treatment of design as well as content. If you&#8217;re building client sites, you can create reusable blocks. This lets your clients add new content anytime, while still maintaining a consistent look and feel.' ); ?></p>
				<video controls autoplay loop>
					<source src="https://wordpress.org/gutenberg/files/2018/11/build.mp4" type="video/mp4">
					<source src="https://wordpress.org/gutenberg/files/2018/11/build.webm" type="video/webm">
					<p><?php printf( __('Your browser doesn&#8217;t support HTML5 video. Here is a %1$slink to the video%2$s instead.'), '<a href="https://wordpress.org/gutenberg/files/2018/11/build.mp4">', '</a>'); ?></p>
				</video>
			</div>
		</div>
		
		<div class="feature-section one-col cta">
			<div class="col">
				<a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php _e( 'Build your first post' ); ?></a>	
			</div>
		</div>
		
		<hr />

		<div class="feature-section one-col">
			<div class="col">
				<h2><?php _e( 'A Stunning New Default Theme' ); ?></h2>
			</div>
		</div>

		<div class="full-width">
			<figure>
				<img src="https://wordpress.org/gutenberg/files/2018/11/twenty-nineteen.png" alt="">
				<figcaption><?php _e( 'The front-end of Twenty Nineteen on the left, and how it looks in the editor on the right.' ); ?></figcaption>
			</figure>
		</div>

		<div class="feature-section one-col">
			<div class="col">
				<p><?php _e( 'Introducing Twenty Nineteen, a new default theme that shows off the power of the new editor.' ); ?></p>
			</div>
		</div>

		<div class="feature-section three-col">
			<div class="col">
				<img src="https://wordpress.org/gutenberg/files/2018/11/twenty-nineteen-gutenberg.png" alt="">
				<h3><?php _e( 'Designed for the block editor' ); ?></h3>
				<p><?php _e( 'Twenty Nineteen features custom styles for the blocks available by default in 5.0. It makes extensive use of editor styles throughout the theme. That way, what you create in your content editor is what you see on the front of your site.' ); ?></p>
			</div>
			<div class="col">
				<img src="https://wordpress.org/gutenberg/files/2018/11/twenty-nineteen-type.png" alt="">
				<h3><?php _e( 'Simple, type-driven layout' ); ?></h3>
				<p><?php _e( 'Featuring ample whitespace, and modern sans-serif headlines paired with classic serif body text, Twenty Nineteen is built to be beautiful on the go. It uses system fonts to increase loading speed. No more long waits on slow networks!' ); ?></p>
			</div>
			<div class="col">
				<img src="https://wordpress.org/gutenberg/files/2018/11/three-3seconds.gif" alt="">
				<h3><?php _e( 'Versatile design for all sites' ); ?></h3>
				<p><?php _e( 'Twenty Nineteen is designed to work for a wide variety of use cases. Whether you’re running a photo blog, launching a new business, or supporting a non-profit, Twenty Nineteen is flexible enough to fit your needs.' ); ?></p>
			</div>
		</div>

		<div class="feature-section one-col cta">
			<div class="col">
				<a class="button button-primary button-hero load-customize hide-if-no-customize" href="<?php echo esc_url( admin_url( 'customize.php?theme=twentynineteen' ) ); ?>"><?php _e( 'Give Twenty Nineteen a try' ); ?></a>	
			</div>
		</div>

		<hr />
			
		<div class="under-the-hood feature-section">
			<div class="col">
				<h2><?php _e( 'Developer Happiness' ); ?></h2>
			</div>
		</div>

		<div class="under-the-hood feature-section three-col">
			<div class="col">
				<h3><?php _e( 'Protect' ); ?></h3>
				<p><?php _e( 'Blocks provide a comfortable way for users to change content directly, while also ensuring the content structure cannot be easily disturbed by accidental code edits. This allows the developer to control the output, building polished and semantic markup that is preserved through edits and not easily broken.' ); ?></p>
			</div>
			<div class="col">
				<h3><?php _e( 'Compose' ); ?></h3>
				<p><?php _e( 'Take advantage of a wide collection of APIs and interface components to easily create blocks with intuitive controls for your clients. Utilizing these components not only speeds up development work but also provide a more consistent, usable, and accessible interface to all users.' ); ?></p>
			</div>
			<div class="col">
				<h3><?php _e( 'Create' ); ?></h3>
				<p><?php _e( 'The new block paradigm opens up a path of exploration and imagination when it comes to solving user needs. With the unified block insertion flow, it&#8217;s easier for your clients and customers to find and use blocks for all types of content. Developers can focus on executing their vision and providing rich editing experiences, rather than fussing with difficult APIs.' ); ?></p>
			</div>
		</div>
		
		<div class="under-the-hood feature-section one-col cta">
			<div class="col">
				<a class="button button-primary button-hero" href="<?php echo esc_url( 'https://wordpress.org/gutenberg/handbook/' ); ?>"><?php _e( 'Learn how to get started' ); ?></a>	
			</div>
		</div>

		<hr />
		
		<div class="feature-section one-col" id="classic-editor">
			<div class="col">
				<h2><?php _e( 'Keep it Classic' ); ?></h2>
			</div>
		</div>
		
		<div class="full-width">
			<img src="https://wordpress.org/gutenberg/files/2018/11/classic.png" alt="">
		</div>
		
		<div class="feature-section one-col">
			<div class="col">
				<p><?php _e( 'Prefer to stick with the familiar Classic Editor? No problem! Support for the Classic Editor plugin will remain in WordPress until 2021.' ); ?></p>
				<p><?php _e( 'The Classic Editor plugin restores the previous WordPress editor and the Edit Post screen. It lets you keep using plugins that extend it, add old-style meta boxes, or otherwise depend on the previous editor. To install, visit your plugins page and click the “Install Now” button next to “Classic Editor.” After the plugin finishes installing, click “Activate.” That’s it! ' ); ?></p>
				<p><em><?php _e( 'Note to users of assistive technology: if you experience usability issues with Gutenberg, we recommend you continue to use the Classic Editor.' ); ?></em></p>
				<div class="col cta">
					<a class="button button-primary button-hero" href="<?php echo esc_url( self_admin_url( 'plugin-install.php?tab=featured' ) ); ?>"><?php _e( 'Install the Classic Editor' ); ?></a>
				</div>
			</div>
		</div>
		
		<hr />

		<div class="return-to-dashboard">
			<?php if ( current_user_can( 'update_core' ) && isset( $_GET['updated'] ) ) : ?>
				<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>">
					<?php is_multisite() ? _e( 'Return to Updates' ) : _e( 'Return to Dashboard &rarr; Updates' ); ?>
				</a> |
			<?php endif; ?>
			<a href="<?php echo esc_url( self_admin_url() ); ?>"><?php is_blog_admin() ? _e( 'Go to Dashboard &rarr; Home' ) : _e( 'Go to Dashboard' ); ?></a>
		</div>
	</div>

	<script>
		(function( $ ) {
			$( function() {
				var $window = $( window );
				var $adminbar = $( '#wpadminbar' );
				var $sections = $( '.floating-header-section' );
				var offset = 0;

				// Account for Admin bar.
				if ( $adminbar.length ) {
					offset += $adminbar.height();
				}

				function setup() {
					$sections.each( function( i, section ) {
						var $section = $( section );
						// If the title is long, switch the layout
						var $title = $section.find( 'h2' );
						if ( $title.innerWidth() > 300 ) {
							$section.addClass( 'has-long-title' );
						}
					} );
				}

				var adjustScrollPosition = _.throttle( function adjustScrollPosition() {
					$sections.each( function( i, section ) {
						var $section = $( section );
						var $header = $section.find( 'h2' );
						var width = $header.innerWidth();
						var height = $header.innerHeight();

						if ( $section.hasClass( 'has-long-title' ) ) {
							return;
						}

						var sectionStart = $section.offset().top - offset;
						var sectionEnd = sectionStart + $section.innerHeight();
						var scrollPos = $window.scrollTop();

						// If we're scrolled into a section, stick the header
						if ( scrollPos >= sectionStart && scrollPos < sectionEnd - height ) {
							$header.css( {
								position: 'fixed',
								top: offset + 'px',
								bottom: 'auto',
								width: width + 'px'
							} );
						// If we're at the end of the section, stick the header to the bottom
						} else if ( scrollPos >= sectionEnd - height && scrollPos < sectionEnd ) {
							$header.css( {
								position: 'absolute',
								top: 'auto',
								bottom: 0,
								width: width + 'px'
							} );
						// Unstick the header
						} else {
							$header.css( {
								position: 'static',
								top: 'auto',
								bottom: 'auto',
								width: 'auto'
							} );
						}
					} );
				}, 100 );

				function enableFixedHeaders() {
					if ( $window.width() > 782 ) {
						setup();
						adjustScrollPosition();
						$window.on( 'scroll', adjustScrollPosition );
					} else {
						$window.off( 'scroll', adjustScrollPosition );
						$sections.find( '.section-header' )
							.css( {
								width: 'auto'
							} );
						$sections.find( 'h2' )
							.css( {
								position: 'static',
								top: 'auto',
								bottom: 'auto',
								width: 'auto'
							} );
					}
				}
				$( window ).resize( enableFixedHeaders );
				enableFixedHeaders();
			} );
		})( jQuery );
	</script>

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
