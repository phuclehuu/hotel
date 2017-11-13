<?php
/**
 * Display secondary image on a single listng.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'single-listing-secondary-image-display', array(
	'default' => false,
) );

$wp_customize->add_control( 'single-listing-secondary-image-display', array(
	'label' => __( 'Display secondary image', 'listify' ),
	'type' => 'checkbox',
	'priority' => 17,
	'section' => 'single-listing',
) );
