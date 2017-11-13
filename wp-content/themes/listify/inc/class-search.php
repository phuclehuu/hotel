<?php
/**
 * Allow searching to return specified post types.
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */
class Listify_Search {

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		add_action( 'pre_get_posts', array( __CLASS__, 'filter_search_results' ), 1 );
	}

	/**
	 * Filter WP_Query and set a post_type if available.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query Current WP_Query.
	 */
	public static function filter_search_results( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( ! $query->is_search ) {
			return;
		}

		if ( get_query_var( 's' ) ) {
			$query->set( 'post_type', get_query_var( 'post_type' ) );
		} else {
			$query->set( 'post_type', 'post' );
		}
	}

}

Listify_Search::init();
