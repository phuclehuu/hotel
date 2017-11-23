<?php
/**
 * Listing Business Hours.
 *
 * @since unknown
 * @package Listify
 */
class Listify_WP_Job_Manager_Business_Hours extends Listify_Integration {

	/**
	 * Constructor Class
	 *
	 * @since unknown
	 */
	public function __construct() {

		// No file to include.
		$this->includes = array();

		// WPJM integration.
		$this->integration = 'wp-job-manager';

		// Load parent constructor.
		parent::__construct();
	}

	/**
	 * Setup Action
	 *
	 * @since unknown.
	 */
	public function setup_actions() {

		// Front end field.
		add_filter( 'submit_job_form_fields', array( $this, 'front_end_fields' ) );

		// Get current value of front end field.
		add_filter( 'submit_job_form_fields_get_job_data', array( $this, 'front_end_fields_data' ), 10, 2 );

		// Save data.
		add_action( 'job_manager_update_job_data', array( $this, 'save_data' ), 10, 2 );
		add_action( 'job_manager_save_job_listing', array( $this, 'save_data' ), 10, 2 );

		// Writepanel meta box fields.
		add_action( 'listify_writepanels_business_hours', array( $this, 'business_hors_meta_box' ) );

		// Register scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * Field Args.
	 *
	 * @since 2.0.0
	 */
	public function field_args() {
		$args = array(
			'label'       => __( 'Hours of Operation', 'listify' ),
			'type'        => 'business-hours', // "form-fields/business-hours-field.php".
			'required'    => false,
			'placeholder' => '',
			'priority'    => 4.9,
			'default'     => '',
			'gmt'         => get_option( 'gmt_offset' ),
			'timezone'    => get_option( 'timezone_string' ),
		);
		return $args;
	}

	/**
	 * Front End Field.
	 * Using custom type "business-hours", it will load field template based on type.
	 * In this case "form-fields/business-hours-field.php".
	 * This field template is loaded in job-submit.php template.
	 *
	 * @since 2.0.0
	 *
	 * @param array $fields Form fields.
	 * @return array
	 */
	public function front_end_fields( $fields ) {
		$fields['job']['job_hours'] = $this->field_args();
		return $fields;
	}

	/**
	 * Get front end field value.
	 *
	 * @since 2.0.0
	 *
	 * @param array  $fields All fields data, field name as key.
	 * @param object $job Listing data WP_Post.
	 */
	public function front_end_fields_data( $fields, $job ) {
		$listing = listify_get_listing( $job );
		$hours = $listing->get_business_hours();

		if ( ! $hours ) {
			return $fields;
		}

		$fields['job']['job_hours']['value'] = $hours;
		$fields['job']['job_hours']['gmt'] = $listing->get_business_hours_gmt();
		$fields['job']['job_hours']['timezone'] = $listing->get_business_hours_timezone();

		return $fields;
	}

	/**
	 * Save data.
	 * This will save on post update both in front end and admin writepanel.
	 *
	 * @since 2.0.0
	 * @see wp_timezone_override_offset()
	 *
	 * @param int   $job_id Listing ID.
	 * @param array $values Fields value.
	 * @return void
	 */
	public function save_data( $job_id, $values ) {
		// Save Opening Hours
		if ( isset( $_POST['job_hours'] ) ) {
			update_post_meta( $job_id, '_job_hours', listify_sanitize_business_hours( $_POST['job_hours'] ) );
		}

		// Save Timezone data.
		if ( isset( $_POST['job_hours_timezone'] ) && ! empty( $_POST['job_hours_timezone'] ) ) {
			$timezone = esc_attr( $_POST['job_hours_timezone'] );

			// Use Manual UTC timezone.
			if ( preg_match( '/^UTC[+-]/', $timezone ) ) {
				$gmt_offset = $timezone;
				$gmt_offset = preg_replace( '/UTC\+?/', '', $gmt_offset );
			} else { // Use string UTC Timezone e.g "Asia/Jakarta" @see 
				$timezone_object = timezone_open( $timezone );
				$datetime_object = date_create();
				if ( false !== $timezone_object && false !== $datetime_object ) {
					$gmt_offset = round( timezone_offset_get( $timezone_object, $datetime_object ) / HOUR_IN_SECONDS, 2 );
				}
			}

			update_post_meta( $job_id, '_job_hours_timezone', $timezone );
			update_post_meta( $job_id, '_job_hours_gmt', $gmt_offset );
		}

	}

	/**
	 * Business hours meta box HTML.
	 * The "listify_business_hours" meta box is registered in writepanels class.
	 *
	 * @since 2.0.0
	 * @see /class-wp-job-manager-writepanels.php
	 *
	 * @param object $post WP_Post object.
	 * @return void
	 */
	public function business_hors_meta_box( $post ) {
		$listing = listify_get_listing( $post );
		$field = $this->field_args();
		$field['value'] = listify_sanitize_business_hours( $listing->get_business_hours() );
		$field['gmt'] = $listing->get_business_hours_gmt();
		$field['timezone'] = $listing->get_business_hours_timezone();
?>
	<div class="form-field" style="position: relative;">

		<?php get_job_manager_template( 'form-fields/business-hours-field.php', array(
			'key'   => 'job_hours',
			'field' => $field,
		) ); ?>

		<style>
			.business-day {
				vertical-align: baseline;
			}
			.add-hours {
				text-decoration: none;
			}
		</style>

	</div><!-- .form-field -->
<?php
	}

	/**
	 * Load Script in Job Listing Edit Screen.
	 *
	 * @since 2.0.0
	 */
	public function register_scripts() {
		wp_register_script( 'timepicker', Listify_Integration::get_url() . 'js/vendor/timepicker/jquery.timepicker.min.js' , array( 'jquery' ) );
		wp_register_style( 'timepicker', Listify_Integration::get_url() . 'js/vendor/timepicker/jquery.timepicker.css' );
	}

}
