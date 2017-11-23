<?php
/**
 * WC Paid Listing - Pricing Table
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Widget
 * @author Astoundify
 */
class Listify_Widget_WCPL_Pricing_Table extends Listify_Widget {

	/**
	 * Register widget settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->widget_description = __( 'Display the pricing packages available for listings', 'listify' );
		$this->widget_id          = 'listify_widget_panel_wcpl_pricing_table';
		$this->widget_name        = __( 'Listify - Page: Pricing Table', 'listify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title:', 'listify' ),
			),
			'description' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Description:', 'listify' ),
			),
			'stacked' => array(
				'type' => 'checkbox',
				'std' => 0,
				'label' => __( 'Use "stacked" display style', 'listify' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		$packages = $this->get_packages();
		$count = count( $packages );

		if ( $count > 3 ) {
			$count = 3;
		}

		if ( ! $packages ) {
			return;
		}

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$description = isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : false;
		$stacked = isset( $instance['stacked'] ) && 1 === (int) $instance['stacked'] ? true : false;

		if ( $description && ( isset( $args['id'] ) && 'widget-area-home' === $args['id'] ) ) {
			$after_title = str_replace( '</div>', '', $after_title ) . '<p class="home-widget-description">' . $description . '</p></div>';
		}

		$layout = 'inline';

		ob_start();

		echo $before_widget; // WPCS: XSS ok.

		if ( $title ) {
			echo $before_title . $title . $after_title; // WPCS: XSS ok.
		}

?>

<ul class="job-packages <?php if ( $stacked ) : ?>job-packages--stacked<?php else : ?> job-packages--inline job-packages--count-<?php echo esc_attr( $count ); ?><?php endif; ?>">

<?php foreach ( $packages as $package ) :
	$product = wc_get_product( method_exists( $package, 'get_id' ) ? $package : $package->ID ); ?>

	<?php $tags = wc_get_product_tag_list( $product->get_id() ); ?>
	<?php $action_url = add_query_arg( 'choose_package', $product->get_id(), job_manager_get_permalink( 'submit_job_form' ) ); ?>

	<li class="job-package<?php if ( $stacked ) : ?> job-package--stacked<?php endif; ?>">
		<?php if ( $tags ) : ?>
			<span class="job-package-tag<?php if ( $stacked ) : ?> job-package-tag--stacked<?php endif; ?>"><span class="job-package-tag__text"><?php echo esc_attr( strip_tags( $tags ) ); ?></span></span>
		<?php endif; ?>

		<div class="job-package-header<?php if ( $stacked ) : ?> job-package-header--stacked<?php endif; ?>">
			<div class="job-package-title<?php if ( $stacked ) : ?> job-package-title--stacked<?php endif; ?>">
				<?php echo esc_attr( $product->get_title() ); ?>
			</div>
			<div class="job-package-price<?php if ( $stacked ) : ?> job-package-price--stacked<?php endif; ?>">
				<?php echo $product->get_price_html(); // WPCS: XSS ok. ?>
			</div>

			<div class="job-package-purchase<?php if ( $stacked ) : ?> job-package-purchase--stacked<?php endif; ?>">
				<a href="<?php echo esc_url( $action_url ); ?>" class="button"><?php esc_html_e( 'Get Started Now &rarr;', 'listify' ); ?></a>
			</div>
		</div>

		<div class="job-package-includes<?php if ( $stacked ) : ?> job-package-includes--stacked<?php endif; ?>">
			<?php
				$content = $product->get_description();
				$content = (array) explode( "\n", $content );
			?>
			<ul>
				<li><?php echo implode( '</li><li>', $content ); // WPCS: XSS ok. ?></li>
			</ul>
		</div>

		<div class="job-package-purchase<?php if ( $stacked ) : ?> job-package-purchase--stacked<?php endif; ?>">
			<a href="<?php echo esc_url( $action_url ); ?>" class="button"><?php esc_html_e( 'Get Started Now &rarr;', 'listify' ); ?></a>
		</div>
	</li>

<?php endforeach; ?>

</ul>

<?php
		echo $after_widget; // WPCS: XSS ok.

		echo apply_filters( $this->widget_id, ob_get_clean() ); // WPCS: XSS ok.
	}

	/**
	 * Find packagees available for purchase.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_packages() {
		$packages = array();

		if ( listify_has_integration( 'wp-job-manager-listing-payments' ) ) {
			add_filter( 'astoundify_wpjmlp_get_job_packages_args', array( $this, 'get_packages_filter' ) );
			$packages = astoundify_wpjmlp_get_job_packages();
		} elseif ( listify_has_integration( 'wp-job-manager-wc-paid-listings' ) ) {
			add_filter( 'wcpl_get_job_packages_args', array( $this, 'get_packages_filter' ) );
			$packages = WP_Job_Manager_WCPL_Submit_Job_Form::get_packages();
		}

		return apply_filters( 'listify_pricing_table_packages_results', $packages );
	}

	/**
	 * Get Packages Filters.
	 * This function added to maintain backward compatibility.
	 * It's recommended to filter the plugin args directly.
	 *
	 * @since 2.2.0
	 *
	 * @param array $args Get packages args.
	 * @return array
	 */
	public function get_packages_filter( $args ) {
		return apply_filters( 'listify_pricing_table_packages', $args );
	}

}
