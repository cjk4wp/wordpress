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

	/**
	 * Check if it returns md5 hashed value correctly.
	 */
	function test_md5_hash() {
		$hashed_text = get_hashed_text();
		$this->assertEquals( 32, strlen( $hashed_text ) );
		$this->assertTrue( ctype_xdigit( $hashed_text ) );
	}
}
