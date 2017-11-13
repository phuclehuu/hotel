<?php
/**
 * Author favorites.
 *
 * @since Listify 2.0.0
 *
 * @package Listify
 * @category Widget
 * @author Astoundify
 */
class Listify_Widget_Author_Favorites extends Listify_Widget {

	/**
	 * Register widget settings.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->widget_description = __( 'Author Favorites', 'listify' );
		$this->widget_id          = 'listify_widget_author_favorites';
		$this->widget_name        = __( 'Listify - Author: Favorites', 'listify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '[username]&#39;s Favorites ([count])',
				'label' => __( 'Title:', 'listify' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @since 1.7.0
	 * @access public
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '[username]&#39;s Favorites ([count])', $instance, $this->id_base );

		// Gather favorites to query listings for.
		$_favorites = array();
		$favorites = new \Astoundify\Favorites\Favorite_Query( array(
			'user_id' => get_queried_object_id(),
			'item_per_page' => -1,
		) );

		foreach ( $favorites->favorites as $favorite ) {
			$_favorites[] = $favorite->get_target_id();
		}

		if ( ! $_favorites ) {
			return;
		}

		$listings = listify_get_listings( '#widget-author-favorites .job_listings', array(
			'post__in' => $_favorites,
			'update_post_term_cache' => false,
		) );

		if ( ! $listings ) {
			return;
		}

		ob_start();

		echo $args['before_widget']; // WPCS: XSS ok.

		if ( $title ) {
			$title = str_replace(
				array( '[username]', '[count]' ),
				array( get_the_author_meta( 'display_name', get_queried_object_id() ), count( $favorites->favorites ) ),
				$title
			);

			echo $args['before_title'] . $title . $args['after_title']; // WPCS: XSS ok.
		}

		echo '<div id="widget-author-favorites"><ul class="job_listings"></ul></div>'; // WPCS: XSS ok.

		echo $args['after_widget']; // WPCS: XSS ok.

		echo apply_filters( $this->widget_id, ob_get_clean() ); // WPCS: XSS ok.
	}

}
