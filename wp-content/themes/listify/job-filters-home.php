<?php
/**
 * Search filters for the homepage.
 *
 * This is the same as `job-filters.php` except it uses a different class
 * as not to interfer with the WP Job Manager AJAX triggers.
 *
 * @since unknown
 */

global $listify_job_manager;

$atts = apply_filters( 'job_manager_output_jobs_defaults', array(
	'per_page'                   => get_option( 'job_manager_per_page' ),
	'orderby'                    => 'featured',
	'order'                      => 'DESC',
	'show_categories'            => true,
	'categories'                 => true,
	'selected_category'          => false,
	'job_types'                  => false,
	'location'                   => false,
	'keywords'                   => false,
	'selected_job_types'         => false,
	'show_category_multiselect'  => false,
	'selected_region'            => false,
	'flat'                       => true,
) );

$filters = Listify_WP_Job_Manager_Template_Filters::get_filters( 'home', $atts );

if ( empty( $filters ) ) {
	return;
}
?>

<?php do_action( 'job_manager_job_filters_before', $atts ); ?>

<form class="job_search_form job_search_form--count-<?php echo absint( count( $filters ) ); ?>" action="<?php echo get_post_type_archive_link( 'job_listing' ); ?>" method="GET">
	<?php do_action( 'job_manager_job_filters_start', $atts ); ?>

	<div class="search_jobs">
		<?php do_action( 'job_manager_job_filters_search_jobs_start', $atts ); ?>
		
		<?php foreach ( $filters as $key => $filter ) : ?>
			<?php echo $filter; ?>
		<?php endforeach; ?>

		<?php do_action( 'job_manager_job_filters_search_jobs_end', $atts ); ?>
	</div>

	<?php do_action( 'job_manager_job_filters_end', $atts ); ?>
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>
