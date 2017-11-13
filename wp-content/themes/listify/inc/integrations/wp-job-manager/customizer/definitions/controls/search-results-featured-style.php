<?php
/**
 * Featured style.
 *
 * Badge or outline.
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-archive-feature-style', array(
	'default' => 'outline',
) );

$wp_customize->add_control( 'listing-archive-feature-style', array(
	'label' => __( 'Feature Style', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'outline' => __( 'Outline', 'listify' ),
		'badge' => __( 'Badge', 'listify' ),
	),
	'priority' => 20,
	'section' => 'search-results',
) );
