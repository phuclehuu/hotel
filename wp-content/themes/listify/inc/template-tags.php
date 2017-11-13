<?php
/**
 * Custom template tags for this theme.
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

/**
 * Get a listing.
 *
 * @since 2.0.0
 *
 * @param null|int|WP_Post $post Get an existing listing.
 * @return false|Listify_Listing
 */
function listify_get_listing( $post = null ) {
	$factory = new Listify_Listing_Factory();
	$listing = $factory->get_listing( $post );

	return $listing;
}

/**
 * Get multiple listings.
 *
 * @since 2.0.0
 *
 * @param string $anchor Where to load the results.
 * @param array  $args Modify default arguments.
 * @return bool
 */
function listify_get_listings( $anchor, $args = array() ) {
	$response = array(
		'found_jobs' => false,
		'found_posts' => 0,
		'listings' => array(),
	);

	$listings = get_job_listings( $args );

	if ( ! $listings->have_posts() ) {
		return false;
	}

	$response['found_jobs'] = true;
	$response['found_posts'] = $listings->found_posts;

	$posts = $listings->get_posts();

	if ( empty( $posts ) ) {
		return false;
	}

	foreach ( $listings->get_posts() as $post ) {
		$response['listings'][] = listify_get_listing( $post->ID )->to_array();
	}

	ob_start();
?>

(function () {
	wp.listifyResults.controllers.dataService.response = <?php echo wp_json_encode( $response ); ?>;
	wp.listifyResults.controllers.dataService.addResults( <?php echo wp_json_encode( $anchor ); ?> );
}) ();

<?php

	$script = ob_get_clean();

	wp_enqueue_script( 'listify-results' );
	wp_enqueue_script( 'listify-listings' );

	wp_add_inline_script( 'listify-listings', $script );

	return true;
}

/**
 * Output the listing's JSON-LD.
 *
 * Automatically output for single listings.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_json_ld( $post = null ) {
	$object = listify_get_listing( $post );

	if ( ! $object || ! $object->get_object() ) {
		return;
	}

	if ( 'job_listing' !== $object->get_object()->post_type ) {
		return;
	}

	if ( ! is_singular( 'job_listing' ) ) {
		return;
	}

	$data = $object->get_json_ld();

	if ( empty( $data ) ) {
		return;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $data ) . '</script>';
}
add_action( 'wp_footer', 'listify_the_listing_json_ld' );

/**
 * Get a listing card's CSS class.
 *
 * This is a less-intensive version of the built in WP Job Manager function.
 *
 * @see https://github.com/Automattic/WP-Job-Manager/blob/master/wp-job-manager-template.php#L652
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 * @return string
 */
function listify_get_listing_card_class( $post = null ) {
	if ( ! $post ) {
		$post = get_post();
	}

	$listing = listify_get_listing( $post );

	$classes = array(
		'listing-card',
		'type-job_listing',
		'style-grid',
	);

	// HTML markup for making columns.
	$cols = (
		is_front_page() ||
		'results' === get_theme_mod( 'listing-archive-output', 'map-results' )
	) ? 'col-xs-12 col-md-6 col-lg-4' : 'col-xs-12 col-md-6';

	$classes[] = apply_filters( 'listify_get_card_listing_class_columns', $cols );

	if ( $listing->is_featured() ) {
		$classes[] = 'job_position_featured listing-featured--' . get_theme_mod( 'listing-archive-feature-style', 'outline' );
	}

	return implode( ' ', apply_filters( 'listify_get_listing_card_classes', $classes ) );
}

