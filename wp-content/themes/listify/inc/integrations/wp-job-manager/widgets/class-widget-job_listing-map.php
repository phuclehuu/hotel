<?php
/**
 * Job Listing: Map
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Listing_Map extends Listify_Widget {

	public function __construct() {
		$this->widget_description = __( 'Display the listing location and contact details.', 'listify' );
		$this->widget_id          = 'listify_widget_panel_listing_map';
		$this->widget_name        = __( 'Listify - Listing: Map & Contact Details', 'listify' );
		$this->widget_areas       = array( 'single-job_listing-widget-area', 'single-job_listing' );
		$this->widget_notice      = __( 'Add this widget only in "Single Listing" widget areas.' );
		$this->settings           = array(
			'map' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Map', 'listify' ),
			),
			'address' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Address', 'listify' ),
			),
			'phone' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Phone Number', 'listify' ),
			),
			'email' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Email', 'listify' ),
			),
			'web' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display Website', 'listify' ),
			),
			'directions' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display "Get Directions"', 'listify' ),
			),
		);

		parent::__construct();
	}

	function widget( $args, $instance ) {
		global $job_preview;

		if ( ! is_singular( 'job_listing' ) && ! $job_preview ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.
			return false;
		}

		extract( $args );

		$listing = listify_get_listing();

		$fields = array( 'map', 'address', 'phone', 'email', 'web', 'directions' );

		foreach ( $fields as $field ) {
			$$field = ( isset( $instance[ $field ] ) && 1 == $instance[ $field ] ) || ! isset( $instance[ $field ] ) ? true : false;
		}

		// map also needs location data
		$map = $map && $listing->get_lat();
		$map_behavior_api_key = listify_get_google_maps_api_key();

		// figure out split
		$just_directions = $directions && ! ( $web || $address || $phone || $email );
		$split = $map && ! $just_directions && ( $phone || $web || $address || $directions || $email ) ? 'map-widget-section--split' : '';

		/* Check if data available */
		$_email     = $listing->get_email();
		$_url       = $listing->get_url();
		$_location  = $listing->get_location( 'raw' );
		$_phone     = $listing->get_telephone();

		$email      = $_email ? $email : false;
		$web        = $_url ? $web : false;
		$address    = $_location ? $address : false;
		$directions = $_location ? $directions : false;
		$phone      = $_phone ? $phone : false;

		ob_start();

		/* Only load if data exists */
		if ( ( $map && $map_behavior_api_key ) || $phone || $web || $address || $directions ) {
			echo $before_widget;
?>

<div class="map-widget-sections">

	<?php if ( $map && $map_behavior_api_key ) : ?>
	<div class="map-widget-section <?php echo $split; ?>">
		<div id="listing-contact-map"></div>
	</div>
	<?php endif; ?>

	<?php if ( $phone || $web || $address || $directions ) : ?>
	<div class="map-widget-section <?php echo $split; ?>">

		<?php
			do_action( 'listify_widget_job_listing_map_before' );

		if ( $address ) :
			listify_the_listing_location();
			endif;

		if ( $phone ) :
			listify_the_listing_telephone();
			endif;

		if ( $email ) :
			listify_the_listing_email();
			endif;

		if ( $web ) :
			listify_the_listing_url();
			endif;

		if ( $directions ) :
			listify_the_listing_directions_form();
			endif;

			do_action( 'listify_widget_job_listing_map_after' );
		?>

	</div>
	<?php endif; ?>

</div>

<?php
			echo $after_widget;
		} // End if().

		$content = ob_get_clean();
		echo apply_filters( $this->widget_id, $content );

		add_filter( 'listify_page_needs_map', '__return_false' );
	}
}
