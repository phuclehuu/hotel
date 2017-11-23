<?php
/**
 * Comment Sorting
 */
class Listify_Comments {

	/**
	 * Class Constructor
	 */
	public function __construct() {

		/* Modify comments loop */
		add_action( 'pre_get_comments', array( $this, 'pre_get_comments' ) );
	}


	/**
	 * Modify Comments Loop Based on Sort Drop Down Options
	 *
	 * @return null
	 * @param object $query Comment query
	 */
	public function pre_get_comments( $query ) {

		/* Only in job_listing post type. */
		if ( ! is_singular( 'job_listing' ) ) {
			return;
		}

		/* Get selected sort */
		$sort = isset( $_GET['sort-comments'] ) ? esc_attr( $_GET['sort-comments'] ) : 'date-desc';

		/* Bail if not valid */
		if ( ! array_key_exists( $sort, self::get_sort_options() ) ) {
			return;
		}

		/* Sort by Date: DESC */
		if ( 'date-desc' == $sort ) {
			$query->query_vars['order']    = 'desc';
		} // End if().

		elseif ( 'date-asc' == $sort ) {
			$query->query_vars['order']    = 'asc';
		} /* Sort by Rating */
		elseif ( 'rating-desc' == $sort || 'rating-asc' == $sort ) {
			$query->query_vars['order']    = 'rating-desc' == $sort ? 'desc' : 'asc';
			$query->query_vars['orderby']  = 'meta_value_num';
			$query->query_vars['meta_key'] = listify_has_integration( 'wp-job-manager-reviews' ) ? 'review_average' : 'rating';
		}

		/* Run the meta query again because for some reason the hook fires too late. */
		$query->meta_query = new WP_Meta_Query();
		$query->meta_query->parse_query_vars( $query->query_vars );
	}


	/**
	 * Sort Options Helper Function
	 *
	 * @return array
	 */
	public static function get_sort_options() {

		/* Comment sorting */
		$sorting = array(
			'date-desc'   => __( 'Newest First', 'listify' ),
			'date-asc'    => __( 'Oldest First', 'listify' ),
		);

		/* If rating enabled, add rating sort option */
		if ( listify_has_integration( 'wp-job-manager-reviews' ) || get_theme_mod( 'listing-ratings', true ) ) {
			$sorting['rating-desc'] = __( 'Rating (High-Low)', 'listify' );
			$sorting['rating-asc']  = __( 'Rating (Low-High)', 'listify' );
		}

		return $sorting;
	}
}

new Listify_Comments;
