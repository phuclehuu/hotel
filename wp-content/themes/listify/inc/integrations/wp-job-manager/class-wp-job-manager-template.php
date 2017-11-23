<?php
/**
 * Handles all "template" related items for Job Manager.
 *
 * @package Listify
 */
class Listify_WP_Job_Manager_Template extends Listify_Integration {

	/**
	 * Register integration.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->includes = array();
		$this->integration = 'wp-job-manager';

		$this->is_home = false;

		parent::__construct();
	}

	/**
	 * This is quite large because the majorify of the templates
	 * are built through actions. This allows them to be unhooked
	 * or rearranged farily easily.
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		// WordPress template loader.
		add_filter( 'template_include', array( $this, 'template_include' ) );

		// WP Job Manager template loder.
		add_filter( 'job_manager_locate_template', array( $this, 'locate_template' ), 10, 3 );

		// Body class suppliments.
		add_filter( 'body_class', array( $this, 'body_class' ) );

		// Register widget areas/widgets
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );

		// breadcrumb links
		add_filter( 'term_links-job_listing_category', array( $this, 'term_links' ) );
		add_filter( 'term_links-job_listing_type', array( $this, 'term_links' ) );

		// output the results
		add_action( 'listify_output_results', array( $this, 'output_results' ) );
	}

	/**
	 * Check if we are on a Job Manager-related taxonomy. If so, load
	 * the standard job listing archive which will handle it all.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template
	 * @return string $template
	 */
	public function template_include( $template ) {
		$this->is_home = listify_is_widgetized_page();
		$taxes = apply_filters( 'listify_job_listing_taxonomies', array(
			'job_listing_category',
			'job_listing_type',
			'job_listing_tag',
			'job_listing_region',
		) );

		if ( is_tax( $taxes ) ) {
			$template = locate_template( array( 'archive-job_listing.php' ) );

			if ( '' != $template ) {
				return $template;
			}
		}

		return $template;
	}

	/**
	 * Job Manager template loader suppliment. Any time Job Manager looks for
	 * a template file it will also check the /templates/ directory in this
	 * integration directory
	 *
	 * @since 1.0.0
	 *
	 * @param string $template
	 * @param string $template_name
	 * @param string $template_path
	 * @return string $template
	 */
	public function locate_template( $template, $template_name, $template_path ) {
		global $job_manager;

		if ( ! file_exists( $template ) ) {
			$default_path = listify_Integration::get_dir() . 'templates/';

			$template = $default_path . $template_name;
		}

		return $template;
	}

	/**
	 * Add supplimentary body classes so we can target certain things.
	 *
	 * @since 1.0.0
	 *
	 * @param array $classes
	 * @return array $classes
	 */
	public function body_class( $classes ) {
		global $wp_query;

		$categories = true;

		$categories = get_option( 'job_manager_enable_categories' );
		$categories = $categories && ! is_tax( 'job_listing_category' );

		if ( $categories ) {
			$classes[] = 'wp-job-manager-categories-enabled';

			if ( get_option( 'job_manager_enable_default_category_multiselect' ) && ! listify_is_widgetized_page() ) {
				$classes[] = 'wp-job-manager-categories-multi-enabled';
			}
		}

		if ( 'map' == listify_theme_mod( 'listing-archive-output', 'map-results' ) ) {
			$classes[] = 'listing-archive-display-map-only';
		}

		if ( isset( $wp_query->query_vars['gallery'] ) ) {
			$classes[] = 'single-job_listing-gallery';
		}

		if ( ! listify_theme_mod( 'gallery-comments', true ) ) {
			$classes[] = 'no-gallery-comments';
		}

		if ( listify_is_job_manager_archive() ) {
			$classes[] = 'job-manager-archive';
		}

		return $classes;
	}

	/**
	 * Register widgets and sidebar areas.
	 *
	 * @since 1.0.0
	 */
	public function widgets_init() {
		global $listify_strings;

		$widgets = array(
			'job_listing-content.php',
			'job_listing-comments.php',
			'job_listing-gallery.php',
			'job_listing-gallery-slider.php',
			'job_listing-map.php',
			'job_listing-business-hours.php',
			'job_listing-author.php',
			'job_listing-video.php',
			'job_listing-related-listings.php',
			'job_listing-social-profiles.php',

			'home-recent-listings.php',
			'home-search-listings.php',
			'home-term-lists.php',
			'home-tabbed-listings.php',
			'home-taxonomy-image-grid.php',
			'home-map-listings.php',
		);

		foreach ( $widgets as $widget ) {
			include_once( listify_Integration::get_dir() . 'widgets/class-widget-' . $widget );
		}

		register_widget( 'Listify_Widget_Listing_Content' );
		register_widget( 'Listify_Widget_Listing_Comments' );
		register_widget( 'Listify_Widget_Listing_Gallery' );
		register_widget( 'Listify_Widget_Listing_Gallery_Slider' );
		register_widget( 'Listify_Widget_Listing_Map' );
		register_widget( 'Listify_Widget_Listing_Business_Hours' );
		register_widget( 'Listify_Widget_Listing_Author' );
		register_widget( 'Listify_Widget_Listing_Video' );
		register_widget( 'Listify_Widget_Listing_Social_Profiles' );

		if ( get_option( 'job_manager_enable_categories', true ) ) {
			register_widget( 'Listify_Widget_Listing_Related_Listings' );
		}

		register_widget( 'Listify_Widget_Recent_Listings' );
		register_widget( 'Listify_Widget_Search_Listings' );
		register_widget( 'Listify_Widget_Taxonomy_Image_Grid' );
		register_widget( 'Listify_Widget_Map_Listings' );
		register_widget( 'Listify_Widget_Tabbed_Listings' );
		register_widget( 'Listify_Widget_Term_Lists' );

		unregister_widget( 'WP_Job_Manager_Widget_Recent_Jobs' );
		unregister_widget( 'WP_Job_Manager_Widget_Featured_Jobs' );

		register_sidebar( listify_register_sidebar_args( 'archive-job_listing' ) );
		register_sidebar( listify_register_sidebar_args( 'single-job_listing-widget-area' ) );
		register_sidebar( listify_register_sidebar_args( 'single-job_listing' ) );
	}

	/**
	 * Add schema data to term links
	 *
	 * @since 1.0.0
	 *
	 * @param array $term_links
	 * @return array $links
	 */
	public function term_links( $term_links ) {
		$links = array();

		foreach ( $term_links as $link ) {
			$link = str_replace( 'rel="tag">', 'rel="tag">', $link );
			$link = str_replace( '</a>', '</a>', $link );

			$links[] = $link;
		}

		return $links;
	}

	/**
	 * When viewing an archive output the Job Manager-specific
	 * archive results. In this case, we load the job shortcode.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function output_results( $content ) {
		if ( '' != $content ) {
			echo do_shortcode( $content );
		} else {
			echo do_shortcode( apply_filters( 'listify_default_jobs_shortcode', '[jobs show_pagination=true]' ) );
		}
	}

}
