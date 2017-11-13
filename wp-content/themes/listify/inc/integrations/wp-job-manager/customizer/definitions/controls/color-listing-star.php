<?php
/**
 * Listing Star
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-listing-star', array(
	'default' => listify_theme_color( 'color-listing-star' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-listing-star',
	array(
		'label' => __( 'Rating Star', 'listify' ),
		'priority' => 10,
		'section' => 'color-listing',
	)
) );
