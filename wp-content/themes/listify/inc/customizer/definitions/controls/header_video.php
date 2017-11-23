<?php
/**
 * Hide the "Header Media" added by the custom header.
 *
 * @uses $wp_customize
 * @since 1.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->remove_section( 'header_image' );
