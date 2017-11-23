<?php
/**
 * Listify functions and definitions
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Core
 * @author Astoundify
 */

/**
 * PHP Compat Notice
 *
 * @since 2.1.0
 */
function listify_php_compat_notice() {
	// translators: %1$s minimum PHP version, %2$s current PHP version.
	$notice = sprintf( __( 'Listify requires at least PHP %1$s. You are running PHP %2$s. Please upgrade and try again.', 'listify' ), '<code>5.3.0</code>', '<code>' . PHP_VERSION . '</code>' );
	return $notice;
}

/**
 * PHP Compat Admin Notice
 *
 * @since 2.1.0
 */
function listify_php_compat_admin_notice() {
?>

<div class="notice notice-error">
	<p><?php echo wp_kses_post( listify_php_compat_notice(), array( 'code' ) ); ?></p>
</div>

<?php
}

/**
 * PHP Compat Switch to Default Theme
 *
 * @since 2.1.0
 */
function listify_php_compat_switch_default_theme() {
	switch_theme( WP_DEFAULT_THEME, WP_DEFAULT_THEME );
	unset( $_GET['activated'] );
}

/**
 * PHP Compat Disable Customize.
 *
 * @since 2.1.0
 */
function listify_php_compat_disable_customize() {
	wp_die( wp_kses_post( listify_php_compat_notice(), array( 'code' ) ), '', array( 'back_link' => true ) );
}

/**
 * PHP Compat Disable Preview.
 *
 * @since 2.1.0
 */
function listify_php_compat_disable_preview() {
	if ( isset( $_GET['preview'] ) ) {
		wp_die( wp_kses_post( listify_php_compat_notice(), array( 'code' ) ), '', array( 'back_link' => true ) );
	}
}

// Check for PHP version..
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {

	// Add admin notices.
	add_action( 'admin_notices', 'listify_php_compat_admin_notice' );

	// Switch theme.
	add_action( 'after_switch_theme', 'listify_php_compat_switch_default_theme' );

	// Disable customize.
	add_action( 'load-customize.php', 'listify_php_compat_disable_customize' );

	// Disable preview.
	add_action( 'load-customize.php', 'listify_php_compat_disable_preview' );

	// Stop load.
	return;
}

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 750;
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @since 1.0.0
 */
function listify_setup() {
	// Load tranlsations.
	load_theme_textdomain( 'listify', get_template_directory() . '/languages' );

	// Enable support for WordPress features.
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'commentlist',
		'gallery',
		'caption',
	) );

	add_theme_support( 'custom-background', apply_filters( 'listify_custom_background_args', array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) ) );

	add_theme_support( 'custom-header', apply_filters( 'listify_custom_header_args', array(
		'video' => true,
		'default-image'          => '',
		'default-text-color'     => 'fff',
		'header-text'            => true,
		'width'                  => 100,
		'height'                 => 35,
		'flex-height'            => true,
		'flex-width'             => true,
		'wp-head-callback'       => '__return_true',
	) ) );

	// Register navigation menus.
	register_nav_menus( array(
		'primary'   => __( 'Primary Menu (header)', 'listify' ),
		'secondary' => __( 'Secondary Menu', 'listify' ),
		'tertiary'  => __( 'Tertiary Menu', 'listify' ),
		'social'    => __( 'Social Menu (footer)', 'listify' ),
	) );

	// Add custom TinyMCE styles.
	add_editor_style( 'css/editor-style.css' );
}
add_action( 'after_setup_theme', 'listify_setup' );

/**
 * Sidebars and Widgets
 *
 * @since 1.0.0
 */
function listify_widgets_init() {
	register_widget( 'Listify_Widget_Ad' );
	register_widget( 'Listify_Widget_Features' );
	register_widget( 'Listify_Widget_Feature_Callout' );
	register_widget( 'Listify_Widget_Recent_Posts' );
	register_widget( 'Listify_Widget_Call_To_Action' );

	// Standard sidebar.
	register_sidebar( listify_register_sidebar_args( 'widget-area-sidebar-1' ) );

	// Custom homepage.
	register_sidebar( listify_register_sidebar_args( 'widget-area-home' ) );

	// Footer column 1.
	register_sidebar( listify_register_sidebar_args( 'widget-area-footer-1' ) );

	// Footer column 2.
	register_sidebar( listify_register_sidebar_args( 'widget-area-footer-2' ) );

	// Footer column 3.
	register_sidebar( listify_register_sidebar_args( 'widget-area-footer-3' ) );
}
add_action( 'widgets_init', 'listify_widgets_init' );


