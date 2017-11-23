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

$wp_customize->add_setting( 'listing-single-hero-style', array(
	'default' => 'default',
) );

$wp_customize->add_control( 'listing-single-hero-style', array(
	'label' => __( 'Header Style', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'default' => __( 'Featured Image', 'listify' ),
		'gallery' => __( 'Gallery Slider', 'listify' ),
	),
	'priority' => 10,
	'section' => 'single-listing',
) );
