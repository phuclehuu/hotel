<?php
/**
 * WP Job Manager - Predefined Regions
 */

class Listify_WP_Job_Manager_Regions extends listify_Integration {

	public function __construct() {
		$this->includes = array();
		$this->integration = 'wp-job-manager-regions';

		parent::__construct();
	}

	public function setup_actions() {
		// Remove search this location button.
		add_filter( 'listify_search_this_location_button', array( $this, 'disable_search_this_location_button' ) );
	}

	/**
	 * Disable search this location button if region filter enabled.
	 *
	 * @since 2.2.1
	 *
	 * @param string $button Button HTML.
	 * @return string
	 */
	public function disable_search_this_location_button( $button ) {
		return get_option( 'job_manager_regions_filter', false ) ? '' : $button;
	}
}

$GLOBALS['listify_job_manager_regions'] = new Listify_WP_Job_Manager_Regions();
