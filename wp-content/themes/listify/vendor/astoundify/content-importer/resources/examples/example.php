<?php
/**
 * Plugin Name: Astoundify Content Importer EXAMPLE
 * Plugin URI: https://astoundify.com
 * Description: Example Importer Implementation Outside Import Screen.
 * Version: 1.0.0
 * Author: Astoundify
 * Author URI: https://astoundify.com
 * Requires at least: 4.8.0
 * Tested up to: 4.8
 *
 *    Copyright: 2017 Astoundify
 *    License: GNU General Public License v3.0
 *    License URI: http://www.gnu.org/licenses/gpl-3.0.html
**/

/**
 * HOW TO TEST:
 *
 * 1. Copy this file to root content importer plugin file.
 * 2. Deactivate "Astoundify Content Importer" Plugin.
 * 3. Activate "Astoundify Content Importer EXAMPLE" Plugin.
 * 4. Navigate to "Importer Example" Plugin Setting
 * 5. Import sample data.
 */

// Helper constants.
define( 'ACI_EX_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'ACI_EX_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );

// Load main plugin file.
require_once( ACI_EX_PATH . 'astoundify-contentimporter.php' );

// Setup Importer Config.
Astoundify_ContentImporter::instance();
Astoundify_ContentImporter::set_url( ACI_EX_URI . 'public' );

// Create Settings Page.
add_action( 'admin_menu', function() {

	$page = add_menu_page(
		$page_title = 'Importer Example',
		$menu_title = 'Importer Example',
		$capability = 'import',
		$menu_slug  = 'aci-ex', 
		$function   = function() {
			echo '<h1>Example Importer</h1>';

			// Get all json files in sample-data dir.
			$files = glob( ACI_EX_PATH . 'resources/sample-data/*.json' );

			// Get importer.
			echo Astoundify_ContentImporter::get_importer_html( $files );
		},
		$icon       = 'dashicons-arrow-down-alt',
		$position   = 99
	);

} );
