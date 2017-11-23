<?php
/**
 * Business Hour Field.
 * The $field var contain this data:
 * - label.
 * - type.
 * - required.
 * - placeholder.
 * - priority.
 * - default.
 * - value.
 * - gmt.
 * - timezone.
 *
 * @since unknown
 *
 * @var string $key The value is "job_hours".
 * @var array  $field Field data.
 */

// Load WP_Locale object.
global $wp_locale;

// Load required script.
wp_enqueue_script( 'timepicker' );
wp_enqueue_style( 'timepicker' ); // i know. whatevs.

// Var.
$days = listify_get_days();
$job_hours = listify_sanitize_business_hours( isset( $field['value'] ) ? $field['value'] : array() );
?>

<table>
	<tr>
		<th width="40%">&nbsp;</th>
		<th align="left"><?php _e( 'Open', 'listify' ); ?></th>
		<th align="left"><?php _e( 'Close', 'listify' ); ?></th>
	</tr>

	<?php foreach ( $days as $day_num => $day ) : ?>

		<tr class="business-hours" data-day="<?php echo esc_attr( $day ); ?>">

			<td align="left" class="business-day">
				<?php echo $wp_locale->get_weekday( $day_num ); ?> <a class="add-hours" href="#"><span aria-hidden="true">[+]</span><span class="screen-reader-text"><?php _e( 'Add hours' ); ?></span></a>
			</td>

			<td align="left" class="business-hour-open">

				<?php if ( isset( $job_hours[ $day ] ) && is_array( $job_hours[ $day ] ) ) : ?>
					<?php foreach ( $job_hours[ $day ] as $index => $hours ) :
						$hour = isset( $hours['open'] ) ? $hours['open'] : '';
						?>
						<input type="text" class="timepicker regular-text" name="job_hours[<?php echo $day; ?>][<?php echo $index; ?>][open]" value="<?php echo sanitize_text_field( $hour ); ?>" autocomplete="off"/>
					<?php endforeach; ?>
				<?php else : ?>
					<input type="text" class="timepicker regular-text" name="job_hours[<?php echo $day; ?>][0][open]" value="" autocomplete="off"/>
				<?php endif; ?>

			</td><!-- .business-hour-open -->

			<td align="left" class="business-hour-close">

				<?php if ( isset( $job_hours[ $day ] ) && is_array( $job_hours[ $day ] ) ) : ?>
					<?php foreach ( $job_hours[ $day ] as $index => $hours ) :
						$hour = isset( $hours['close'] ) ? $hours['close'] : '';
						?>
						<input type="text" class="timepicker regular-text" name="job_hours[<?php echo $day; ?>][<?php echo $index; ?>][close]" value="<?php echo sanitize_text_field( $hour ); ?>" autocomplete="off"/>
					<?php endforeach; ?>
				<?php else : ?>
					<input type="text" class="timepicker regular-text" name="job_hours[<?php echo $day; ?>][0][close]" value="" autocomplete="off"/>
				<?php endif; ?>

			</td><!-- .business-hour-close -->

		</tr><!-- .business-days -->

	<?php endforeach; ?>

	<tr>
		<td width="40%">
			<?php esc_html_e( 'Timezone', 'listify' ); ?>
		</td>
		<td colspan="2">
			<select class="business-hour-timezone widefat" name="job_hours_timezone">
				<?php echo wp_timezone_choice( $field['timezone'], get_user_locale() ); ?>
			</select>
		</td>
	</tr>

</table>

<script>
jQuery( document ).ready( function($) {

	function load_time_picker() {
		$( '.timepicker' ).timepicker( {
			timeFormat: '<?php echo str_replace( '\\', '\\\\', get_option( 'time_format' ) ); ?>',
			noneOption: [
					{
						label: '<?php _e( 'Closed', 'listify' ); ?>',
						value: 'Closed',
					},
					{
						label: '<?php _e( 'Open 24 Hours', 'listify' ); ?>',
						value: '24h',
					},
				],
		} );
	}
	load_time_picker();

	$( '.add-hours' ).click( function(e) {
		e.preventDefault();

		var row = $( this ).parents( '.business-hours' );
		var day = row.data( 'day' );
		var bh_open_el = row.find( '.business-hour-open' );
		var bh_close_el = row.find( '.business-hour-close' );

		// Add inputs.
		bh_open_el.append( '<input type="text" class="timepicker regular-text" value="" autocomplete="off"/>' );
		bh_close_el.append( '<input type="text" class="timepicker regular-text" value="" autocomplete="off"/>' );

		// Reindex and reset name attr.
		bh_open_el.find( 'input[type="text"]' ).each( function(i) {
			$( this ).attr( 'name', 'job_hours[' + day + '][' + i + '][open]');
		} );
		bh_close_el.find( 'input[type="text"]' ).each( function(i) {
			$( this ).attr( 'name', 'job_hours[' + day + '][' + i + '][close]');
		} );

		// re-init time picker.
		load_time_picker();

	} );
} );
</script>
