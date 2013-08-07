<?php

if ( is_multisite() ) :

/**
 * A set of unit tests for WordPress Multisite
 *
 * @group multisite
 */
class Tests_MS extends WP_UnitTestCase {

	protected $plugin_hook_count = 0;

	function test_create_and_delete_blog() {
		global $wpdb;

		$blog_ids = $this->factory->blog->create_many( 4 );
		foreach ( $blog_ids as $blog_id ) {
			$this->assertInternalType( 'int', $blog_id );
			$prefix = $wpdb->get_blog_prefix( $blog_id );

			// $get_all = false
			$details = get_blog_details( $blog_id, false );
			$this->assertEquals( $details, wp_cache_get( $blog_id . 'short', 'blog-details' ) );

			// get_id_from_blogname(), see #20950
			$this->assertEquals( $blog_id, get_id_from_blogname( $details->path ) );
			$this->assertEquals( $blog_id, wp_cache_get( 'get_id_from_blogname_' . trim( $details->path, '/' ), 'blog-details' ) );

			// get_blog_id_from_url()
			$this->assertEquals( $blog_id, get_blog_id_from_url( $details->domain, $details->path ) );
			$key = md5( $details->domain . $details->path );
			$this->assertEquals( $blog_id, wp_cache_get( $key, 'blog-id-cache' ) );

			// These are empty until get_blog_details() is called with $get_all = true
			$this->assertEquals( false, wp_cache_get( $blog_id, 'blog-details' ) );
			$key = md5( $details->domain . $details->path );
			$this->assertEquals( false, wp_cache_get( $key, 'blog-lookup' ) );

			// $get_all = true should propulate the full blog-details cache and the blog slug lookup cache
			$details = get_blog_details( $blog_id, true );
			$this->assertEquals( $details, wp_cache_get( $blog_id, 'blog-details' ) );
			$this->assertEquals( $details, wp_cache_get( $key, 'blog-lookup' ) );

			foreach ( $wpdb->tables( 'blog', false ) as $table ) {
				$wpdb->suppress_errors();
				$table_fields = $wpdb->get_results( "DESCRIBE $prefix$table;" );
				$wpdb->suppress_errors( false );
				$this->assertNotEmpty( $table_fields );
				$result = $wpdb->get_results( "SELECT * FROM $prefix$table LIMIT 1" );
				if ( 'commentmeta' == $table || 'links' == $table )
					$this->assertEmpty( $result );
				else
					$this->assertNotEmpty( $result );
			}
		}

		// update the blog count cache to use get_blog_count()
		wp_update_network_counts();
		$this->assertEquals( 4 + 1, (int) get_blog_count() );

		$drop_tables = false;
		// delete all blogs
		foreach ( $blog_ids as $blog_id ) {
			// drop tables for every second blog
			$drop_tables = ! $drop_tables;
			$details = get_blog_details( $blog_id, false );

			wpmu_delete_blog( $blog_id, $drop_tables );

			$this->assertEquals( false, wp_cache_get( 'get_id_from_blogname_' . trim( $details->path, '/' ), 'blog-details' ) );
			$this->assertEquals( false, wp_cache_get( $blog_id, 'blog-details' ) );
			$this->assertEquals( false, wp_cache_get( $blog_id . 'short', 'blog-details' ) );
			$key = md5( $details->domain . $details->path );
			$this->assertEquals( false, wp_cache_get( $key, 'blog-lookup' ) );
			$this->assertEquals( false, wp_cache_get( $key, 'blog-id-cache' ) );

			$prefix = $wpdb->get_blog_prefix( $blog_id );
			foreach ( $wpdb->tables( 'blog', false ) as $table ) {
				$wpdb->suppress_errors();
				$table_fields = $wpdb->get_results( "DESCRIBE $prefix$table;" );
				$wpdb->suppress_errors( false );
				if ( $drop_tables )
					$this->assertEmpty( $table_fields );
				else
					$this->assertNotEmpty( $table_fields, $prefix . $table );
			}
		}

		// update the blog count cache to use get_blog_count()
		wp_update_network_counts();
		$this->assertEquals( 1, get_blog_count() );
	}

