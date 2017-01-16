<?php

defined( 'ABSPATH' ) or die();

class Random_File_Test extends WP_UnitTestCase {

	protected static $wp_includes_images = array();

	public static function setUpBeforeClass() {
		$files = glob( ABSPATH . 'wp-includes/images/*.*' );
		sort( $files );
		self::$wp_includes_images = $files;
	}


	//
	//
	// TESTS
	//
	//


	public function test_valid_directory() {
		$random_file = c2c_random_file( 'wp-includes' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\//', $random_file );
	}

	public function test_valid_directory_with_preceding_forward_slash() {
		$random_file = c2c_random_file( '/wp-includes' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\//', $random_file );
	}

	public function test_valid_directory_with_trailing_slash() {
		$random_file = c2c_random_file( 'wp-includes/images/' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\/images\//', $random_file );
	}

	public function test_valid_directory_with_subdirectory() {
		$random_file = c2c_random_file( 'wp-includes/images' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\/images\//', $random_file );
	}

	public function test_invalid_directory() {
		$this->assertEmpty( c2c_random_file( 'nonexistent' ) );
	}

	public function test_matching_extension() {
		$random_file = c2c_random_file( 'wp-includes/images', 'gif' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\/images\/.+\.gif$/', $random_file );
	}

	public function test_matching_extension_case_insensitivity( $random_file = '' ) {
		if ( empty( $random_file ) ) {
			$random_file = c2c_random_file( 'wp-includes/images', 'GIF' );
		}

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\/images\/.+\.gif$/', $random_file );
	}

	public function test_matching_multiple_extensions() {
		$random_file = c2c_random_file( 'wp-includes/images', 'jpg gif' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\/images\/.+\.(jpg|gif)$/', $random_file );
	}

	public function test_no_matching_extension() {
		$this->assertEmpty( c2c_random_file( 'wp-includes/images', 'xxx' ) );
	}

	public function test_reftype_relative() {
		$random_file = c2c_random_file( 'wp-includes/images', '', 'relative' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . $random_file );
		$this->assertRegExp( '/wp-includes\/images\//', $random_file );
	}

	public function test_reftype_absolute( $random_file = '' ) {
		if ( empty( $random_file ) ) {
			$random_file = c2c_random_file( 'wp-includes/images', '', 'absolute' );
		}

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( $random_file );
		$this->assertStringStartsWith( ABSPATH . 'wp-includes/images/', $random_file );
	}

	public function test_reftype_filename() {
		$random_file = c2c_random_file( 'wp-includes/images', '', 'filename' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( ABSPATH . 'wp-includes/images/' . $random_file );
	}

	public function test_reftype_url() {
		$random_file = c2c_random_file( 'wp-includes/images', '', 'url' );

		$this->assertNotEmpty( $random_file );
		$this->assertFileExists( str_replace( 'http://example.org/', ABSPATH, $random_file ) );
		$this->assertRegExp( '/^http:\/\/example.org\/wp-includes\/images\//', $random_file );
	}

	public function test_reftype_hyperlink() {
		$random_file = c2c_random_file( 'wp-includes/images', '', 'hyperlink' );

		$this->assertNotEmpty( $random_file );
		$this->assertRegExp( '/<a href="http:\/\/example.org\/wp-includes\/images\//', $random_file );
	}

	public function test_file_exclusion() {
		$files = self::$wp_includes_images;

		// Get the file we know should be chosen as "random"
		$to_be_random = array_pop( $files );

		$random_file = c2c_random_file( 'wp-includes/images', '', 'absolute', $files );

		$this->assertEquals( $to_be_random, $random_file );
	}

	public function test_return_empty_when_all_files_in_directory_excluded() {
		$this->assertEmpty( c2c_random_file( 'wp-includes/images', '', '', self::$wp_includes_images ) );
	}

	public function test_random_files( $random_files = array() ) {
		if ( empty( $random_files ) ) {
			$random_files = c2c_random_files( 3, 'wp-includes/images', '', 'absolute' );
		}

		$this->assertEquals( 3, count( $random_files ) );

		foreach ( $random_files as $f ) {
			$this->test_reftype_absolute( $f );
		}
	}

	public function test_random_files_can_only_return_as_many_files_as_exist() {
		$num_images = count( self::$wp_includes_images );

		$random_files = c2c_random_files( $num_images + 5, 'wp-includes/images' );

		$this->assertEquals( $num_images, count( $random_files ) );
	}

	public function test_random_files_returns_all_files_if_number_exists_number_of_files() {
		$num_images = count( self::$wp_includes_images );

		$random_files = c2c_random_files( $num_images + 5, 'wp-includes/images', '', 'absolute' );
		sort( $random_files );

		// If asking for more files than exist, every file in directory should get returned
		$this->assertEquals( self::$wp_includes_images, $random_files );
	}

	public function test_filter_invocation_method_for_c2c_random_file() {
		$random_file = apply_filters( 'c2c_random_file', 'wp-includes/images', 'GIF' );

		$this->test_matching_extension_case_insensitivity( $random_file );
	}

	public function test_filter_invocation_method_for_c2c_random_files() {
		$random_files = apply_filters( 'c2c_random_files', 3, 'wp-includes/images', '', 'absolute' );

		$this->test_random_files( $random_files );
	}

}
