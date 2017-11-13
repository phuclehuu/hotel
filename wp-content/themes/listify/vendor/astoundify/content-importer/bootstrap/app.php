<?php
/**
 * Load the application.
 *
 * @since 1.0.0
 *
 * @package PluginScaffold
 * @category Bootstrap
 * @author Astoundify
 */

// Plugin init.
add_action( 'plugins_loaded', 'astoundify_ci_init' );

/**
 * Initialize plugin.
 *
 * @since 1.0.0
 */
function astoundify_ci_init() {

	// Load text domain.
	load_plugin_textdomain( dirname( ASTOUNDIFY_CI_PATH ), false, dirname( ASTOUNDIFY_CI_PATH ) . '/resources/languages/' );

	// Load Importer Library.
	Astoundify_ContentImporter::instance();
	Astoundify_ContentImporter::set_url( ASTOUNDIFY_CI_URI . 'public' );

	// Load WP Importer only in import screen.
	if ( defined( 'WP_LOAD_IMPORTERS' ) ) {

		// WP Importer.
		$importer = new Astoundify_CI_WP_Importer();

		// Register Importer.
		register_importer( 'astoundify', __( 'Astoundify', '' ), 'Import content from JSON files.',  array( $importer, 'dispatch' ) );
	}
}
