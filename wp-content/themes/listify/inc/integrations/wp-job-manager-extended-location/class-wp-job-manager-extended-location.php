<?php
/**
 * Extended Locations for WP Job Manager.
 *
 * @since 2.3.0
 *
 * @package Listify
 * @category Integration
 * @author Astoundify
 */

/**
 * WP Job Manager - Extended Location
 *
 * @since 2.3.0
 */
class Listify_WP_Job_Manager_Extended_Location extends Listify_Integration {

	/**
	 * Constructor
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		$this->includes = array();
		$this->integration = 'wp-job-manager-extended-location';

		parent::__construct();
	}

	/**
	 * Setup Action
	 *
	 * @since 2.3.0
	 */
	public function setup_actions() {

		// Get Direction Field.
		add_filter( 'listify_listing_directions_destination_type', array( $this, 'get_direction_type' ) );
	}

	/**
	 * Change Get Direction Field Using Coordinate.
	 *
	 * @since 2.3.0
	 *
	 * @param string $type Destination type, "coordinate" or "address".
	 * @return string
	 */
	public function get_direction_type( $type ) {
		return 'coordinate';
	}
}

$GLOBALS['listify_job_manager_extended_location'] = new Listify_WP_Job_Manager_Extended_Location();