/**
 * Output the listing's title with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_title( $post = null ) {
	$title = listify_get_listing( $post )->get_title();

	if ( '' === $title ) {
		return;
	}

	$heading = is_singular( 'job_listing' ) ? 'h1' : 'h2';
?>

<<?php echo esc_attr( $heading ); ?> class="job_listing-title">
	<?php echo esc_html( $title ); ?>
</<?php echo esc_attr( $heading ); ?>>

<?php
}

/**
 * Output the listing's location with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_location( $post = null ) {
	$format = get_theme_mod( 'listing-address-format', 'formatted' );
	$location = listify_get_listing( $post )->get_location_formatted();

	if ( ! $location ) {
		return;
	}
?>

<div class="job_listing-location job_listing-location-<?php echo esc_attr( $format ); ?>">
	<?php echo $location; // WPCS: XSS ok. ?>
</div>

<?php
}

/**
 * Output the listing's phone number with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_phone( $post = null ) {
	$phone = listify_get_listing( $post )->get_telephone();
	$link = listify_get_listing( $post )->get_telephone( true );

	if ( ! $phone ) {
		return;
	}
?>

<div class="job_listing-phone">
	<a href="tel:<?php echo esc_attr( $link ); ?>"><?php echo esc_attr( $phone ); ?></a>
</div>

<?php
}

/**
 * Output the listing's featured badge if required.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_featured_badge( $post = null ) {
	if ( ! listify_get_listing( $post )->is_featured() ) {
		return;
	}
?>

<div class="listing-featured-badge">
	<?php echo esc_html( _x( 'Featured', 'featured listing', 'listify' ) ); ?>
</div>

<?php
}

/**
 * Output the listing's email with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_email( $post = null ) {
	$email = listify_get_listing( $post )->get_email();

	if ( ! $email ) {
		return;
	}
?>

<div class="listing-email">
	<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_attr( antispambot( $email ) ); ?></a>
</div>

<?php
}

/**
 * Output the listing's URL with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_url( $post = null ) {
	$url = listify_get_listing( $post )->get_url();

	$base = wp_parse_url( $url );
	$base = $base['host'];

	$attr = apply_filters( 'listify_listing_url_attr', array(
		'href'   => esc_url( $url ),
		'rel'    => 'nofollow',
		'target' => '_blank',
	) );

	$attr_str = '';

	foreach ( $attr as $name => $value ) {
		$attr_str .= false !== $value ? sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) ) : esc_html( " {$name}" );
	}
?>

<div class="job_listing-url">
	<a <?php echo $attr_str; // WPCS: XSS ok. ?>><?php echo esc_attr( $base ); ?></a>
</div>

<?php
}

/**
 * Output the listing's telephone with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_telephone( $post = null ) {
	$telephone = listify_get_listing( $post )->get_telephone();

	if ( ! $telephone ) {
		return;
	}
?>

<div class="job_listing-phone">
	<span><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9,.]/', '', $telephone ) ); ?>"><?php echo esc_attr( $telephone ); ?></a></span>
</div>

<?php
}

/**
 * Output the listing's category breadcrumb with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_category( $post = null ) {
	$types = false;

	if ( ! listify_theme_mod( 'categories-only', true ) ) {
		$types = get_the_term_list(
			get_post()->ID,
			'job_listing_type',
			'<span>',
			'<span class="ion-chevron-right"></span>',
			'</span>'
		);
	}

	$terms = false;

	if ( get_option( 'job_manager_enable_categories' ) ) {
		$crumbs = new Listify_Taxonomy_Breadcrumbs( apply_filters( 'listify_taxonomy_breadcrumbs', array(
			'taxonomy' => 'job_listing_category',
			'sep' => '<span class="ion-chevron-right"></span>',
		) ) );
	}
?>

<div class="content-single-job_listing-title-category">

	<?php if ( $types && ! is_wp_error( $types ) ) : ?>
		<?php echo $types; // WPCS: XSS ok. ?>
		<span class="ion-chevron-right"></span>
	<?php endif; ?>

	<?php if ( ! empty( $crumbs->crumbs ) ) : ?>
		<?php $crumbs->output(); ?>
	<?php endif; ?>

</div>

<?php
}

/**
 * Output the listing's secondary image with wrapper HTML.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 * @param array   $args Modify default arguments.
 */
function listify_the_listing_secondary_image( $post = null, $args = array() ) {
	$defaults = array(
		'size' => 'thumbnail',
		'type' => get_theme_mod( 'listing-archive-card-avatar', 'avatar' ),
		'style' => get_theme_mod( 'listing-archive-card-avatar-style', 'circle' ),
	);

	$args = wp_parse_args( $args, $defaults );

	$image = listify_get_listing( $post )->get_secondary_image( $args );

	if ( ! $image ) {
		return;
	}

	$context = did_action( 'listify_content_job_listing_before' ) ? 'card' : 'single';

	$wrapper_class = array(
		'listing-entry-company-image',
		'listing-entry-company-image--' . esc_attr( $context ),
		'listing-entry-company-image--type-' . esc_attr( $args['type'] ),
		'listing-entry-company-image--style-' . esc_attr( $args['style'] ),
	);
	$wrapper_class = implode( ' ', $wrapper_class );

	$image_class = array(
		'listing-entry-company-image__img',
		'listing-entry-company-image__img--type-' . esc_attr( $args['type'] ),
		'listing-entry-company-image__img--style-' . esc_attr( $args['style'] ),
	);
	$image_class = implode( ' ', $image_class );
?>

<div class="<?php echo esc_attr( $wrapper_class ); ?>">
	<?php if ( 'avatar' === $args['type'] ) : ?>
		<a href="<?php echo esc_url( get_author_posts_url( get_post()->post_author ) ); ?>">
	<?php endif; ?>

	<img class="<?php echo esc_attr( $image_class ); ?>" src="<?php echo  esc_url( $image ); ?>" alt="<?php the_title_attribute(); ?>" />

	<?php if ( 'avatar' === $args['type'] ) : ?>
		</a>
	<?php endif; ?>
</div>

<?php
}

