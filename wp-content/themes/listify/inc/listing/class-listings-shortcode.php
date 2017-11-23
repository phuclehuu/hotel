<?php
/**
 * Listings Shortcode.
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Listing
 * @author Astoundify
 */
class Listings_Shortcode {

	/**
	 * Register.
	 *
	 * @since 2.0.0
	 */
	public static function register() {
		add_action( 'init', array( __CLASS__, 'add_shortcodes' ) );
	}

	/**
	 * Add Shortcodes.
	 *
	 * @since 2.0.0
	 */
	public static function add_shortcodes() {
		add_shortcode( 'listings', array( __CLASS__, 'listings_shortcode_callback' ) );
	}

	/**
	 * Listings Shortcodes Callback.
	 *
	 * @since 2.0.0
	 */
	public static function listings_shortcode_callback( $atts ) {
		$atts = shortcode_atts( array(
			'ids' => '', // Listings IDs, comma separated.
		), $atts );

		// Get listings.
		$args = array();
		if ( $atts['ids'] ) {
			$args['post__in'] = wp_parse_id_list( $atts['ids'] );
		}
		listify_get_listings( '#listings-shortcode-' . md5( serialize( $atts ) ), $args );

		ob_start();
?>
	<ul id="listings-shortcode-<?php echo md5( serialize( $atts ) ); ?>" class="listings-shortcode">
	</ul>
<?php
		return ob_get_clean();
	}

}

// Load class.
Listings_Shortcode::register();
