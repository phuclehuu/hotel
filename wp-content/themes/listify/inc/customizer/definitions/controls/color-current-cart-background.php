<?php
/**
 * Current Cart Background Color
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-current-cart-background', array(
	'default' => listify_theme_color( 'color-current-cart-background' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-current-cart-background',
	array(
		'label' => __( 'Current Cart Background Color', 'listify' ),
		'priority' => 90,
		'section' => 'color-header-navigation',
	)
) );
