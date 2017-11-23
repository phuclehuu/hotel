<?php
/**
 * Submission process for WP Job Manager.
 *
 * @since 2.2.0
 *
 * @package Listify
 * @category WP Job Manager
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add a class to the page's `post_class()` so we know we are on the
 * [submit_job] shortcode.
 *
 * @since 2.2.0
 *
 * @param array $classes Current classes.
 * @return array $classes
 */
function listify_wp_job_manager_submit_post_class( $classes ) {
	// Don't do anything if we are not on the submission page.
	if ( ! get_option( 'job_manager_submit_job_form_page_id' ) || ! is_page( get_option( 'job_manager_submit_job_form_page_id' ) ) ) {
		return $classes;
	}

	// Don't do anything if there is no package selection.
	if ( ! ( listify_has_integration( 'wp-job-manager-listing-payments' ) || listify_has_integration( 'wp-job-manager-wc-paid-listings' ) ) ) {
		return $classes;
	}

	// Don't do anything if there is no package selection.
	if ( isset( $_REQUEST['job_id'] ) || isset( $_REQUEST['job_package'] ) || isset( $_REQUEST['choose_package']) ) {
		return $classes;
	}

	$classes[] = 'listify-wp-job-manager-package-selection';

	return $classes;
}
add_filter( 'post_class', 'listify_wp_job_manager_submit_post_class' );
