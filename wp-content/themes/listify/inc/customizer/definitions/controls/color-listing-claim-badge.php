<?php
/**
 * Listing Heart
 *
 * @uses $wp_customize
 * @since 1.8.0
 */

if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-listing-claim-badge', array(
	'default' => listify_theme_color( 'color-listing-claim-badge' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-listing-claim-badge',
	array(
		'label' => __( 'Verified Listing Badge', 'listify' ),
		'priority' => 20,
		'section' => 'color-listing',
	)
) );
