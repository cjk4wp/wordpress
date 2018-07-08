<?php

/**
 * @group formatting
 */
class Tests_Formatting_FileName extends WP_UnitTestCase {
	/**
	 * Check if it contains none-ASCII character or not.
	 */
	function test_contains_ascii_chars() {
		$ascii_text = 'Welcome to WordPress.';
		$this->assertTrue( is_ascii_text( $ascii_text ) );

		$none_ascii_text = 'WordPressへようこそ';
		$this->assertFalse( is_ascii_text( $none_ascii_text ) );
	}
}
