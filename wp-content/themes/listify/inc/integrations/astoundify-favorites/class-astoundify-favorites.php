<?php
/**
 * Astoundify Favorites.
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Integration
 * @author Astoundify
 */

/**
 * Astoundify Favorites.
 *
 * @since 2.0.0
 */
class Listify_Astoundify_Favorites extends Listify_Integration {

	/**
	 * Register integration.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->integration = 'astoundify-favorites';
		$this->has_customizer = true;

		$this->includes = array(
			'widgets/class-widget-author-favorites.php'
		);

		parent::__construct();
	}

	/**
	 * Hook in to WordPress.
	 *
	 * @since 2.0.0
	 */
	public function setup_actions() {
		// Modify plugin behavior.
		add_filter( 'astoundify_favorites_post_types', array( $this, 'favorites_post_types' ) );
		add_filter( 'astoundify_favorites_link_text', array( $this, 'link_text' ), 10, 3 );
		add_filter( 'astoundify_favorites_content_filter', '__return_false' );

		// Modify listing behavior.
		add_filter( 'listify_get_listing_to_array', array( $this, 'listing_to_array' ), 10, 2 );
		add_filter( 'listify_get_listing_favorite_count', array( $this, 'get_favorite_count' ), 10, 2 );

		// Output for JS template.
		add_action( 'listify_content_job_listing_before', array( $this, 'render_js' ) );

		// Output for single listing.
		add_action( 'single_job_listing_meta_after', array( $this, 'render' ), 20 );

		// Modify author.php.
		add_action( 'listify_author_meta', array( $this, 'author_meta' ), 15 );
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Modify the allowed post types that can be favaorited.
	 *
	 * @since 2.0.0
	 *
	 * @param array $post_types Whitelist of post types.
	 * @return array
	 */
	public function favorites_post_types( $post_types ) {
		// Reset to just job listings.
		$post_types = array( 'job_listing' );

		return $post_types;
	}

	/**
	 * Modify the rendered link text depending on the context.
	 *
	 * @since 2.0.0
	 *
	 * @param string $text The current text.
	 * @param int    $target_id Target ID.
	 * @param bool   $is_favorited If the target is currently favorited.
	 * @return string
	 */
	public function link_text( $text, $target_id, $is_favorited ) {
		if ( 'job_listing' !== get_post_type( $target_id ) ) {
			return $text;
		}

		if ( is_singular( 'job_listing' ) ) {
			return $text;
		}

		$text = astoundify_favorites_get_svg( 'heart' ) . astoundify_favorites_count( $target_id );

		return $text;
	}

	/**
	 * Modify the favorites data a listing has access to.
	 *
	 * @since 2.0.0
	 *
	 * @param array           $data Current listing data.
	 * @param Listify_Listing $listing Current listing.
	 * @return array
	 */
	public function listing_to_array( $data, $listing ) {
		// Adjust if the favorite action should show on a card.
		$data['cardDisplay']['favorites'] = get_theme_mod( 'listing-card-display-favorites', true );

		// Send favorites-specific data to the listing.
		$data['favorites'] = array(
			'rendered' => astoundify_favorites_link( $listing->get_id(), '', '' ),
			'count' => astoundify_favorites_count( $listing->get_id() ),
		);

		return $data;
	}

	/**
	 * Return a listing's favorite count.
	 *
	 * @since 2.0.0
	 *
	 * @param int             $count Current count.
	 * @param Listify_Listing $listing Current listnig.
	 * @return int
	 */
	public function get_favorite_count( $count, $listing ) {
		return astoundify_favorites_count( $listing->get_id() );
	}

	/**
	 * Hook in to the single listing template and output a link/count.
	 *
	 * @since 2.0.0
	 */
	public function render() {
		echo astoundify_favorites_link( get_the_ID() ); // WPCS: XSS ok.
	}

	/**
	 * Hook in to the card javascript template and output our custom data.
	 *
	 * @since 2.0.0
	 */
	public function render_js() {
?>

<# if ( data.cardDisplay.favorites ) { #>
	{{{data.favorites.rendered}}}
<# } #>

<?php
	}

	/**
	 * Hook in to the author profile meta on author.php
	 *
	 * @since 2.0.0
	 */
	public function author_meta() {
		$count = astoundify_favorites_user_favorites_count( get_queried_object_id() );
?>

<span class="favorite-count">
<?php
	// Translators: %d Number of favorites.
	echo esc_html( sprintf( _n( '%d Favorite', '%d Favorites', $count, 'listify' ), $count ) );
?>
</span>

<?php
	}

	/**
	 * Register the widgets for the main content and sidebar of the
	 * author.php page template.
	 *
	 * @since 2.0.0
	 */
	public function register_widgets() {
		register_widget( 'Listify_Widget_Author_Favorites' );
	}
}

new Listify_Astoundify_Favorites();
