<?php
/**
 * Search filters for the archive.
 *
 * @see https://github.com/Automattic/WP-Job-Manager/blob/master/templates/job-filters.php
 *
 * @since 1.8.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

$filters = Listify_WP_Job_Manager_Template_Filters::get_filters( 'archive', $atts );

if ( empty( $filters ) ) {
	return;
}
?>

<?php do_action( 'job_manager_job_filters_before', $atts ); ?>

<form class="job_filters">
	<?php echo listify_partial_search_filters_archive( $atts ); // WPCS: XSS ok. ?>

	<div class="archive-job_listing-filter-title">
		<h3 class="archive-job_listing-found">
			<span class="results-found"><?php esc_html_e( 'Loading results...', 'listify' ); ?>
		</h3>

		<?php echo Listify_WP_Job_Manager_Template_Filters::get_sort_filter( $atts ); ?>
	</div>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>
