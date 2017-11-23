<?php
/**
 * WooCommerce - Social Login
 *
 * @since 1.7.0
 * @package Listify
 * @subpackage WooCommerce_Social_Login
 */
class Listify_WooCommerce_Social_Login extends Listify_Integration {

	/**
	 * Social Login instance.
	 *
	 * @var WC_Social_Login
	 * @access public
	 */
	public $wc_social_login;

	/**
	 * Define the integration.
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function __construct() {
		$this->integration = 'woocommerce-social-login';
		$this->includes = array();

		$this->wc_social_login = wc_social_login();

		parent::__construct();
	}

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function setup_actions() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Everything in this plugin happens after the `init` hook.
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function init() {
		remove_action( 'woocommerce_login_form_end', array( $this->wc_social_login->get_frontend_instance(), 'render_social_login_buttons' ) );

		add_action( 'woocommerce_login_form_start', array( $this->wc_social_login->get_frontend_instance(), 'render_social_login_buttons' ) );
		add_action( 'woocommerce_login_form_start', array( $this, 'render_social_login_buttons_divider' ), 11 );

		// Also add it in Listify login form.
		add_action( 'listify_login_form_before', array( $this, 'output' ) );
		add_action( 'listify_login_form_before', array( $this, 'render_social_login_buttons_divider' ), 11 );
	}

	/**
	 * Output buttons.
	 *
	 * Does not check if the buttons should be output in the particular area.
	 *
	 * @since 2.1.0
	 */
	public function output() {
		global $wp;

		$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );

		woocommerce_social_login_buttons( $current_url );
	}

	/**
	 * Add an "or" divider below the Social Login buttons.
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function render_social_login_buttons_divider() {
?>

<p class="wc-social-login-divider">
	<span><?php echo esc_attr( _x( 'or', 'social login divider', 'listify' ) ); ?></span>
</p>

<?php
	}

}

$GLOBALS['listify_woocommerce_social_login'] = new Listify_WooCommerce_Social_Login;