/**
 * Register Sidebar Args.
 * Filter need to be added in `widgets_init` with < 10 priority.
 *
 * @since 2.3.0
 *
 * @param string $sidebar Sidebar ID.
 * @return array
 */
function listify_register_sidebar_args( $sidebar ) {
	global $listify_strings;
	$args = array();
	if ( 'widget-area-sidebar-1' === $sidebar ) {
		$args = array(
			'name'          => __( 'Sidebar', 'listify' ),
			'id'            => 'widget-area-sidebar-1',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		);
	} elseif ( 'widget-area-home' === $sidebar ) {
		$args = array(
			'name'          => __( 'Homepage', 'listify' ),
			'description'   => __( 'Widgets that appear on the "Homepage" Page Template', 'listify' ),
			'id'            => 'widget-area-home',
			'before_widget' => '<aside id="%1$s" class="home-widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<div class="home-widget-section-title"><h2 class="home-widget-title">',
			'after_title'   => '</h2></div>',
		);
	} elseif( 'widget-area-footer-1' === $sidebar ) {
		$args = array(
			'name'          => __( 'Footer Column 1 (wide)', 'listify' ),
			'id'            => 'widget-area-footer-1',
			'before_widget' => '<aside id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h4 class="footer-widget-title">',
			'after_title'   => '</h4>',
		);
	} elseif( 'widget-area-footer-2' === $sidebar ) {
		$args = array(
			'name'          => __( 'Footer Column 2', 'listify' ),
			'id'            => 'widget-area-footer-2',
			'before_widget' => '<aside id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h4 class="footer-widget-title">',
			'after_title'   => '</h4>',
		);
	} elseif( 'widget-area-footer-3' === $sidebar ) {
		$args = array(
			'name'          => __( 'Footer Column 3', 'listify' ),
			'id'            => 'widget-area-footer-3',
			'before_widget' => '<aside id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h4 class="footer-widget-title">',
			'after_title'   => '</h4>',
		);
	} elseif( 'widget-area-author-main' === $sidebar ) { // Author Sidebars.
		$args = array(
			'name'          => __( 'Author - Main Content', 'listify' ),
			'id'            => 'widget-area-author-main',
			'before_widget' => '<aside id="%1$s" class="widget widget--author widget--author-main %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title widget-title--author widget--author-main %s">',
			'after_title'   => '</h3>',
		);
	} elseif( 'widget-area-author-sidebar' === $sidebar ) {
		$args = array(
			'name'          => __( 'Author - Sidebar', 'listify' ),
			'id'            => 'widget-area-author-sidebar',
			'before_widget' => '<aside id="%1$s" class="widget widget--author widget--author-sidebar %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h3 class="widget-title widget-title--author widget--author-sidebar %s">',
			'after_title'   => '</h3>',
		);
	} elseif( 'archive-job_listing' === $sidebar ) { // WPJM.
		$args = array(
			'name'          => sprintf( __( '%s Archives - Sidebar', 'listify' ), $listify_strings->label( 'singular' ) ),
			'id'            => 'archive-job_listing',
			'before_widget' => '<aside id="%1$s" class="widget widget-job_listing-archive %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title widget-title-job_listing %s">',
			'after_title'   => '</h2>',
		);
	} elseif( 'single-job_listing-widget-area' === $sidebar ) {
		$args = array(
			'name'          => sprintf( __( 'Single %s - Main Content', 'listify' ), $listify_strings->label( 'singular' ) ),
			'id'            => 'single-job_listing-widget-area',
			'before_widget' => '<aside id="%1$s" class="widget widget-job_listing %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title widget-title-job_listing %s">',
			'after_title'   => '</h2>',
		);
	} elseif( 'single-job_listing' === $sidebar ) {
		$args = array(
			'name'          => sprintf( __( 'Single %s - Sidebar', 'listify' ), $listify_strings->label( 'singular' ) ),
			'id'            => 'single-job_listing',
			'before_widget' => '<aside id="%1$s" class="widget widget-job_listing %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h2 class="widget-title widget-title-job_listing %s">',
			'after_title'   => '</h2>',
		);
	} elseif( 'widget-area-sidebar-product' === $sidebar ) { // WooCommerce.
		$args = array(
			'name'          => __( 'Single Product - Sidebar', 'listify' ),
			'id'            => 'widget-area-sidebar-product',
			'before_widget' => '<aside id="%1$s" class="widget %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h1 class="widget-title %s">',
			'after_title'   => '</h1>',
		);
	} elseif( 'widget-area-sidebar-shop' === $sidebar ) {
		$args = array(
			'name'          => __( 'Shop - Sidebar', 'listify' ),
			'id'            => 'widget-area-sidebar-shop',
			'before_widget' => '<aside id="%1$s" class="widget widget-shop %2$s">',
			'after_widget'  => '</aside>',
			'before_title'  => '<h1 class="widget-title %s">',
			'after_title'   => '</h1>',
		);
	} 
	return apply_filters( 'listify_register_' . $sidebar, $args, $sidebar );
}