/**
 * Output the listing's "Get Directions" form.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_directions_form( $post = null ) {
	$listing = listify_get_listing( $post );
	$destination = $listing->get_location( 'raw' );

	$attr = apply_filters( 'listify_listing_directions_url_attr', array(
		'href'   => esc_url( $listing->get_map_url() ),
		'rel'    => 'nofollow',
		'target' => '_blank',
		'class'  => 'js-toggle-directions',
		'id'     => 'get-directions',
	) );

	$attr_str = '';

	foreach ( $attr as $name => $value ) {
		$attr_str .= false !== $value ? sprintf( ' %s="%s"', esc_html( $name ), esc_attr( $value ) ) : esc_html( " {$name}" );
	}

	// Destination field type: address or coordinate.
	$destination_type = apply_filters( 'listify_listing_directions_destination_type', 'address', $listing );
?>

<div class="job_listing-directions">
	<a <?php echo $attr_str; // WPCS: XSS ok. ?>><?php esc_html_e( 'Get Directions', 'listify' ); ?></a>

	<div class="job_listing-get-directions" id="get-directions-form">

		<form class="job-manager-form" action="https://maps.google.com/maps" target="_blank">

			<fieldset class="fieldset-starting">
				<label for="daddr"><?php esc_html_e( 'Starting Location', 'listify' ); ?></label>

				<div class="field">
					<?php global $is_chrome; if ( ! $is_chrome || ( is_ssl() && $is_chrome ) ) : ?>
						<i id="get-directions-locate-me" class="js-locate-me locate-me"></i>
					<?php endif; ?>

					<input type="text"  name="saddr" value="" id="get-directions-start">
				</div>

			</fieldset>

			<?php if ( 'address' === $destination_type ) : ?>

			<fieldset class="fieldset-destination">
				<label for="daddr"><?php esc_html_e( 'Destination', 'listify' ); ?></label>
				<div class="field">
					<input type="text" name="daddr" value="<?php echo esc_attr( $destination ); ?>">
				</div>
			</fieldset>

			<?php elseif ( 'coordinate' === $destination_type ) : ?>

				<input type="hidden" name="daddr" value="<?php echo esc_attr( "{$listing->get_lat()},{$listing->get_lng()}" ); ?>">

			<?php endif; ?>

			<p>
				<input type="submit" name="submit" value="<?php esc_attr_e( 'Get Directions', 'listify' ); ?>">
			</p>
		</form>

	</div>

</div>

<?php
}

/**
 * Output a listing's ratings HTML stars.
 *
 * @since 2.0.0
 *
 * @param WP_Post $post The current object.
 */
function listify_the_listing_rating( $post = null ) {
	if ( ! ( listify_has_integration( 'ratings' ) || listify_has_integration( 'wp-job-manager-reviews' ) ) ) {
		return;
	}

	$listing = listify_get_listing( $post );

	$rating = $listing->get_rating_average();
	$rating = round( round( $rating * 2 ) / 2, 1 ); // Get round average for star display.

	// Supported star number.
	$star_num = 5;
	if ( listify_has_integration( 'wp-job-manager-reviews' ) ) {
		$star_num = absint( get_option( 'wpjmr_star_count', 5 ) );
	}

	$full_stars = floor( $rating );
	$half_stars = ceil( $rating - $full_stars );
	$empty_stars = $star_num - $full_stars - $half_stars;

	$context = is_singular( 'job_listing' ) ? 'single' : 'card';
?>

<div class="listing-rating listing-rating--<?php echo esc_attr( $context ); ?>">
	<span class="listing-stars listing-stars--<?php echo esc_attr( $context ); ?>">
		<?php
			echo str_repeat( '<span class="listing-star listing-star--full"></span>', $full_stars ); // WPCS: XSS ok.
			echo str_repeat( '<span class="listing-star listing-star--half"></span>', $half_stars ); // WPCS: XSS ok.
			echo str_repeat( '<span class="listing-star listing-star--empty"></span>', $empty_stars ); // WPCS: XSS ok.
		?>
	</span>

	<span class="listing-rating-count listing-rating-count--<?php echo esc_attr( $context ); ?>">
	<?php
		// Translators: %d Number of reviews.
		$text = esc_html( sprintf( _n( '%d Review', '%d Reviews', $listing->get_rating_count(), 'listify' ), $listing->get_rating_count() ) );

		// Link.
		$link = listify_submit_review_url( $post );
		if ( $link && is_singular() ) {
			$text = '<a href="' . esc_url( $link ) . '">' . $text . '</a>';
		}

		// Output.
		echo $text;
	?>
	</span>
</div>

<?php
}

/**
 * Submit Review URL
 *
 * @since 2.3.0
 *
 * @param object|int $post Post ID or WP_Post Object.
 * @return false|string
 */
function listify_submit_review_url( $post_id = null ) {
	$post = get_post( $post_id );
	$url = false;

	// Bail if closed.
	if ( comments_open( $post ) ) {
		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			if ( listify_has_integration( 'woocommerce' ) ) {
				$url = get_permalink( wc_get_page_id( 'myaccount' ) );
			} else {
				$url = wp_login_url( get_permalink( $post ) );
			}
		} else {
			$url = '#respond';
		}
	}
	return esc_url( apply_filters( 'listify_submit_review_link_anchor', $url ) );
}

/**
 * Determine if a the results map should display.
 *
 * @since 2.0.0
 *
 * @return bool
 */
function listify_results_has_map() {
	if ( ! in_array( get_theme_mod( 'listing-archive-output', 'map-results' ), array( 'map', 'map-results' ), true ) ) {
		return false;
	}

	return true;
}

/**
 * Determine the unit of measure for geolocation results.
 *
 * @since 2.0.0
 *
 * @return string
 */
function listify_results_map_unit() {
	$english = apply_filters( 'listify_map_english_units', array( 'US', 'GB', 'LR', 'MM' ) );

	if ( in_array( get_theme_mod( 'region-bias', false ), $english, true ) ) {
		return 'mi';
	}

	return 'km';
}

