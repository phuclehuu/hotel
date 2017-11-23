<?php
/**
 * Display location.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-location', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-location', array(
	'label' => __( 'Display location', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.2,
	'section' => 'search-results',
) );
