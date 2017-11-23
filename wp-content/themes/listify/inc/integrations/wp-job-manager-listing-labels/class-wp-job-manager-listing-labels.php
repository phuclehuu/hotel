<?php
/**
 * Listing Labels for WP Job Manager
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Integration
 * @author Astoundify
 */
class Listify_WP_Job_Manager_Listing_Labels extends Listify_Integration {

	/**
	 * Register integration.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->integration = 'wp-job-manager-listing-labels';
		$this->includes = array(
			'widgets/class-widget-job_listing-listing-labels.php',
		);

		parent::__construct();
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 2.0.0
	 */
	public function setup_actions() {
		// Init hook.
		add_action( 'init', array( $this, 'init' ), -1 );

		// Job description.
		remove_filter( 'the_job_description', array( 'Astoundify\WPJobManager\ListingLabels\Frontend', 'render' ) );

		// Register widgets.
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
	}

	/**
	 * Init hook.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'astoundify_wpjmll_taxonomy', array( $this, 'set_taxonomy' ) );
		add_filter( 'astoundify_wpjmll_taxonomy_permalink', array( $this, 'set_permalink' ) );
	}

	/**
	 * Set taxonomy.
	 *
	 * This need to be loaded in init hook, but before the taxonomy registered (priority 10).
	 * It is set to 'job_listing_tag' for back-compat.
	 *
	 * @since 2.0.0
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return string $taxonomy Taxonomy slug.
	 */
	public function set_taxonomy( $taxonomy ) {
		return 'job_listing_tag';
	}

	/**
	 * Set taxonomy permalink slug.
	 *
	 * @since 2.0.0
	 *
	 * @param string $slug Taxonomy permalink slug.
	 * @return string $slug Taxonomy permalink slug.
	 */
	public function set_permalink( $slug ) {
		global $listify_strings;

		return sprintf( '%s-tag', $listify_strings->label( 'singular' ) );
	}

	/**
	 * Register widgets.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function widgets_init() {
		register_widget( 'Listify_Widget_Listing_Labels' );
	}

}

new Listify_WP_Job_Manager_Listing_Labels();
