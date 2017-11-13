<?php

class Listify_WP_Job_Manager_Map extends listify_Integration {

	/**
	 * @since 1.9.0
	 * @access protected
	 * @var array $distances
	 */
	protected $distances = array();

	public function __construct() {
		$this->includes = array(
			'map/class-wp-job-manager-map-schemes.php',
		);

		$this->integration = 'wp-job-manager';

		parent::__construct();
	}

	/**
	 * Hook in to WordPress.
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		$this->schemes = new Listify_WP_Job_Manager_Map_Schemes();

		// Use JSON to plot results and pins.
		add_filter( 'job_manager_ajax_get_jobs_html_results', '__return_false' );
		add_filter( 'job_manager_get_listings_result', array( $this, 'get_listings' ), 10, 2 );

		add_filter( 'job_manager_geolocation_endpoint', array( $this, 'geolocation_endpoint' ) );

		// If we are not sorting by a region we need to do more things.
		if ( ! ( get_option( 'job_manager_regions_filter', true ) && listify_has_integration( 'wp-job-manager-regions' ) ) ) {
			// Find the listings.
			add_filter( 'get_job_listings_query_args', array( $this, 'apply_proximity_filter' ), 10, 2 );

			// Let WP Jb Manager know we need to sort by the found posts.
			add_filter( 'job_manager_get_listings', array( $this, 'job_manager_get_listings' ), 99, 2 );
			add_filter( 'job_manager_get_listings_args', array( $this, 'job_manager_get_listings_args' ) );
		}
	}

	/**
	 * Short circut WP Job Manager so no HTML is returned in the JSON response.
	 *
	 * @since 2.0.0
	 *
	 * @param array    $result The current information about the result to return.
	 * @param WP_Query $listings Queried listings.
	 * @return array $result The result information.
	 */
	public function get_listings( $result, $listings ) {
		$result['listings'] = array();

		if ( $listings->have_posts() ) {
			$result['found_jobs'] = true;
			$result['found_posts'] = $listings->found_posts;

			// Send back WP Job Manager pagination.
			if ( isset( $_REQUEST['show_pagination'] ) && $_REQUEST['show_pagination'] === 'true' && isset( $_REQUEST['page'] ) ) {
				$result['pagination'] = get_job_listing_pagination( $listings->max_num_pages, absint( $_REQUEST['page'] ) );
			}
		}

		while ( $listings->have_posts() ) {
			$listings->the_post();

			$result['listings'][] = listify_get_listing()->to_array();
		}

		return $result;
	}

	/**
	 * Adjust the geocoding endpoint parameters sent to Google for
	 * reverse-lookup of an address.
	 *
	 * - Add site language
	 *
	 *   @see https://developers.google.com/maps/documentation/geocoding/intro#ReverseGeocoding
	 *
	 * - Add API key if set.
	 *   @see https://developers.google.com/maps/documentation/geocoding/intro#top_of_page
	 *
	 * @since unknown
	 *
	 * @param string $url
	 * @return string $url The URl with the langauge attached to it
	 */
	public function geolocation_endpoint( $url ) {

		/* Query Args */
		$args = array();

		/* Add Language */
		$language = get_locale() ? explode( '_', get_locale() ) : array();
		if ( isset( $language[0] ) ) {
			$args['language'] = $language[0];
		}

		/* Add API Key if not yet available */
		$api_key = listify_get_google_maps_api_key();
		$query = parse_url( $url, PHP_URL_QUERY );
		parse_str( $query, $params );
		if ( ! isset( $params['key'] ) && $api_key ) {
			$args['key'] = $api_key;
		}

		/* Add Query Args */
		$url = add_query_arg( $args, $url );

		/* Use HTTPS */
		return set_url_scheme( $url, 'https' );
	}

	/**
	 * If we are sorting by a location and radius then we need to query
	 * out listings based on that. Ordered by distance closest first
	 *
	 * @since unknown
	 *
	 * @param array $query_args
	 * @param array $query_args
	 * @return array $args
	 */
	public function apply_proximity_filter( $query_args, $args ) {
		$params = array();

		if ( ! isset( $_REQUEST['form_data'] ) ) {
			return $query_args;
		}

		global $wpdb, $wp_query;

		parse_str( $_REQUEST['form_data'], $params );

		$lat = isset( $params['search_lat'] ) ? (float) $params['search_lat'] : false;
		$lng = isset( $params['search_lng'] ) ? (float) $params['search_lng'] : false;
		$radius = isset( $params['search_radius'] ) ? (int) $params['search_radius'] : false;

		if ( ! ( $lat && $lng && $radius ) ) {
			return $query_args;
		}

		if ( is_tax( 'job_listing_region' ) ) {
			return $query_args;
		}

		$post_ids = self::geolocation_search( array(
			'latitude' => $lat,
			'longitude' => $lng,
			'radius' => $radius,
		) );

		if ( empty( $post_ids ) || ! $post_ids ) {
			$post_ids = array( 0 );
		}

		// Allow the calculated distances to be accessed later.
		$this->distances = $post_ids;

		$query_args['post__in'] = array_keys( (array) $post_ids );
		$query_args['orderby'] = 'post__in';

		$query_args = $this->remove_location_meta_query( $query_args );

		return $query_args;
	}

