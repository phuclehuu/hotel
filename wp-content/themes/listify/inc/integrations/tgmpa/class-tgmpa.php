<?php
/**
 * TGMPA Integration
 *
 * @since 2.0.0
 *
 * @link http://tgmpluginactivation.com/
 **/
class Listify_TGMPA extends Listify_Integration {

	public function __construct() {
		$this->includes = array(
			'class-tgm-plugin-activation.php',
		);
		$this->integration = 'tgmpa';

		parent::__construct();
	}

	public function setup_actions() {

		// TGMPA Plugin Activation Setup
		add_action( 'tgmpa_register', array( __CLASS__, 'tgmpa_register' ), 20 );

	}

	/**
	 * Register Plugin Activation
	 *
	 * @since 2.0.0
	 */
	public static function tgmpa_register() {
		$plugins = array(
			array(
				'name'      => 'WP Job Manager',
				'slug'      => 'wp-job-manager',
				'required'  => true,
			),
			array(
				'name'      => 'WooCommerce',
				'slug'      => 'woocommerce',
				'required'  => true,
			),
			array(
				'name'      => 'WP Job Manager - Predefined Regions',
				'slug'      => 'wp-job-manager-locations',
				'required'  => false,
			),
			array(
				'name'      => 'WP Job Manager - Contact Listing',
				'slug'      => 'wp-job-manager-contact-listing',
				'required'  => false,
			),
			array(
				'name'      => 'Ninja Forms',
				'slug'      => 'ninja-forms',
				'required'  => false,
			),
			array(
				'name'      => 'If Menu',
				'slug'      => 'if-menu',
				'required'  => false,
			),
		);

		$config = array(
			'id' => 'listify',
			'has_notices' => false,
			'parent_slug' => Astoundify_Setup_Guide::get_page_id(),
			'is_automatic' => true,
			'force_activation' => true,
		);

		tgmpa( $plugins, $config );
	}
}

$GLOBALS['listify_tgmpa'] = new Listify_TGMPA();
