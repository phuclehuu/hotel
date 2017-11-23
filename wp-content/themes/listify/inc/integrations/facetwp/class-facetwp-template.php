<?php

class Listify_FacetWP_Template extends Listify_FacetWP {

	public function __construct() {
		add_filter( 'facetwp_proximity_load_js', array( $this, 'proximity_maybe_load_js' ) );

		// Archive Listings
		remove_all_actions( 'listify_output_results' );
		add_action( 'listify_output_results', array( $this, 'output_results' ), 20 );
		add_action( 'listify_output_results', array( $this, 'output_filters' ) );

		add_action( 'listify_facetwp_sort', array( $this, 'archive_job_listing_layout_before' ) );

		if ( 'side' == $this->position() ) {
			add_action( 'listify_sidebar_archive_job_listing_after', array( $this, 'output_filters' ) );
		} else {
			add_action( 'listify_output_results', array( $this, 'output_filters' ), 10 );
		}

		add_filter( 'facetwp_template_use_archive', '__return_true' );

		// enqueue scripts when results are loaded
		add_action( 'listify_facetwp_sort', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Maybe Load the Proximity JS
	 *
	 * If the Listify mapping has already been loaded do not duplicate.
	 *
	 * @since 1.5.0
	 *
	 * @return boolean
	 */
	public function proximity_maybe_load_js() {
		return ! wp_script_is( 'google-maps', 'enqueued' );
	}

	/**
	 * Enqueue scripts when the template is displayed.
	 *
	 * @since 2.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'listify-results' );
		wp_enqueue_script( 'listify-listings' );

		if ( listify_results_has_map() ) {
			wp_enqueue_script( 'listify-map' );

			// Leaflet Style.
			if ( 'mapbox' === get_theme_mod( 'map-service-provider', 'googlemaps' ) ) {
				wp_enqueue_style( 'leaflet' );
			}
		}
	}

	/**
	 * Output filters.
	 *
	 * Build HTML around the actual facet outputs. These can either be output above the results
	 * or to the side, depending on the Customizer settings.
	 *
	 * @since unknown
	 * @return void
	 */
	public function output_filters() {
		global $listify_facetwp;

		if ( did_action( 'listify_output_results' ) && 'side' == $this->position() && ! is_front_page() ) {
			return;
		}

		$front = false;
		$key = 'listing-archive-facetwp-defaults';
		$more_facets = $listify_facetwp->get_facets( get_theme_mod( 'listing-archive-facetwp-more', array() ) );

		if ( is_front_page() &&
			(
				is_page_template( 'page-templates/template-home.php' ) ||
				is_page_template( 'page-templates/template-home-vc.php' ) ||
				is_page_template( 'page-templates/template-home-slider.php' )
			)
		) {
			$front = true;
			$key = 'listing-archive-facetwp-home';
			$more_facets = array();
		}

		$default_facets = $listify_facetwp->get_facets( get_theme_mod( $key, array( 'keyword', 'location', 'category' ) ) );
?>

<a href="#" data-toggle=".job_filters" class="js-toggle-area-trigger"><?php _e( 'Toggle Filters', 'listify' ); ?></a>

<div class="facets job_filters job_filters--<?php echo get_theme_mod( 'listing-filters-style', 'content-box' ); ?> <?php if ( ! $front ) : ?>content-box<?php endif; ?> <?php echo esc_attr( $this->position() ); ?>">
	<?php echo $this->output_facet_html( $default_facets ); ?>

	<?php if ( ! empty( $more_facets ) ) : ?>
		<div class="more-filters">
			<button class="more-filters__toggle js-toggle-more-filters" data-label-show="<?php _e( 'More Filters', 'listify' ); ?>" data-label-hide="<?php _e( 'Fewer Filters', 'listify' ); ?>"><?php _e( 'More Filters', 'listify' ); ?></button>

			<div class="more-filters__filters">
				<?php echo $this->output_facet_html( $more_facets ); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php
	if ( 'top' === $this->position() ) {
		do_action( 'listify_facetwp_sort' );
	}
	?>
</div>

<?php
	}

	/**
	 * Output sorting options.
	 *
	 * @since unnknown
	 * @return void
	 */
	public function archive_job_listing_layout_before() {
?>

<div class="archive-job_listing-filter-title">
	<h3 class="archive-job_listing-found">
		<span class="results-found"><?php echo facetwp_display( 'counts' ); ?></span>
	</h3>

	<div class="search-sort">
		<label for="search_sort" class="screen-reader-text"><?php esc_html_e( 'Sort by:', 'listify' ); ?></label>

		<?php echo facetwp_display( 'sort' ); // WPCS: XSS ok. ?>
	</div>

</div>

<?php
	}

	/**
	 * Output results.
	 *
	 * @since unknown
	 * @return void
	 */
	public function output_results() {
		if ( 'side' === $this->position() ) {
			do_action( 'listify_facetwp_sort' );
		}
?>

<div class="facetwp_job_listings">
	<ul class="job_listings">
		<?php echo facetwp_display( 'template', 'listings' ); ?>
	</ul>
</div>

<?php echo facetwp_display( 'pager' ); ?>

<?php
	}

	/**
	 * Output facets with their relevant markup
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function output_facet_html( $facets = array() ) {
		global $listify_facetwp;

		$output = array();

		if ( empty( $facets ) ) {
			return;
		}

		foreach ( $facets as $key => $facet ) {
			$output[] = '
				<aside class="facetwp-filter facetwp-filter-' . esc_attr( $facet['type'] ) . ' widget-job_listing-archive">
					<h2 class="widget-title">' . esc_attr( facetwp_i18n( $facet['label'] ) ) . '</h2>' .
					facetwp_display( 'facet', $facet['name'] ) . '
				</aside>';
		}

		return implode( '', $output );
	}

	/**
	 * Get the position the facets are output.
	 *
	 * Because you can select "invalid" options in the customizer.
	 *
	 * @since unknown
	 * @return string $position
	 */
	public function position() {
		return get_theme_mod( 'listing-archive-facetwp-position', 'side' );
	}

}
