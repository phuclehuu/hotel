<?php
/**
 * Job Listing: Amenity
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Listing_Amenity extends Listify_Widget {

	/**
	 * Register widget and settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->widget_description = __( 'Display the listing amenity.', 'listify' );
		$this->widget_id          = 'listify_widget_panel_listing_amenity';
		$this->widget_name        = __( 'Listify - Listing: Amenity', 'listify' );
		$this->widget_areas       = array( 'single-job_listing-widget-area', 'single-job_listing' );
		$this->widget_notice      = __( 'Add this widget only in "Single Listing" widget areas.' );
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
	 * Output the widget amenity on the page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		global $job_preview, $job_manager, $wp_embed;

		if ( ! is_singular( 'job_listing' ) && ! $job_preview ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.
			return false;
		}

		extract( $args );

		if ( '' == get_the_content() ) {
			return;
		}

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$icon = isset( $instance['icon'] ) ? $instance['icon'] : null;

		if ( $icon ) {
			if ( strpos( $icon, 'ion-' ) !== false ) {
				$before_title = sprintf( $before_title, $icon );
			} else {
				$before_title = sprintf( $before_title, 'ion-' . $icon );
			}
		}

		ob_start();

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		/*
		$cats = get_the_term_list(
			get_post()->ID,
			'job_listing_category',
			'<span>',
			', ',
			'</span>'
		);
		*/
		$amenity_str = '';
		$amenity = get_the_terms(get_post()->ID,'job_listing_category'); //array object
		foreach ($amenity as $item) {
			$amenity_str.='<span class="amenity">';

			$icons = get_option("templtax_".$item->term_id);
			$img = '';
			if(is_array($icons)){
				foreach($icons as $size => $attach_id) {
					if($attach_id > 0) { 
						$img = wp_get_attachment_image($attach_id,'templ_icon_small');
					}
				}
			}

			/*if($item->term_font_icon != '0'){
				$amenity_str .= $item->term_font_icon;
			}*/
			if($img!='' && $item->term_type == 'templ_upload_img'){
				$amenity_str .= $img;
			} else {
				if($item->term_font_icon != '0'){
					$amenity_str .= $item->term_font_icon;
				}
			}
			$amenity_str .= $item->name;
			$amenity_str.='</span>';
		}
		echo $amenity_str;

		echo $after_widget;

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}
}
