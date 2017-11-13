<?php
/**
 * Reviews for WP Job Manager.
 *
 * @since unknown
 *
 * @package Listify
 * @category Integration
 * @author Astoundify
 */

/**
 * Reviews for WP Job Manager.
 *
 * @since unknown
 */
class Listify_WP_Job_Manager_Reviews extends Listify_Integration {

	/**
	 * Register integration.
	 *
	 * @since unknown
	 */
	public function __construct() {
		$this->has_customizer = true;
		$this->includes = array();
		$this->integration = 'wp-job-manager-reviews';

		parent::__construct();
	}

	/**
	 * Hook in to WordPress.
	 *
	 * @since unknown
	 */
	public function setup_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 11 );

		// Filter the listing's average rating.
		add_filter( 'listify_get_listing_rating_average', array( $this, 'rating_average' ), 10, 2 );

		// Filter the listing's rating count.
		add_filter( 'listify_get_listing_rating_count', array( $this, 'rating_count' ), 10, 2 );

		// Filter the listing's best rating.
		add_filter( 'listify_get_listing_rating_best' , array( $this, 'rating_best' ) , 10, 2 );

		// Filter gallery output
		add_filter( 'wpjmr-gallery-output', array( $this, 'gallery_output' ), 10, 3 );
	}

	/**
	 * Scripts.
	 *
	 * @since 2.2.0
	 */
	public function scripts() {
		// Remove WPJM Reviews Scripts
		wp_dequeue_style( 'wp-job-manager-reviews' );

		// Register Gallery PopUp Scripts.
		wp_register_script( 'listify-wpjm-reviews-gallery', self::get_url() . 'js/gallery.js', array( 'jquery', 'listify' ) );
	}

	/**
	 * Filter the rating average based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current average.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_average( $average, $listing ) {
		$wpjmr = wpjmr();

		return $wpjmr->listing->reviews_average( $listing->get_id() );
	}

	/**
	 * Filter the rating count based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current count.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_count( $average, $listing ) {
		$wpjmr = wpjmr();

		return $wpjmr->listing->reviews_count( $listing->get_id() );
	}

	/**
	 * Filter the best rating based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current count.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_best( $best, $listing ) {

		return absint( get_option( 'wpjmr_star_count', 5 ) );
	}

	/**
	 * Review Gallery Output.
	 * This feature was added in WPJMR Reviews v.2
	 *
	 * @since 2.2.0
	 *
	 * @param string $html       Gallery HTML.
	 * @param int    $comment_id Comment/Review.
	 * @param array  $gallery    Attachments IDs.
	 * @return string
	 */
	public function gallery_output( $html, $comment_id, $gallery ) {
		if ( ! is_singular( 'job_listing' ) ) {
			return $html;
		}
		wp_enqueue_script( 'listify-wpjm-reviews-gallery' );
		ob_start();
?>
<ul class="listify-gallery-images listify-gallery-review">
	<?php foreach ( $gallery as $id ) : ?>
		<?php $thumb = wp_get_attachment_image_src( $id, 'thumbnail' ); ?>
		<?php $full  = wp_get_attachment_image_src( $id, 'fullsize' ); ?>
		<li class="gallery-preview-image" style="background-image:url(<?php echo esc_url( $thumb[0] ); ?>);">
			<a href="<?php echo esc_url( $full[0] ); ?>"></a>
		</li>
	<?php endforeach; ?>
</ul><!-- .listify-gallery-images -->
<?php
		return ob_get_clean();
	}

}

$GLOBALS['listify_job_manager_reviews'] = new Listify_WP_Job_Manager_Reviews();
