<?php
/**
 * Current Cart Text Color
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-current-cart-text', array(
	'default' => listify_theme_color( 'color-current-cart-text' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-current-cart-text',
	array(
		'label' => __( 'Current Cart Text Color', 'listify' ),
		'priority' => 80,
		'section' => 'color-header-navigation',
	)
) );
