<?php
/**
 * Home: Recent Listings
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Recent_Listings extends Listify_Widget {

	public function __construct() {
		$this->widget_description = __( 'Display a grid of recent or featured listings', 'listify' );
		$this->widget_id          = 'listify_widget_recent_listings';
		$this->widget_name        = __( 'Listify - Page: Listings', 'listify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => 'Listings',
				'label' => __( 'Title:', 'listify' ),
			),
			'description' => array(
				'type'  => 'text',
				'std'   => 'Discover some of our best listings',
				'label' => __( 'Description:', 'listify' ),
			),
			'featured' => array(
				'type'  => 'checkbox',
				'std'   => 0,
				'label' => __( 'Show only featured listings', 'listify' ),
			),
			'sort' => array(
				'type'  => 'select',
				'std'   => 'date-desc',
				'label' => __( 'Sort listings:', 'listify' ),
				'options' => listify_get_sort_options(),
			),
			'categories' => array(
				'label' => __( 'Show listings in these categories:', 'listify' ),
				'type' => 'multicheck-term',
				'std'  => '',
				'options' => listify_get_top_level_taxonomy(),
			),
			'keywords' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Search Keywords:', 'listify' ),
			),
			'limit' => array(
				'type'  => 'number',
				'std'   => 3,
				'min'   => 3,
				'max'   => 30,
				'step'  => 3,
				'label' => __( 'Number to show:', 'listify' ),
			),
		);
		parent::__construct();
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$description = isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : false;
		$featured = isset( $instance['featured'] ) && '1' === $instance['featured'] ? true : null;
		$sort_default = isset( $instance['random'] ) && '1' === $instance['random'] ? 'rand' : 'date-desc'; // Back compat.
		$this->sort_option = isset( $instance['sort'] ) && array_key_exists( $instance['sort'], listify_get_sort_options() ) ? $instance['sort'] : $sort_default;
		$limit = isset( $instance['limit'] ) ? absint( $instance['limit'] ) : 3;
		$categories = isset( $instance['categories'] ) ? maybe_unserialize( $instance['categories'] ) : false;
		$keywords = isset( $instance['keywords'] ) ? esc_attr( $instance['keywords'] ) : '';

		if ( $description && ( isset( $args['id'] ) && 'widget-area-home' === $args['id'] ) ) {
			$after_title = str_replace( '</div>', '', $after_title ) . '<p class="home-widget-description">' . $description . '</p></div>';
		}

		// Listing args.
		$listing_args = array(
			'posts_per_page'         => $limit,
			'featured'               => $featured,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'orderby'                => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'order'                  => 'DESC',
			'search_categories'      => $categories,
			'search_keywords'        => $keywords,
		);

		// Get listings.
		add_filter( 'job_manager_get_listings', array( $this, 'sort_filter' ), 10, 2 );
		$listings = listify_get_listings( '#' . $this->id . ' ul.job_listings', $listing_args );
		remove_filter( 'job_manager_get_listings', array( $this, 'sort_filter' ), 10, 2 );

		if ( ! $listings ) {
			return;
		}

		ob_start();

		echo $before_widget; // WPCS: XSS ok.

		if ( $title ) {
			echo $before_title . $title . $after_title; // WPCS: XSS ok.
		}

		echo '<div id="' . $this->id . '"><ul class="job_listings"></ul></div>'; // WPCS: XSS ok.

		echo $after_widget; // WPCS: XSS ok.

		wp_reset_postdata();

		echo apply_filters( $this->widget_id, ob_get_clean() ); // WPCS: XSS ok.
	}

	/**
	 * Filter Job Query Based on Selected Sort Option.
	 *
	 * @since 2.1.0
	 *
	 * @param array $query_args WP_Query Args.
	 * @param array $args       Get Job Listing Args.
	 * @return array WP_Query Args.
	 */
	public function sort_filter( $query_args, $args ) {
		$query_args = listify_sort_listings_query( $query_args, $this->sort_option );

		return $query_args;
	}

}