/**
 * Get Google Maps API Key
 *
 * @since 2.1.0
 *
 * @return string
 */
function listify_get_google_maps_api_key() {
	$wpjm_api_key = get_option( 'job_manager_google_maps_api_key', false );
	$theme_api_key = get_theme_mod( 'map-behavior-api-key', $wpjm_api_key );
	return esc_attr( trim( $theme_api_key ) );
}

/**
 * Get Google Maps JS API URL
 *
 * @since 1.7.0
 * @return string $url
 */
function listify_get_google_maps_api_url() {
	$base = '//maps.googleapis.com/maps/api/js';

	$args = array(
		'language' => get_locale() ? substr( get_locale(), 0, 2 ) : '',
		'v' => '3.28',
	);

	// Add Places if using autocomplete.
	if ( get_theme_mod( 'search-filters-autocomplete', true ) ) {
		$args['libraries'] = 'places';
	}

	// API key.
	$key = listify_get_google_maps_api_key();

	if ( '' !== $key ) {
		$args['key'] = $key;
	}

	// Region bias.
	$bias = strtolower( get_theme_mod( 'region-bias', '' ) );

	if ( '' !== $bias ) {
		$args['region'] = $bias;

		// Special for China.
		if ( 'cn' === $bias ) {
			$base = 'http://maps.google.cn/maps/api/js';
		}
	}

	$url = esc_url_raw( add_query_arg( $args, $base ) );

	return apply_filters( 'listify_google_maps_api_url', $url, $args );
}

/**
 * Check if the current page is widgetized.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function listify_is_widgetized_page() {
	$widgetized = false;

	$page_templates = apply_filters( 'listify_widgetized_page_templates', array(
		'page-templates/template-home.php',
		'page-templates/template-home-vc.php',
		'page-templates/template-home-slider.php',
		'page-templates/template-widgetized.php',
	) );

	foreach ( $page_templates as $template ) {
		if ( is_page_template( $template ) ) {
			$widgetized = true;

			break;
		}
	}

	return apply_filters( 'listify_is_widgetized_page', $widgetized );
}

/**
 * Check if we ar on a WP Job Manager page.
 *
 * This needs to be outside of the integration since it's called
 * in standard template files.
 *
 * @since 1.0.0
 *
 * @return boolean
 */
function listify_is_job_manager_archive() {
	if ( ! listify_has_integration( 'wp-job-manager' ) ) {
		return false;
	}

	$page = ( is_singular() && has_shortcode( get_post()->post_content, 'jobs' ) ) || is_page_template( 'page-templates/template-archive-job_listing.php' );

	$cpt = is_post_type_archive( 'job_listing' );

	$single = is_singular( 'job_listing' );

	$tax = array(
		'job_listing_category',
		'job_listing_tag',
		'job_listing_type',
		'job_listing_region',
	);

	$tax = is_tax( $tax );

	$search = is_search() && isset( $_GET['listings'] );

	return ( $page || $cpt || /*$single ||*/ $tax || $search );
}

/**
 * Determine if the current WP Job Manager page has a sidebar.
 *
 * @since 1.0.0
 *
 * @return bool
 */
function listify_job_listing_archive_has_sidebar() {
	$sidebar = is_active_sidebar( 'archive-job_listing' );
	$map     = 'side' === get_theme_mod( 'listing-archive-map-position', 'side' );
	$facetwp = false;

	if ( listify_has_integration( 'facetwp' ) ) {
		if ( 'side' === get_theme_mod( 'listing-archive-facetwp-position', 'side' ) ) {
			$facetwp = true;
		}
	}

	if (
		$sidebar ||
		$facetwp ||
		has_action( 'listify_sidebar_archive_job_listing_after' ) &&
		! $map
	) {
		return true;
	}

	return false;
}

/**
 * Get the top level taxonomy.
 *
 * Either job_listing_type or job_listing_category
 *
 * @since 1.0.0
 *
 * @return string
 */
function listify_get_top_level_taxonomy() {
	$categories_enabled = get_option( 'job_manager_enable_categories' );
	$categories_only = get_theme_mod( 'categories-only', true );

	$tax = '';

	if ( $categories_enabled && $categories_only ) {
		$tax = 'job_listing_category';
	} else {
		$tax = 'job_listing_type';
	}

	return $tax;
}

if ( ! function_exists( 'listify_get_theme_menu' ) ) :
	/**
	 * Get a nav menu object.
	 *
	 * @uses get_nav_menu_locations To get all available locations
	 * @uses get_term To get the specific theme location
	 *
	 * @since 1.0.0
	 *
	 * @param string $theme_location The slug of the theme location.
	 * @return object $menu_obj The found menu object
	 */
	function listify_get_theme_menu( $theme_location ) {
		$theme_locations = get_nav_menu_locations();

		if ( ! isset( $theme_locations[ $theme_location ] ) ) {
			return false;
		}

		$menu_obj = get_term( $theme_locations[ $theme_location ], 'nav_menu' );

		if ( ! $menu_obj ) {
			return false;
		}

		return $menu_obj;
	}
