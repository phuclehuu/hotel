<?php
/**
 * A single listing result.
 *
 * Facilitate the template building and provide helper methods
 * to determine if specific information shuold be output.
 *
 * @since 1.8.0
 *
 * @package Listify
 */
class Listify_WP_Job_Manager_Template_Single_Listing {

	/**
	 * @since 1.8.0
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( __CLASS__, 'template_tags' ) );
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.8.0
	 */
	public static function template_tags() {
		add_action( 'single_job_listing_start', array( __CLASS__, 'single_job_listing_start' ), 10 );

		add_action( 'listify_single_job_listing_meta', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'listify_single_job_listing_meta', array( __CLASS__, 'single_job_listing_meta' ) );

		add_action( 'single_job_listing_meta_start', array( __CLASS__, 'the_secondary_image' ), 7 );
		add_action( 'single_job_listing_meta_start', 'listify_the_listing_title', 10 );
		add_action( 'single_job_listing_meta_start', 'listify_the_listing_location', 20 );
		add_action( 'single_job_listing_meta_start', 'listify_the_listing_category', 30 );

		add_action( 'single_job_listing_meta_after', 'listify_the_listing_rating' );

		add_action( 'listify_single_job_listing_actions', array( __CLASS__, 'the_actions' ) );
		add_action( 'listify_single_job_listing_actions_after', array( __CLASS__, 'submit_review_link' ) );

		add_action( 'listify_single_job_listing_cover_end', array( __CLASS__, 'cover_gallery' ) );
	}

	/**
	 * Remove default WP Job Manager action outputs.
	 *
	 * @since 2.0.0
	 */
	public static function single_job_listing_start() {
		remove_action( 'single_job_listing_start', 'job_listing_meta_display', 20 );
		remove_action( 'single_job_listing_start', 'job_listing_company_display', 30 );
	}

	/**
	 * Output Job Manger's custom template hooks that we use
	 * to attach the rest of the listing information to.
	 *
	 * @since 2.0.0
	 */
	public static function single_job_listing_meta() {
		/**
		 * Output at the top of the listing hero area.
		 *
		 * @since 1.0.0
		 *
		 * @hooked self::the_secondary_image()
		 * @hooked listify_the_listing_title()
		 * @hooked listify_the_listing_location()
		 * @hooked listify_the_listing_category()
		 */
		do_action( 'single_job_listing_meta_start' );

		/**
		 * @since 1.0.0
		 */
		do_action( 'single_job_listing_meta_end' );

		/**
		 * @since 1.0.0
		 *
		 * @hooked listify_the_listing_rating() - 10
		 * @hooked Listify_Astoundify_Favorites::render() - 20
		 */
		do_action( 'single_job_listing_meta_after' );
	}

	/**
	 * Load scripts for galleries, maps, etc for single listings.
	 *
	 * @since 2.0.0
	 */
	public static function enqueue_scripts() {
		Listify_WP_Job_Manager_Template_Filters::enqueue_scripts();

		$listing = listify_get_listing( get_the_ID() );

		$map_vars = apply_filters( 'listify_single_map_settings', array(
			'provider'      => 'googlemaps' === get_theme_mod( 'map-service-provider', 'googlemaps' ) ? 'googlemaps' : 'mapbox',
			'lat'           => $listing->get_lat(),
			'lng'           => $listing->get_lng(),
			'term'          => $listing->get_marker_term()->term_id,
			'icon'          => $listing->get_marker_term_icon(),
			'mapOptions'    => array(
				'zoom'          => apply_filters( 'listify_single_listing_map_zoom', 15 ),
				'styles'        => get_theme_mod( 'map-appearance-scheme', 'blue-water' ),
				'mapboxTileUrl' => get_theme_mod( 'mapbox-tile-url', '' ),
				'maxZoom'       => get_theme_mod( 'map-behavior-max-zoom', 17 ),
			),
		) );

		$comments_var = array(
			'defaultRating' => apply_filters( 'listify_ratings_default_rating', 3 ),
		);

		wp_enqueue_script( 'listify-app-listing', get_template_directory_uri() . '/inc/integrations/wp-job-manager/js/listing/app.min.js', array( 'jquery', 'listify', 'wp-util', 'listify-map' ), 20161114 );
		wp_localize_script( 'listify-app-listing', 'listifySingleMap', $map_vars );
		wp_localize_script( 'listify-app-listing', 'listifyListingComments', $comments_var );

		// Leaflet style.
		if ( 'mapbox' === get_theme_mod( 'map-service-provider', 'googlemaps' ) ) {
			wp_enqueue_style( 'leaflet' );
		}

		// Add JS Template.
		add_action( 'wp_footer', array( __CLASS__, 'load_js_template' ) );
	}

