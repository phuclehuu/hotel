<?php
/**
 * Partials to be used with Selective Refresh.
 *
 * @since 1.9.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

if ( ! function_exists( 'listify_partial_site_branding' ) ) {
	/**
	 * Site branding.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	function listify_partial_site_branding() {
		$custom_logo = get_theme_mod( 'custom_logo', null );
		$header_image = false;
		$base_header_image = false;

		if ( $custom_logo ) {
			$header_image = wp_get_attachment_url( $custom_logo );
			$base_header_image = $header_image;
		}

		$transparent = false;

		if ( is_front_page() ) {
			$transparent = 'transparent' === get_theme_mod( 'home-header-style', 'default' );

			if ( get_theme_mod( 'home-header-logo', false ) ) {
				$header_image = set_url_scheme( get_theme_mod( 'home-header-logo' ) );
			}
		}

		ob_start();
	?>

<?php if ( ! empty( $header_image ) ) : ?>
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home" class="custom-header">
		<img src="<?php echo esc_url_raw( $base_header_image ); ?>" alt="" aria-hidden="true" role="presentation" class="custom-header-image" />

		<?php if ( $base_header_image !== $header_image ) : ?>
			<img src="<?php echo esc_url_raw( $header_image ); ?>" alt="" aria-hidden="true" role="presentation" class="custom-header-image--transparent" />
		<?php endif; ?>
	</a>
<?php endif; ?>

<h2 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h2>
<h3 class="site-description"><?php bloginfo( 'description' ); ?></h3>

	<?php
		return ob_get_clean();
	}
} // End if().

if ( ! function_exists( 'listify_partial_search_filters_home' ) ) {
	/**
	 * Search filters for the homepage (redirects).
	 *
	 * @since 1.9.0
	 *
	 * @return string.
	 */
	function listify_partial_search_filters_home() {
		ob_start();

		if ( listify_has_integration( 'facetwp' ) ) {
			locate_template( array( 'job-filters-home-facetwp.php' ), true, false );
		} else {
			locate_template( array( 'job-filters-home.php' ), true, false );
		}

		return ob_get_clean();
	}
} // End if().

if ( ! function_exists( 'listify_partial_search_filters_archive' ) ) {
	/**
	 * Search filters for the archive (results).
	 *
	 * @since 1.9.0
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	function listify_partial_search_filters_archive( $atts ) {
		ob_start();

		$filters = Listify_WP_Job_Manager_Template_Filters::get_filters( 'archive', $atts );

		do_action( 'job_manager_job_filters_start', $atts );
?>

<div class="search_jobs">
	<?php do_action( 'job_manager_job_filters_search_jobs_start', $atts ); ?>
	
	<?php foreach ( $filters as $key => $filter ) : ?>
		<?php echo $filter; // WPCS: XSS ok. ?>
	<?php endforeach; ?>

	<?php do_action( 'job_manager_job_filters_search_jobs_end', $atts ); ?>
</div>

<?php
		do_action( 'job_manager_job_filters_end', $atts );

		return ob_get_clean();
	}
} // End if().

if ( ! function_exists( 'listify_partial_listing_card' ) ) {
	/**
	 * Single listing card.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	function listify_partial_listing_card() {
		ob_start();

		get_template_part( 'content', 'job_listing' );

		return ob_start();
	}
} // End if().

if ( ! function_exists( 'listify_partial_copyright_text' ) ) {
	/**
	 * Footer copyright.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	function listify_partial_copyright_text() {
		// Translators: %1$s: website name, %2$s current year.
		return get_theme_mod( 'copyright-text', sprintf( __( 'Copyright %1$s &copy; %2$s. All Rights Reserved', 'listify' ), get_bloginfo( 'name' ), date( 'Y' ) ) );
	}
}
