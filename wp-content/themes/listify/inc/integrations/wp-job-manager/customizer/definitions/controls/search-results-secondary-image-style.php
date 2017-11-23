<?php
/**
 * Secondary image diplay style.
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-archive-card-avatar-style', array(
	'default' => 'circle',
) );

$wp_customize->add_control( 'listing-archive-card-avatar-style', array(
	'label' => __( 'Secondary Image Style', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'square' => __( 'Square', 'listify' ),
		'circle' => __( 'Circle', 'listify' ),
	),
	'priority' => 6.1,
	'section' => 'search-results',
) );