	/**
	 * Get a list of object IDs based on geolocation data.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args
	 * @return mixed
	 */
	public static function geolocation_search( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'earth_radius' => 'mi' == listify_results_map_unit() ? 3959 : 6371,
			'orderby' => array(),
			'latitude' => null,
			'longitude' => null,
			'radius' => null,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( apply_filters( 'listify_feature_listings_in_location_search', true ) ) {
			$args['orderby'][] = "$wpdb->posts.menu_order ASC";
		}

		$args['orderby'][] = 'distance ASC';

		$sql = $wpdb->prepare( "
			SELECT $wpdb->posts.ID, 
				( %s * acos( 
					cos( radians(%s) ) * 
					cos( radians( latitude.meta_value ) ) * 
					cos( radians( longitude.meta_value ) - radians(%s) ) + 
					sin( radians(%s) ) * 
					sin( radians( latitude.meta_value ) ) 
				) ) 
				AS distance, latitude.meta_value AS latitude, longitude.meta_value AS longitude
				FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta 
					AS latitude 
					ON $wpdb->posts.ID = latitude.post_id
				INNER JOIN $wpdb->postmeta 
					AS longitude 
					ON $wpdb->posts.ID = longitude.post_id
				WHERE 1=1
					AND ($wpdb->posts.post_status = 'publish' ) 
					AND latitude.meta_key='geolocation_lat'
					AND longitude.meta_key='geolocation_long'
				HAVING distance < %s
				ORDER BY " . implode( ',', $args['orderby'] ),
			$args['earth_radius'],
			$args['latitude'],
			$args['longitude'],
			$args['latitude'],
			$args['radius']
		);

		return $wpdb->get_results( $sql, OBJECT_K );
	}

	/**
	 * Remove other location meta query items from a normal query.
	 *
	 * Only applies when a radius search is happening.
	 *
	 * @since unknown
	 *
	 * @param array $query_args
	 * @return array $query_args
	 */
	private function remove_location_meta_query( $query_args ) {
		$found = false;

		if ( ! isset( $query_args['meta_query'] ) ) {
			return $query_args;
		}

		foreach ( $query_args['meta_query'] as $query_key => $meta ) {
			foreach ( $meta as $key => $args ) {
				if ( ! is_int( $key ) ) {
					continue;
				}

				if ( 'geolocation_formatted_address' == $args['key'] ) {
					$found = true;
					unset( $query_args['meta_query'][ $query_key ] );
					break;
				}
			}

			if ( $found ) {
				break;
			}
		}

		return $query_args;
	}

	/**
	 * If we are querying a geocoded location we should order by
	 * 'distance' (which is really just a way to not order by 'featured'
	 *
	 * @since unknown
	 *
	 * @param array $args
	 * @return array $args
	 */
	public function job_manager_get_listings_args( $args ) {
		if ( ! isset( $_REQUEST['form_data'] ) ) {
			return $args;
		}

		parse_str( $_REQUEST['form_data'], $params );

		$lat = isset( $params['search_lat'] ) && 0 != $params['search_lat'];

		if ( ! $lat || '' == $params['search_location'] ) {
			return $args;
		}

		$args['orderby'] = 'distance';
		$args['order'] = 'asc';

		return $args;
	}

	/**
	 * If we are (fake) ordering by 'distance' then we should really
	 * be ordering by the found post IDs.
	 *
	 * @since unknown
	 *
	 * @param array $query_args
	 * @param array $args
	 * @return array $query_args
	 */
	public function job_manager_get_listings( $query_args, $args ) {
		if ( 'distance' == $args['orderby'] ) {
			$query_args['orderby'] = 'post__in';
			$query_args['order'] = 'asc';
		}

		return $query_args;
	}

	/**
	 * Get the distance from the location search query for a specific listing.
	 *
	 * @since 1.9.0
	 *
	 * @param int $listing_id
	 * @return int|false
	 */
	public function get_distance( $listing_id ) {
		if ( empty( $this->distances ) ) {
			return false;
		}

		if ( ! isset( $this->distances[ $listing_id ] ) ) {
			return false;
		}

		return $this->distances[ $listing_id ]->distance;
	}

}
