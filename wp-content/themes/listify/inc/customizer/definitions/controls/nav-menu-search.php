<?php
/**
 * Search Menu Icon
 *
 * @uses $wp_customize
 * @since 1.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'nav-search', array(
	'default' => 'left',
) );

$wp_customize->add_control( 'nav-search', array(
	'label' => __( 'Search Icon', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'left' => __( 'Left', 'listify' ),
		'right' => __( 'Right', 'listify' ),
		'none' => __( 'None', 'listify' ),
	),
	'priority' => 20,
	'section' => 'nav-menus',
) );
