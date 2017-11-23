<?php
/**
 * Mapbox title URL.
 *
 * @uses $wp_customize
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'mapbox-tile-url', array(
	'default' => '',
) );

$wp_customize->add_control( 'mapbox-tile-url', array(
	'label' => __( 'Mapbox Style URL', 'listify' ),
	'priority' => 10,
	'type' => 'text',
	'description' => '<a href="http://listify.astoundify.com/article/1070-creating-a-mapbox-tileset">' . __( 'Create a style URL', 'listify' ) . '</a>',
	'section' => 'map-settings',
) );
