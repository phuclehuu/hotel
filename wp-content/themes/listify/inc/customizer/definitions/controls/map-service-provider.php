<?php
/**
 * Map service provider.
 *
 * @uses $wp_customize
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'map-service-provider', array(
	'default' => 'googlemaps',
) );

$wp_customize->add_control( 'map-service-provider', array(
	'label' => __( 'Map Service', 'listify' ),
	'priority' => 5,
	'type' => 'select',
	'choices' => array(
		'googlemaps' => 'Google Maps',
		'mapbox' => 'Mapbox',
	),
	'section' => 'map-settings',
) );
