<?php
/**
 * Manage listing results archive.
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Results
 * @author Astoundify
 */
class Listify_Results {

	/**
	 * Hook in to WordPress
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		// Add body class.
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );

		// Load scripts.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_scripts' ), 5 );

		// Add the view switcher.
		add_action( 'listify_map_before', array( __CLASS__, 'view_switcher' ) );

		// Output the map.
		add_action( 'listify_output_map', array( __CLASS__, 'output_map' ) );

		// Output results templates.
		add_action( 'wp_footer', array( __CLASS__, 'output_results_templates' ) );
	}

	/**
	 * Register scripts for enqueuing later.
	 *
	 * @since 2.0.0
	 */
	public static function register_scripts() {
		$ver   = 20170614;
		$js    = get_template_directory_uri() . '/inc/results/js';
		$debug = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? true : false;
		$min   = ! $debug ? '.min' : '';

		$results_deps = array( 'jquery', 'wp-util' );

		if ( apply_filters( 'job_manager_chosen_enabled', true ) ) {
			$results_deps[] = 'chosen';
		}

		// Results Manager.
		wp_register_script( 'listify-results', $js . "/results{$min}.js", $results_deps, $ver, true );

		// Listings.
		wp_register_script( 'listify-listings', $js . "/listings{$min}.js", array( 'listify-results', 'jquery', 'wp-util' ), $ver );

		/**
		 * Listify Maps.
		 *
		 * This map handles both Google Maps and MapBox Implementation.
		 * To reduce HTTP request, it's best to only load needed scripts.
		 */
		$deps = array( 'listify-results', 'listify-listings', 'jquery', 'wp-util' );

		if ( 'googlemaps' === get_theme_mod( 'map-service-provider', 'googlemaps' ) ) {
			$deps[] = 'listify-googlemaps';
		} else {
			$deps[] = 'listify-mapbox';
		}

		wp_register_script( 'listify-map', $js . "/map{$min}.js", $deps, $ver );

		/* == Google Maps Scripts == */

		// Listify Google Maps Scripts ( combine & minify of infobubble, markerclusterer, and richmarker ).
		$deps = $debug ? array( 'google-maps', 'infobubble', 'markerclusterer', 'richmarker' ) : array( 'google-maps' );
		wp_register_script( 'listify-googlemaps', $js . "/map-googlemaps{$min}.js", $deps, $ver );

		// Google Maps.
		wp_register_script( 'google-maps', listify_get_google_maps_api_url(), array(), '3.exp' );

		// Infobubble.
		wp_register_script( 'infobubble', $js . '/vendor/googlemaps/infobubble/infobubble.js', array( 'google-maps' ), '0.8' );

		// Markerclusterer.
		wp_register_script( 'markerclusterer', $js . '/vendor/googlemaps/markerclusterer/markerclusterer.js', array( 'google-maps' ), '1.2.0' );

		// Richmarker.
		wp_register_script( 'richmarker', $js . '/vendor/googlemaps/richmarker/richmarker.js', array( 'google-maps' ), $ver );

		/* == Leaflet (MapBox) Scritps == */

		// Listify Leaflet Scripts ( combine & minify of leaflet-markercluster and leaflet-geosearch ).
		$deps = $debug ? array( 'leaflet', 'leaflet-markercluster', 'leaflet-geosearch' ) : array( 'leaflet' );
		wp_register_script( 'listify-mapbox', $js . "/map-mapbox{$min}.js", $deps, $ver );

		// Leaflet.
		wp_register_script( 'leaflet', '//unpkg.com/leaflet@1.0.3/dist/leaflet.js', array(), '1.0.3' );
		wp_register_style( 'leaflet', '//unpkg.com/leaflet@1.0.3/dist/leaflet.css', array(), '1.0.3' );

		// Leaflet Markercluster.
		wp_register_script( 'leaflet-markercluster', $js . '/vendor/mapbox/markerclusterer/leaflet.markercluster.js', array( 'leaflet' ), $ver );

		// Leaflet Geosearch.
		wp_register_script( 'leaflet-geosearch', $js . '/vendor/mapbox/geosearch/leaflet.geosearch.js', array( 'leaflet' ), $ver );

		// Map Settings.
		$settings = array(
			'displayMap' => (bool) listify_results_has_map(),
			'dataService' => array(
				'service' => listify_has_integration( 'facetwp' ) ? 'facetwp' : 'wpjobmanager',
				'wpjobmanager' => array(
					'searchRadiusMin' => get_theme_mod( 'map-behavior-search-min', 0 ),
					'searchRadiusMax' => get_theme_mod( 'map-behavior-search-max', 100 ),
					'searchRadiusDefault' => isset( $_GET['search_radius'] ) ? absint( $_GET['search_radius'] ) : get_theme_mod( 'map-behavior-search-default', 50 ), // WPCS: Nonce ok.
				),
			),
			'mapService' => array(
				'service' => get_theme_mod( 'map-service-provider', 'googlemaps' ),
				'center' => explode( ',', listify_sanitize_coordinate( get_theme_mod( 'map-behavior-center', '' ) ) ),
				'useClusters' => (bool) get_theme_mod( 'map-behavior-clusters', true ),
				'autofit' => '' === get_theme_mod( 'map-behavior-center', '' ),
				'autoPan' => get_theme_mod( 'map-behavior-autopan', true ) ? 1 : 0,
				'zoom' => get_theme_mod( 'map-behavior-zoom', 3 ),
				'maxZoom' => get_theme_mod( 'map-behavior-max-zoom', 17 ),
				'maxZoomOut' => get_theme_mod( 'map-behavior-max-zoom-out', 3 ),
				'gridSize' => (int) get_theme_mod( 'map-behavior-grid-size', 60 ),
				'mapbox' => array(
					'tileUrl' => get_theme_mod( 'mapbox-tile-url', '' ),
					'scrollwheel' => (bool) get_theme_mod( 'map-behavior-scrollwheel', true ),
				),
				'googlemaps' => array(
					'infoBubbleTrigger' => wp_is_mobile() ? 'click' : get_theme_mod( 'map-behavior-trigger', 'mouseover' ),
					'autoComplete' => (bool) get_theme_mod( 'search-filters-autocomplete', true ),
					'scrollwheel' => (bool) get_theme_mod( 'map-behavior-scrollwheel', true ),
					'autoCompleteArgs' => array(
						'types' => array( 'geocode' ),
					),
				),
			),
			'i18n' => array(
				'noResults' => __( 'No Results. Try revising your search keyword!', 'listify' ),
				// Translators: %d Number of results found.
				'resultsFound' => __( '%d Results Found', 'listify' ),
			),
			'defaultMobileView' => get_theme_mod( 'listing-archive-mobile-view-default', 'results' ),
			'mapUnit' => listify_results_map_unit(), // Distance unit "mi" or "km".
			'scriptDebug' => (bool) defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
		);

		$settings = apply_filters( 'listify_map_settings', $settings );

		wp_localize_script( 'listify-results', 'listifyResults', apply_filters( 'listify_map_settings', $settings ) );
	}

