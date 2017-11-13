<?php
/**
 * Job Listing: Business Hours
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Listing_Business_Hours extends Listify_Widget {

	public function __construct() {
		$this->widget_description = __( 'Display the business hours of the listing.', 'listify' );
		$this->widget_id          = 'listify_widget_panel_listing_business_hours';
		$this->widget_name        = __( 'Listify - Listing: Business Hours', 'listify' );
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
				'std'     => 'ion-clock',
				'label'   => '<a href="http://ionicons.com/">' . __( 'Icon Class:', 'listify' ) . '</a>',
			),
		);

		parent::__construct();
	}

	function widget( $args, $instance ) {
		global $job_preview, $job_manager;

		if ( ! is_singular( 'job_listing' ) && ! $job_preview ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.
			return false;
		}

		extract( $args );

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$icon = isset( $instance['icon'] ) ? $instance['icon'] : null;

		if ( $icon ) {
			if ( strpos( $icon, 'ion-' ) !== false ) {
				$before_title = sprintf( $before_title, $icon );
			} else {
				$before_title = sprintf( $before_title, 'ion-' . $icon );
			}
		}

		// Get listing data.
		$listing = listify_get_listing( get_post() );

		// Bail if no job hours data.
		$job_hours = $listing->get_business_hours();
		if ( ! $job_hours ) {
			return;
		}

		// Load WP_Locale object.
		global $wp_locale;

		// Loop all job hours and remove empty hours.
		foreach ( $job_hours as $day => $hours ) {
			foreach ( $hours as $index => $hour ) {
				if ( ! $hour['open'] || ! $hour['close'] ) {
					unset( $job_hours[ $day ][ $index ] );
				}
			}
		}
		// Remove empty days.
		foreach ( $job_hours as $day => $hours ) {
			if ( ! $hours ) {
				unset( $job_hours[ $day ] );
			}
		}

		// Empty days, bail.
		if ( empty( $job_hours ) ) {
			return;
		}

		// Timezone & GMT.
		$timezone = $listing->get_business_hours_timezone( true ); // For display.
		$gmt = $listing->get_business_hours_gmt();
		if ( 0 == $gmt ) {
			$gmt = 'UTC+0';
		} elseif ( $gmt < 0 ) {
			$gmt = 'UTC' . $gmt;
		} else {
			$gmt = 'UTC+' . $gmt;
		}
		if ( preg_match( '/^UTC[+-]/', $timezone ) ) {
			$timezone = sprintf( __( 'Timezone: %1$s', 'listify' ), $timezone );
		} else {
			$timezone = sprintf( __( 'Timezone: %1$s (%2$s)', 'listify' ), $timezone, $gmt );
		}

		// Open/Closed Status.
		if ( $title ) {
			if ( null !== $listing->is_open() ) {
				if ( $listing->is_open() ) {
					$text = __( 'Now Open', 'listify' );
					$class = 'business-hour-status business-hour-status-open';
				} else {
					$text = __( 'Closed', 'listify' );
					$class = 'business-hour-status business-hour-status-closed';
				}
				$title = $title . ' <span class="' . esc_attr( $class ) . '" title="' . esc_attr( $timezone ) . '">' . $text . '</span>';
			}
		}

		ob_start();

		echo $before_widget;

		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		// Get days.
		$days = listify_get_days();

		do_action( 'listify_widget_job_listing_hours_before' );
?>

<?php foreach ( $days as $day_num => $day ) : ?>
	<?php if ( isset( $job_hours[ $day ] ) ) : ?>

		<?php foreach ( $job_hours[ $day ] as $index => $hour ) : ?>
			<p class="business-hour">

				<?php if ( 0 === $index ) : // Only display day on first hours. ?>
					<span class="day">
					<?php echo $wp_locale->get_weekday( $day_num ); ?>
					</span>
				<?php else : ?>
					<span class="day duplicate-day"><?php echo $wp_locale->get_weekday( $day_num ); ?></span>
				<?php endif; ?>

				<span class="business-hour-time">
					<?php if ( 'Closed' === $hour['open'] ) : ?>
						<?php _e( 'Closed', 'listify' ); ?>
					<?php elseif ( '24h' === $hour['open'] ) : ?>
						<?php _e( 'Open 24 Hours', 'listify' ); ?>
					<?php else : ?>
						<span class="start"><?php echo $hour['open']; ?></span> &ndash; <span class="end"><?php echo $hour['close']; ?></span>
					<?php endif; ?>
				</span>
			</p><!-- .business-hour -->
		<?php endforeach; ?>

	<?php endif; ?>
<?php endforeach; ?>

<?php
		do_action( 'listify_widget_job_listing_hours_after' );

		echo $after_widget;

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}
}
