<?php
/**
 * Template Name: Page: Ranking
 *
 * @package Listify
 */

global $style;

$blog_style = get_theme_mod( 'content-blog-style', 'default' );
$style = 'grid-standard' == $blog_style ? 'standard' : 'cover';
$sidebar = 'none' != esc_attr( listify_theme_mod( 'content-sidebar-position', 'right' ) ) && is_active_sidebar( 'widget-area-sidebar-1' );

get_header(); ?>

	<div <?php echo apply_filters( 'listify_cover', 'page-cover', array(
		'size' => 'full',
	) ); ?>>
		<h1 class="page-title cover-wrapper"><!-- <?php echo get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) :  _x( 'Blog', 'blog page title', 'listify' ); ?> -->
			 HOT NEWS
		</h1>
	</div>

	<div id="primary" class="container">
		<div class="row content-area">

			<!-- edit by VICTOR HOANG -->
			<?php if ( 'left' == esc_attr( listify_theme_mod( 'content-sidebar-position', 'right' ) ) ) : ?>
				<?php// get_sidebar(); ?>
			<?php endif; ?>
			<!--
			<main id="main" class="site-main col-xs-12 <?php if ( $sidebar ) : ?>col-sm-7 col-md-8<?php endif; ?>" role="main">
			-->
			<main id="main" class="site-main col-xs-12 <?php if ( $sidebar ) : ?>col-sm-12 col-md-12<?php endif; ?>" role="main">
				<?php if ( 'default' != $blog_style ) : ?>
				<!-- <div class="blog-archive blog-archive--grid <?php if ( $sidebar ): ?>blog-archive--has-sidebar<?php endif; ?>" data-columns> -->
				<div class="blog-archive blog-archive--grid" data-columns>
					<?php add_filter( 'excerpt_length', 'listify_short_excerpt_length' ); ?>
				<?php endif; ?>

				<?php
		            if (function_exists('get_post_top_ranking_view')):
		                //Ranking page for category
		                if ($cat_slug && $category){
		                    $popular_posts = get_post_top_ranking_view(25, $category->term_id);
		                }else{
		                    $popular_posts = get_post_top_ranking_view(25);
		                }

		            endif;
                ?>

                <?php if ( $popular_posts ) : ?>
                	 <?php
		                if( $popular_posts && count( $popular_posts ) > 0){
		                    $query = new WP_Query( array( 'post_type' => 'post', 'post__in' => $popular_posts, 'posts_per_page' => -1 ) );
		                    while ( $query->have_posts() ) : $query->the_post();
		                        get_template_part( 'content', 'recent-posts' );
		                    endwhile;
		                    wp_reset_postdata();
		                }?>
                <?php
		            else :
		                // If no content, include the "No posts found" template.
		               // get_template_part( 'content', 'none' );
		            endif;
		            wp_reset_query();
		        ?>

				<?php get_template_part( 'content', 'pagination' ); ?>

			</main>

			<?php if ( 'right' == esc_attr( get_theme_mod( 'content-sidebar-position', 'right' ) ) ) : ?>
				<?php // get_sidebar(); ?>
			<?php endif; ?>

		</div>
	</div>

<?php get_footer(); ?>