	/**
	 * Set the body class based on how the map is being output
	 *
	 * @since 2.0.0
	 *
	 * @param array $classes Current body classes.
	 * @return array $classes
	 */
	public static function body_class( $classes ) {
		$position = get_theme_mod( 'listing-archive-map-position', 'side' );

		if (
			listify_is_job_manager_archive() &&
			in_array( $position, array( 'side', 'right' ), true ) &&
			listify_results_has_map() &&
			! ( listify_is_widgetized_page() )
		) {
			$classes[] = 'fixed-map';
			$classes[] = 'fixed-map--' . $position;
		}

		return $classes;
	}

	/**
	 * Display the map if needed.
	 *
	 * @since 2.0.0
	 */
	public static function output_map() {
		if ( ! listify_results_has_map() ) {
			return;
		}

		// Map canvas.
		locate_template( array( 'content-job_listing-map.php' ), true );

		// JS templates for markers and infobubble.
		locate_template( array( 'templates/tmpl-map-pin.php' ), true );
		locate_template( array( 'templates/tmpl-map-popup.php' ), true );
	}

	/**
	 * Output JS templates for results. Needs to be global as
	 * widgets can call these directly.
	 *
	 * @since 2.0.0
	 */
	public static function output_results_templates() {
		locate_template( array( 'templates/tmpl-listing-card.php' ), true );
		locate_template( array( 'templates/tmpl-no-results.php' ), true );
	}

	/**
	 * Display the Map/Results mobile switcher.
	 *
	 * @since 2.0.0
	 */
	public static function view_switcher() {
?>

<div class="archive-job_listing-toggle-wrapper container">
	<div class="archive-job_listing-toggle-inner views">
		<a href="#" class="archive-job_listing-toggle active" data-toggle="results"><?php esc_html_e( 'Results', 'listify' ); ?></a><a href="#" class="archive-job_listing-toggle" data-toggle="map"><?php esc_html_e( 'Map', 'listify' ); ?></a>
	</div>
</div>

<?php
	}

}

Listify_Results::init();
