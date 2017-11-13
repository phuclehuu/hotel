<?php
/**
 * WooCommerce
 */

class Listify_WooCommerce extends listify_Integration {

	/**
	 * @var object $template
	 * @access public
	 */
	public $template;

	public function __construct() {
		$this->includes = array(
			'class-woocommerce-template.php',
			'class-woocommerce-template-account.php',
		);

		$this->integration = 'woocommerce';

		parent::__construct();
	}

	public function setup_actions() {
		// customers are people too!
		remove_action( 'template_redirect', 'wc_disable_author_archives_for_customers' );

		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		add_action( 'widgets_init', array( $this, 'widgets_init' ) );

		add_action( 'pre_get_posts', array( $this, 'hide_packages_from_shop' ), 99 );

		add_action( 'woocommerce_edit_account_form', array( $this, 'woocommerce_edit_account_form' ) );
		add_action( 'woocommerce_save_account_details', array( $this, 'woocommerce_save_account_details' ) );

		add_filter( 'woocommerce_products_widget_query_args', array( $this, 'hide_packages_from_products_widget' ) );

		// Login popup trigger for myaccount link.
		add_filter( 'listify_js_settings', array( $this, 'add_myaccount_login_popup_trigger' ) );
	}

	public function after_setup_theme() {
		$this->template = new Listify_WooCommerce_Template;

		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	public function widgets_init() {

		register_sidebar( listify_register_sidebar_args( 'widget-area-sidebar-product' ) );

		register_sidebar( listify_register_sidebar_args( 'widget-area-sidebar-shop' ) );
	}

	public function hide_packages_from_shop( $query ) {
		if ( ! $query->is_main_query() || ! $query->is_post_type_archive() ) {
			return;
		}

		if ( is_admin() ) {
			return;
		}

		if ( is_shop() || is_search() ) {
			$tax_query = array(
				'taxonomy' => 'product_type',
				'field'    => 'slug',
				'terms'    => array( 'job_package', 'job_package_subscription' ),
				'operator' => 'NOT IN',
			);

			$query->query_vars['tax_query'][] = $tax_query;
		}
	}

	public function login_url( $url, $redirect ) {
		$url = add_query_arg( '_wp_http_referer', $redirect, get_permalink( wc_get_page_id( 'myaccount' ) ) );

		return esc_url( $url );
	}

	public function woocommerce_edit_account_form() {
		$user = wp_get_current_user();
?>

<fieldset>
	<legend><?php _e( 'Biography', 'listify' ); ?></legend>

	<p class="form-row form-row-wide">
		<label for="biography" class="screen-reader-text"><?php _e( 'Biography', 'listify' ); ?></label>
		<textarea class="input-text" name="biography" id="biography"><?php echo esc_textarea( $user->description ); ?></textarea>
	</p>
</fieldset>

<?php
if ( 'user' !== listify_get_social_profile_association() ) {
	return;
}

	$methods = wp_get_user_contact_methods( get_current_user_id() );

if ( ! empty( $methods ) ) :
?>

<fieldset>
<legend><?php _e( 'Social Profiles', 'listify' ); ?></legend>

<?php foreach ( $methods as $method => $label ) : ?>
		<p class="form-row form-row-wide">
			<label for="<?php echo esc_attr( $method ); ?>"><?php echo esc_attr( $label ); ?></label>
			<input type="text" class="input-text" name="<?php echo esc_attr( $method ); ?>" id="<?php echo esc_attr( $method ); ?>" value="<?php echo esc_attr( $user->$method ); ?>" />
		</p>
	<?php endforeach; ?>
</fieldset>

<?php endif; ?>

<?php
	}

	public function woocommerce_save_account_details( $user_id ) {
		if ( isset( $_POST['biography'] ) ) {
			$biography = esc_textarea( $_POST['biography'] );

			update_user_meta( $user_id, 'description', $biography );
		}

		if ( 'user' !== listify_get_social_profile_association() ) {
			return $user_id;
		}

		$methods = wp_get_user_contact_methods( get_current_user_id() );

		if ( empty( $methods ) ) {
			return;
		}

		foreach ( $methods as $method => $label ) {
			$value = isset( $_POST[ $method ] ) ? esc_url( $_POST[ $method ] ) : null;

			update_user_meta( $user_id, $method, $value );
		}
	}

	/**
	 * Hide listing package products from the Recent Products widget.
	 *
	 * @since 1.8.0
	 *
	 * @param array $query_args
	 * @return array $query_args
	 */
	public function hide_packages_from_products_widget( $query_args ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'product_type',
			'field'    => 'slug',
			'terms'    => array( 'job_package', 'job_package_subscription' ),
			'operator' => 'NOT IN',
		);

		return $query_args;
	}

	/**
	 * My Account Login Popup Trigger
	 *
	 * @since 2.3.0
	 *
	 * @param array $settings Setting.
	 * @return array
	 */
	public function add_myaccount_login_popup_trigger( $settings ) {
		$wc_account_url = get_permalink( get_option('woocommerce_myaccount_page_id') );

		if ( ! is_user_logged_in() && $wc_account_url ) {
			$settings['loginPopupLink'][] = 'a[href="' . $wc_account_url . '"]';
		}

		return $settings;
	}

}

$GLOBALS['listify_woocommerce'] = new Listify_WooCommerce();
