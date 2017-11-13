<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Listify
 */

get_header(); ?>

	<div <?php echo apply_filters( 'listify_cover', 'page-cover' ); ?>>
		<h1 class="page-title cover-wrapper"><?php the_archive_title(); ?></h1>
	</div>

	<div id="primary" class="container">
		<div class="row content-area">

			<?php if ( 'left' == esc_attr( listify_theme_mod( 'content-sidebar-position', 'right' ) ) ) : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>

			<main id="main" class="site-main col-md-8 col-sm-7 col-xs-12" role="main">

				<?php if ( have_posts() ) : ?>

					<?php while ( have_posts() ) : the_post(); ?>

						<?php get_template_part( 'content' ); ?>

					<?php endwhile; ?>

					<?php get_template_part( 'content', 'pagination' ); ?>

				<?php else : ?>

					<?php get_template_part( 'content', 'none' ); ?>

				<?php endif; ?>

			</main>

			<?php if ( 'right' == esc_attr( get_theme_mod( 'content-sidebar-position', 'right' ) ) ) : ?>
				<?php get_sidebar(); ?>
			<?php endif; ?>

		</div>
	</div>

<?php get_footer(); ?>