	/**
	 * Load JS Template
	 *
	 * @since 2.0.0
	 */
	public static function load_js_template() {
		locate_template( array( 'templates/tmpl-map-pin.php' ), true );
	}

	/**
	 * Maybe display secondary image.
	 *
	 * @since 1.8.0
	 */
	public static function the_secondary_image() {
		if ( ! get_theme_mod( 'single-listing-secondary-image-display', false ) ) {
			return;
		}

		listify_the_listing_secondary_image( null, array(
			'type' => get_theme_mod( 'single-listing-secondary-image', 'avatar' ),
			'style' => get_theme_mod( 'single-listing-secondary-image-style', 'circle' ),
		) );
	}

	/**
	 * Listing Actions
	 *
	 * Break up action sections in to a primary and secondary area
	 * via secondary hooks.
	 *
	 * @since 2.0.0
	 */
	public static function the_actions() {

		echo '<div class="content-single-job_listing-actions-start">';

			/**
			 * Minor action links.
			 *
			 * @since 1.0.0
			 *
			 * @hooked Listify_Jetpack_Share::output()
			 * @hooked Listify_WP_Job_Manager_Claim::claim_button()
			 * @hooked Listify_WP_Job_Manager_Gallery::add_link()
			 * @hooked Listify_WP_Job_Manager_Claim_Listing::add_claim_link()
			 */
			do_action( 'listify_single_job_listing_actions_start' );

		echo '</div>';

		/**
		 * Primary action links.
		 *
		 * @since 1.0.0
		 *
		 * @hooked Listify_Widget_Listing_Bookings::output_button()
		 * @hooked Listify_WP_Job_Manager_Service::render()
		 * @hooked self::submit_review_link()
		 */
		do_action( 'listify_single_job_listing_actions_after' );

	}

	/**
	 * @since 2.0.0
	 */
	public static function submit_review_link() {
		global $post;

		if ( ! comments_open( $post ) || ! ( is_active_widget( false, false, 'listify_widget_panel_listing_comments', true ) || !
		is_active_sidebar( 'single-job_listing-widget-area' ) ) || 'preview' == $post->post_type ) {
			return;
		}

		ob_start();
	?>

<a href="<?php echo esc_url( listify_submit_review_url( $post ) ); ?>" class="single-job_listing-respond button button-secondary"><?php _e( 'Write a Review', 'listify' ); ?></a>

	<?php
		$link = apply_filters( 'listify_submit_review_markup', ob_get_clean() );

		echo $link;
	}

	/**
	 * Single Listing Cover
	 *
	 * @since 2.00
	 */
	public static function single_listing_cover( $atts ) {
		if ( false === strpos( $atts, 'content-single-job_listing-hero' ) ) {
			return $atts;
		}

		$listing = listify_get_listing();

		if ( get_theme_mod( 'single-listing-secondary-image-display', false ) ) {
			if ( $listing->get_secondary_image() ) {
				$atts = str_replace( 'class="', 'class="listing-hero--company-logo ', $atts );
			}
		}

		return $atts;
	}

	/**
	 * Create a slider of gallery images to use on the cover.
	 *
	 * This lays on top of the standard cover which is shown on mobile devices.
	 *
	 * @since 2.0.0
	 */
	public static function cover_gallery() {
		if ( 'gallery' != esc_attr( listify_theme_mod( 'listing-single-hero-style', 'default' ) ) ) {
			return;
		}

		$gallery = Listify_WP_Job_Manager_Gallery::get( get_post()->ID );

		if ( empty( $gallery ) ) {
			return;
		}
?>

<div class="single-job_listing-cover-gallery">
	<div class="single-job_listing-cover-gallery-slick">
		<?php
		foreach ( $gallery as $image ) :
			$image = wp_get_attachment_image_src( $image, 'large', false );
			echo '<div>';
			echo '<img data-lazy="' . esc_url( $image[0] ) . '" />';
			echo '</div>';
			endforeach;
		?>
	</div>
</div>

<?php
	}
}

new Listify_WP_Job_Manager_Template_Single_Listing();
