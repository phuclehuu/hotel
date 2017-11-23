<?php
/**
 * Single listing hero style
 *
 * @uses $wp_customize
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-single-hero-overlay-style', array(
	'default' => 'solid',
) );

$wp_customize->add_control( 'listing-single-hero-overlay-style', array(
	'label' => __( 'Header Overlay Style', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'solid' => __( 'Solid', 'listify' ),
		'gradient' => __( 'Gradient', 'listify' ),
	),
	'priority' => 15,
	'section' => 'single-listing',
) );
