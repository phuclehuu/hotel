<?php
/**
 * WP Job Manager - Claim Listing
 *
 * 💩
 */
class Listify_WP_Job_Manager_Claim_Listing extends Listify_Integration {

	public function __construct() {
		$this->includes = array();
		$this->integration = 'wp-job-manager-claim-listing';

		parent::__construct();
	}

	public function setup_actions() {
		// Claim Listing >= 3.x
		if ( defined( 'WPJMCL_VERSION' ) ) {
			$job_listing = wpjmcl\job_listing\Setup::get_instance();

			remove_action( 'single_job_listing_start', array( $job_listing, 'add_claim_link' ) );
			add_action( 'listify_single_job_listing_actions_start', array( $job_listing, 'add_claim_link' ) );

			/* Disable Claim Listing Front CSS */
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_scripts' ), 20 );

			remove_filter( 'wpjmcl_submit_claim_link', array( $job_listing, 'add_verified_badge' ), 99, 3 );
		}

		// Claim Listing < 3.x
		if ( class_exists( 'WP_Job_Manager_Claim_Listing' ) ) {
			$this->wpjmcl = wpjmcl();

			remove_action( 'single_job_listing_start', array( $this->wpjmcl->listing, 'claim_listing_link' ) );
			add_action( 'listify_single_job_listing_actions_start', array( $this, 'claim_button' ) );
		}

		// Output the badge on single listings.
		add_action( 'single_job_listing_meta_start', array( $this, 'the_badge' ), 8 );
	}

	/**
	 * Dequeue Scripts
	 *
	 * @since 1.13.0
	 */
	public function dequeue_scripts() {
		wp_dequeue_style( 'wpjmcl_job-listing_front' );
	}

	/**
	 * Please don't look at this.
	 */
	public function is_claimed() {
		// Claim Listing >= 3.x
		if ( defined( 'WPJMCL_VERSION' ) ) {
			return ! get_post_meta( get_post()->ID, '_claimed', true );
		}

		// Claim Listing < 3.x
		if ( class_exists( 'WP_Job_Manager_Claim_Listing' ) ) {
			if ( method_exists( $this->wpjmcl->listing, 'is_claimable' ) ) {
				return $this->wpjmcl->listing->is_claimable();
			} else {
				return ! $this->wpjmcl->listing->is_claimed();
			}
		}
	}

	public function the_badge() {
		if ( ! is_singular( 'job_listing' ) && ! get_theme_mod( 'listing-card-display-claim-badge', true ) ) {
			return;
		}

		if ( $this->is_claimed() ) {
			return;
		}

		get_template_part( 'content-badge-claimed', 'claim-listing' );
	}

	public function claim_button() {
		global $post;

		if ( ! $this->is_claimed() ) {
			return;
		}

		$paid_claim_listing_page = job_manager_get_permalink( 'claim_listing' );

		$href = add_query_arg( array(
			'action' => 'claim_listing',
			'listing_id' => $post->ID,
		), $paid_claim_listing_page );

		$href = esc_url( wp_nonce_url( $href, 'claim_listing', 'claim_listing_nonce' ) );
	?>
		<a href="<?php echo esc_url( $href ); ?>" class="claim-listing"><i class="ion-thumbsup"></i> <?php _e( 'Claim Listing', 'listify' ); ?></a>
	<?php
	}

}

$GLOBALS['listify_job_manager_claim_listing'] = new Listify_WP_Job_Manager_Claim_Listing();