endif;

if ( ! function_exists( 'listify_get_theme_menu_name' ) ) :
	/**
	 * Get a nav menu name
	 *
	 * @uses listify_get_theme_menu To get the menu object
	 *
	 * @since 1.0.0
	 *
	 * @param string $theme_location The slug of the theme location.
	 * @return string The name of the nav menu location
	 */
	function listify_get_theme_menu_name( $theme_location ) {
		$menu_obj = listify_get_theme_menu( $theme_location );
		$default  = _x( 'Menu', 'noun', 'listify' );

		if ( ! $menu_obj ) {
			return $default;
		}

		if ( ! isset( $menu_obj->name ) ) {
			return $default;
		}

		return $menu_obj->name;
	}
endif;

/**
 * Get the days of the week in an array of numerals.
 *
 * @since 1.0.0
 *
 * @return array
 */
function listify_get_days_of_week() {
	$days = array( 0, 1, 2, 3, 4, 5, 6 );
	$start = get_option( 'start_of_week' );

	$first = array_splice( $days, $start, count( $days ) - $start );
	$second = array_splice( $days, 0, $start );
	$days = array_merge( $first, $second );

	return $days;
}

/**
 * Get Days Name.
 *
 * @since 2.1.0
 */
function listify_get_days() {
	// Days.
	$days_name = array(
		0 => 'sun',
		1 => 'mon',
		2 => 'tue',
		3 => 'wed',
		4 => 'thu',
		5 => 'fri',
		6 => 'sat',
	);

	// Get numeral days.
	$num_days = listify_get_days_of_week();

	// Format days.
	$days = array();
	foreach ( $num_days as $num_day ) {
		$days[ $num_day ] = $days_name[ $num_day ];
	}

	return $days;
}

/**
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since 1.0.0
 *
 * @param object $comment The current comment.
 * @param array  $args Comment arguments.
 * @param int    $depth Comment depth.
 */
function listify_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;

	$post = get_post();
?>

<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">

	<article id="comment-<?php comment_ID(); ?>" class="comment row">
		<header class="comment-author vcard col-md-2 col-sm-3 col-xs-12">
			<?php echo get_avatar( $comment, 100 ); ?>
		</header><!-- .comment-meta -->

		<section class="comment-content comment col-md-10 col-sm-9 col-xs-12">

			<cite>
				<b class="fn"><?php echo esc_html( get_comment_author() ); ?></b> 

				<?php if ( is_singular() && ( $comment->user_id === $post->post_author && $post->post_author > 0 ) ) : ?>
					<span class="listing-owner"><?php esc_html_e( 'Listing Owner', 'listify' ); ?></span>
				<?php endif; ?>
			</cite>

			<div class="comment-meta">
				<?php do_action( 'listify_comment_meta_before', $comment ); ?>

				<?php
					comment_reply_link( wp_parse_args(
						array(
							'reply_text' => '<i class="ion-ios-chatboxes-outline"></i>',
							'after'      => ' ',
							'depth'      => $depth,
							'max_depth'  => $args['max_depth'],
						)
					, $args ) );
				?>

				<?php edit_comment_link( __( '<span class="ion-edit"></span>', 'listify' ) ); ?>

				<?php do_action( 'listify_comment_meta_after', $comment ); ?>
			</div>

			<?php if ( '0' === $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'listify' ); ?></p>
			<?php endif; ?>

			<?php do_action( 'listify_comment_before', $comment ); ?>

			<?php comment_text(); ?>

			<?php do_action( 'listify_comment_after', $comment ); ?>

			<?php
				printf( '<a href="%1$s" class="comment-ago"><time datetime="%2$s">%3$s</time></a>',
					esc_url( get_comment_link( $comment->comment_ID ) ),
					get_comment_time( 'c' ),
					// Translators: %s Time difference.
					esc_attr( sprintf( __( '%s ago', 'listify' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ) )
				);
			?>
		</section><!-- .comment-content -->

	</article><!-- #comment-## -->

<?php
}

if ( ! function_exists( 'listify_content_nav' ) ) :
	/**
	 * Display navigation to next/previous pages when applicable
	 *
	 * @since 1.0.0
	 *
	 * @param string $nav_id Identify the current navigation.
	 */
	function listify_content_nav( $nav_id ) {
		global $wp_query, $post;

		// Don't print empty markup on single pages if there's nowhere to navigate.
		if ( is_single() ) {
			$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
			$next = get_adjacent_post( false, '', false );

			if ( ! $next && ! $previous ) {
				return;
			}
		}

		// Don't print empty markup in archives if there's only one page.
		if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) ) {
			return;
		}

		$nav_class = ( is_single() ) ? 'post-navigation' : 'paging-navigation';

		?>

		<nav id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo esc_attr( $nav_class ); ?>">
		<h1 class="screen-reader-text"><?php esc_html_e( 'Post navigation', 'listify' ); ?></h1>

		<?php
			$big = 999999999;

			echo paginate_links( array(
				'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => $wp_query->max_num_pages,
			) );
		?>

		</nav><!-- #<?php echo esc_html( $nav_id ); ?> -->

		<?php
	}
endif;


