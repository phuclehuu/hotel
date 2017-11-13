<?php
/**
 * Determine which Listing child class to load based on the
 * active integration.
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Listing
 * @author Astoundify
 */
class Listify_Listing_Factory {

	/**
	 * Get a single listing.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $listing Get a listing.
	 */
	public function get_listing( $listing ) {
		$integration = $this->get_integration();

		if ( ! $integration ) {
			return false;
		}

		$classname = $this->get_listing_classname( $integration );

		if ( ! class_exists( $classname ) ) {
			return false;
		}

		return new $classname( $listing );
	}

	/**
	 * Get the current active integration.
	 *
	 * @since 2.0.0
	 *
	 * @return null|string
	 */
	public function get_integration() {
		$integration = null;

		if ( listify_has_integration( 'wp-job-manager' ) ) {
			$integration = 'wp-job-manager';
		}

		return $integration;
	}

	/**
	 * Get the classname based on the active integration.
	 *
	 * @since 2.0.0
	 *
	 * @param string $integration The current integration.
	 * @return string
	 */
	public function get_listing_classname( $integration ) {
		$classname = '';

		if ( 'wp-job-manager' === $integration ) {
			$classname = 'Listify_WP_Job_Manager_Listing';
		}

		return $classname;
	}

}
