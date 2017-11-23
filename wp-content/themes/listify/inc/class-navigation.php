<?php
/**
 * Custom navigation functionality.
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Navigation
 * @author Astoundify
 */
class Listify_Navigation {

	/**
	 * Hook in to WordPress
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_filter( 'wp_page_menu_args', array( $this, 'always_show_home' ) );

		add_filter( 'nav_menu_css_class', array( $this, 'popup_trigger_class' ), 10, 3 );

		// Tertiary.
		add_action( 'listify_page_before', array( $this, 'tertiary_menu' ) );

		// Avatar.
		add_filter( 'walker_nav_menu_start_el', array( $this, 'avatar_item' ), 10, 4 );
		add_filter( 'nav_menu_css_class', array( $this, 'avatar_item_class' ), 10, 3 );

		// Search.
		add_filter( 'wp_nav_menu_items', array( $this, 'search_icon' ), 1, 2 );

		// Megamenu.
		add_filter( 'wp_nav_menu_items', array( $this, 'taxonomy_mega_menu' ), 0, 2 );
	}

	/**
	 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Current arguments.
	 * @return array $args
	 */
	public function always_show_home( $args ) {
		$args['show_home'] = true;

		return $args;
	}

	/**
	 * Custom Account menu item.
	 *
	 * Look for a menu item with a title of `{{account}}` and replace the
	 * content with information about the current account.
	 *
	 * @since 1.0.0
	 *
	 * @param string $item_output Current item output.
	 * @param object $item Current item.
	 * @param int    $depth Current depth.
	 * @param array  $args Nav menu item arguments.
	 * @return string $item_output
	 */
	public function avatar_item( $item_output, $item, $depth, $args ) {
		if ( '{{account}}' !== $item->title ) {
			return $item_output;
		}

		$user = wp_get_current_user();

		if ( ! is_user_logged_in() ) {
			$display_name = apply_filters( 'listify_account_menu_guest_label', __( 'Guest', 'listify' ) );

			$avatar = '';
		} else {
			if ( $user->first_name ) {
				$display_name = $user->first_name;
			} else {
				$display_name = $user->display_name;
			}

			$display_name = apply_filters( 'listify_acount_menu_user_label', $display_name, $user );

			$avatar =
				'<div class="current-account-avatar" data-href="' . esc_url( apply_filters( 'listify_avatar_menu_link', get_author_posts_url( $user->ID, $user->user_nicename ) ) ) .
				'">' .
				get_avatar( $user->ID, 90 )
				. '</div>';
		}

		$item_output = str_replace( '{{account}}', $avatar . $display_name, $item_output );

		return $item_output;
	}

	/**
	 * If the menu item has the `{{account}}` tag add a custom class to the item.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $classes Current nav item classes.
	 * @param object $item Current nav item.
	 * @param array  $args Arguments.
	 * @return array $classes
	 */
	public function avatar_item_class( $classes, $item, $args ) {
		if ( 'primary' !== $args->theme_location ) {
			return $classes;
		}

		if ( '{{account}}' !== $item->title || ! is_user_logged_in() ) {
			return $classes;
		}

		$classes[] = 'account-avatar';

		return $classes;
	}

	/**
	 * Add a special class to popup menu items.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $classes Current nav item classes.
	 * @param object $item Current nav item.
	 * @param array  $args Arguments.
	 * @return array $classes
	 */
	public function popup_trigger_class( $classes, $item, $args ) {
		$popup = array_search( 'popup', $classes, true );

		if ( false === $popup ) {
			remove_filter( 'nav_menu_link_attributes', array( $this, 'popup_trigger_attributes' ), 10, 3 );

			return $classes;
		} else {
			unset( $classes[ $popup ] );

			add_filter( 'nav_menu_link_attributes', array( $this, 'popup_trigger_attributes' ), 10, 3 );
		}

		return $classes;
	}

	/**
	 * Callback for adding a custom class to a menu item.
	 *
	 * @todo I'm pretty sure this can be done directly now.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $atts Current item attributes.
	 * @param object $item Current item.
	 * @param arrray $args Current item arguments.
	 * @return array $atts
	 */
	public function popup_trigger_attributes( $atts, $item, $args ) {
		$atts['class'] = 'popup-trigger-ajax';

		if ( in_array( 'popup-wide', $item->classes, true ) ) {
			$atts['class'] .= ' popup-wide';
		}

		if ( in_array( 'popup-split', $item->classes, true ) ) {
			$atts['class'] .= ' popup-split';
		}

		return $atts;
	}

	/**
	 * Output a search icon in the menu items.
	 *
	 * @since 1.0.0
	 *
	 * @param string $items Current menu items.
	 * @param array  $args Current navigation menu arguments.
	 * @return string
	 */
	public function search_icon( $items, $args ) {
		if ( 'primary' !== $args->theme_location || ! listify_theme_mod( 'nav-search', true ) ) {
			return $items;
		}

		$icon = '<li class="menu-item menu-type-link menu-item-search"><a href="#search-header" data-toggle="#search-header" class="search-overlay-toggle"></a></li>';

		$position = get_theme_mod( 'nav-search', 'left' );

		if ( 'left' === $position ) {
			return $icon . $items;
		} elseif ( 'right' === $position ) {
			return $items . $icon;
		}

		return $items;
	}

