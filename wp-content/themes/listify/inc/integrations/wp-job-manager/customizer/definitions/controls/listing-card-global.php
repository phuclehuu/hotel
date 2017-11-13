<?php
/**
 * Add a Visible Shortcut to listing cards.
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! isset( $wp_customize->selective_refresh ) ) {
	return;
}

$wp_customize->add_setting( 'listing-card-global', array(
	'transport' => 'postMessage',
) );

$wp_customize->selective_refresh->add_partial( 'listing-card-global', array(
	'selector' => 'li.type-job_listing',
	'settings' => array( 'listing-card-display-title' ),
	'render_callback' => 'listify_partial_listing_card',
) );
