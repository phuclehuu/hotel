<?php
/**
 * Determine the type of item to import.
 *
 * @since 1.0.0
 *
 * @package Astoundify\ContentImporter
 * @category Factory
 * @author Astoundify
 */

/**
 * Item Import factory
 *
 * @since 1.0.0
 */
class Astoundify_CI_Import_Item_Factory {

	/**
	 * Instantiate a new item import class depending on the type of item
	 *
	 * @since 1.0.0
	 * @param array $item The item to import.
	 * @return object|WP_Error The instantiated importer or WP_Error if type is invalid
	 */
	public static function create( $item ) {
		$type = self::is_valid_type( $item );

		if ( false === $type ) {
			return new WP_Error( 'invalid-type', 'Invalid item type cannot be imported' );
		}

		$classname = "Astoundify_CI_Import_Item_{$type}";

		$import = new $classname( $item );

		return $import;
	}

	/**
	 * Determine if the item to be imported is a supported item type
	 *
	 * @since 1.0.0
	 * @param array $item The item to import.
	 * @return bool Item Type if the item is valid. False if not valid.
	 */
	public static function is_valid_type( $item ) {
		$valid = array(
			'setting'        => 'Setting',
			'thememod'       => 'ThemeMod',
			'object'         => 'Object',
			'nav-menu'       => 'NavMenu',
			'nav-menu-item'  => 'NavMenuItem',
			'term'           => 'Term',
			'widget'         => 'Widget',
			'comment'        => 'Comment',
		);

		$type = isset( $item['type'] ) ? esc_attr( $item['type'] ) : '';

		return array_key_exists( $type, $valid ) ? $valid[ $type ] : false;
	}

}
