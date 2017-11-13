<?php
/**
 * Listing Stars
 *
 * @uses $wp_customize
 * @since 1.8.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( listify_has_integration( 'wp-job-manager-reviews' ) ) {
	return;
}

$wp_customize->add_setting( 'listing-ratings', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-ratings', array(
	'label' => __( 'Allow star ratings on listings.', 'listify' ),
	'description' => __( 'Collect a star-rating when leaving a comment.', 'listify' ),
	'type' => 'checkbox',
	'priority' => 65,
	'section' => 'labels',
) );
