<?php
/**
 * Display rating.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-rating', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-rating', array(
	'label' => __( 'Display rating', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.4,
	'section' => 'search-results',
) );