/**
 * Listify Get Formatted Address
 *
 * This is modified version from WooCommerce WC_Countries()->get_formatted_address()
 *
 * @since 2.0.0
 * @author Automattic
 *
 * @param array $args Modify default arguments.
 * @return string
 */
function listify_get_formatted_address( $args ) {
	$default_args = array(
		'first_name'    => '',
		'last_name'     => '',
		'company'       => '',
		'street_number' => '',
		'address_1'     => '',
		'address_2'     => '',
		'city'          => '',
		'state'         => '',
		'full_state'    => '',
		'postcode'      => '',
		'country'       => '',
		'full_country'  => '',
	);

	$args = array_map( 'trim', wp_parse_args( $args, $default_args ) );

	// Get all formats.
	$formats = lisfity_get_address_formats();

	// Get format for the address' country.
	$format = ( $args['country'] && isset( $formats[ $args['country'] ] ) ) ? $formats[ $args['country'] ] : $formats['default'];

	// Substitute address parts into the string.
	$replace = array_map( 'esc_html', apply_filters( 'listify_formatted_address_replacements', array(
		'{street_number}'    => $args['street_number'],
		'{address_1}'        => $args['address_1'],
		'{address_2}'        => $args['address_2'],
		'{city}'             => $args['city'],
		'{state}'            => $args['full_state'],
		'{postcode}'         => $args['postcode'],
		'{country}'          => $args['full_country'],
		'{address_1_upper}'  => strtoupper( $args['address_1'] ),
		'{address_2_upper}'  => strtoupper( $args['address_2'] ),
		'{city_upper}'       => strtoupper( $args['city'] ),
		'{state_upper}'      => strtoupper( $args['full_state'] ),
		'{state_code}'       => strtoupper( $args['state'] ),
		'{postcode_upper}'   => strtoupper( $args['postcode'] ),
		'{country_upper}'    => strtoupper( $args['country'] ),
	), $args ) );

	// Filter to Display Country.
	if ( ! apply_filters( 'listify_listing_address_display_country', false ) ) {
		$replace['{country}'] = '';
	}

	$replace = array_map( 'esc_html', $replace );

	$formatted_address = str_replace( array_keys( $replace ), $replace, $format );

	// Clean up white space.
	$formatted_address = preg_replace( '/  +/', ' ', trim( $formatted_address ) );
	$formatted_address = preg_replace( '/\n\n+/', "\n", $formatted_address );

	// Break newlines apart and remove empty lines/trim commas and white space.
	$formatted_address = array_filter( array_map( 'listify_trim_formatted_address_line', explode( "\n", $formatted_address ) ) );

	// Add html breaks.
	$formatted_address = implode( '<br/>', $formatted_address );

	// We're done!
	return $formatted_address;
}

/**
 * Utility: Trim white space and commas off a line.
 *
 * @since 2.0.0
 * @author Automattic
 *
 * @param  string $line Line to format.
 * @return string
 */
function listify_trim_formatted_address_line( $line ) {
	return trim( $line, ', ' );
}

/**
 * Get Country Address Formats
 *
 * This is modified version from WooCommerce WC_Countries()->get_formatted_address()
 *
 * @since 2.0.0
 * @author Automattic
 */
function lisfity_get_address_formats() {
	// Common formats.
	$postcode_before_city = "{address_1}\n{address_2}\n{postcode} {city}\n{country}";
	$street_after = "{address_1} {street_number}\n{address_2}\n{postcode} {city}\n{country}";

	// Define address formats.
	$address_formats = array(
		'default' => "{address_1}\n{address_2}\n{city}\n{state}\n{postcode}\n{country}",
		'AU' => "{address_1}\n{address_2}\n{city} {state} {postcode}\n{country}",
		'AT' => $postcode_before_city,
		'AW' => "{address_1}\n{address_2}\n{postcode} {city}\n{country}",
		'BE' => $postcode_before_city,
		'BR' => $street_after,
		'CA' => "{address_1}\n{address_2}\n{city} {state} {postcode}\n{country}",
		'CH' => $postcode_before_city,
		'CL' => "{address_1}\n{address_2}\n{state}\n{postcode} {city}\n{country}",
		'CN' => "{country} {postcode}\n{state}, {city}, {address_2}, {address_1}",
		'CW' => "{address_1}\n{address_2}\n{city}\n{country}",
		'CZ' => $postcode_before_city,
		'DE' => $street_after,
		'DK' => "{address_1}\n{address_2}\n{postcode} {city}\n{country}",
		'EE' => $postcode_before_city,
		'FI' => $postcode_before_city,
		'FR' => "{address_1}\n{address_2}\n{postcode} {city_upper}\n{country}",
		'HK' => "{address_1}\n{address_2}\n{city_upper}\n{state_upper}\n{country}",
		'HU' => "{city}\n{address_1}\n{address_2}\n{postcode}\n{country}",
		'IE' => "{address_1}\n{address_2}\n{postcode} {city}\n{country}",
		'IN' => "{address_1}\n{address_2}\n{city} - {postcode}\n{state}, {country}",
		'IS' => $postcode_before_city,
		'IT' => $street_after,
		'JP' => "{postcode}\n{state}{city}{address_1}\n{address_2}",
		'TW' => "{address_1}\n{address_2}\n{state}, {city} {postcode}\n{country}",
		'LI' => $postcode_before_city,
		'NL' => $street_after,
		'NZ' => "{address_1}\n{address_2}\n{city} {postcode}\n{country}",
		'NO' => $postcode_before_city,
		'PL' => $postcode_before_city,
		'SG' => "{address_1}\n{address_2}\n{country}",
		'SK' => $postcode_before_city,
		'SI' => $postcode_before_city,
		'SR' => "{address_1}\n{address_2}\n{postcode} {city}\n{country}",
		'ES' => "{address_1}\n{address_2}\n{postcode} {city}\n{state}\n{country}",
		'SE' => $postcode_before_city,
		'TR' => "{address_1}\n{address_2}\n{postcode} {city} {state}\n{country}",
		'US' => "{address_1}\n{address_2}\n{city}, {state_code} {postcode}\n{country}",
		'VN' => "n{address_1}\n{city}\n{country}",
	);

	// Add treet number in address 1.
	foreach ( $address_formats as $country => $format ) {
		// No street number, add it.
		if ( false === strpos( $format, '{street_number}' ) ) {
			$address_formats[ $country ] = str_replace( '{address_1}', '{street_number} {address_1}', $format );
		}
	}

	$address_formats = apply_filters( 'woocommerce_localisation_address_formats', $address_formats );
	$address_formats = apply_filters( 'listify_address_formats', $address_formats );

	return $address_formats;
}

