<?php
/**
 * Global Colors
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_section( 'color-listing', array(
	'title' => _x( 'Listing', 'customizer section title', 'listify' ),
	'panel' => 'colors',
	'priority' => 55,
) );