/**
 * Load fonts in TinyMCE
 *
 * @since 1.0.0
 *
 * @param string $css Current CSS.
 */
function listify_mce_css( $css ) {
	$css .= ', ' . Listify_Customizer::$fonts->get_google_font_url();

	return $css;
}
add_filter( 'mce_css', 'listify_mce_css' );

/**
 * Scripts and Styles
 *
 * Load Styles and Scripts depending on certain conditions. Not all assets
 * will be loaded on every page.
 *
 * @since 1.0.0
 */
function listify_scripts() {
	/*
	 * Collect all the custom styles from the Customizer.
	 *
	 * @since 1.4.0
	 */
	do_action( 'listify_output_customizer_css' );

	// Output Google fonts if set.
	$google = Listify_Customizer::$fonts->get_google_font_url();

	if ( false !== $google ) {
		wp_enqueue_style( 'listify-fonts', esc_url( $google ) );
	}

	// Enqueue primary CSS and RTL.
	wp_enqueue_style( 'listify', get_template_directory_uri() . '/css/style.min.css', array(), 20170719 );
	wp_style_add_data( 'listify', 'rtl', 'replace' );

	// Add customizer CSS inline.
	wp_add_inline_style( 'listify', Listify_Customizer_CSS::build() );

	// Inline comments.
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	$deps = array( 'jquery' );

	if ( listify_has_integration( 'wp-job-manager-regions' ) && get_option( 'job_manager_regions_filter' ) ) {
		$deps[] = 'job-regions';
	}

	wp_enqueue_script( 'listify', get_template_directory_uri() . '/js/app.min.js', $deps, 20170814, true );
	wp_enqueue_script( 'salvattore', get_template_directory_uri() . '/js/vendor/salvattore/salvattore.min.js', array(), '', true );

	// Polyfill for Flexbox.
	wp_enqueue_script( 'flexibility', get_template_directory_uri() . '/js/vendor/flexibility/flexibility.min.js', array(), '', true );
	wp_script_add_data( 'flexibility', 'conditional', 'lt IE 11' );

	wp_localize_script( 'listify', 'listifySettings', apply_filters( 'listify_js_settings', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'homeurl' => home_url( '/' ),
		'archiveurl' => get_post_type_archive_link( 'job_listing' ),
		'is_job_manager_archive' => listify_is_job_manager_archive(),
		'is_rtl' => is_rtl(),
		'isMobile' => function_exists( 'jetpack_is_mobile' ) ? jetpack_is_mobile() : wp_is_mobile(),
		'megamenu' => array(
			'taxonomy' => listify_theme_mod( 'nav-megamenu', 'job_listing_category' ),
		),
		'l10n' => array(
			'closed' => __( 'Closed', 'listify' ),
			'timeFormat' => get_option( 'time_format' ),
			'magnific' => array(
				'tClose' => __( 'Close', 'listify' ),
				'tLoading' => '<span class="popup-loading"></span><span class="screen-reader-text">' . __( 'Loading...', 'listify' ) . '</span>',
				'tError' => __( 'The content could not be loaded.', 'listify' ),
			),
		),
		'loginPopupLink' => is_user_logged_in() ? array() : array(
			'a[href^="' . home_url( '/wp-login.php' ) . '?redirect_to"]',
			'.popup-trigger[href="#add-photo"]',
		),
	) ) );
}
add_action( 'wp_enqueue_scripts', 'listify_scripts' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @since 1.0.0
 *
 * @param array $classes CSS classes for the <body> element.
 * @return array $classes CSS classes for the <body> element.
 */
function listify_body_classes( $classes ) {
	global $wp_query, $post;

	if ( is_page_template( 'page-templates/template-archive-job_listing.php' ) ) {
		$classes[] = 'template-archive-job_listing';
	}

	if ( listify_is_widgetized_page() ) {
		$classes[] = 'template-home';
	}

	if (
		is_page_template( 'page-templates/template-full-width-blank.php' ) ||
		( isset( $post ) && has_shortcode( get_post()->post_content, 'jobs' ) )
	) {
		$classes[] = 'unboxed';
	}

	if ( is_singular() && get_post()->enable_tertiary_navigation ) {
		$classes[] = 'tertiary-enabled';
	}

	if (
		get_theme_mod( 'fixed-header', true ) ||
		(
			is_front_page() &&
			'transparent' !== get_theme_mod( 'home-header-style', 'default' ) &&
			get_theme_mod( 'fixed-header', true )
		) ||
		(
			listify_page_has_map()
		)
	) {
		$classes[] = 'fixed-header';
	}

	if ( is_front_page() ) {
		$classes[] = 'site-header--' . get_theme_mod( 'home-header-style', 'default' );
	}

	if ( listify_theme_mod( 'custom-submission', true ) ) {
		$classes[] = 'directory-fields';
	}

	$classes[] = 'color-scheme-' . sanitize_title( listify_theme_mod( 'color-scheme', 'default' ) );

	$classes[] = 'footer-' . listify_theme_mod( 'footer-display', 'dark' );

	$theme = wp_get_theme( 'listify' );

	if ( $theme->get( 'Name' ) ) {
		$classes[] = sanitize_title( $theme->get( 'Name' ) );
		$classes[] = sanitize_title( $theme->get( 'Name' ) . '-' . str_replace( '.', '', $theme->get( 'Version' ) ) );
	}

	return $classes;
}
add_filter( 'body_class', 'listify_body_classes' );

/**
 * Adds custom classes to the array of post classes.
 *
 * @since 1.0.0
 *
 * @param array $classes CSS classes for posts.
 * @return array $classes CSS classes for posts.
 */
function listify_post_classes( $classes ) {
	global $post;

	if (
		in_array( $post->post_type, array( 'post', 'page' ), true ) ||
		is_search() &&
		! has_shortcode( $post->post_content, 'jobs' )
	) {
		$classes[] = 'content-box content-box-wrapper';
	}

	return $classes;
}
add_filter( 'post_class', 'listify_post_classes' );

/**
 * "Cover" images for pages and other content.
 *
 * If on an archive the current query will be used. Otherwise it will
 * look for a single item's featured image or an image from its gallery.
 *
 * @since 1.0.0
 *
 * @param string $class Current CSS class for the element.
 * @param array  $args Modify the default arguments.
 * @return string $atts Compiled attributes for the element.
 */
function listify_cover( $class, $args = array() ) {
	$defaults = apply_filters( 'listify_cover_defaults', array(
		'images' => false,
		'object_ids' => false,
		'size' => 'large',
	) );

	$args  = wp_parse_args( $args, $defaults );
	$image = false;
	$atts  = array();

	global $wp_query;

	$post = get_post();

	if ( ( function_exists( 'is_shop' ) && is_shop() ) || is_singular( 'product' ) ) { // WooCommerce shop and product.
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( wc_get_page_id( 'shop' ) ), $args['size'] );

	} elseif ( is_tax( array( 'product_cat', 'product_tag' ) ) ) { // WooCommerce archive.
		$thumbnail_id = get_woocommerce_term_meta( get_queried_object_id(), 'thumbnail_id', true );
		$image = wp_get_attachment_image_src( $thumbnail_id, $args['size'] );

	} elseif ( ( is_home() && ! in_the_loop() ) ) { // Blog.
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( get_option( 'page_for_posts' ) ), $args['size'] );

	} elseif ( ! in_the_loop() && is_singular( 'post' ) ) { // Blog post.
		$image = array( get_the_post_thumbnail_url( get_post(), $args['size'] ) );

	} elseif ( ( ! did_action( 'loop_start' ) && is_archive() ) || ( $args['images'] || $args['object_ids'] ) ) { // Blog archive.
		$image = listify_get_cover_from_group( $args );

	} elseif ( is_a( $post, 'WP_Post' ) ) { // Single.
		if ( '' !== $post->_thumbnail_id ) {
			$image = wp_get_attachment_image_src( get_post_thumbnail_id(), $args['size'] );
		} elseif ( apply_filters( 'listify_listing_cover_use_gallery_images', false ) && listify_has_integration( 'wp-job-manager' ) ) {
			$gallery = Listify_WP_Job_Manager_Gallery::get( $post->ID );

			if ( $gallery ) {
				$args['images'] = $gallery;
				unset( $args['object_ids'] );

				$image = listify_get_cover_from_group( $args );
			}
		}
	}

	$image = apply_filters( 'listify_cover_image', $image, $args );

	if ( ! $image ) {
		$class .= ' no-image';

		return sprintf( 'class="%s"', $class );
	}

	$class .= ' has-image';

	$atts[] = sprintf( 'style="background-image: url(%s);"', $image[0] );
	$atts[] = sprintf( 'class="%s"', $class );

	return implode( ' ', $atts );
}
add_filter( 'listify_cover', 'listify_cover', 10, 2 );

