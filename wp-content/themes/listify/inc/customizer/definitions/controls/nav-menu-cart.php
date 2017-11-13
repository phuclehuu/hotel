<?php
/**
 * Cart Menu Icon
 *
 * @uses $wp_customize
 * @since 1.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'nav-cart', array(
	'default' => 'left',
) );

$wp_customize->add_control( 'nav-cart', array(
	'label' => __( 'Cart Icon', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'left' => __( 'Left', 'listify' ),
		'right' => __( 'Right', 'listify' ),
		'none' => __( 'None', 'listify' ),
	),
	'priority' => 10,
	'section' => 'nav-menus',
) );
