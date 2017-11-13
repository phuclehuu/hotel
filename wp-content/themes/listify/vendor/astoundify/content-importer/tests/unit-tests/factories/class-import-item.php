<?php
/**
 * Item Import factory
 *
 * @since 1.0.0
 */
class Test_ItemImportFactory extends WP_UnitTestCase {

	public function test_ItemImportFactory_returns_wp_error_with_no_item() {
		$importer = Astoundify_CI_Import_Item_Factory::create( array() );

		$this->assertTrue( is_wp_error( $importer ) );
	}

	public function test_ItemImportFactory_returns_wp_error_with_invalid_item_type() {
		$importer = Astoundify_CI_Import_Item_Factory::create( 'invalid' );

		$this->assertTrue( is_wp_error( $importer ) );
	}

	public function test_ItemImportFactory_returns_item_import_class_with_object_type() {
		$importer = Astoundify_CI_Import_Item_Factory::create( 'object' );

		$this->assertInstanceOf( 'Astoundify_CI_Import_Item_Object', $importer );
	}

	public function test_ItemImportFactory_returns_item_import_class_with_navmenu_type() {
		$importer = Astoundify_CI_Import_Item_Factory::create( 'nav-menu' );

		$this->assertInstanceOf( 'Astoundify_CI_Import_Item_NavMenu', $importer );
	}

	public function test_ItemImportFactory_returns_item_import_class_with_navmenuitem_type() {
		$importer = Astoundify_CI_Import_Item_Factory::create( 'nav-menu-item' );

		$this->assertInstanceOf( 'Astoundify_CI_Import_Item_NavMenuItem', $importer );
	}

}
