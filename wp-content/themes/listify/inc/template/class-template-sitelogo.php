<?php
/**
 * Manage the Site Logo.
 *
 * @see https://codex.wordpress.org/Theme_Logo
 *
 * @since 1.9.0
 * @package Listify
 */
class Listify_Template_SiteLogo {

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.9.0
	 */
	public static function init() {
		add_action( 'after_setup_theme', array( __CLASS__, 'add_theme_support' ) );
		add_filter( 'theme_mod_custom_logo', array( __CLASS__, 'update_logo_id' ) );
	}

	/**
	 * Add support for a custom logo.
	 *
	 * @since 1.9.0
	 */
	public static function add_theme_support() {
		add_theme_support( 'custom-logo', array(
			'flex-width' => true,
			'flex-height' => true,
		) );
	}

	/**
	 * If the site is using an image for the `header_image` theme mod
	 * replace the ID of `custom_logo` with that value.
	 *
	 * @since 1.9.0
	 *
	 * @param mixed $mod
	 * @return $mod
	 */
	public static function update_logo_id( $mod ) {
		if ( $mod || is_int( $mod ) ) {
			return $mod;
		}

		$header_image = get_header_image();
		$header_id = attachment_url_to_postid( $header_image );

		if ( $header_id ) {
			// Migrate header image to custom logo.
			set_theme_mod( 'custom_logo', $header_id );
			remove_theme_mod( 'header_image' );

			return $header_id;
		}

		return $mod;
	}

}

Listify_Template_SiteLogo::init();
