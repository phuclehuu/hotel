<?php
/**
 * Search filters.
 *
 * The theme contains a `job-filters-flat.php` and `job-filters.php` template file.
 * The latter being an override of the WP Job Manager plugin.
 *
 * This handles some modifications to those template files.
 *
 * @since 1.8.0
 *
 * @package Listify
 */
class Listify_WP_Job_Manager_Template_Filters {

	/**
	 * Class constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( __CLASS__, 'hooks' ) );
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 2.0.0
	 */
	public static function hooks() {
		// Load scripts.
		add_action( 'job_manager_job_filters_before', array( __CLASS__, 'enqueue_scripts' ) );

		// Filter listing output.
		add_filter( 'job_manager_job_listings_output', array( __CLASS__, 'job_manager_job_listings_output' ) );

		// Add labels to job type.
		add_action( 'job_manager_job_filters_end', array( __CLASS__, 'job_types_label' ), 9 );

		// Add a submit button to the bottom of the Job Manager filters.
		add_action( 'job_manager_job_filters_end', array( __CLASS__, 'add_submit_button' ), 25 );

		// Toggles for displaying filters on mobile.
		add_action( 'job_manager_job_filters_before', array( __CLASS__, 'toggle_filters' ) );

		// Add the hidden fields and radius slider.
		if ( ! ( get_option( 'job_manager_regions_filter', true ) && listify_has_integration( 'wp-job-manager-regions' ) ) ) {
			add_action( 'job_manager_job_filters_search_jobs_end', array( __CLASS__, 'job_manager_job_filters_distance' ), 0 );
		}

		// Sort: Filter listing results.
		add_filter( 'get_job_listings_query_args', array( __CLASS__, 'sort_listings_result' ), 10, 2 );

		// Remove "Search this location" button if location search disabled.
		add_filter( 'listify_search_this_location_button', array( __CLASS__, 'disable_search_this_location_button' ) );
	}

	/**
	 * Enqueue scripts via the job_manager_job_filters_before action in the
	 * [jobs] shortcode output. All instances of the results in Listify are called
	 * via the [jobs] shortcode, so this will only be added on pages that is used.
	 *
	 * @since 1.5.0
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function enqueue_scripts( $atts = array() ) {
		$js = get_template_directory_uri() . '/inc/integrations/wp-job-manager/js/vendor';

		wp_enqueue_script( 'wnumb', $js . '/wNumb/wNumb.js' );
		wp_enqueue_script( 'nouislider', $js . '/nouislider/nouislider.js' );
		wp_enqueue_style( 'nouislider', $js . '/nouislider/nouislider.css' );

		wp_enqueue_script( 'listify-results' );
		wp_enqueue_script( 'listify-listings' );

		// Load map script.
		if ( listify_results_has_map() ) {
			wp_enqueue_script( 'listify-map' );

			// Leaflet Style.
			if ( 'mapbox' === get_theme_mod( 'map-service-provider', 'googlemaps' ) ) {
				wp_enqueue_style( 'leaflet' );
			}
		}
	}

	/**
	 * Filter the HTML so we can show/hide certain items depending on customzier settings.
	 *
	 * @since 1.6.0
	 * @param $output
	 * @return $output
	 */
	public static function job_manager_job_listings_output( $output ) {
		$classes = array();

		if ( get_theme_mod( 'search-filters-meta', true ) ) {
			$classes[] = 'showing_jobs--has-meta';
		}

		if ( get_theme_mod( 'search-filters-rss', true ) ) {
			$classes[] = 'showing_jobs--has-rss';
		}

		if ( get_theme_mod( 'search-filters-reset', true ) ) {
			$classes[] = 'showing_jobs--has-reset';
		}

		if ( ! empty( $classes ) ) {
			$output = str_replace( 'class="showing_jobs"', 'class="showing_jobs ' . implode( ' ', $classes ) . '"', $output );
		}

		return $output;
	}

	/**
	 * Add a label to the job types
	 *
	 * @since Listify 1.0.0
	 *
	 * return void
	 */
	public static function job_types_label() {
		if ( is_tax( 'job_listing_type' ) ) {
			return;
		}

		echo '<p class="filter-by-type-label">' . __( 'Filter by type:', 'listify' ) . '</p>';
	}

	/**
	 * Add a submit button to the bottom of the Job Manager filters
	 *
	 * @since Listify 1.0.0
	 *
	 * @return void
	 */
	public static function add_submit_button() {
		if ( listify_has_integration( 'facetwp' ) ) {
			return;
		}

		if ( ! is_front_page() && ! get_theme_mod( 'search-filters-submit', true ) ) {
			return;
		}

		$label = _x( 'Update', 'search filters submit', 'listify' );

		if ( is_front_page() ) {
			$label = _x( 'Search', 'search filters submit', 'listify' );
		}

		$refreshing = __( 'Loading...', 'listify' );

		echo '<button type="submit" data-refresh="' . $refreshing . '" data-label="' . $label . '" name="update_results" class="update_results">' . $label . '</button>';
	}

