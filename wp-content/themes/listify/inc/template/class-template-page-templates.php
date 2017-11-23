<?php
/**
 * Page Templates Setup
 *
 * @since 1.0.3
 */
class Listify_Template_Page_Templates {

	/**
	 * Constructor Class
	 *
	 * @since 1.0.3
	 */
	public function __construct() {

		/* Visual Composer */
		add_filter( 'theme_page_templates', array( $this, 'visual_composer' ) );

		/* Page Templates Notice */
		add_action( 'admin_head-post.php', array( __CLASS__, 'write_panel_setup' ), 0 );
	}

	/**
	 * Remove visual composer page template if VC not active
	 *
	 * @since 1.0.3
	 * @return array list of page templates
	 */
	public function visual_composer( $page_templates ) {
		if ( listify_has_integration( 'visual-composer' ) ) {
			return $page_templates;
		}

		unset( $page_templates['page-templates/template-home-vc.php'] );

		return $page_templates;
	}

	/**
	 * Editor Notice and Setup For Page Templates
	 *
	 * @since 1.11.0
	 */
	public static function write_panel_setup() {
		global $post_type, $post;

		/* Check */
		if ( ! isset( $post, $post_type ) || ! is_a( $post, 'WP_Post' ) || 'page' !== $post_type ) {
			return;
		}

		/* Get Page Template */
		$page_template = $post->_wp_page_template;

		/* template-archive-job_listing.php */
		if ( 'page-templates/template-archive-job_listing.php' == $page_template ) {
			add_action( 'edit_form_after_title', array( __CLASS__, 'notice_template_archive_job_listing' ) );
		}

		/* template-home.php */
		if ( 'page-templates/template-home.php' == $page_template ) {
			add_action( 'edit_form_after_title', array( __CLASS__, 'notice_template_home' ) );
		}

		/* template-home-slider.php */
		if ( 'page-templates/template-home-slider.php' == $page_template ) {
			add_action( 'edit_form_after_title', array( __CLASS__, 'notice_template_home_slider' ) );
		}

		/* template-plans-pricing.php & template-plans-pricing-stacked.php */
		if ( 'page-templates/template-plans-pricing.php' == $page_template || 'page-templates/template-plans-pricing-stacked.php' == $page_template ) {
			add_action( 'edit_form_after_title', array( __CLASS__, 'notice_template_pricing' ) );
		}

		/* template-widgetized.php */
		if ( 'page-templates/template-widgetized.php' == $page_template ) {
			add_action( 'edit_form_after_title', array( __CLASS__, 'notice_template_widgetized' ) );
		}
	}

	/**
	 * Admin notice for: template-archive-job_listing.php
	 *
	 * @since 1.11.0
	 */
	public static function notice_template_archive_job_listing() {
		?>
		<div class="notice notice-warning inline">
			<p><?php _e( 'This page has no content. Your listing results will automatically be loaded on this page.', 'listify' ); ?></p>
		</div><!-- .notice -->
		<?php
	}

	/**
	 * Admin notice for: template-home.php
	 *
	 * @since 1.11.0
	 */
	public static function notice_template_home() {
		?>
		<div class="notice notice-warning inline">
			<p><?php _e( 'The Home page content is managed by widgets.', 'listify' ); ?> <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="button button-small"><?php _e( 'Manage Widgets', 'listify' ); ?></a></p>
		</div><!-- .notice -->
		<?php
	}

	/**
	 * Admin notice for: template-home.php
	 *
	 * @since 1.11.0
	 */
	public static function notice_template_home_slider() {
		?>
		<div class="notice notice-warning inline">
			<p><?php _e( 'You can paste slider shortcode in the editor below. The Home page content is managed by widgets.', 'listify' ); ?> <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="button button-small"><?php _e( 'Manage Widgets', 'listify' ); ?></a></p>
		</div><!-- .notice -->
		<?php
	}

	/**
	 * Admin notice for: template-plans-pricing.php, template-plans-pricing-stacked.php
	 *
	 * @since 1.11.0
	 */
	public static function notice_template_pricing() {
		?>
		<div class="notice notice-warning inline">
			<p><?php _e( 'This page will automatically display listing packages created in WooCommerce.', 'listify' ); ?></p>
		</div><!-- .notice -->
		<?php
	}

	/**
	 * Admin notice for: template-widgetized.php
	 *
	 * @since 1.11.0
	 */
	public static function notice_template_widgetized() {
		?>
		<div class="notice notice-warning inline">
			<p><?php _e( "You are currently editing the page that's content is managed by widgets.", 'listify' ); ?> <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=widgets' ) ); ?>" class="button button-small"><?php _e( 'Manage Widgets', 'listify' ); ?></a></p>
		</div><!-- .notice -->
		<?php
	}


}
