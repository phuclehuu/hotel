<?php
/**
 * Single listing hero size
 *
 * @uses $wp_customize
 * @since 1.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-single-hero-size', array(
	'default' => 'default',
) );

$wp_customize->add_control( 'listing-single-hero-size', array(
	'label' => __( 'Header Size', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'default' => __( 'Normal', 'listify' ),
		'large' => __( 'Large', 'listify' ),
	),
	'priority' => 5,
	'section' => 'single-listing',
) );