	function test_get_blogs_of_user() {
		// Logged out users don't have blogs.
		$this->assertEquals( array(), get_blogs_of_user( 0 ) );

		$user1_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_ids = $this->factory->blog->create_many( 10, array( 'user_id' => $user1_id ) );

		foreach ( $blog_ids as $blog_id )
			$this->assertInternalType( 'int', $blog_id );

		$blogs_of_user = array_keys( get_blogs_of_user( $user1_id, false ) );
		sort( $blogs_of_user );
		$this->assertEquals ( array_merge( array( 1 ), $blog_ids), $blogs_of_user );

		$this->assertTrue( remove_user_from_blog( $user1_id, 1 ) );

		$blogs_of_user = array_keys( get_blogs_of_user( $user1_id, false ) );
		sort( $blogs_of_user );
		$this->assertEquals ( $blog_ids, $blogs_of_user );

		foreach ( get_blogs_of_user( $user1_id, false ) as $blog ) {
			$this->assertTrue( isset( $blog->userblog_id ) );
			$this->assertTrue( isset( $blog->blogname ) );
			$this->assertTrue( isset( $blog->domain ) );
			$this->assertTrue( isset( $blog->path ) );
			$this->assertTrue( isset( $blog->site_id ) );
			$this->assertTrue( isset( $blog->siteurl ) );
			$this->assertTrue( isset( $blog->archived ) );
			$this->assertTrue( isset( $blog->spam ) );
			$this->assertTrue( isset( $blog->deleted ) );
		}

		// Non-existent users don't have blogs.
		wpmu_delete_user( $user1_id );
		$user = new WP_User( $user1_id );
		$this->assertFalse( $user->exists(), 'WP_User->exists' );
		$this->assertEquals( array(), get_blogs_of_user( $user1_id ) );
	}

	function test_is_blog_user() {
		global $wpdb;

		$user1_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$old_current = get_current_user_id();
		wp_set_current_user( $user1_id );

		$this->assertTrue( is_blog_user() );
		$this->assertTrue( is_blog_user( $wpdb->blogid ) );

		$blog_ids = array();

		$blog_ids = $this->factory->blog->create_many( 5 );
		foreach ( $blog_ids as $blog_id ) {
			$this->assertInternalType( 'int', $blog_id );
			$this->assertTrue( is_blog_user( $blog_id ) );
			$this->assertTrue( remove_user_from_blog( $user1_id, $blog_id ) );
			$this->assertFalse( is_blog_user( $blog_id ) );
		}

		wp_set_current_user( $old_current );
	}

	function test_is_user_member_of_blog() {
		global $wpdb;

		$user1_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$old_current = get_current_user_id();
		wp_set_current_user( $user1_id );

		$this->assertTrue( is_user_member_of_blog() );
		$this->assertTrue( is_user_member_of_blog( 0, 0 ) );
		$this->assertTrue( is_user_member_of_blog( 0, $wpdb->blogid ) );
		$this->assertTrue( is_user_member_of_blog( $user1_id ) );
		$this->assertTrue( is_user_member_of_blog( $user1_id, $wpdb->blogid ) );

		$blog_ids = $this->factory->blog->create_many( 5 );
		foreach ( $blog_ids as $blog_id ) {
			$this->assertInternalType( 'int', $blog_id );
			$this->assertTrue( is_user_member_of_blog( $user1_id, $blog_id ) );
			$this->assertTrue( remove_user_from_blog( $user1_id, $blog_id ) );
			$this->assertFalse( is_user_member_of_blog( $user1_id, $blog_id ) );
		}

		wpmu_delete_user( $user1_id );
		$user = new WP_User( $user1_id );
		$this->assertFalse( $user->exists(), 'WP_User->exists' );
		$this->assertFalse( is_user_member_of_blog( $user1_id ), 'is_user_member_of_blog' );

		wp_set_current_user( $old_current );
	}

