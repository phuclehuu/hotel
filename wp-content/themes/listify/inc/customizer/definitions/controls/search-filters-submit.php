<?php
/**
 * Search Filters Submit
 *
 * @uses $wp_customize
 * @since 1.6.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( listify_has_integration( 'facetwp' ) ) {
	return;
}

$wp_customize->add_setting( 'search-filters-submit', array(
	'default' => true,
) );

$wp_customize->add_control( 'search-filters-submit', array(
	'label' => __( 'Show Update Button', 'listify' ),
	'type' => 'checkbox',
	'description' => __( 'Display a button to trigger search criteria update.', 'listify' ),
	'priority' => 17,
	'section' => 'search-filters',
) );
