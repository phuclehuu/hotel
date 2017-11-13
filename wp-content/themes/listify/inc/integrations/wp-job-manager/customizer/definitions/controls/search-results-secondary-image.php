<?php
/**
 * Secondary image.
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-archive-card-avatar', array(
	'default' => 'avatar',
) );

$wp_customize->add_control( 'listing-archive-card-avatar', array(
	'label' => __( 'Secondary Image', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'avatar' => __( 'Listing Owner Avatar', 'listify' ),
		'logo' => __( 'Company Logo', 'listify' ),
	),
	'priority' => 6,
	'section' => 'search-results',
) );
