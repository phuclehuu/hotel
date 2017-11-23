<?php
/**
 * Navigation Dropdown Text Color
 *
 * @uses $wp_customize
 * @since 1.9.0
 */

if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-navigation-dropdown-text', array(
	'default' => listify_theme_color( 'color-navigation-dropdown-text' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-navigation-dropdown-text',
	array(
		'label' => __( 'Navigation Dropdown Text Color', 'listify' ),
		'priority' => 22,
		'section' => 'color-header-navigation',
	)
) );
