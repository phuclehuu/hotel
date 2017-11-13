<?php
/**
 * Plugin Name: Astoundify Content Importer
 * Plugin URI: https://astoundify.com
 * Description: Import content via JSON files for easier immediate reference and manipulation.
 * Version: 1.2.1
 * Author: Astoundify
 * Author URI: https://astoundify.com
 * Requires at least: 4.8.0
 * Tested up to: 4.8
 * Text Domain: astoundify-contentimporter
 * Domain Path: resources/languages/
 *
 *    Copyright: 2017 Astoundify
 *    License: GNU General Public License v3.0
 *    License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package ContentImporter
 * @category Core
 * @author Astoundify
 */

// Do not access this file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activation PHP Notice
 *
 * @since 1.0.0
 */
function astoundify_ci_php_notice() {
	// translators: %1$s minimum PHP version, %2$s current PHP version.
	$notice = sprintf( __( 'Astoundify Content Importer requires at least PHP %1$s. You are running PHP %2$s. Please upgrade and try again.', 'astoundify-favorites' ), '<code>5.3.0</code>', '<code>' . PHP_VERSION . '</code>' );
?>

<div class="notice notice-error">
	<p><?php echo wp_kses_post( $notice, array( 'code' ) ); ?></p>
</div>

<?php
}

// Check for PHP version..
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', 'astoundify_ci_php_notice' );

	return;
}

// Plugin can be loaded... define some constants.
define( 'ASTOUNDIFY_CI_VERSION', '2.0.0' );
define( 'ASTOUNDIFY_CI_FILE', __FILE__ );
define( 'ASTOUNDIFY_CI_PLUGIN', plugin_basename( __FILE__ ) );
define( 'ASTOUNDIFY_CI_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ASTOUNDIFY_CI_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'ASTOUNDIFY_CI_TEMPLATE_PATH', trailingslashit( ASTOUNDIFY_CI_PATH . 'resources/templates' ) );

/**
 * Load auto loader.
 *
 * @since 1.0.0
 */
require_once( ASTOUNDIFY_CI_PATH . 'bootstrap/autoload.php' );

/**
 * Start the application.
 *
 * @since 1.0.0
 */
require_once( ASTOUNDIFY_CI_PATH . 'bootstrap/app.php' );