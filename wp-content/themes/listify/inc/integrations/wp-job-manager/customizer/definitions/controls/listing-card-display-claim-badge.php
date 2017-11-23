<?php
/**
 * Display claim badge.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-claim-badge', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-claim-badge', array(
	'label' => __( 'Display claimed badge', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.5,
	'section' => 'search-results',
) );
