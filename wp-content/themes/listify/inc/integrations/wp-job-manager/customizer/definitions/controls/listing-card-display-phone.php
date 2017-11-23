<?php
/**
 * Display phone.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-phone', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-phone', array(
	'label' => __( 'Display phone', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.3,
	'section' => 'search-results',
) );