/**
 * Output theme (and child theme) version number in meta tag.
 *
 * @since 2.1.0
 *
 * @param string $gen  Generator.
 * @param string $type Type.
 * @return string
 */
function listify_generator( $gen, $type ) {
	// Listify.
	$theme = wp_get_theme( get_template() );
	$content = $theme->Name . ' ' . $theme->Version;

	// Child theme data.
	if ( is_child_theme() ) {
		$child_theme = wp_get_theme( get_stylesheet() );
		$content .= '/' . $child_theme->Name . ' ' . $child_theme->Version;
	}

	switch ( $type ) {
		case 'html':
			$gen .= "\n" . '<meta name="generator" content="' . esc_attr( $content ) . '">';
			break;
		case 'xhtml':
			$gen .= "\n" . '<meta name="generator" content="' . esc_attr( $content ) . '" />';
			break;
	}
	return $gen;
}
add_action( 'get_the_generator_html', 'listify_generator', 10, 2 );
add_action( 'get_the_generator_xhtml', 'listify_generator', 10, 2 );

/**
 * Check if the current page has a map on it.
 *
 * @since 2.0.4
 *
 * @return bool
 */
function listify_page_has_map() {
	$has = false;

	$has = ( get_post() && has_shortcode( get_the_content(), 'jobs' ) );

	if ( ! $has ) {
		$has = is_post_type_archive( 'job_listing' );
	}

	if ( ! $has ) {
		$has = is_tax( array(
			'job_listing_category',
			'job_listing_type',
			'job_listing_tag',
			'job_listing_region',
		) );
	}

	return $has;
}

/**
 * Sort options for listing filters.
 *
 * @since 2.1.0
 *
 * @return array
 */
function listify_get_sort_options() {
	$options = array(
		'date-desc' => __( 'Newest First', 'listify' ),
		'date-asc' => __( 'Oldest First', 'listify' ),
		'random' => __( 'Random', 'listify' ),
	);

	if ( listify_has_integration( 'ratings' ) || listify_has_integration( 'wp-job-manager-reviews' ) ) {
		$options['rating-desc'] = __( 'Highest Rating', 'listify' );
		$options['rating-asc'] = __( 'Lowest Rating', 'listify' );
	}

	if ( listify_has_integration( 'wp-job-manager-stats' ) ) {
		$options['stats-visits-desc'] = __( 'Most Views', 'listify' );
	}

	return $options;
}

/**
 * Modify Query Args By Sort Options.
 *
 * @since 2.1.0
 *
 * @param array  $query_args  WP Query Args.
 * @param string $sort_option Selected Sort Option.
 * @return array
 */
function listify_sort_listings_query( $query_args, $sort_option ) {
	if ( 'date-desc' === $sort_option ) { // Newest First (default).
		$query_args['orderby'] = 'date';
		$query_args['order'] = 'DESC';
	} elseif ( 'date-asc' === $sort_option ) { // Oldest First.
		$query_args['orderby'] = 'date';
		$query_args['order'] = 'ASC';
	} elseif ( 'rating-desc' === $sort_option ) { // Highest Rating.
		if ( listify_has_integration( 'wp-job-manager-reviews' ) ) {
			$query_args['meta_key'] = '_average_rating';
		} else {
			$query_args['meta_key'] = 'rating';
		}
		$query_args['orderby'] = array(
			'meta_value_num' => 'DESC',
			'comment_count'  => 'DESC',
		);
	} elseif ( 'rating-asc' === $sort_option ) { // Lowest Rating.
		if ( listify_has_integration( 'wp-job-manager-reviews' ) ) {
			$query_args['meta_key'] = '_average_rating';
		} else {
			$query_args['meta_key'] = 'rating';
		}
		$query_args['orderby'] = array(
			'meta_value_num' => 'ASC',
			'comment_count'  => 'DESC',
		);
	} elseif ( 'stats-visits-desc' === $sort_option ) { // Most Visited.
		$query_args['meta_key'] = '_wpjms_visits_total';
		$query_args['orderby'] = 'meta_value_num';
		$query_args['order'] = 'DESC';
	} elseif ( 'random' === $sort_option ) { // Random.
		$query_args['orderby'] = 'rand';
	}

	return $query_args;
}

