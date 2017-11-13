<?php
/**
 * Author: Listings
 *
 * @since 1.7.0
 *
 * @package Listify
 * @category Widget
 * @author Astoundify
 */
class Listify_Widget_Author_Listings extends Listify_Widget {

	/**
	 * Register widget settings.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$this->widget_description = __( 'Author recent listings.', 'listify' );
		$this->widget_id          = 'listify_widget_author_listings';
		$this->widget_name        = __( 'Listify - Author: Recent Listings', 'listify' );
		$this->widget_areas       = array( 'widget-area-author-main' ); // Valid widget areas.
		$this->widget_notice      = __( 'Add this widget only in "Author - Main Content" widget area.' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '[username]&#39;s Recent Listings',
				'label' => __( 'Title:', 'listify' ),
			),
			'per_page' => array(
				'type'  => 'number',
				'std'   => 3,
				'min'   => 3,
				'max'   => 30,
				'step'  => 3,
				'label' => __( 'Number of listings:', 'listify' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		// Check context.
		if ( ! is_author() ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.

			return false;
		}

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '[username]&#39;s Recent Listings', $instance, $this->id_base );
		$per_page = isset( $instance['per_page'] ) ? absint( $instance['per_page'] ) : 3;

		add_filter( 'get_job_listings_query_args', array( $this, 'current_author' ) );

		$listings = listify_get_listings( '#widget-author-recent-listings .job_listings', array(
			'posts_per_page' => $per_page,
			'update_post_term_cache' => false,
		) );

		remove_filter( 'get_job_listings_query_args', array( $this, 'current_author' ) );

		if ( ! $listings ) {
			return;
		}

		echo $args['before_widget']; // WPCS: XSS ok.

		if ( $title ) {
			$title = str_replace(
				array( '[username]' ),
				array( get_the_author_meta( 'display_name', get_queried_object_id() ) ),
				$title
			);

			echo $args['before_title'] . $title . $args['after_title']; // WPCS: XSS ok.
		}

		echo '<div id="widget-author-recent-listings"><ul class="job_listings"></ul></div>'; // WPCS: XSS ok.

		echo $args['after_widget']; // WPCS: XSS ok.

		echo apply_filters( $this->widget_id, ob_get_clean() ); // WPCS: XSS ok.
	}

	/**
	 * Restrict listings to the current author.
	 *
	 * Updates WP_Query made in `get_job_listings()`
	 *
	 * @since 2.0.3
	 *
	 * @param array $query_args Current query arguments.
	 * @return array $query_args Modified query arguments.
	 */
	public function current_author( $query_args ) {
		$query_args['author'] = get_queried_object_id();

		return $query_args;
	}

}
