<?php
/**
 * Display price.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// not now!
return;

$wp_customize->add_setting( 'listing-card-display-price', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-price', array(
	'label' => __( 'Display product price', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.4,
	'section' => 'search-results',
) );
