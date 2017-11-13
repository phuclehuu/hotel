<?php
/**
 * Polylang
 */

class Listify_Polylang extends Listify_Integration {

	/**
	 * Constructor.
	 *
	 * @since 2.1.0
	 */
	public function __construct() {
		$this->includes = array();
		$this->integration = 'polylang';

		parent::__construct();
	}

	/**
	 * Setup Actions.
	 *
	 * @since 2.1.0
	 */
	public function setup_actions() {

		// Register polylang strings.
		add_action( 'init', array( $this, 'register_strings' ), 5 );

		// Translatable string.
		add_action( 'template_redirect', array( $this, 'translatable_theme_mods_names' ) );
	}

	/**
	 * Translatable Theme Mods Names.
	 *
	 * @since 2.1.0
	 *
	 * @return array List of translatable theme mod names.
	 */
	public static function translatable_theme_mods_names() {
		$names = array(
			'as-seen-on-title',
			'copyright-text',
			'content-login-title',
			'content-register-title',
			'label-singular',
			'label-plural',
		);
		return apply_filters( 'listify_ppl_translatable_theme_mods_names', $names );
	}

	/**
	 * Register Polylang Strings Translations.
	 *
	 * @since 2.1.0
	 * @link https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
	 *
	 * @return void
	 */
	public function register_strings() {
		$strings = array();

		// Get translatable theme mods.
		$names = self::translatable_theme_mods_names();

		// Add all. No need to check empty value, PPL will do the check.
		foreach ( $names as $name ) {
			$strings[] = get_theme_mod( $name );
		}

		// Make filterable.
		$strings = apply_filters( 'listify_ppl_strings', $strings );

		// Register each strings.
		foreach ( $strings as $string ) {
			pll_register_string( 'Listify', $string, 'Listify' );
		}
	}

	/**
	 * Translate Strings.
	 * Loaded in template redirect to make sure it's loaded as late as possible.
	 *
	 * @since 2.1.0
	 * @link https://developer.wordpress.org/reference/functions/get_theme_mod/
	 *
	 * @return void
	 */
	public function translate_template_strings() {
		// Translateable theme mod names.
		$names = self::translatable_theme_mods_names();

		// Translate all by filtering theme mod output.
		foreach ( $names as $name ) {
			add_filter( "theme_mod_{$name}", array( $this, 'translate_string' ) );
		}

	}

	/**
	 * Translate string.
	 *
	 * @since 2.1.0
	 *
	 * @param string $string Text string to translate.
	 * @return string
	 */
	public function translate_string( $string ) {
		if ( is_string( $string ) ) {
			$string = pll__( $string );
		}
		return $string;
	}

}

$GLOBALS['listify_polylang'] = new Listify_Polylang();
