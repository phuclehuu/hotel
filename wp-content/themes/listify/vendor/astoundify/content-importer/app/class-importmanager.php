<?php
/**
 * Import manager
 *
 * @since 1.0.0
 */
class Astoundify_CI_ImportManager {

	/**
	 * Init Class.
	 *
	 * @since 1.0.0
	 */
	public static function init() {

		// AJAX Importer.
		add_action( 'wp_ajax_astoundify_ci', array( __CLASS__, 'ajax_importer' ) );

		// AJAX Iterate item.
		add_action( 'wp_ajax_astoundify_ci_iterate_item', array( __CLASS__, 'ajax_iterate_items' ) );

		// WP Importer clean up.
		add_action( 'delete_attachment', array( __CLASS__, 'delete_wp_importer_files' ) );
	}

	/**
	 * AJAX Importer.
	 *
	 * @since 2.0.0
	 */
	public static function ajax_importer() {
		check_ajax_referer( 'setup-guide-stage-import', 'security' );

		// Check user caps.
		if ( ! current_user_can( 'import' ) ) {
			wp_send_json_error( __( 'You do not have permission to import content.', 'astoundify-contentimporter' ) );
		}

		// Request.
		$request = stripslashes_deep( $_POST );

		// Get all importer files.
		$files = apply_filters( 'astoundify_ci_files', $request['files'] );

		// Content pack filter.
		if ( $request['pack'] ) {
			$files = apply_filters( 'astoundify_ci_pack_' . trim( $request['pack'] ), $files );
		}

		// No file specify, bail.
		if ( ! $files || ! is_array( $files ) ) {
			return wp_send_json_error( __( 'Imported files not found.', 'astoundify-contentimporter' ) );
		}

		// Load all files to importer library to process.
		$importer = Astoundify_CI_Importer_Factory::create( $files );

		// Start.
		if ( ! is_wp_error( $importer ) ) {
			$staged = $importer->stage();

			if ( is_wp_error( $staged ) ) {
				return wp_send_json_error( $staged->get_error_message() );
			}

			if ( 0 === count( $importer->get_items() ) ) {
				return wp_send_json_error( __( 'Cannot read files on system. Try changing your <code>FS_METHOD</code> to <code>direct</code>. <a href="https://codex.wordpress.org/Editing_wp-config.php#WordPress_Upgrade_Constants">More information</a>', 'astoundify-contentimporter' ) );
			}

			$data = array(
				'total'  => count( $importer->get_items() ),
				'groups' => $importer->item_groups,
				'items'  => $importer->get_items(),
			);

			return wp_send_json_success( $data );
		} else {
			return wp_send_json_error();
		}

		exit();
	}

	/**
	 * AJAX iterate items.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function ajax_iterate_items() {
		if ( ! isset( $_POST['items'] ) ) {
			return wp_send_json_error();
		}

		// Clean up http request
		$items = wp_unslash( $_POST['items'] );
		$iterate_action = wp_unslash( $_POST['iterate_action'] );
		$iterate_action = 'import' === $iterate_action ? 'import' : 'reset';

		// Responses.
		$responses = array();

		// Process each items.
		foreach ( $items as $item ) {
			$responses[] = self::ajax_process_item( $item, $iterate_action );
		}

		wp_send_json( $responses );
	}

	/**
	 * Process Item
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public static function ajax_process_item( $item, $iterate_action ) {
		// Get strings.
		$strings = Astoundify_ContentImporter::get_strings();

		// Default response.
		$response = array(
			'success' => false,
			'data'    => '',
			'item'    => $item, // Pass back item for JS.
		);

		// Validate user.
		if ( ! current_user_can( 'import' ) ) {
			$response['data'] = $strings['errors']['cap_check_fail'];
			return $response;
		}

		if ( is_array( $item['data'] ) ) {
			$item['data'] = array_map( array( 'Astoundify_CI_Utils', 'numeric_to_int' ), $item['data'] );
		} else {
			$item['data'] = Astoundify_CI_Utils::numeric_to_int( $item['data'] );
		}

		$item = Astoundify_CI_Import_Item_Factory::create( $item );

		if ( is_wp_error( $item ) ) {
			$response['data'] = $strings['errors']['process_type'];
			return $response;
		}

		$item = $item->iterate( $iterate_action );

		if ( ! $item ) {
			$response['data'] = $strings['errors']['iterate'];
			return $response;
		}

		if ( ! is_wp_error( $item->get_processed_item() ) ) {
			$response['success'] = true;
			$response['data'] = array(
				'item' => $item,
			);
			return $response;
		} else {
			$response['data'] = $item->get_processed_item()->get_error_message();
			return $response;
		}
	}

	/**
	 * Delete Importer Files 
	 * This functionality is not added in Astoundify_CI_WP_Importer class because that class only loaded on import screen.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Attachment ID.
	 */
	public static function delete_wp_importer_files( $post_id ) {
		// Only for astoundify importer attachment.
		$ci = get_post_meta( $post_id, '_astoundify_ci_file', true );

		if ( ! $ci ) {
			return $post_id;
		}

		// Load WP Filesystem.
		global $wp_filesystem;
		if ( ! isset( $wp_filesystem ) ) {
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem();
		}

		// Get zip files dir.
		$dir = wp_upload_dir();
		$dest = $dir['basedir'] . '/astoundify-import';

		// Delete all when attachment deleted.
		$wp_filesystem->delete( $dest, true );
	}
}

