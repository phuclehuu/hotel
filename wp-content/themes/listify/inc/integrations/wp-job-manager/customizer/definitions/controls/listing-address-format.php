<?php
/**
 * Address Format
 *
 * @uses $wp_customize
 * @since 1.8.0
 */

$wp_customize->add_setting( 'listing-address-format', array(
	'default' => 'formatted',
) );

$wp_customize->add_control( 'listing-address-format', array(
	'label' => __( 'Address Format', 'listify' ),
	'type' => 'select',
	'description' => sprintf( __( 'Learn how to define per-country formats in our <a href="%s">documentation</a>', 'listify' ), 'http://listify.astoundify.com/article/652-add-a-custom-address-format' ),
	'choices' => array(
		'none' => __( 'None', 'listify' ),
		'formatted' => __( 'Auto Formatted', 'listify' ),
		'coordinates' => __( 'Coordinates', 'listify' ),
	),
	'priority' => 35,
	'section' => 'labels',
) );
