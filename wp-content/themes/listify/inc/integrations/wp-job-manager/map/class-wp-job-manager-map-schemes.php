<?php
/**
 * Manage and apply Google Maps color schemes.
 *
 * @todo make it more obvious this is Google-specific.
 *
 * @since unknown
 */
class Listify_WP_Job_Manager_Map_Schemes {

	/**
	 * Hook in to WordPress
	 *
	 * @since unknown
	 */
	public function __construct() {
		add_filter( 'listify_map_settings', array( $this, 'apply_color_scheme' ) );
		add_filter( 'listify_single_map_settings', array( $this, 'apply_color_scheme' ) );

		add_action( 'wp_ajax_listify-customizer-map-scheme', array( $this, 'ajax_apply_color_scheme' ) );
	}

	/**
	 * Return default stylers.
	 *
	 * @since unknown
	 *
	 * @return array
	 */
	public function default_styles() {
		$default = apply_filters( 'listify_map_default_styles', array(
			array(
				'featureType' => 'poi',
				'stylers' => array(
					array(
						'visibility' => 'off',
					),
				),
			),
		) );

		return $default;
	}

	/**
	 * Return the JSON of the color scheme for AJAX.
	 *
	 * @since 1.7.0
	 * @return array
	 */
	public function ajax_apply_color_scheme() {
		$scheme = isset( $_POST['scheme'] ) ? esc_attr( $_POST['scheme'] ) : false;

		if ( ! $scheme ) {
			return wp_send_json_error();
		}

		add_filter( 'theme_mod_map-appearance-scheme', array( $this, 'ajax_preview_color_scheme' ) );

		$settings = $this->apply_color_scheme( array() );

		return wp_send_json_success( $settings['mapService'] );
	}

	/**
	 * Preview value for map color scheme.
	 *
	 * @since 1.7.0
	 * @return array
	 */
	public function ajax_preview_color_scheme( $value ) {
		$scheme = isset( $_POST['scheme'] ) ? esc_attr( $_POST['scheme'] ) : false;

		if ( $scheme ) {
			$value = $scheme;
		}

		return $value;
	}

	/**
	 * Decode chosen color scheme file and apply it to the JS settings that
	 * are sent to the scripts.
	 *
	 * @since 1.5.0
	 *
	 * @param array $settings
	 * @return array $settings
	 */
	public function apply_color_scheme( $settings ) {
		$scheme = get_theme_mod( 'map-appearance-scheme', 'blue-water' );
		$scheme = sanitize_title( $scheme ) . '.json';

		$styles = array();
		$file   = false;

		$custom = trailingslashit( get_stylesheet_directory() ) . $scheme;
		$included = trailingslashit( dirname( __FILE__ ) ) . trailingslashit( 'schemes' ) . $scheme;

		if ( file_exists( $custom ) ) {
			$file = @file_get_contents( $custom );
		} elseif ( file_exists( $included ) ) {
			$file = @file_get_contents( $included );
		}

		if ( $file ) {
			$styles = json_decode( $file, true );
		}

		$settings['mapService']['googlemaps']['styles'] = array_merge( $this->default_styles(), $styles );

		return $settings;
	}

}
