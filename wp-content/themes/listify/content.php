<?php
/**
 * The template for displaying standard blog content.
 *
 * @package Listify
 */
?>

<?php if ( listify_has_integration( 'woocommerce' ) ) : ?>
	<?php wc_print_notices(); ?>
<?php endif; ?>

<?php
if ( '' == get_the_content() ) {
	return;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php if ( ! is_singular() ) : ?>
	<header <?php echo apply_filters( 'listify_cover', 'entry-header entry-cover' ); ?>>
		<div class="cover-wrapper">
			<h2 class="entry-title entry-title--in-cover"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
		</div>
	</header><!-- .entry-header -->
	<?php endif; ?>

	<div class="content-box-inner">
		<?php if ( ! is_singular( 'page' ) ) : ?>

		<div class="entry-meta">
			<h2 class="entry-title entry-title-change"><?php the_title(); ?></h2>
			<!-- <span class="entry-author">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 50 ); ?>
				<?php the_author_posts_link(); ?>
			</span>

			<span class="entry-date">
				<?php echo get_the_date(); ?>
			</span>

			<?php if ( ! post_password_required() && ( comments_open() || '0' != get_comments_number() ) ) : ?>
			<span class="entry-comments">
				<?php comments_popup_link( __( '0 Comments', 'listify' ), __( '1 Comment', 'listify' ), __( '% Comments', 'listify' ) ); ?>
			</span>
			<?php endif; ?>

			<span class="entry-share">
				<?php do_action( 'listify_share_object' ); ?>
			</span> -->
		</div>
		<?php endif; ?>

		<!-- edit by VICTOR HOANG -->
		<?php if ( is_singular() ) : ?>
			<div class="entry-content">
				<?php the_post_thumbnail(); ?>
				<?php //the_content(); ?>
			</div>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div>
			<?php if ( ! is_singular( 'page' ) ) : ?>
				<div class="entry-meta">

					<?php
					    $url = get_the_permalink();
					    $href = "https://www.facebook.com/sharer/sharer.php?u=" . $url;
					?>
					<a class="facebook" href="<?php echo $href; ?>" onclick="window.open(this.href, 'snswindow', 'width=550,height=450,personalbar=0,toolbar=0,scrollbars=1,resizable=1'); return false;">
		                <!-- <span class="icon icon-facebook"></span>
		                <p></p>
		                <span>Share</span> -->
		                <img src="https://misskick.vn/wp-content/themes/misskick-PC/images/button-social/bt-facebook.svg" alt="Smiley face" style="width:100px">
		            </a>
					<span class="entry-author f-right">
						<?php the_author_posts_link(); ?>
					</span>
				</div>
				<div class="clear-both"></div>
				<hr style="margin: 10px 0px">
			<?php endif; ?>
			<div class="entry-content">
				<?php the_content(); ?>
			</div>
		<?php else : ?>
			<div class="entry-summary">
				<?php the_excerpt(); ?>
			</div><!-- .entry-summary -->
		<?php endif; ?>

		<?php wp_link_pages(); ?>

		<?php if ( ! is_singular() ) : ?>
		<footer class="entry-footer">
			<a href="<?php the_permalink(); ?>" class="button button-small"><?php _e( 'Read More', 'listify' ); ?></a>
		</footer><!-- .entry-footer -->
		<?php endif; ?>
	</div>
</article><!-- #post-## -->