	/**
	 * Output the tertiary menu.
	 *
	 * @since 1.0.0
	 */
	function tertiary_menu() {
		global $post, $wp_query, $listify_woocommerce;

		$enabled = (bool) get_post_meta( $post->ID, 'enable_tertiary_navigation', true );

		if ( ! $enabled ) {
			return;
		}

		// Hack based on where our page titles fall.
		$wp_query->in_the_loop = false;

		ob_start();

		wp_nav_menu( array(
			'theme_location' => 'tertiary',
			'container_class' => 'navigation-bar tertiary nav-menu',
			'menu_class' => 'tertiary nav-menu',
			'fallback_cb' => false,
		) );

		$menu = ob_get_clean();

		if ( '' === $menu ) {
			return;
		}

		if ( listify_has_integration( 'woocommerce' ) ) {
			remove_filter( 'the_title', 'wc_page_endpoint_title' );
		}
?>

<nav class="tertiary-navigation">
	<div class="container">
		<a href="#" class="navigation-bar-toggle">
			<i class="ion-navicon-round"></i>
			<?php echo esc_attr( listify_get_theme_menu_name( 'tertiary' ) ); ?>
		</a>
		<div class="navigation-bar-wrapper">
			<?php echo $menu; // WPCS: XSS ok. ?>
		</div>
	</div>
</nav><!-- #site-navigation -->

<?php
if ( listify_has_integration( 'woocommerce' ) ) {
	add_filter( 'the_title', 'wc_page_endpoint_title' );
}
	}

	/**
	 * Output the megamenu.
	 *
	 * @since 1.0.0
	 *
	 * @param string $items Current menu items.
	 * @param array  $args Current menu arguments.
	 * @return string
	 */
	public function taxonomy_mega_menu( $items, $args ) {
		$taxonomy = listify_theme_mod( 'nav-megamenu', 'job_listing_category' );

		if ( 'none' === $taxonomy ) {
			return $items;
		}

		if ( 'secondary' !== $args->theme_location ) {
			return $items;
		}

		$taxonomy = get_taxonomy( $taxonomy );

		if ( ! $taxonomy || is_wp_error( $taxonomy ) ) {
			return $items;
		}

		global $listify_strings;

		$link = sprintf(
			// Translators: %1$s URL to all terms. %2$s Label for menu item.
			'<a href="%s">' . __( 'Browse %s', 'listify' ) . '</a>',
			get_post_type_archive_link( 'job_listing' ),
			str_replace( $listify_strings->label( 'singular' ) . ' ', '', $taxonomy->labels->name )
		);

		$args = apply_filters( 'listify_mega_menu_list', array(
			'taxonomy' => $taxonomy->name,
			'parent' => 0,
			'orderby' => 'name',
		) );

		$terms = listify_get_terms( $args );
		$submenu = array();
		$dropdown = array();

		if ( empty( $terms ) ) {
			return $items;
		}

		// Translators: %s taxonomy label.
		$dropdown[] = '<option value="">' . sprintf( __( 'Choose a %s', 'listify' ), $taxonomy->labels->singular_name ) . '</option>';

		foreach ( $terms as $term ) {
			$submenu[] = sprintf(
				// Translators: %1$s URL to term. %2$s Title of term. %3$d Number of terms. %4$s Name of term.
				'<a href="%1$s" title="%2$s"><span class="category-count">%3$d</span>%4$s</a>',
				esc_url( get_term_link( $term ) ),
				// Translators: %s Name of term.
				sprintf( __( 'View all listings in %s', 'listify' ), $term->name ),
				absint( $term->count ),
				esc_attr( $term->name )
			);

			$dropdown[] = sprintf(
				apply_filters( 'listify_mega_menu_mobile_option', '<option value="%s">%s&nbsp;(%d)</option>' ),
				esc_url( get_term_link( $term ) ),
				esc_attr( $term->name ),
				absint( $term->count )
			);
		}

		$submenu = '<ul><li>' . implode( '</li><li>', $submenu ) . '</li></ul>';
		$dropdown = '<select class="postform" name="' . $taxonomy->name . '" id="' . $taxonomy->name . '">' . implode( '', $dropdown ) . '</select>';

		$submenu =
			'<ul class="sub-menu category-list">' .
			'<form id="job_listing_tax_mobile" action="' . home_url() . '" method="get">' . $dropdown . '</form>
			<div class="container">
			<div class="mega-category-list-wrapper">' . $submenu . '</div>
			</div>
			</ul>';

		return '<li id="categories-mega-menu" class="ion-navicon-round menu-item menu-type-link">' . $link . $submenu . '</li>' . $items;
	}

}

new Listify_Navigation();
