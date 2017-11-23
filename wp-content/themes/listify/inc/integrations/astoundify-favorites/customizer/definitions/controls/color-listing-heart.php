<?php
/**
 * Listing Heart
 *
 * @uses $wp_customize
 * @since 2.0.0
 *
 * @package Listify
 * @category Customize
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-listing-heart', array(
	'default' => listify_theme_color( 'color-listing-heart' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-listing-heart',
	array(
		'label' => __( 'Favorited Heart', 'listify' ),
		'priority' => 15,
		'section' => 'color-listing',
	)
) );
