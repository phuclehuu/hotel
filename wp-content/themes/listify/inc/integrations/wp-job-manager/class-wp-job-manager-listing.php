<?php
/**
 * WP Job Manager listing implementation.
 *
 * @since 2.0.0
 */
class Listify_WP_Job_Manager_Listing extends Listify_Listing {

	/**
	 * Return the URL to the logo
	 *
	 * This is not the "Company Logo" that is default with WP Job Manager. Instead
	 * it is a separate File Upload field that is stored as a plain URL.
	 *
	 * @since 2.0.0
	 */
	public function get_company_logo( $args = array() ) {
		$defaults = array(
			'size' => 'medium',
		);

		$args = wp_parse_args( $args, $defaults );

		$custom_field = apply_filters( 'listify_company_logo_field', '_company_avatar' );

		$logo = $this->get_object()->$custom_field;

		if ( ! empty( $logo ) && ( strstr( $logo, 'http' ) || file_exists( $logo ) ) ) {
			if ( $args['size'] !== 'full' ) {
				$logo = job_manager_get_resized_image( $logo, $args['size'] );
			}
		}

		$logo = $logo ? $logo : false;

		return $logo;
	}

	/**
	 * Phone Number
	 *
	 * @since 2.0.0
	 *
	 * @param $sanitize Strip non-numeric characters or not.
	 * @return null|string
	 */
	public function get_telephone( $sanitize = false ) {
		$phone = $this->get_object()->_phone;

		if ( $sanitize ) {
			$phone = preg_replace( '/[^0-9,.,+]/', '', $phone );
		}

		return apply_filters( 'listify_get_listing_telephone', $phone, $sanitize, $this );
	}

	/**
	 * Email
	 *
	 * @since 2.0.0
	 *
	 * @return null|string
	 */
	public function get_email() {
		$email = $this->get_object()->_application;

		if ( ! is_email( $email ) ) {
			return null;
		}

		return $email;
	}

	/**
	 * URL
	 *
	 * @since 2.0.0
	 *
	 * @return null|string
	 */
	public function get_url() {
		$url = get_the_company_website( $this->get_object()->ID );

		if ( ! $url ) {
			return;
		}

		return esc_url( $url );
	}

	/**
	 * Location
	 *
	 * @since 2.0.0
	 *
	 * @param string|false $context False for default, "raw" for unfiltered location data.
	 * @return string
	 */
	public function get_location( $context = false ) {
		return 'raw' === $context ? $this->get_object()->_job_location : get_the_job_location( $this->get_id() );
	}

	/**
	 * Get Location Formatted.
	 *
	 * @since 2.2.1
	 *
	 * @param string $format Location Format.
	 * @return string
	 */
	public function get_location_formatted( $format = null ) {
		return apply_filters( 'the_job_location', parent::get_location_formatted( $format ), $this->get_object() );
	}

	/**
	 * Latitude
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_lat() {
		return $this->get_object()->geolocation_lat ? $this->get_object()->geolocation_lat : null;
	}

	/**
	 * Longitude
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_lng() {
		return $this->get_object()->geolocation_long ? $this->get_object()->geolocation_long : null;
	}

	/**
	 * Get location data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_location_data() {
		return array(
			'address_1' => $this->get_object()->geolocation_street,
			'address_2' => '',
			'street_number' => $this->get_object()->geolocation_street_number,
			'city' => $this->get_object()->geolocation_city,
			'state' => $this->get_object()->geolocation_state_short,
			'full_state' => $this->get_object()->geolocation_state_long,
			'postcode' => $this->get_object()->geolocation_postcode,
			'country' => $this->get_object()->geolocation_country_short,
			'full_country' => $this->get_object()->geolocation_country_long,
			'latitude'  => $this->get_object()->geolocation_latitude,
			'longitude'  => $this->get_object()->geolocation_longitude,
		);
	}


	/**
	 * Get Business Hours
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function get_business_hours() {
		return listify_sanitize_business_hours( get_post_meta( $this->get_id(), '_job_hours', true ) );
	}

	/**
	 * Get Business Hours Timezone
	 *
	 * @since 2.1.0
	 *
	 * @param bool $display Remove underscore for display in front end.
	 * @return string
	 */
	public function get_business_hours_timezone( $display = false ) {
		$post_timezone_string = $this->get_object()->_job_hours_timezone;
		if ( ! $post_timezone_string ) {
			$post_timezone_string = get_option( 'timezone_string' );
		}

		if (! $post_timezone_string ) {
			$post_gmt_offset = $this->get_business_hours_gmt();
			if ( 0 == $post_gmt_offset ) {
				$post_timezone_string = 'UTC+0';
			} elseif ( $post_gmt_offset < 0 ) {
				$post_timezone_string = 'UTC' . $post_gmt_offset;
			} else {
				$post_timezone_string = 'UTC+' . $post_gmt_offset;
			}
		}

		// Clean up underscores.
		if ( $display ) {
			$post_timezone_string =  str_replace( '_', ' ', $post_timezone_string );
		}
		return $post_timezone_string;
	}

	/**
	 * Get Business Hours GMT
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	public function get_business_hours_gmt() {
		$post_gmt_offset = $this->get_object()->_job_hours_gmt;
		if ( ! $post_gmt_offset ) {
			$post_gmt_offset = get_option( 'gmt_offset' );
		}
		return intval( $post_gmt_offset );
	}

	/**
	 * Featured?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_featured() {
		return (bool) apply_filters( 'listify_get_listing_is_featured', is_position_featured( $this->post ), $this );
	}

	/**
	 * Filled?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_filled() {
		return (bool) apply_filters( 'listify_get_listing_is_filled', is_position_filled( $this->post ), $this );
	}

	/**
	 * Claimed?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_claimed() {
		return (bool) apply_filters( 'listify_get_listing_is_claimed', $this->get_object()->_claimed, $this );
	}

}