	function test_active_network_plugins() {
		$path = "hello.php";

		// local activate, should be invisible for the network
		activate_plugin($path); // $network_wide = false
		$active_plugins = wp_get_active_network_plugins();
		$this->assertEquals( Array(), $active_plugins );

		add_action( 'deactivated_plugin', array( $this, '_helper_deactivate_hook' ) );

		// activate the plugin sitewide
		activate_plugin($path, '', $network_wide = true);
		$active_plugins = wp_get_active_network_plugins();
		$this->assertEquals( Array(WP_PLUGIN_DIR . '/hello.php'), $active_plugins );

		//deactivate the plugin
		deactivate_plugins($path);
		$active_plugins = wp_get_active_network_plugins();
		$this->assertEquals( Array(), $active_plugins );

		$this->assertEquals( 1, $this->plugin_hook_count ); // testing actions and silent mode

		activate_plugin($path, '', $network_wide = true);
		deactivate_plugins($path, true); // silent

		$this->assertEquals( 1, $this->plugin_hook_count ); // testing actions and silent mode
	}

	function _helper_deactivate_hook() {
		$this->plugin_hook_count++;
	}

	function test_get_user_count() {
		// Refresh the cache
		wp_update_network_counts();
		$start_count = get_user_count();

		$this->factory->user->create( array( 'role' => 'administrator' ) );

		$count = get_user_count(); // No change, cache not refreshed
		$this->assertEquals( $start_count, $count );

		wp_update_network_counts(); // Magic happens here

		$count = get_user_count();
		$this->assertEquals( $start_count + 1, $count );
	}

	function test_wp_schedule_update_network_counts() {
		$this->assertFalse(wp_next_scheduled('update_network_counts'));

		// We can't use wp_schedule_update_network_counts() because WP_INSTALLING is set
		wp_schedule_event(time(), 'twicedaily', 'update_network_counts');

		$this->assertInternalType('int', wp_next_scheduled('update_network_counts'));
	}

	function test_users_can_register_signup_filter() {

		$registration = get_site_option('registration');
		$this->assertFalse( users_can_register_signup_filter() );

		update_site_option('registration', 'all');
		$this->assertTrue( users_can_register_signup_filter() );

		update_site_option('registration', 'user');
		$this->assertTrue( users_can_register_signup_filter() );

		update_site_option('registration', 'none');
		$this->assertFalse( users_can_register_signup_filter() );
	}

