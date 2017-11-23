<?php
/**
 * Display title.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-title', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-title', array(
	'label' => __( 'Display title', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.1,
	'section' => 'search-results',
) );
