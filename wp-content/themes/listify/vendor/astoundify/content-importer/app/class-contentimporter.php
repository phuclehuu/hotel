<?php
/**
 * Import content via JSON files for easier immediate reference and manipulation.
 *
 * @since 1.0.0
 * @package Astoundify_ContentImporter
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Astoundify_ContentImporter' ) ) :
	/**
	 * Main ContentImporter Class.
	 *
	 * @class Astoundify_ContentImporter
	 * @since 1.0.0
	 * @version 1.0.0
	 */
	class Astoundify_ContentImporter {

		/**
		 * The single class instance.
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object
		 */
		private static $_instance = null;

		/**
		 * The strings used for any output in the drop-ins.
		 *
		 * @since 1.0.0
		 * @access public
		 * @var array
		 */
		public static $strings = array();

		/**
		 * The URL to where the import is located.
		 *
		 * @since 1.1.0
		 * @access public
		 * @var string
		 */
		public static $url;

		/**
		 * Library Version to where the import is located.
		 *
		 * @since 1.1.0
		 * @access public
		 * @var string
		 */
		public static $version = '2.0.0';

		/**
		 * Static instance of Astoundify_ContentImporter
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @see Astoundify_ContentImporter
		 *
		 * @since 1.0.0
		 * @static
		 * @return object The one true Astoundify_Content_Importer
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
				self::init();
			}

			return self::$_instance;
		}

		/**
		 * Set the strings to be used inside the other drop in files.
		 *
		 * @since 1.0.0
		 * @return self::$strings
		 */
		public static function set_strings( $strings = array() ) {
			$defaults = array(
				'type_labels' => array(
					'setting' => array(
						__( 'Setting', 'astoundify-contentimporter' ),
						__( 'Settings', 'astoundify-contentimporter' ),
					),
					'thememod' => array(
						__( 'Theme Customization', 'astoundify-contentimporter' ),
						__( 'Theme Customizations', 'astoundify-contentimporter' ),
					),
					'nav-menu' => array(
						__( 'Navigation Menu', 'astoundify-contentimporter' ),
						__( 'Navigation Menus', 'astoundify-contentimporter' ),
					),
					'term' => array(
						__( 'Term', 'astoundify-contentimporter' ),
						__( 'Terms', 'astoundify-contentimporter' ),
					),
					'object' => array(
						__( 'Content', 'astoundify-contentimporter' ),
						__( 'Contents', 'astoundify-contentimporter' ),
					),
					'nav-menu-item' => array(
						__( 'Navigation Menu Item', 'astoundify-contentimporter' ),
						__( 'Navigation Menu Items', 'astoundify-contentimporter' ),
					),
					'widget' => array(
						__( 'Widget', 'astoundify-contentimporter' ),
						__( 'Widgets', 'astoundify-contentimporter' ),
					),
					'comment' => array(
						__( 'Comment', 'astoundify-contentimporter' ),
						__( 'Comments', 'astoundify-contentimporter' ),
					),
				),
				'import' => array(
					'complete' => __( 'Import Complete!', 'astoundify-contentimporter' ),
				),
				'reset' => array(
					'complete' => __( 'Reset Complete', 'astoundify-contentimporter' ),
				),
				'errors' => array(
					'process_action' => __( 'Invalid process action.', 'astoundify-contentimporter' ),
					'process_type' => __( 'Invalid process type.', 'astoundify-contentimporter' ),
					'iterate' => __( 'Iteration process failed.', 'astoundify-contentimporter' ),
					'cap_check_fail' => __( 'You do not have permission to manage content.', 'astoundify-contentimporter' ),
				),
			);

			$strings = wp_parse_args( $strings, $defaults );

			self::$strings = $strings;
		}

		/**
		 * Set the URL
		 *
		 * @since 1.1.0
		 * @param string $url
		 * @return string $url
		 */
		public static function set_url( $url ) {
			self::$url = $url;
		}

		/**
		 * Get the URL
		 *
		 * @since 1.1.0
		 * @return string $url
		 */
		public static function get_url() {
			return self::$url;
		}

		/**
		 * Get strings.
		 *
		 * Set the defaults if none are available.
		 *
		 * @since 1.0.0
		 * @return self::$strings
		 */
		public static function get_strings() {
			if ( empty( self::$strings ) ) {
				self::set_strings();
			}

			return self::$strings;
		}

		/**
		 * Include necessary files and hook in to WordPres
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init() {
			self::setup_actions();

			// Load Manager.
			Astoundify_CI_ImportManager::init();

			// Load Plugin Importer.
			Astoundify_CI_Plugin_EasyDigitalDownloads::init();
			Astoundify_CI_Plugin_FrontendSubmissions::init();
			Astoundify_CI_Plugin_MultiplePostThumbnails::init();
			Astoundify_CI_Plugin_WooCommerce::init();
			Astoundify_CI_Plugin_WooThemesTestimonials::init();
			Astoundify_CI_Plugin_WPJobManager::init();
			Astoundify_CI_Plugin_WPJobManagerProducts::init();
			Astoundify_CI_Plugin_WPJobManagerResumes::init();

			// Load Theme Importer.
			Astoundify_CI_Theme_Listify::init();
		}

		/**
		 * Hooks/filters
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public static function setup_actions() {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		}

		/**
		 * Enqueue import scripts
		 *
		 * @since 1.1.0
		 * @return void
		 */
		public static function admin_enqueue_scripts( $hook_suffix ) {
			//$admin_screen_ids = apply_filters( 'astoundify_ci_screen', array() );

			//if ( ! in_array( $hook_suffix, (array) $admin_screen_ids ) || apply_filters( 'astoundify_ci_wp', false ) ) {
			//return;
			//}

			wp_register_style( 'astoundify-contentimporter', self::get_url() . '/css/content-importer.min.css', array(), self::$version );
			wp_register_script( 'astoundify-contentimporter', self::get_url() . '/js/content-importer.min.js' , array( 'jquery', 'underscore' ), self::$version, true );

			wp_localize_script( 'astoundify-contentimporter', 'astoundifyContentImporter', array(
				'page'       => $hook_suffix,
				'nonces'     => array(
					'stage'       => wp_create_nonce( 'setup-guide-stage-import' ),
				),
				'chunkSize'  => 5,
				'i18n'       => self::get_strings(),
			) );
		}

		/**
		 * Importer HTML
		 *
		 * @since 2.0.0
		 *
		 * @param array  $files Full path to files path to import.
		 * @param string $pack  Content Pack.
		 * @return string
		 */
		public static function get_importer_html( $files = array(), $pack = '' ) {
			wp_enqueue_style( 'astoundify-contentimporter' );
			wp_enqueue_script( 'astoundify-contentimporter' );
			wp_localize_script( 'astoundify-contentimporter', 'astoundifyContentImporterFiles', $files );

			$strings     = self::get_strings();
			$type_labels = $strings['type_labels'];
			ob_start();
?>
<div id="astoundify-ci">

	<div id="import-summary" style="display: none;">

		<p><?php _e( 'Please do not navigate away from this page. This process may take a few minutes depending on your server capabilities and internet connection.', 'astoundify-contentimporter' ); ?></p>

		<p><?php _e( 'Summary:', 'astoundify-contentimporter' ); ?> <strong id="import-status"></strong></p>

		<?php foreach ( $type_labels as $key => $labels ) : ?>
			<p id="import-type-<?php echo esc_attr( $key ); ?>" class="import-type" data-type="<?php echo esc_attr( $key ); ?>">
				<span class="dashicons import-type-<?php echo esc_attr( $key ); ?>"></span>&nbsp;
				<strong class="process-type"><?php echo esc_attr( $labels[1] ); ?>:</strong>
				<span class="process-count">
					<span id="<?php echo esc_attr( $key ); ?>-processed" class="processed-count">0</span> / <span id="<?php echo esc_attr( $key ); ?>-total" class="total-count">0</span>
				</span>
				<span id="<?php echo esc_attr( $key ); ?>-spinner" class="spinner"></span>
			</p>
		<?php endforeach; ?>

	</div><!-- #import-summary -->

	<ul id="import-errors"></ul><!-- #import-errors -->

	<p id="import-actions">
		<a id="import-action" href="#" class="button-import button button-primary" data-action="import"><?php _e( 'Import Content', 'astoundify-contentimporter' ); ?></a>
		&nbsp;
		<a id="reset-action" href="#" class="button-import button button-secondary" data-action="reset"><?php _e( 'Reset Content', 'astoundify-contentimporter' ); ?></a>
		<?php do_action( 'astoundify_ci_actions' ); ?>
	</p>

	<input type="hidden" name="astoundify_ci_pack" value="<?php echo esc_attr( $pack ); ?>">

</div><!-- #astoundify-ci -->
<?php
			return ob_get_clean();
		}

	}
endif;