	function test_get_dashboard_blog() {
		// if there is no dashboard blog set, current blog is used
		$dashboard_blog = get_dashboard_blog();
		$this->assertEquals( 1, $dashboard_blog->blog_id );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id ) );
		$this->assertInternalType( 'int', $blog_id );

		// set the dashboard blog to another one
		update_site_option( 'dashboard_blog', $blog_id );
		$dashboard_blog = get_dashboard_blog();
		$this->assertEquals( $blog_id, $dashboard_blog->blog_id );
	}

	function test_wpmu_log_new_registrations() {
		global $wpdb;

		$user = new WP_User( 1 );
		$ip = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR'] );

		wpmu_log_new_registrations(1,1);

		// currently there is no wrapper function for the registration_log
		$reg_blog = $wpdb->get_col( "SELECT email FROM {$wpdb->registration_log} WHERE {$wpdb->registration_log}.blog_id = 1 AND IP LIKE '" . $ip . "'" );
		$this->assertEquals( $user->user_email, $reg_blog[ count( $reg_blog )-1 ] );
	}

	/**
	 * @ticket 18119
	 */
	function test_upload_is_user_over_quota() {
		$default_space_allowed = 50;
		$echo = false;

		$this->assertFalse( upload_is_user_over_quota( $echo ) );
		$this->assertTrue( is_upload_space_available() );

		update_site_option('upload_space_check_disabled', true);
		$this->assertFalse( upload_is_user_over_quota( $echo ) );
		$this->assertTrue( is_upload_space_available() );

		update_site_option( 'blog_upload_space', 0 );
		$this->assertFalse( upload_is_user_over_quota( $echo ) );
		$this->assertEquals( $default_space_allowed, get_space_allowed() );
		$this->assertTrue( is_upload_space_available() );

		update_site_option('upload_space_check_disabled', false);
		$this->assertFalse( upload_is_user_over_quota( $echo ) );
		$this->assertTrue( is_upload_space_available() );

		if ( ! file_exists( BLOGSUPLOADDIR ) )
			$this->markTestSkipped( 'This test is broken when blogs.dir does not exist. ');

		/*
		This is broken when blogs.dir does not exist, as get_upload_space_available()
		simply returns the value of blog_upload_space (converted to bytes), which would
		be negative but still not false. When blogs.dir does exist, < 0 is returned as 0.
		*/

		update_site_option( 'blog_upload_space', -1 );
		$this->assertTrue( upload_is_user_over_quota( $echo ) );
		$this->assertEquals( -1, get_space_allowed() );
		$this->assertFalse( is_upload_space_available() );

		update_option( 'blog_upload_space', 0 );
		$this->assertFalse( upload_is_user_over_quota( $echo ) );
		$this->assertEquals( $default_space_allowed, get_space_allowed() );
		$this->assertTrue( is_upload_space_available() );

		update_option( 'blog_upload_space', -1 );
		$this->assertTrue( upload_is_user_over_quota( $echo ) );
		$this->assertEquals( -1, get_space_allowed() );
		$this->assertFalse( is_upload_space_available() );
	}

	function test_wpmu_update_blogs_date() {
		global $wpdb;

		wpmu_update_blogs_date();

		// compare the update time with the current time, allow delta < 2
		$blog = get_blog_details( $wpdb->blogid );
		$current_time = time();
		$time_difference = $current_time - strtotime( $blog->last_updated );
		$this->assertLessThan( 2, $time_difference );
	}

	function test_getters(){
		global $current_site;

		$blog_id = get_current_blog_id();
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( $blog_id, $blog->blog_id );
		$this->assertEquals( $current_site->domain, $blog->domain );
		$this->assertEquals( '/', $blog->path );

		// Test defaulting to current blog
		$this->assertEquals( $blog, get_blog_details() );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/test_blogname', 'title' => 'Test Title' ) );
		$this->assertInternalType( 'int', $blog_id );

		$this->assertEquals( 'http://' . DOMAIN_CURRENT_SITE . PATH_CURRENT_SITE . 'test_blogname/', get_blogaddress_by_name('test_blogname') );

		$this->assertEquals( $blog_id, get_id_from_blogname('test_blogname') );
	}

	function _action_counter_cb( $blog_id ) {
		global $test_action_counter;
		$test_action_counter++;
	}

	function test_update_blog_details() {
		global $test_action_counter;

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/test_blogpath', 'title' => 'Test Title' ) );
		$this->assertInternalType( 'int', $blog_id );

		$result = update_blog_details( $blog_id, array('domain' => 'example.com', 'path' => 'my_path/') );
		$this->assertTrue( $result );

		$blog = get_blog_details( $blog_id );
		$this->assertEquals( 'example.com', $blog->domain );
		$this->assertEquals( 'my_path/', $blog->path );
		$this->assertEquals( '0', $blog->spam );

		$result = update_blog_details( $blog_id, array('domain' => 'example2.com','spam' => 1) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( 'example2.com', $blog->domain );
		$this->assertEquals( 'my_path/', $blog->path );
		$this->assertEquals( '1', $blog->spam );

		$result = update_blog_details( $blog_id );
		$this->assertFalse( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( 'example2.com', $blog->domain );
		$this->assertEquals( 'my_path/', $blog->path );
		$this->assertEquals( '1', $blog->spam );

		$test_action_counter = 0;

		add_action( 'make_ham_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'spam' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->spam );
		$this->assertEquals( 1, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'spam' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->spam );
		$this->assertEquals( 1, $test_action_counter );
		remove_action( 'make_ham_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'make_spam_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'spam' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->spam );
		$this->assertEquals( 2, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'spam' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->spam );
		$this->assertEquals( 2, $test_action_counter );
		remove_action( 'make_spam_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'archive_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'archived' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->archived );
		$this->assertEquals( 3, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'archived' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->archived );
		$this->assertEquals( 3, $test_action_counter );
		remove_action( 'archive_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'unarchive_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'archived' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->archived );
		$this->assertEquals( 4, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'archived' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->archived );
		$this->assertEquals( 4, $test_action_counter );
		remove_action( 'unarchive_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'make_delete_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'deleted' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->deleted );
		$this->assertEquals( 5, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'deleted' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->deleted );
		$this->assertEquals( 5, $test_action_counter );
		remove_action( 'make_delete_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'make_undelete_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'deleted' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->deleted );
		$this->assertEquals( 6, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'deleted' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->deleted );
		$this->assertEquals( 6, $test_action_counter );
		remove_action( 'make_undelete_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'mature_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'mature' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->mature );
		$this->assertEquals( 7, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'mature' => 1 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->mature );
		$this->assertEquals( 7, $test_action_counter );
		remove_action( 'mature_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'unmature_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_details( $blog_id, array( 'mature' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->mature );
		$this->assertEquals( 8, $test_action_counter );

		// Same again
		$result = update_blog_details( $blog_id, array( 'mature' => 0 ) );
		$this->assertTrue( $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->mature );
		$this->assertEquals( 8, $test_action_counter );
		remove_action( 'unmature_blog', array( $this, '_action_counter_cb' ), 10, 1 );
	}

	/**
	 * Test fetching a blog that doesn't exist and again after it exists.
	 * 
	 * @ticket 23405
	 */
	function test_get_blog_details_blog_does_not_exist() {
		global $wpdb;

		$blog_id = $wpdb->get_var( "SELECT MAX(blog_id) FROM $wpdb->blogs" );

		// An idosyncrancy of the unit tests is that the max blog_id gets reset
		// to 1 in between test cases but picks up where it previously left off
		// on the next insert. If 1 is reported, burn a blog create to get
		// the max counter back in sync.
		if ( 1 == $blog_id ) {
			$blog_id = $this->factory->blog->create();
		}
		$blog_id++;

		$this->assertFalse( wp_cache_get( $blog_id, 'blog-details' ) );
		$this->assertFalse( get_blog_details( $blog_id ) );
		$this->assertEquals( -1, wp_cache_get( $blog_id, 'blog-details' ) );
		$this->assertFalse( get_blog_details( $blog_id ) );
		$this->assertEquals( -1, wp_cache_get( $blog_id, 'blog-details' ) );

		$this->assertEquals( $blog_id, $this->factory->blog->create() );
		$this->assertFalse( wp_cache_get( $blog_id, 'blog-details' )  );

		$blog = get_blog_details( $blog_id );
		$this->assertEquals( $blog_id, $blog->blog_id );
		$this->assertEquals( $blog, wp_cache_get( $blog_id, 'blog-details' ) );

		wpmu_delete_blog( $blog_id );
		$this->assertFalse( wp_cache_get( $blog_id, 'blog-details' ) );
		$blog->deleted = '1';
		$this->assertEQuals( $blog, get_blog_details( $blog_id ) );
		$this->assertEquals( $blog, wp_cache_get( $blog_id, 'blog-details' ) );

		wpmu_delete_blog( $blog_id, true );
		$this->assertFalse( get_blog_details( $blog_id ) );
		$this->assertEquals( -1, wp_cache_get( $blog_id, 'blog-details' ) );
	}

	function test_update_blog_status() {
		global $test_action_counter;

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/test_blogpath', 'title' => 'Test Title' ) );
		$this->assertInternalType( 'int', $blog_id );

		$test_action_counter = 0;
		$count = 1;

		add_action( 'make_ham_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_status( $blog_id, 'spam', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->spam );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'spam', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->spam );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'make_ham_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		$count++;
		add_action( 'make_spam_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$result = update_blog_status( $blog_id, 'spam', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->spam );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'spam', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->spam );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'make_spam_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'archive_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'archived', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->archived );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'archived', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->archived );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'archive_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'unarchive_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'archived', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->archived );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$result = update_blog_status( $blog_id, 'archived', 0 );
		$count++;
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->archived );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'unarchive_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'make_delete_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'deleted', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->deleted );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'deleted', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->deleted );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'make_delete_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'make_undelete_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'deleted', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->deleted );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'deleted', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->deleted );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'make_undelete_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'mature_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'mature', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->mature );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'mature', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->mature );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'mature_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'unmature_blog', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'mature', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->mature );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'mature', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->mature );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'unmature_blog', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'update_blog_public', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'public', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->public );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'public', 0 );
		$this->assertEquals( 0, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '0', $blog->public );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'update_blog_public', array( $this, '_action_counter_cb' ), 10, 1 );

		add_action( 'update_blog_public', array( $this, '_action_counter_cb' ), 10, 1 );
		$count++;
		$result = update_blog_status( $blog_id, 'public', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->public );
		$this->assertEquals( $count, $test_action_counter );

		// Same again
		$count++;
		$result = update_blog_status( $blog_id, 'public', 1 );
		$this->assertEquals( 1, $result );
		$blog = get_blog_details( $blog_id );
		$this->assertEquals( '1', $blog->public );
		$this->assertEquals( $count, $test_action_counter );
		remove_action( 'update_blog_public', array( $this, '_action_counter_cb' ), 10, 1 );

		// Updating a dummy field returns the value passed. Go fig.
		$result = update_blog_status( $blog_id, 'doesnotexist', 1 );
		$this->assertEquals( 1, $result );
	}

	function test_switch_restore_blog() {
		global $_wp_switched_stack, $wpdb;

		$this->assertEquals( array(), $_wp_switched_stack );
		$this->assertFalse( ms_is_switched() );
		$current_blog_id = get_current_blog_id();
		$this->assertInternalType( 'integer', $current_blog_id );

		wp_cache_set( 'switch-test', $current_blog_id, 'switch-test' );
		$this->assertEquals( $current_blog_id, wp_cache_get( 'switch-test', 'switch-test' ) );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/test_blogpath', 'title' => 'Test Title' ) );

		$cap_key = wp_get_current_user()->cap_key;
		switch_to_blog( $blog_id );
		$this->assertNotEquals( $cap_key, wp_get_current_user()->cap_key );
		$this->assertEquals( array( $current_blog_id ), $_wp_switched_stack );
		$this->assertTrue( ms_is_switched() );
		$this->assertEquals( $blog_id, $wpdb->blogid );
		$this->assertFalse( wp_cache_get( 'switch-test', 'switch-test' ) );
		wp_cache_set( 'switch-test', $blog_id, 'switch-test' );
		$this->assertEquals( $blog_id, wp_cache_get( 'switch-test', 'switch-test' ) );

		switch_to_blog( $blog_id );
		$this->assertEquals( array( $current_blog_id, $blog_id ), $_wp_switched_stack );
		$this->assertTrue( ms_is_switched() );
		$this->assertEquals( $blog_id, $wpdb->blogid );
		$this->assertEquals( $blog_id, wp_cache_get( 'switch-test', 'switch-test' ) );

		restore_current_blog();
		$this->assertEquals( array( $current_blog_id ), $_wp_switched_stack );
		$this->assertTrue( ms_is_switched() );
		$this->assertEquals( $blog_id, $wpdb->blogid );
		$this->assertEquals( $blog_id, wp_cache_get( 'switch-test', 'switch-test' ) );

		restore_current_blog();
		$this->assertEquals( $cap_key, wp_get_current_user()->cap_key );
		$this->assertEquals( $current_blog_id, get_current_blog_id() );
		$this->assertEquals( array(), $_wp_switched_stack );
		$this->assertFalse( ms_is_switched() );
		$this->assertEquals( $current_blog_id, wp_cache_get( 'switch-test', 'switch-test' ) );

		$this->assertFalse( restore_current_blog() );
	}

	function test_get_blog_post() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/test_blogpath', 'title' => 'Test Title' ) );
		$current_blog_id = get_current_blog_id();

		$post_id = $this->factory->post->create();
		$this->assertInstanceOf( 'WP_Post', get_post( $post_id ) );
		switch_to_blog( $blog_id );
		$this->assertNull( get_post( $post_id ) );
		$post = get_blog_post( $current_blog_id, $post_id );
		$this->assertInstanceOf( 'WP_Post', $post );
		$this->assertEquals( $post_id, $post->ID );
		restore_current_blog();

		wp_update_post( array( 'ID' => $post_id, 'post_title' => 'A Different Title' ) );
		switch_to_blog( $blog_id );
		$post = get_blog_post( $current_blog_id, $post_id );
		// Make sure cache is good
		$this->assertEquals( 'A Different Title', $post->post_title );

		$post_id2 = $this->factory->post->create();
		// Test get_blog_post() with currently active blog ID.
		$post = get_blog_post( $blog_id, $post_id2 );
		$this->assertInstanceOf( 'WP_Post', $post );
		$this->assertEquals( $post_id2, $post->ID );
		restore_current_blog();
	}

	/**
	 * @ticket 21570
	 */
	function test_is_email_address_unsafe() {
		update_site_option( 'banned_email_domains', array( 'bar.com', 'foo.co' ) );

		foreach ( array( 'test@bar.com', 'test@foo.bar.com', 'test@foo.co', 'test@subdomain.foo.co' ) as $email_address ) {
			$this->assertTrue( is_email_address_unsafe( $email_address ), "$email_address should be UNSAFE" );
		}

		foreach ( array( 'test@foobar.com', 'test@foo-bar.com', 'test@foo.com', 'test@subdomain.foo.com' ) as $email_address ) {
			$this->assertFalse( is_email_address_unsafe( $email_address ), "$email_address should be SAFE" );
		}
	}

	/**
	 * @ticket 21552
	 * @ticket 23418
	 */
	function test_sanitize_ms_options() {
		update_site_option( 'illegal_names', array( '', 'Woo', '' ) );
		update_site_option( 'limited_email_domains', array(  'woo', '', 'boo.com', 'foo.net.biz..'  ) );
		update_site_option( 'banned_email_domains', array(  'woo', '', 'boo.com', 'foo.net.biz..'  ) );

		$this->assertEquals( array( 'Woo' ), get_site_option( 'illegal_names' ) );
		$this->assertEquals( array( 'woo', 'boo.com' ), get_site_option( 'limited_email_domains' ) );
		$this->assertEquals( array( 'woo', 'boo.com' ), get_site_option( 'banned_email_domains' ) );

		update_site_option( 'illegal_names', 'foo bar' );
		update_site_option( 'limited_email_domains', "foo\nbar" );
		update_site_option( 'banned_email_domains', "foo\nbar" );

		$this->assertEquals( array( 'foo', 'bar' ), get_site_option( 'illegal_names' ) );
		$this->assertEquals( array( 'foo', 'bar' ), get_site_option( 'limited_email_domains' ) );
		$this->assertEquals( array( 'foo', 'bar' ), get_site_option( 'banned_email_domains' ) );

		foreach ( array( 'illegal_names', 'limited_email_domains', 'banned_email_domains' ) as $option ) {
			update_site_option( $option, array() );
			$this->assertSame( '', get_site_option( $option ) );
		}
	}

	function _domain_exists_cb( $exists, $domain, $path, $site_id ) {
		if ( 'foo' == $domain && 'bar' == $path )
			return 1234;
		else
			return null;
	}

	function test_domain_exists() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/testdomainexists', 'title' => 'Test Title' ) );

		$details = get_blog_details( $blog_id, false );

		$this->assertEquals( $blog_id, domain_exists( $details->domain, $details->path ) );
		$this->assertEquals( $blog_id, domain_exists( $details->domain, $details->path, $details->site_id ) );
		$this->assertEquals( null, domain_exists( $details->domain, $details->path, 999 ) );
		$this->assertEquals( null, domain_exists( 'foo', 'bar' ) );

		add_filter( 'domain_exists', array( $this, '_domain_exists_cb' ), 10, 4 );
		$this->assertEquals( 1234, domain_exists( 'foo', 'bar' ) );
		$this->assertEquals( null, domain_exists( 'foo', 'baz' ) );
		$this->assertEquals( null, domain_exists( 'bar', 'foo' ) );

		remove_filter( 'domain_exists', array( $this, '_domain_exists_cb' ), 10, 4 );
		$this->assertEquals( null, domain_exists( 'foo', 'bar' ) );

		wpmu_delete_blog( $blog_id );
		$this->assertEquals( $blog_id, domain_exists( $details->domain, $details->path ) );
		wpmu_delete_blog( $blog_id, true );
		$this->assertEquals( null, domain_exists( $details->domain, $details->path ) );
	}

	function test_get_blog_id_from_url() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id, 'path' => '/testdomainexists', 'title' => 'Test Title' ) );

		$details = get_blog_details( $blog_id, false );

		$this->assertEquals( $blog_id, get_blog_id_from_url( $details->domain, $details->path ) );
		$key = md5( $details->domain . $details->path );
		$this->assertEquals( $blog_id, wp_cache_get( $key, 'blog-id-cache' ) );

		$this->assertEquals( 0, get_blog_id_from_url( $details->domain, 'foo' ) );

		wpmu_delete_blog( $blog_id );
		$this->assertEquals( $blog_id, get_blog_id_from_url( $details->domain, $details->path ) );
		wpmu_delete_blog( $blog_id, true );

		$this->assertEquals( false, wp_cache_get( $key, 'blog-id-cache' ) );
		$this->assertEquals( 0, get_blog_id_from_url( $details->domain, $details->path ) );
	}

	function test_is_main_site() {
		$this->assertTrue( is_main_site() );
		$this->assertTrue( is_main_site( get_current_blog_id() ) );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id ) );

		switch_to_blog( $blog_id  );
		$this->assertFalse( is_main_site( $blog_id ) );
		$this->assertFalse( is_main_site( get_current_blog_id() ) );
		$this->assertFalse( is_main_site() );

		restore_current_blog();
	}

	function test_switch_upload_dir() {
		$this->assertTrue( is_main_site() );

		$site = get_current_site();

		$info = wp_upload_dir();
		$this->assertEquals( 'http://' . $site->domain . '/wp-content/uploads/' . gmstrftime('%Y/%m'), $info['url'] );
		$this->assertEquals( ABSPATH . 'wp-content/uploads/' . gmstrftime('%Y/%m'), $info['path'] );
		$this->assertEquals( gmstrftime('/%Y/%m'), $info['subdir'] );
		$this->assertEquals( '', $info['error'] );

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id = $this->factory->blog->create( array( 'user_id' => $user_id ) );

		switch_to_blog( $blog_id );
		$info = wp_upload_dir();
		$this->assertEquals( 'http://' . $site->domain . '/wp-content/uploads/sites/' . get_current_blog_id() . '/' . gmstrftime('%Y/%m'), $info['url'] );
		$this->assertEquals( ABSPATH . 'wp-content/uploads/sites/' . get_current_blog_id() . '/' . gmstrftime('%Y/%m'), $info['path'] );
		$this->assertEquals( gmstrftime('/%Y/%m'), $info['subdir'] );
		$this->assertEquals( '', $info['error'] );
		restore_current_blog();

		$info = wp_upload_dir();
		$this->assertEquals( 'http://' . $site->domain . '/wp-content/uploads/' . gmstrftime('%Y/%m'), $info['url'] );
		$this->assertEquals( ABSPATH . 'wp-content/uploads/' . gmstrftime('%Y/%m'), $info['path'] );
		$this->assertEquals( gmstrftime('/%Y/%m'), $info['subdir'] );
		$this->assertEquals( '', $info['error'] );

		update_site_option( 'ms_files_rewriting', 1 );
		ms_upload_constants();

		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$blog_id2 = $this->factory->blog->create( array( 'user_id' => $user_id ) );
		$info = wp_upload_dir();
		$this->assertEquals( 'http://' . $site->domain . '/wp-content/uploads/' . gmstrftime('%Y/%m'), $info['url'] );
		$this->assertEquals( ABSPATH . 'wp-content/uploads/' . gmstrftime('%Y/%m'), $info['path'] );
		$this->assertEquals( gmstrftime('/%Y/%m'), $info['subdir'] );
		$this->assertEquals( '', $info['error'] );

		switch_to_blog( $blog_id2 );
		$info2 = wp_upload_dir();
		$this->assertNotEquals( $info, $info2 );
		$this->assertEquals( get_option( 'siteurl' )  . '/wp-content/blogs.dir/' . get_current_blog_id() . '/files/' . gmstrftime('%Y/%m'), $info2['url'] );
		$this->assertEquals( ABSPATH . 'wp-content/blogs.dir/' . get_current_blog_id() . '/files/' . gmstrftime('%Y/%m'), $info2['path'] );
		$this->assertEquals( gmstrftime('/%Y/%m'), $info2['subdir'] );
		$this->assertEquals( '', $info2['error'] );
		restore_current_blog();
		update_site_option( 'ms_files_rewriting', 0 );
	}

	/**
	 * @ticket 23192
	 */
	function test_is_user_spammy() {
		$user_id = $this->factory->user->create( array(
			'role' => 'author',
			'user_login' => 'testuser1',
		) );

		$spam_username = (string) $user_id;
		$spam_user_id = $this->factory->user->create( array(
			'role' => 'author',
			'user_login' => $spam_username,
		) );
		update_user_status( $spam_user_id, 'spam', '1' );
		
		$this->assertTrue( is_user_spammy( $spam_username ) );
		$this->assertFalse( is_user_spammy( 'testuser1' ) );
	}

}

endif;
