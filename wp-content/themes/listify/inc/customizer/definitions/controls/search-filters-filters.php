<?php
/**
 * Search Filters
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( listify_has_integration( 'facetwp' ) ) {
	return;
}

$wp_customize->add_setting( 'search-filters-home', array(
	'default' => array( 'keyword', 'location', 'category' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new Listify_Customize_Control_Multiselect(
	$wp_customize,
	'search-filters-home',
	array(
		'label' => __( 'Homepage Filters', 'listify' ),
		'type' => 'multiselect',
		'description' => __( 'Filters to display in the homepage hero search.', 'listify' ),
		'choices' => array(
			'keyword' => __( 'Keywords', 'listify' ),
			'location' => get_option( 'job_manager_regions_filter', false ) ? __( 'Region', 'listify' ) : __( 'Location', 'listify' ),
			'category' => __( 'Category', 'listify' ),
		),
		'section' => 'search-filters',
		'priority' => 10,
	)
) );

$wp_customize->add_setting( 'search-filters-archive', array(
	'default' => array( 'keyword', 'location', 'category' ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( new Listify_Customize_Control_Multiselect(
	$wp_customize,
	'search-filters-archive',
	array(
		'label' => __( 'Results Page Filters', 'listify' ),
		'description' => __( 'The filters chosen for the homepage must be included here to allow those filters to be updated.', 'listify' ),
		'type' => 'multiselect',
		'choices' => array(
			'keyword' => __( 'Keywords', 'listify' ),
			'location' => get_option( 'job_manager_regions_filter', false ) ? __( 'Region', 'listify' ) : __( 'Location', 'listify' ),
			'category' => get_option( 'job_manager_enable_default_category_multiselect', false ) ? __( 'Categories', 'listify' ) : __( 'Category', 'listify' ),
		),
		'section' => 'search-filters',
		'priority' => 11,
	)
) );

if ( ! isset( $wp_customize->selective_refresh ) ) {
	return;
}

$wp_customize->selective_refresh->add_partial( 'search-filters-home', array(
	'selector' => '.search-filters-home',
	'settings' => array( 'search-filters-home' ),
	'render_callback' => 'listify_partial_search_filters_home',
) );

$wp_customize->selective_refresh->add_partial( 'search-filters-archive', array(
	'selector' => '.job_filters',
	'settings' => array( 'search-filters-archive' ),
	'render_callback' => 'listify_partial_search_filters_archive',
) );