/**
 * Get a cover image from a "group" (WP_Query or array of IDS)
 *
 * @since 1.0.0
 *
 * @param array|object $args Current arguments.
 * @return array $image
 */
function listify_get_cover_from_group( $args ) {
	$image = false;

	if ( empty( $args['object_ids'] ) && ( ! isset( $args['images'] ) || empty( $args['images'] ) ) ) {
		global $wp_query, $wpdb;

		if ( empty( $wp_query->posts ) ) {
			return $image;
		}

		$args['object_ids'] = wp_list_pluck( $wp_query->posts, 'ID' );
	}

	if ( ( ! isset( $args['images'] ) || empty( $args['images'] ) ) && ( isset( $args['object_ids'] ) && ! empty( $args['object_ids'] ) ) ) {

		$image = false;

		if ( listify_has_integration( 'wp-job-manager' ) ) {
			$objects_hash = 'listify_cover_' . md5( wp_json_encode( $args ) . WP_Job_Manager_Cache_Helper::get_transient_version( 'listify_cover_' . md5( wp_json_encode( $args['object_ids'] ) ) ) );
			$image = get_transient( $objects_hash );
		}

		if ( ! $image ) {
			$attachments = new WP_Query( array(
				'post_parent__in' => $args['object_ids'],
				'post_type' => 'attachment',
				'post_status' => 'inherit',
				'posts_per_page' => 1,
				'orderby' => 'rand',
				'update_post_term_cache' => false,
				'no_found_rows' => true,
			) );

			if ( $attachments->have_posts() ) {
				$image = wp_get_attachment_image_src( $attachments->posts[0]->ID, $args['size'] );

				$company_logo = $image[0] === $attachments->posts[0]->_company_avatar;

				if ( file_exists( $image[0] ) && ! $company_logo ) {
					set_transient( $objects_hash, $image, 6 * HOUR_IN_SECONDS );
				}
			}
		}
	} elseif ( isset( $args['images'] ) && ! empty( $args['images'] ) ) {
		shuffle( $args['images'] );

		$image = wp_get_attachment_image_src( current( $args['images'] ), $args['size'] );
	}

	return $image;
}

