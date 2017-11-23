<?php
/**
 * WP Job Manager Products
 */

class Listify_WP_Job_Manager_Products extends Listify_Integration {

	public function __construct() {
		$this->has_customizer = true;
		$this->includes = array();
		$this->integration = 'wp-job-manager-products';

		parent::__construct();
	}

	public function setup_actions() {
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );
	}

	public function widgets_init() {
		$widgets = array(
			'job_listing-products.php',
			'job_listing-products-main.php',
		);

		foreach ( $widgets as $widget ) {
			include_once( listify_Integration::get_dir() . 'widgets/class-widget-' . $widget );
		}

		register_widget( 'Listify_Widget_Listing_Products' );
		register_widget( 'Listify_Widget_Listing_Products_Main' );
	}

	/**
	 * Get the base product out of all associated products.
	 *
	 * Get teh lowest price product.
	 *
	 * @since 1.8.0
	 *
	 * @return string $price
	 */
	public function get_base_product( $listing_id = false ) {
		if ( ! $listing_id ) {
			$listing_id = get_post()->ID;
		}

		$products = wpjmp_get_products( $listing_id );

		if ( empty( $products ) ) {
			return false;
		}

		$min_price = 0;
		$base_product = false;

		foreach ( $products as $product ) {
			$product = wc_get_product( $product );

			if ( $product->get_price() < $min_price || 0 == $min_price ) {
				$min_price = $product->get_price();
				$base_product = $product;
			}
		}

		return $base_product;
	}

	/**
	 * Listing "Price"
	 *
	 * No direct price is associated with a listing; use the products instead.
	 *
	 * @since Listify 1.8.0
	 */
	public function the_price() {
		$product = $this->get_base_product();

		if ( ! $product ) {
			return;
		}
	?>

<div class="job_listing-price">
		<?php echo $product->get_price_html(); ?>
</div>

	<?php
	}

	/**
	 * Listing "Attribute"
	 *
	 * No direct attribute is associated with a listing; use the products instead.
	 *
	 * @since Listify 1.8.0
	 */
	public function the_attribute() {
		$product = $this->get_base_product();

		if ( ! $product ) {
			return;
		}

		$attributes = $product->get_attributes();

		if ( empty( $attributes ) || ! $attributes ) {
			return;
		}

		$attribute = current( $attributes );

		if ( $attribute['is_taxonomy'] ) {
			$values = wc_get_product_terms( $product->get_id(), $attribute['name'], array(
				'fields' => 'names',
			) );
			$attribute = apply_filters( 'woocommerce_attribute', wptexturize( implode( ', ', $values ) ), $attribute, $values );
		} else {
			// Convert pipes to commas and display values
			$values = array_map( 'trim', explode( WC_DELIMITER, $attribute['value'] ) );
			$attribute = apply_filters( 'woocommerce_attribute', wptexturize( implode( ', ', $values ) ), $attribute, $values );
		}
	?>

<div class="job_listing-attribute">
	<?php echo esc_attr( $attribute ); ?>
</div>

	<?php
	}
}

$GLOBALS['listify_job_manager_products'] = new Listify_WP_Job_Manager_Products();