	/**
	 * Get homepage filters.
	 *
	 * @since 1.8.0
	 *
	 * @return array $filters An array of filters and their respective markup.
	 */
	public static function get_filters( $display = 'home', $atts = array() ) {
		$filters = self::get_all_filters( $atts );
		$output = array();

		$default = array( 'keyword', 'location', 'category' );
		$chosen = get_theme_mod( 'search-filters-' . $display, $default );

		// convert a list
		if ( ! is_array( $chosen ) ) {
			$chosen = array_map( 'trim', explode( ',', $chosen ) );
		}

		$chosen = array_unique( $chosen );

		foreach ( $chosen as $key ) {
			$output[ $key ] = $filters[ $key ];
		}

		return $output;
	}

	/**
	 * Get the available filters and their output.
	 *
	 * @since 1.8.0
	 *
	 * @return array $filters An array of filters and their respective markup.
	 */
	public static function get_all_filters( $atts ) {
		return apply_filters( 'listify_wp_job_manager_filters', array(
			'keyword'  => self::get_keywords_filter( $atts ),
			'location' => self::get_location_filter( $atts ),
			'category' => self::get_category_filter( $atts ),
		), $atts );
	}

	/**
	 * Get the keywords filter markup.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_keywords_filter( $atts = array() ) {
		ob_start();

		if ( ! isset( $atts['keywords'] ) ) {
			$atts['keywords'] = '';
		}

		if ( ! empty( $_GET['search_keywords'] ) ) {
			$atts['keywords'] = sanitize_text_field( $_GET['search_keywords'] );
		}
?>

<div class="search_keywords">
	<label for="search_keywords"><?php _e( 'Keywords', 'listify' ); ?></label>
	<input type="text" name="search_keywords" id="search_keywords" placeholder="<?php esc_attr_e( 'What are you looking for?', 'listify' ); ?>" value="<?php echo esc_attr( $atts['keywords'] ); ?>" />
</div>

<?php
		return ob_get_clean();
	}

	/**
	 * Get the location filter markup.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_location_filter( $atts = array() ) {
		ob_start();

		if ( ! isset( $atts['location'] ) ) {
			$atts['location'] = '';
		}

		if ( ! empty( $_GET['search_location'] ) ) {
			$atts['location'] = sanitize_text_field( $_GET['search_location'] );
		}
?>

<div class="search_location">
	<label for="search_location"><?php _e( 'Location', 'listify' ); ?></label>
	<input type="text" name="search_location" id="search_location" placeholder="<?php esc_attr_e( 'Location', 'listify' ); ?>" value="<?php echo esc_attr( $atts['location'] ); ?>" />
</div>

<?php
		return ob_get_clean();
	}

	/**
	 * Get the category filter markup.
	 *
	 * @since 1.8.0
	 *
	 * @return string
	 */
	public static function get_category_filter( $atts = array() ) {
		if ( ! get_option( 'job_manager_enable_categories' ) ) {
			return;
		}

		if ( ! isset( $atts['show_category_multiselect'] ) ) {
			$atts['show_category_multiselect'] = get_option( 'job_manager_enable_default_category_multiselect', false );

			/**
			 * Don't output a placeholder or select option on mobile since the chosen
			 * library will think this is a selected option.
			 *
			 * @see https://github.com/Astoundify/listify/issues/1319
			 */
			if ( $atts['show_category_multiselect'] ) {
				if ( wp_is_mobile() ) {
					$atts['placeholder'] = null;
					$atts['show_option_all'] = false;
				}
			}
		}

		if ( ! isset( $atts['selected_category'] ) ) {
			$atts['selected_category'] = '';
		}

		if ( ! empty( $_GET['search_category'] ) ) {
			$atts['selected_category'] = sanitize_text_field( $_GET['search_category'] );
		}

		$atts['selected_category'] = is_array( $atts['selected_category'] ) ? $atts['selected_category'] : array_map( 'trim', explode( ',', $atts['selected_category'] ) );

		// Drop down args.
		$dropdown_args = apply_filters( 'listify_wp_job_manager_filters_dropdown_category', array(
			'taxonomy'        => 'job_listing_category',
			'hierarchical'    => 1,
			'show_option_all' => __( 'All categories', 'listify' ),
			'name'            => 'search_categories',
			'orderby'         => 'name',
			'multiple'        => $atts['show_category_multiselect'] ? true : false,
			'selected'        => $atts['selected_category'],
		), $atts );

		if ( is_tax( 'job_listing_category' ) ) {
			$term = get_queried_object();
			if ( 0 === $term->count ) {
				$dropdown_args['hide_empty'] = 0;
			}
		}

		ob_start();
?>

<div class="search_categories<?php if ( $atts['show_category_multiselect'] ) : ?> search_categories--multiselect<?php endif; ?>">

	<label for="search_categories"><?php _e( 'Category', 'listify' ); ?></label>
	<?php job_manager_dropdown_categories( $dropdown_args ); ?>

</div>

<?php
		return ob_get_clean();
	}


