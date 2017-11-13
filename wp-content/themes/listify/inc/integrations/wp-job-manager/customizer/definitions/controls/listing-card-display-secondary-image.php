<?php
/**
 * Display secondary image.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-secondary-image', array(
	'default' => 'avatar',
) );

$wp_customize->add_control( 'listing-card-display-secondary-image', array(
	'label' => __( 'Display secondary image', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.5,
	'section' => 'search-results',
) );