/**
 * Login Form
 *
 * @since 2.1.0
 */
function listify_login_form( $form_id = 'listify-loginform' ) {
	if ( is_user_logged_in() ) {
		return;
	}
?>

<?php do_action( 'listify_login_form_before', $form_id ); ?>

<?php wp_login_form( array(
	'form_id' => $form_id,
) ); ?>

<?php do_action( 'listify_login_form', $form_id ); ?>

<p class="forgot-password">
	<?php echo listify_register_link(); ?>
	<?php echo listify_lostpassword_link(); ?>
</p>

<?php do_action( 'listify_login_form_after', $form_id ); ?>

<?php
}

/**
 * Listify Register Link
 *
 * @since 2.3.0
 */
function listify_register_link() {
	$link = '<a href="' . esc_url( wp_registration_url() ) . '">' . esc_html__( 'Register', 'listify' ) . '</a>&nbsp;|&nbsp;';
	return apply_filters( 'listify_register_link', $link );
}

/**
 * Listify Lost Password Link
 *
 * @since 2.3.0
 */
function listify_lostpassword_link() {
	$link = '<a href="' . esc_url( wp_lostpassword_url() ) . '">' . esc_html__( 'Lost your password?', 'listify' ) . '</a>';
	return apply_filters( 'listify_lostpassword_link', $link );
}

/**
 * Load Login Form WP Footer.
 *
 * @since 1.0.0
 */
function listify_login_form_wp_footer() {
	if ( is_user_logged_in() ) {
		return;
	}
	get_template_part( 'popup-login-form' );
}
add_action( 'wp_footer', 'listify_login_form_wp_footer' );

/**
 * Get Current GMT Time
 *
 * @since 2.1.0
 *
 * @param string $format     PHP Time Format.
 * @param int    $gmt_offset GMT Offset.
 * @return string
 */
function listify_get_current_time( $format, $gmt_offset ) {
	return date( $format, time() + ( intval( $gmt_offset ) * HOUR_IN_SECONDS ) );
}

/**
 * Sanitize Business Hours
 *
 * @since 2.1.0
 */
function listify_sanitize_business_hours( $datas ) {
	// If no data, return empty data block.
	if ( ! is_array( $datas ) || ! $datas ) {
		return array();
	}

	// Get days.
	$days = listify_get_days();

	// Old datas, reformat it with new structure:
	if ( isset( $datas[1] ) ) {
		$old_datas = array();
		foreach ( $datas as $day_index => $data ) {
			$day = $days[ $day_index ];
			$old_datas[ $day ][0] = array(
				'open'  => isset( $data['start'] ) ? $data['start'] : '',
				'close' => isset( $data['end'] ) ? $data['end'] : '',
			);
		}
		$datas = $old_datas;
	}

	// Sanitize datas.
	$new_datas = array();
	foreach ( $datas as $day => $hours ) {
		if ( in_array( $day, $days ) && is_array( $hours ) ) {
			$i = 0;
			foreach ( $hours as $hour ) {
				if ( isset( $hour['open'], $hour['close'] ) && $hour['open'] && $hour['close'] ) {
					$new_datas[ $day ][ $i ] = $hour;
				}
				$i++;
			}
		}
	}

	return $new_datas;
}

/**
 * Sanitize Coordinate.
 *
 * @since 2.1.0
 *
 * @param string $input Coordinate input.
 * @return string
 */
function listify_sanitize_coordinate( $input ) {
	$default = '42.0616453, -88.2670675';
	$input = explode( ',', $input );
	$input = array_map( 'trim', $input );

	if ( ! isset( $input[0], $input[1] ) || ! is_numeric( $input[0] ) || ! is_numeric( $input[1] ) ) {
		return $default;
	}

	$lat = $input[0];
	$lng = $input[1];

	if ( $lat < -90 || $lat > 90 ) {
		return $default;
	}
	if ( $lng < -180 || $lng > 180 ) {
		return $default;
	}

	return implode( ', ', $input );
}

/**
 * Search This Location Button.
 *
 * @since 2.2.1
 *
 * @return string Search this location button HTML.
 */
function listify_get_search_this_location_button() {
	$button = '<a href="#" id="search-this-location">' . esc_html__( 'Search This Location', 'listify' ) . '</a>';
	return apply_filters( 'listify_search_this_location_button', $button );
}

/**
 * Determine what social profiles are linked to.
 *
 * @since 2.3.0
 *
 * @return string 'listing' or 'user'. User requires WooCommerce.
 */
function listify_get_social_profile_association() {
	if ( ! listify_has_integration( 'woocommerce' ) ) {
		return 'listing';
	}

	return get_theme_mod( 'social-association', 'user' );
}
