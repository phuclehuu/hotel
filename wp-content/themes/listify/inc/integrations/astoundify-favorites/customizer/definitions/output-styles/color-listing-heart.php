<?php
/**
 * Output favorites colors
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Customize
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Output colors.
 *
 * @since 2.0.0
 */
class Listify_Favorites_Output_Color_Listing_Heart extends Listify_Customizer_OutputCSS {

	/**
	 * Add items to the CSS object that will be built and output.
	 *
	 * @since 2.0.0
	 */
	public function output() {
		$heart = listify_theme_color( 'color-listing-heart' );

		Listify_Customizer_CSS::add( array(
			'selectors' => array(
				'.astoundify-favorites-link.active .astoundify-favorites-icon svg',
			),
			'declarations' => array(
				'fill' => esc_attr( $heart ),
			),
		) );
	}

}

new Listify_Favorites_Output_Color_Listing_Heart();
