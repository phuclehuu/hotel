<?php
/**
 * Display favorites action.
 *
 * @uses $wp_customize
 * @since 2.0.0
 *
 * @package Listify
 * @category Customize
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-display-favorites', array(
	'default' => true,
) );

$wp_customize->add_control( 'listing-card-display-favorites', array(
	'label' => __( 'Display favorites', 'listify' ),
	'type' => 'checkbox',
	'priority' => 5.4,
	'section' => 'search-results',
) );
