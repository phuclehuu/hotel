<?php
/**
 * Output homepage information.
 *
 * @since 2.0.0
 * @package Customizer
 */

class Listify_Customizer_OutputCSS_Home extends Listify_Customizer_OutputCSS {

	/**
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Add items to the CSS object that will be built and output.
	 *
	 * @since 2.0.0
	 */
	public function output() {
		Listify_Customizer_CSS::add( array(
			'selectors' => array(
				'.homepage-cover',
			),
			'declarations' => array(
				'background-attachment' => get_theme_mod( 'home-hero-image-attachment', 'initial' ),
			),
			'media' => 'screen and (min-width: 992px)',
		) );
	}

}

new Listify_Customizer_OutputCSS_Home();
