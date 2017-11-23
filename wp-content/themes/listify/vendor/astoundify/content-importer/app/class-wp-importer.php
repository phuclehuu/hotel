<?php
/**
 * Extend standard WordPress importer to allow standalone usage.
 *
 * @since 2.0.0
 *
 * @package
 * @category
 * @author
 */

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

// Load importer class.
if ( ! class_exists( 'WP_Importer' ) ) {
	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_wp_importer ) )
		require $class_wp_importer;
}

/**
 * Importer.
 *
 * @since 2.0.0
 */
class Astoundify_CI_WP_Importer extends WP_Importer {

	/**
	 * Run the steps.
	 *
	 * @since 2.0.0
	 */
	public function dispatch() {
		if ( ! isset( $_GET['step'] ) ) {
			$step = 0;
		} else {
			$step = (int) $_GET['step'];
		}

		echo '<div class="wrap">';
		echo '<h2>'. esc_html__( 'Import Content', 'astoundify-contentimporer' ) . '</h2>';

		switch ( $step ) {
			case 0:
				$this->upload_html();
				break;
			case 1:
				check_admin_referer( 'import-upload' );
				$this->import_html();
				break;
		}

		echo '</div>';
	}

	/**
	 * Upload Form/initial step.
	 *
	 * @since 2.0.0
	 * @link https://developer.wordpress.org/reference/functions/wp_import_upload_form/
	 */
	public function upload_html() {
		wp_import_upload_form( add_query_arg( array(
			'import' => 'astoundify',
			'step'   => '1',
		), admin_url( 'admin.php' ) ) );
	}

	/**
	 * Import/first step.
	 *
	 * @since 2.0.0
	 */
	public function import_html() {

		// Attemp to upload the ZIP file.
		$upload = $this->handle_upload();

		// If no upload.
		if ( ! $upload && isset( $_FILES['import'] ) ) {
			esc_html_e( 'Sorry, there has been an error. Unable to extract files.', 'astoundify-contentimporter' );
			return;
		} elseif ( $upload && isset( $_FILES['import'] ) ) { // Uploaded and unzipped.
			$this->check_files();
		}

		// Get JSON files.
		$files = $this->get_files();

		if ( ! $files ) {
			esc_html_e( 'Sorry, there has been an error. No import files found.', 'astoundify-contentimporter' );
			return;
		}
?>

	<p><?php esc_html_e( 'Great! We can either Import or Reset:', 'astoundify-contentimporter' ); ?></p>

	<ol>
		<?php foreach ( $files as $file_name => $file_path ) : ?>
			<li><?php echo $file_name ?></li>
		<?php endforeach; ?>
	</ol>

	<?php echo Astoundify_ContentImporter::get_importer_html( $files ); ?>

<?php
	}

	/**
	 * Return upload directory if valid.
	 *
	 * Ensure we have a Filesystem to work with.
	 *
	 * @since 2.0.0
	 *
	 * @return string|false Path to unzip directory.
	 */
	public function get_upload_dir() {
		// Try and get filesystem.
		$creds = request_filesystem_credentials( admin_url() );

		if ( ! WP_Filesystem( $creds ) ) {
			return false;
		}

		global $wp_filesystem;

		$dir = wp_upload_dir();
		$dest = $dir['basedir'] . '/astoundify-import';

		return $dest;
	}

	/**
	 * Handle upload.
	 *
	 * @since 2.0.0
	 *
	 * @return bool|object True on successful unzip. False or WP_Error on failure.
	 */
	public function handle_upload() {
		$upload_dir = $this->get_upload_dir();

		if ( ! $upload_dir ) {
			return false;
		}

		global $wp_filesystem;

		// New upload.
		if ( isset( $_FILES['import'] ) ) {

			/**
			 * Delete previous ZIP attachment.
			 * This will also clear destination and previously uploaded zip.
			 * @see Astoundify_CI_ImportManager::delete_wp_importer_files()
			 */
			$attachments = get_posts( array(
				'post_type'      => 'attachment',
				'post_status'    => 'private',
				'post_per_page'  => 1,
				'fields'         => 'ids',
				'meta_key'       => '_astoundify_ci_file',
			) );
			if ( $attachments ) {
				wp_delete_attachment( $attachments[0], true);
			}
		}

		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			return false;
		}

		// Unzip file.
		$unzip = unzip_file( $file['file'], $upload_dir );

		// Track attachment using custom meta.
		add_post_meta( $file['id'], '_astoundify_ci_file', is_wp_error( $unzip ) ? 'unzip_error' : 'unzip_success', true );

		// Unzip file to "uploads/astoundify-import".
		return $unzip;
	}

	/**
	 * Get files to import.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $json False to get all files. Only return JSON files if set to true.
	 * @return array JSON files path.
	 */
	public function get_files( $json = true ) {
		$upload_dir = $this->get_upload_dir();

		if ( ! $upload_dir ) {
			return array();
		}

		global $wp_filesystem;

		$dir_files = $wp_filesystem->dirlist( $upload_dir, true, true );

		if ( ! $dir_files ) {
			return array();
		}

		$files = array();

		foreach ( $dir_files as $file_name => $file_data ) {
			if ( $json && '.json' !== substr( $file_name, -5 ) ) {
				continue;
			}
			$files[ $file_name ] = trailingslashit( $upload_dir ) . $file_data['name'];
		}


		return $files;
	}

	/**
	 * Initial Check Files in Upload Dir after unzip.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
	public function check_files() {
		$upload_dir = $this->get_upload_dir();

		if ( ! $upload_dir ) {
			return false;
		}

		global $wp_filesystem;

		// Protect Dir with .htaccess file.
		$htaccess  = "Options -Indexes" . "\n"; // Prevent indexing.
		$htaccess .= "<Files *.*>" . "\n";
		$htaccess .= "order allow,deny" . "\n";
		$htaccess .= "deny from all" . "\n";
		$htaccess .= "</Files>";
		$wp_filesystem->put_contents( trailingslashit( $upload_dir ) . '.htaccess', $htaccess, FS_CHMOD_FILE );

		// Get all files.
		$files = $this->get_files( false );

		// Check each files.
		foreach ( $files as $file_name => $file_path ) {

			// Htaccess.
			if ( '.htaccess' === $file_name ) {
				continue;
			}

			// Check file type. Delete if not JSON.
			if ( '.json' !== substr( $file_name, -5 ) ) {
				$wp_filesystem->delete( $file_path, true );
				continue;
			}

			// Get JSON content for initial validation.
			$file_content = json_decode( $wp_filesystem->get_contents( $file_path ) );

			// Not a valid JSON files, delete.
			if ( null === $file_content ) {
				$wp_filesystem->delete( $file_path, true );
			}
		}

	}

}
