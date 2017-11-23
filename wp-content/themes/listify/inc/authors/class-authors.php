<?php
/**
 * Manage authors on the frontend.
 *
 * Deals mostly with the `author.php` template file.
 *
 * @since 1.7.0
 *
 * @package Listify
 * @category Authors
 * @author Astoundify
 */
class Listify_Authors {

	/**
	 * Hooks/filters for the WordPress API.
	 *
	 * @since 1.7.0
	 */
	public static function setup_actions() {
		// Register widgets and sidebars.
		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_sidebars' ) );

		// Add some template output to author.php.
		add_action( 'listify_author_meta', array( __CLASS__, 'author_meta' ) );

		// Filter the default WP_Widget_Recent_Posts query.
		add_filter( 'widget_posts_args', array( __CLASS__, 'widget_post_args' ) );
	}

	/**
	 * Register the widgets for the main content and sidebar of the
	 * author.php page template.
	 *
	 * @since 1.7.0
	 */
	public static function register_widgets() {
		$widgets = array(
			'class-widget-author-biography.php',
			'class-widget-author-listings.php',
		);

		foreach ( $widgets as $file ) {
			require( dirname( __FILE__ ) . '/widgets/' . $file );
		}

		register_widget( 'Listify_Widget_Author_Biography' );
		register_widget( 'Listify_Widget_Author_Listings' );
	}

	/**
	 * Register the sidebars for the main content and sidebar of the
	 * author.php page template.
	 *
	 * @since 1.7.0
	 */
	public static function register_sidebars() {

		register_sidebar( listify_register_sidebar_args( 'widget-area-author-main' ) );

		register_sidebar( listify_register_sidebar_args( 'widget-area-author-sidebar' ) );
	}

	/**
	 * Additional Author Meta
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public static function author_meta() {
		echo '<span class="listing-count">';

		// Translators: %d Number of listings.
		printf( esc_attr__( '%d Listed', 'listify' ), absint( listify_count_posts( 'job_listing', get_queried_object_id() ) ) );

		echo '</span>';
	}

	/**
	 * When on the author.php page template filter the use of the Recent Posts
	 * widget to only include blog posts by the author being viewed.
	 *
	 * @since 1.7.0
	 * @param array $query_args Current WP_Query arguments.
	 * @return array $query_args
	 */
	public static function widget_post_args( $query_args ) {
		if ( ! is_author() ) {
			return $query_args;
		}

		$query_args['author__in'] = array( get_queried_object_id() );

		return $query_args;
	}

}

Listify_Authors::setup_actions();
