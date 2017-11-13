<?php
/**
 * Listing: Related Listings
 *
 * @since Listify 1.6.0
 */
class Listify_Widget_Listing_Related_Listings extends Listify_Widget {

	public function __construct() {
		$this->widget_description = __( 'Display listings related to the listing currently being viewed.', 'listify' );
		$this->widget_id          = 'listify_related_listings';
		$this->widget_name        = __( 'Listify - Listing: Related Listings', 'listify' );
		$this->widget_areas       = array( 'single-job_listing-widget-area', 'single-job_listing' );
		$this->widget_notice      = __( 'Add this widget only in "Single Listing" widget areas.' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => 'Related Listings',
				'label' => __( 'Title:', 'listify' ),
			),
			'location' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Limit based on location', 'listify' ),
			),
			'category' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Limit based on category', 'listify' ),
			),
			'featured' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show only featured listings', 'listify' ),
			),
			'limit' => array(
				'type'  => 'number',
				'std'   => 3,
				'min'   => 3,
				'max'   => 30,
				'step'  => 3,
				'label' => __( 'Number to show:', 'listify' ),
			),
		);
		parent::__construct();
	}

	function widget( $args, $instance ) {
		global $job_preview;

		if ( ! is_singular( 'job_listing' ) && ! $job_preview ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.
			return false;
		}

		extract( $args );

		$listing = listify_get_listing( get_post() );

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$featured = isset( $instance['featured'] ) && 1 == $instance['featured'] ? true : null;
		$limit = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 3;

		$location = isset( $instance['location'] ) && 1 == $instance['location'] ? true : null;
		$category = isset( $instance['category'] ) && 1 == $instance['category'] ? true : null;

		add_filter( 'get_job_listings_query_args', array( $this, 'exclude_current_listing' ) );

		$args = array(
			'posts_per_page' => $limit,
			'featured' => $featured,
			'no_found_rows' => true,
			'update_post_term_cache' => false,
		);

		// Perform a proper geo search for related by location.
		if ( $location && $listing->get_lat() && $listing->get_lng() ) {
			$related_location = Listify_WP_Job_Manager_Map::geolocation_search( array(
				'latitude' => $listing->get_lat(),
				'longitude' => $listing->get_lng(),
				'radius' => apply_filters( 'listify_widget_related_listings_radius', 50 ),
			) );

			if ( ! empty( $related_location ) ) {
				$args['post__in'] = wp_list_pluck( $related_location, 'ID' );
				$args['orderby'] = 'post__in';

				// Manually remove because you cannot use post__in and post__not_in.
				if ( isset( $args['post__in'][ $listing->get_object()->ID ] ) ) {
					unset( $args['post__in'][ $listing->get_object()->ID ] );
				}
			}
		}

		if ( $category ) {
			$args['search_categories'] = wp_get_post_terms( get_post()->ID, 'job_listing_category', array(
				'fields' => 'ids',
			) );
		}

		$listings = listify_get_listings( '#' . $this->id . ' ul.job_listings', $args );

		remove_filter( 'get_job_listings_query_args', array( $this, 'exclude_current_listing' ) );

		if ( ! $listings ) {
			return;
		}

		ob_start();

		echo str_replace( 'widget', '', $before_widget );

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo '<ul class="job_listings"></ul>';

		echo $after_widget;

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}

	/**
	 * Exclude the current listing from the `get_job_listings()` call.
	 *
	 * @since 1.6.0
	 * @param array $query_args
	 * @return array $query_args
	 */
	public function exclude_current_listing( $query_args ) {
		$query_args['post__not_in'] = array( get_post()->ID );

		return $query_args;
	}
}
