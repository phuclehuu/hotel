<?php
/**
 * Homepage hero background image attachment.
 *
 * @uses $wp_customize
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'home-hero-image-attachment', array(
	'default' => 'initial',
) );

$wp_customize->add_control( 'home-hero-image-attachment', array(
	'label' => __( 'Hero Image Style', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'initial' => __( 'Default', 'listify' ),
		'fixed' => __( 'Parallax', 'listify' ),
	),
	'priority' => 30,
	'section' => 'static_front_page',
) );
