<?php
/**
 * Purchase add-ons.
 *
 * @since 1.4.0
 *
 * @package Listify
 * @category Admin
 * @author Astoundify
 */
class Listify_Admin_Addons {

	/**
	 * Hook in to WordPress.
	 *
	 * @since 1.4.0
	 */
	public function __construct() {
		if ( ! apply_filters( 'listify_show_addons_page', true ) ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'addons_menu' ), 1000 );
	}

	/**
	 * Add admin menu.
	 *
	 * @since 1.4.0
	 */
	public function addons_menu() {
		add_submenu_page( 'edit.php?post_type=job_listing', __( 'Add-ons', 'listify' ),  __( 'Add-ons', 'listify' ) , 'manage_options', 'listify-addons', array( $this, 'output' ) );
	}

	/**
	 * Admin menu output.
	 *
	 * @since 1.4.0
	 */
	public static function output() {
		// Cache.
		$addons = get_transient( 'listify_addons_data' );
		$addons = is_array( $addons ) ? $addons : array();

		// No cache, get new data.
		if ( ! $addons ) {
			$addons_json_url = apply_filters( 'listify_addons_json_url', 'https://astoundify.com/wp-json/wp/v2/download' );

			$addons_json = wp_remote_get( esc_url_raw( $addons_json_url ), array(
				'sslverify' => false,
				'user-agent' => 'Listify Addons Page',
			) );

			if ( ! is_wp_error( $addons_json ) ) {
				$addons_data = json_decode( wp_remote_retrieve_body( $addons_json ), true );

				foreach ( $addons_data as $addon_data ) {
					$addons[] = array(
						'title' => isset( $addon_data['title']['rendered'] ) ? $addon_data['title']['rendered'] : '',
						'excerpt' => isset( $addon_data['excerpt']['rendered'] ) ? $addon_data['excerpt']['rendered'] : '',
						'link' => isset( $addon_data['link'] ) ? $addon_data['link'] : '',
					);
				}

				/**
				 * Now try to get Myles'.
				 *
				 * @see https://github.com/Astoundify/listify/issues/1512
				 */
				$tripflex = wp_safe_remote_get( 'https://plugins.smyl.es/wp-json/smyles/v1/plugins', array(
					'sslverify' => false,
					'user-agent' => 'Listify Addons Page',
				) );

				if ( ! is_wp_error( $tripflex ) ) {
					$more_addons = json_decode( wp_remote_retrieve_body( $tripflex ), true );

					foreach ( $more_addons as $addon_data ) {
						$addons[] = array(
							'title' => isset( $addon_data['title'] ) ? $addon_data['title'] : '',
							'excerpt' => isset( $addon_data['excerpt'] ) ? $addon_data['excerpt'] : '',
							'link' => isset( $addon_data['link'] ) ? $addon_data['link'] : '',
						);
					}
				}

				if ( $addons ) {
					set_transient( 'listify_addons_data', $addons, WEEK_IN_SECONDS );
				}
			} // End if().
		} // End if().
?>

<style>
	.listify_addons_wrap .products {
		overflow: hidden;
	}

	.listify_addons_wrap .products li {
		float: left;
		margin: 0 1em 1em 0 !important;
		padding: 0;
		vertical-align: top;
		width: 300px;
		min-height: 290px;
	}

	.listify_addons_wrap .products li .product-inner {
		text-decoration: none;
		color: inherit;
		border: 1px solid #ddd;
		display: block;
		min-height: 220px;
		overflow: hidden;
		background: #f5f5f5;
		box-shadow:
			inset 0 1px 0 rgba(255,255,255,0.2),
			inset 0 -1px 0 rgba(0,0,0,0.1);
	}

	.listify_addons_wrap .products li h3 {
		margin: 0 !important;
		padding: 20px !important;
		background: #fff;
		line-height: 1.5;
		font-size: 14px;
	}

	.listify_addons_wrap .products li p {
		padding: 20px !important;
		margin: 0 !important;
		border-top: 1px solid #f1f1f1;
	}

	.listify_addons_wrap .products li a:hover,
	.listify_addons_wrap .products li a:focus {
		background-color: #fff;
	}
</style>

<div class="wrap listify listify_addons_wrap">
	<h1><?php esc_html_e( 'Extend Your Website&#39;s Functionality', 'listify' ); ?></h1>

	<?php if ( $addons ) : ?>

	<ul class="products">

		<?php foreach ( $addons as $addon ) : ?>

		<li class="product"><div class="product-inner">
			<h3><?php echo esc_html( $addon['title'] ); ?></h3>
			<?php echo wp_kses_post( wpautop( $addon['excerpt'] ) ); ?>
			<p><a href="<?php echo esc_url( $addon['link'] ); ?>" class="button primary"><?php esc_html_e( 'More Information', 'marketify' ); ?></a></p>
		</div></li>
		<?php endforeach; ?>

	</ul>

	<?php else : ?>
		<p> <?php echo wp_kses_post( __( 'Shop for Listify compatible WP Job Manager add-ons <a href="https://astoundify.com/downloads/category/plugins/">here</a>.', 'listify' ) ); ?></p>
	<?php endif; ?>
<?php
	}

}

new Listify_Admin_Addons();
