<?php
/**
 * Single secondary image diplay style.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'single-listing-secondary-image-style', array(
	'default' => 'circle',
) );

$wp_customize->add_control( 'single-listing-secondary-image-style', array(
	'label' => __( 'Secondary Image Style', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'square' => __( 'Square', 'listify' ),
		'circle' => __( 'Circle', 'listify' ),
	),
	'priority' => 19,
	'section' => 'single-listing',
) );