/**
 * Count the number of posts for a specific user.
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type to count.
 * @param int    $user_id User ID for author.
 * @return int $count
 */
function listify_count_posts( $post_type, $user_id ) {
	$count = get_transient( $post_type . $user_id );

	if ( false === $count ) {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = '$user_id' AND post_type = '$post_type' and post_status = 'publish'" );

		set_transient( $post_type . $user_id, $count );
	}

	return $count;
}

/**
 * Check if a specific integration is active.
 *
 * @since 1.0.0
 *
 * @param string $integration Slug of integration to check.
 * @return boolean
 */
function listify_has_integration( $integration ) {
	return array_key_exists( $integration, Listify_Integration::get_integrations() );
}

/** Standard Includes */
$includes = array(
	// Addons.
	'addons/class-addons-admin.php',

	// Customize.
	'customizer/class-customizer.php',

	// Setup.
	'class-strings.php',

	'class-activation.php',
	'setup/class-setup.php',

	'class-integration.php',

	// Listings/Results/Map.
	'listing/class-listing-factory.php',
	'listing/class-listing.php',
	'listing/class-listings-shortcode.php',
	'results/class-results.php',

	// Template.
	'template/class-template.php',
	'class-navigation.php',
	'class-widget.php',
	'class-page-settings.php',

	'template-tags.php',
	'partials.php',
	'extras.php',

	// Authors.
	'authors/class-authors.php',

	// Misc.
	'class-search.php',

	// Widgets.
	'class-widgetized-pages.php',
	'widgets/class-widget-ad.php',
	'widgets/class-widget-home-features.php',
	'widgets/class-widget-home-feature-callout.php',
	'widgets/class-widget-home-recent-posts.php',
	'widgets/class-widget-home-cta.php',
);

