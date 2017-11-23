<?php
/**
 * Importer factory
 *
 * @since 1.0.0
 */
class Test_ImporterFactory extends WP_UnitTestCase {

	public function test_importer_factory_returns_wp_error_with_no_files() {
		$importer = Astoundify_CI_Importer_Factory::create( array() );

		$this->assertTrue( is_wp_error( $importer ) );
	}

	public function test_importer_factory_returns_wp_error_with_mixed_file_types() {
		$importer = Astoundify_CI_Importer_Factory::create( array(
			'http://test.com/content.json',
			'http://test.com/content.xml',
		) );

		$this->assertTrue( is_wp_error( $importer ) );
	}

	public function test_importer_factory_returns_wp_error_with_invalid_type() {
		$importer = Astoundify_CI_Importer_Factory::create( array(
			'http://test.com/content.jpg'
		) );

		$this->assertTrue( is_wp_error( $importer ) );
	}

	public function test_importer_factory_returns_instance_of_importer_class_with_valid_type() {
		$importer = Astoundify_CI_Importer_Factory::create( array(
			'http://test.com/content.json'
		) );

		$this->assertInstanceOf( 'Astoundify_CI_Importer_JSON', $importer );

	}
}
