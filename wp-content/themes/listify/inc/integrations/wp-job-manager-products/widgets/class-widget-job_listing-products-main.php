<?php
/**
 * Job Listing: Products
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Listing_Products_Main extends Listify_Widget {

	public function __construct() {
		$this->widget_description = __( 'Display the listings products (main content)', 'listify' );
		$this->widget_id          = 'listify_widget_panel_listing_products_main';
		$this->widget_name        = __( 'Listify - Listing: Products (Content)', 'listify' );
		$this->widget_areas       = array( 'single-job_listing-widget-area' );
		$this->widget_notice      = __( 'Add this widget only in "Single Listing - Main Content" widget area.' );
		$this->settings           = array(
			array(
				'type' => 'description',
				'std' => __( 'This widget has no options', 'listify' ),
			),
		);

		$wpjmp = WPJMP();

		remove_action( 'single_job_listing_end', array( $wpjmp->products, 'listing_display_products' ) );

		parent::__construct();
	}

	function widget( $args, $instance ) {
		global $job_preview, $job_manager, $post;

		if ( ! is_singular( 'job_listing' ) && ! $job_preview ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.
			return false;
		}

		if ( 'preview' == $post->post_status ) {
			return;
		}

		extract( $args );

		$wpjmp = WPJMP();

		ob_start();

		$wpjmp->products->listing_display_products();

		echo apply_filters( $this->widget_id, ob_get_clean() );
	}
}