foreach ( $includes as $file ) {
	require( get_template_directory() . '/inc/' . $file );
}

/** Integrations */
$integrations = apply_filters( 'listify_integrations', array(
	'astoundify-favorites' => defined( 'ASTOUNDIFY_FAVORITES_VERSION' ) && version_compare( PHP_VERSION, '5.4.0', '>=' ),

	'wp-job-manager' => defined( 'JOB_MANAGER_VERSION' ),
	'wp-job-manager-wc-paid-listings' => defined( 'JOB_MANAGER_VERSION' ) && defined( 'JOB_MANAGER_WCPL_VERSION' ),

	'wp-job-manager-field-editor' => defined( 'JOB_MANAGER_VERSION' ) && defined( 'WPJM_FIELD_EDITOR_VERSION' ),

	'wp-job-manager-regions' => defined( 'JOB_MANAGER_VERSION' ) && class_exists( 'Astoundify_Job_Manager_Regions' ),
	'wp-job-manager-reviews' => defined( 'JOB_MANAGER_VERSION' ) && class_exists( 'WP_Job_Manager_Reviews' ),
	'wp-job-manager-products' => defined( 'JOB_MANAGER_VERSION' ) && class_exists( 'WP_Job_Manager_Products' ),
	'wp-job-manager-claim-listing' => defined( 'JOB_MANAGER_VERSION' ) && ( class_exists( 'WP_Job_Manager_Claim_Listing' ) || defined( 'WPJMCL_VERSION' ) ) && version_compare( PHP_VERSION, '5.5.0', '>=' ),
	'wp-job-manager-listing-payments' => defined( 'JOB_MANAGER_VERSION' ) && defined( 'ASTOUNDIFY_WPJMLP_VERSION' ) && version_compare( PHP_VERSION, '5.4.0', '>=' ),
	'wp-job-manager-listing-labels' => defined( 'JOB_MANAGER_VERSION' ) && defined( 'ASTOUNDIFY_WPJMLL_VERSION' ) && version_compare( PHP_VERSION, '5.4.0', '>=' ),
	'wp-job-manager-stats' => defined( 'JOB_MANAGER_VERSION' ) && defined( 'WPJMS_VERSION' ),
	'wp-job-manager-extended-location' => defined( 'JOB_MANAGER_VERSION' ) && class_exists( 'WP_Job_Manager_Extended_Location' ),

	'woocommerce' => class_exists( 'Woocommerce' ),
	'woocommerce-bookings' => class_exists( 'Woocommerce' ) && class_exists( 'WC_Bookings' ),
	'woocommerce-social-login' => class_exists( 'Woocommerce' ) && class_exists( 'WC_Social_Login' ),
	'woocommerce-simple-registration' => class_exists( 'Woocommerce' ) && class_exists( 'WooCommerce_Simple_Registration' ),

	'facetwp' => class_exists( 'FacetWP' ),
	'jetpack' => defined( 'JETPACK__VERSION' ),
	'polylang' => defined( 'POLYLANG_VERSION' ) && function_exists( 'pll_register_string' ),
	'visual-composer' => defined( 'WPB_VC_VERSION' ),
	'private-messages' => class_exists( 'Private_Messages' ),

	'tgmpa' => true,
	'ratings' => apply_filters( 'listify_has_ratings', true ),
) );

foreach ( $integrations as $file => $dependancy ) {
	if ( $dependancy ) {
		require( get_template_directory() . sprintf( '/inc/integrations/%1$s/class-%1$s.php', $file ) );
	}
}
