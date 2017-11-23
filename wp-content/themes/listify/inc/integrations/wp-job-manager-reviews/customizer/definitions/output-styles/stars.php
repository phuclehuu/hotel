<?php
/**
 * Output booking form colors
 *
 * @since 1.8.0
 * @package Customizer
 */
class
	Listify_Customizer_OutputCSS_WPJobManagerReviews
extends
	Listify_Customizer_OutputCSS {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add items to the CSS object that will be built and output.
	 *
	 * @since 1.8.0
	 */
	public function output() {
		/**
		 * Star
		 */
		$star = listify_theme_color( 'color-listing-star' );

		Listify_Customizer_CSS::add( array(
			'selectors' => array(
				'.stars-rating .dashicons',
				'.review-form-stars [class*="dashicon"]',
				'.listing-star',
			),
			'declarations' => array(
				'color' => esc_attr( $star ),
			),
		) );
	}

}

new Listify_Customizer_OutputCSS_WPJobManagerReviews();
