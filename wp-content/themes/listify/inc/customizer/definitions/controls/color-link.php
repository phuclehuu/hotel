<?php
/**
 * Link Text Color
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'color-link', array(
	'default' => listify_theme_color( 'color-link' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new WP_Customize_Color_Control(
	$wp_customize,
	'color-link',
	array(
		'label' => __( 'Link Text Color', 'listify' ),
		'priority' => 50,
		'section' => 'color-global',
	)
) );
