<?php
/**
 * Secondary image type
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'single-listing-secondary-image', array(
	'default' => 'avatar',
) );

$wp_customize->add_control( 'single-listing-secondary-image', array(
	'label' => __( 'Secondary Image', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'avatar' => __( 'Listing Owner Avatar', 'listify' ),
		'logo' => __( 'Company Logo', 'listify' ),
	),
	'priority' => 18,
	'section' => 'single-listing',
) );
