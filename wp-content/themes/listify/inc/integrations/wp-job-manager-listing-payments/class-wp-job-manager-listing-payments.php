<?php
/**
 * Listing Payments for WP Job Manager
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Integration
 * @author Astoundify
 */
class Listify_WP_Job_Manager_Listing_Payments extends Listify_Integration {

	/**
	 * Register integration.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->includes = array();
		$this->integration = 'wp-job-manager-listing-payments';

		parent::__construct();
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 2.0.0
	 */
	public function setup_actions() {
		add_action( 'widgets_init', array( $this, 'widgets_init' ), 9 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'add_to_cart_url' ), 10, 2 );
	}

	/**
	 * Register widgets.
	 *
	 * @since 2.0.0
	 */
	public function widgets_init() {
		// To avoid duplicating the entire pricing table widget.
		include_once( get_template_directory() . '/inc/integrations/wp-job-manager-wc-paid-listings/widgets/class-widget-pricing-table.php' );

		register_widget( 'Listify_Widget_WCPL_Pricing_Table' );
	}

	/**
	 * Update "Add to Cart" button URL on page templates to submission page.
	 *
	 * @since 2.0.0
	 *
	 * @param string     $url The URL to the cart.
	 * @param WC_Product $product The current product.
	 * @return string
	 */
	public function add_to_cart_url( $url, $product ) {
		if ( ! ( is_page_template( 'page-templates/template-plans-pricing.php' ) || listify_is_widgetized_page() ) ) {
			return $url;
		}

		if ( ! in_array( $product->product_type, array( 'subscription', 'job_package', 'job_package_subscription' ), true ) ) {
			return $url;
		}

		$submit = job_manager_get_permalink( 'submit_job_form' );

		if ( '' === $submit ) {
			return $url;
		}

		$url = add_query_arg( 'chosen_package', $product->get_id(), $submit );

		return esc_url( $url );
	}

}

new Listify_WP_Job_Manager_Listing_Payments();
