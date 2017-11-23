<?php
/**
 * Map output.
 *
 * @since 1.0.0
 * @version 2.2.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<?php do_action( 'listify_map_before' ); ?>

<div class="job_listings-map-wrapper listings-map-wrapper--<?php echo esc_attr( get_theme_mod( 'listing-archive-map-position', 'side' ) ); ?>">
	<?php do_action( 'listify_map_above' ); ?>

	<div class="job_listings-map" data-service="<?php echo esc_attr( get_theme_mod( 'map-service-provider', 'google' ) ); ?>">
		<div id="job_listings-map-canvas"></div>

		<?php echo listify_get_search_this_location_button(); ?>
	</div>

	<?php do_action( 'listify_map_below' ); ?>
</div>

<?php do_action( 'listify_map_after' ); ?>
