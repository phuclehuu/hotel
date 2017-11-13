<?php
/**
 * Output booking form colors
 *
 * @since 1.8.0
 * @package Customizer
 */
class
	Listify_Customizer_OutputCSS_BookingForm
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
		$body_text_color = listify_theme_color( 'color-body-text' );

		Listify_Customizer_CSS::add( array(
			'selectors' => array(
				'#wc-bookings-booking-form label',
				'#wc-bookings-booking-form .wc-bookings-date-picker .ui-datepicker-header',
				'#wc-bookings-booking-form .wc-bookings-date-picker .ui-datepicker td.bookable a',
				'#wc-bookings-booking-form .wc-bookings-date-picker .ui-datepicker td.bookable span',
			),
			'declarations' => array(
				'color' => esc_attr( $body_text_color ) . ' !important',
			),
		) );

		if ( ! in_array( get_theme_mod( 'color-scheme' ), array( 'ultra-dark' ) ) ) {
			Listify_Customizer_CSS::add( array(
				'selectors' => array(
					'.listify_widget_panel_listing_bookings .price .amount'
				),
				'declarations' => array(
					'color' => esc_attr( Listify_Customizer_CSS::darken( $body_text_color, -20 ) ) . ' !important',
				),
			) );
		}

		$primary = listify_theme_color( 'color-primary' );

		Listify_Customizer_CSS::add( array(
			'selectors' => array(
				'#wc-bookings-booking-form .wc-bookings-date-picker .ui-datepicker td.ui-datepicker-current-day a',
				'#wc-bookings-booking-form .wc-bookings-date-picker .ui-datepicker td.bookable-range .ui-state-default',
			),
			'declarations' => array(
				'color' => '#ffffff !important',
				'background-color' => esc_attr( $primary ) . ' !important',
			),
		) );
	}

}

new Listify_Customizer_OutputCSS_BookingForm();
