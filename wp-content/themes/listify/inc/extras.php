<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category WordPress
 * @author Astoundify
 */

/**
 * Callback for a short excerpt length.
 *
 * @since 1.5.0
 *
 * @param int $length Length of the excerpt.
 * @return int
 */
function listify_short_excerpt_length( $length ) {
	return 15;
}

/**
 * Remove ellipsis from the excerpt
 *
 * @since 1.5.0
 *
 * @return string
 */
function listify_excerpt_more() {
	return '&hellip;';
}
add_filter( 'excerpt_more', 'listify_excerpt_more' );

// Shortcodes in widgets.
add_filter( 'widget_text', 'do_shortcode' );

/**
 * Deep filter an array.
 *
 * @since 1.0.0
 *
 * @param mixed $item Item to filter.
 * @return bool
 */
function listify_array_filter_deep( $item ) {
	if ( is_array( $item ) ) {
		return array_filter( $item, 'listify_array_filter_deep' );
	}

	if ( ! empty( $item ) ) {
		return true;
	}
}

/**
 * Helper function for getting terms.
 *
 * Passes current language and makes filterable.
 *
 * @since 1.8.0
 *
 * @param array $args Modify default arguments.
 * @return array $terms
 */
function listify_get_terms( $args = array() ) {
	$defaults = array(
		'orderby'                => 'id',
		'order'                  => 'ASC',
		'hide_empty'             => 1,
		'child_of'               => 0,
		'exclude'                => '',
		'hierarchical'           => 0,
		'update_term_meta_cache' => false,
		'taxonomy'               => 'job_listing_category',
	);

	if ( function_exists( 'pll_current_language' ) ) { // Polylang.
		$defaults['lang'] = pll_current_language();
	} elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) { // WPML.
		$defaults['lang'] = apply_filters( 'wpml_current_language', null );
	}

	$args = apply_filters( 'listify_get_terms_args', wp_parse_args( $args, $defaults ) );
	$terms = get_terms( $args );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array();
	}

	return $terms;
}

/**
 * Use the assigned Homepage's video shortcode URL
 * instead of the theme mod.
 *
 * @since 1.9.0
 *
 * @param string $mod Theme mod name.
 * @return string $mod
 */
function listify_header_video( $mod ) {
	// Ensure we are reading what is expected.
	wp_reset_query();

	if ( '' === get_the_content() ) {
		return $mod;
	}

	// Surely there is a better way to do this that I am missing.
	$shortcode = str_replace( strip_shortcodes( get_the_content() ), '', get_the_content() );

	// Get shortcode atts.
	preg_match( '/' . get_shortcode_regex() . '/s', $shortcode, $matches );
	if ( ! isset( $matches[3] ) ) {
		return $mod;
	}
	$atts = shortcode_parse_atts( $matches[3] );

	if ( empty( $atts ) ) {
		return $mod;
	}

	$srcs = array_merge( array( 'src' ), wp_get_video_extensions() );
	$srcs = array_fill_keys( array_values( $srcs ), '' );

	$url = array_intersect_key( $atts, $srcs );

	if ( ! empty( $url ) ) {
		return current( $url );
	}

	return $mod;
}

/**
 * Ensure the header video settings do not use an image set
 * as the custom header.
 *
 * @since 2.0.0
 *
 * @param arary $settings Video header settings.
 * @return array $settings
 */
function listify_header_video_settings( $settings ) {
	$settings['posterUrl'] = null;

	return $settings;
}
add_filter( 'header_video_settings', 'listify_header_video_settings' );
add_filter( 'get_header_image_tag', '__return_false' );

/**
 * Add User Contact Methods.
 *
 * @since 2.3.0
 *
 * @param array   $methods Contact Methods.
 * @param WP_User $user    User object.
 * @return array
 */
function listify_user_contactmethods( $methods, $user ) {
	$methods['twitter'] = __( 'Twitter URL', 'listify' );
	$methods['facebook'] = __( 'Facebook URL', 'listify' );
	$methods['googleplus'] = __( 'Google+ URL', 'listify' );
	$methods['pinterest'] = __( 'Pinterest URL', 'listify' );
	$methods['linkedin'] = __( 'LinkedIn URL', 'listify' );
	$methods['github'] = __( 'GitHub URL', 'listify' );
	$methods['instagram'] = __( 'Instagram URL', 'listify' );

	return $methods;
}
add_filter( 'user_contactmethods', 'listify_user_contactmethods', 10, 2 );
