<?php
/**
 * Single listing sidebar position
 *
 * @uses $wp_customize
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-single-sidebar-position', array(
	'default' => 'right',
) );

$wp_customize->add_control( 'listing-single-sidebar-position', array(
	'label' => __( 'Sidebar Position', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'none' => __( 'None', 'listify' ),
		'left' => __( 'Left', 'listify' ),
		'right' => __( 'Right', 'listify' ),
	),
	'priority' => 20,
	'section' => 'single-listing',
) );