	/**
	 * Get sort filters.
	 *
	 * The HTML class ".job-manager-filter" will update the results via ajax if fields updated.
	 *
	 * @since 2.1.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function get_sort_filter( $atts = array() ) {
		$options = apply_filters( 'listify_filters_sort_by', listify_get_sort_options() );

		if ( ! $options ) {
			return;
		}

		ob_start();
?>

<div class="search-sort">
	<label for="search_sort" class="screen-reader-text"><?php _e( 'Sort by', 'listify' ); ?>:</label>

	<select id="search_sort" class="job-manager-filter" name="search_sort" autocomplete="off">
		<option value="" selected="selected"><?php esc_html_e( 'Sort by', 'listify' ); ?></option>
		<?php foreach ( $options as $id => $option ) : ?>
			<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $option ); ?></option>
		<?php endforeach; ?>
	</select>
</div>

<?php
		return ob_get_clean();
	}

	/**
	 * Modify Listing Results using sort filters.
	 *
	 * @since 2.1.0
	 *
	 * @param array $query_args Listing results.
	 * @param array $args Arguments sent to get_job_listings().
	 * @return array
	 */
	public static function sort_listings_result( $query_args, $args = array() ) {
		// Bail if no form data.
		if ( ! isset( $_REQUEST['form_data'] ) ) {
			return $query_args;
		}

		// Get form data requested.
		parse_str( $_REQUEST['form_data'], $request );

		// If "search_sort" field not available, bail.
		if ( ! isset( $request['search_sort'] ) ) {
			return $query_args;
		}

		if ( '' === $request['search_sort'] ) { // Default show featured.
			$query_args['orderby'] = array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			);

			$query_args['order'] = 'DESC';
		} else {
			// Disable proximity location.
			$query_args['listify_proximity_filter'] = false;

			// Sort query by selected search sort.
			$query_args = listify_sort_listings_query( $query_args, $request['search_sort'] );
		}

		return $query_args;
	}

	/**
	 * Add a toggle above the filters for mobile devices.
	 *
	 * @since Listify 1.0.0
	 *
	 * @return void
	 */
	public static function toggle_filters() {
?>

		<a href="#" data-toggle=".job_filters" class="js-toggle-area-trigger"><?php esc_html_e( 'Toggle Filters', 'listify' ); ?></a>

<?php
	}

	/**
	 * Add the hidden fields and radius slider
	 *
	 * @since unknown
	 */
	public static function job_manager_job_filters_distance() {
		// Bail if not needed.
		if ( ! array_key_exists( 'location', self::get_filters( 'archive' ) ) || is_tax( 'job_listing_region' ) || ! apply_filters( 'listify_search_by_radius', true ) ) {
			return;
		}

		$radius = isset( $_GET['search_radius'] ) ? absint( $_GET['search_radius'] ) : get_theme_mod( 'map-behavior-search-default', 50 )
	?>
		</div>

		<div class="search-radius-wrapper in-use">
			<div class="search-radius-label">
				<label>
					<?php printf( __( 'Radius: <span class="radi">%1$s</span> %2$s', 'listify' ), $radius, listify_results_map_unit() ); ?>
				</label>
			</div>
			<div class="search-radius-slider">
				<div id="search-radius"></div>
			</div>

			<input type="hidden" id="search_radius" name="search_radius" value="<?php echo isset( $_GET['search_radius'] ) ? absint( $_GET['search_radius'] ) : $radius; ?>" />
		</div>

		<input type="hidden" id="search_lat" name="search_lat" value="<?php echo isset( $_GET['search_lat'] ) ? esc_attr( $_GET['search_lat'] ) : 0; ?>" />
		  <input type="hidden" id="search_lng" name="search_lng" value="<?php echo isset( $_GET['search_lng'] ) ? esc_attr( $_GET['search_lng'] ) : 0; ?>" />

		<div>
	<?php
	}

	/**
	 * Disable "Search this location" button if location filter is not enabled.
	 *
	 * @since 2.3.0
	 *
	 * @param string $button Button HTML.
	 * @return string
	 */
	public static function disable_search_this_location_button( $button ) {
		$filters = self::get_filters( 'archive' );
		return array_key_exists( 'location', $filters ) ? $button : '';
	}

}

new Listify_WP_Job_Manager_Template_Filters();
