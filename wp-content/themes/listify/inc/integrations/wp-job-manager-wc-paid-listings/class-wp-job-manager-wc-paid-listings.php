<?php
/**
 * WooCommerce
 */

class Listify_WP_Job_Manager_WCPL extends listify_Integration {

	public function __construct() {
		$this->includes = array();
		$this->integration = 'wp-job-manager-wc-paid-listings';

		parent::__construct();
	}

	public function setup_actions() {
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
		add_action( 'wp_footer', array( $this, 'package_selection' ) );
	}

	public function widgets_init() {
		$widgets = array(
			'pricing-table.php'
		);

		foreach ( $widgets as $widget ) {
			include_once( listify_Integration::get_dir() . 'widgets/class-widget-' . $widget );
		}

		register_widget( 'Listify_Widget_WCPL_Pricing_Table' );
	}

	public function package_selection() {
		if ( ! isset( $_GET['selected_package'] ) ) {
			return;
		}

		$package = absint( $_GET['selected_package'] );

		echo '<input type="hidden" id="listify_selected_package" value="' . $package . '" />';
	}

}

$GLOBALS['listify_job_manager_wc_paid_listings'] = new Listify_WP_Job_Manager_WCPL();
