<?php
/**
 * Author: Biography
 *
 * @since 1.7.0
 *
 * @package Listify
 * @category Widget
 * @author Astoundify
 */
class Listify_Widget_Author_Biography extends Listify_Widget {

	/**
	 * Register widget settings.
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		$this->widget_description = __( 'Author biography', 'listify' );
		$this->widget_id          = 'listify_widget_author_biography';
		$this->widget_name        = __( 'Listify - Author: Biography', 'listify' );
		$this->widget_areas       = array( 'widget-area-author-main', 'widget-area-author-sidebar' );
		$this->widget_notice      = __( 'Add this widget only in "Author - Main Content" and "Author - Sidebar" widget area.' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title:', 'listify' ),
			),
			'icon' => array(
				'type'    => 'text',
				'std'     => '',
				'label'   => '<a href="http://ionicons.com/">' . __( 'Icon Class:', 'listify' ) . '</a>',
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

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : false, $instance, $this->id_base );
		$icon = isset( $instance['icon'] ) ? $instance['icon'] : null;
		$bio = get_the_author_meta( 'description', get_queried_object_id() );

		if ( '' === $bio ) {
			return;
		}

		ob_start();

		echo $args['before_widget']; // WPCS: XSS ok.

		if ( $title ) {
			if ( $icon ) {
				$icon = str_replace( 'ion-', '', $icon );
				$args['before_title'] = sprintf( $args['before_title'], 'ion-' . $icon );
			}

			echo $args['before_title'] . $title . $args['after_title']; // WPCS: XSS ok.
		}

		echo wp_kses_post( $bio );

		echo $args['after_widget']; // WPCS: XSS ok.

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content ); // WPCS: XSS ok.
	}

}
