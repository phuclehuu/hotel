<?php
/**
 * The Template for displaying a single listing.
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>

		<?php get_template_part( 'content', 'single-job_listing' ); ?>

	<?php endwhile; ?>

<?php get_footer();
