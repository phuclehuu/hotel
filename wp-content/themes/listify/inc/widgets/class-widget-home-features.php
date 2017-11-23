<?php
/**
 * Home: Features
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Features extends Listify_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_description = __( 'Display top features about your site', 'listify' );
		$this->widget_id          = 'listify_widget_features';
		$this->widget_name        = __( 'Listify - Page: Features', 'listify' );
		$this->widget_areas       = array( 'widget-area-home', 'widget-area-page' ); // Valid widget areas.
		$this->widget_notice      = __( 'Add this widget only in "Page" widget area.' );
		$this->control_ops        = array(
			'width' => 400,
		);
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
			'text-align' => array(
				'type'  => 'select',
				'std'   => 'center',
				'label' => __( 'Text Align:', 'listify' ),
				'options' => array(
					'left'   => __( 'Left', 'listify' ),
					'center' => __( 'Center', 'listify' ),
					'right'  => __( 'Right', 'listify' ),
				),
			),
			'features' => array(
				'type' => 'features', // Custom type. See Below.
				'std'  => array(),
				'label' => '',
			),
		);
		parent::__construct();

		// Admin Scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// "features" Type HTML.
		add_action( 'listify_widget_type_features', array( $this, 'output' ), 10, 4 );

		// Enable content filter in feature description.
		add_filter( 'listify_feature_description', 'wptexturize' );
		add_filter( 'listify_feature_description', 'convert_smilies' );
		add_filter( 'listify_feature_description', 'convert_chars' );
		add_filter( 'listify_feature_description', 'wpautop' );
		add_filter( 'listify_feature_description', 'shortcode_unautop' );
		add_filter( 'listify_feature_description', 'prepend_attachment' );
		add_filter( 'listify_feature_description', 'do_shortcode' );

		// Load JS Template if widget is loaded.
		add_action( 'admin_footer', array( $this, 'load_js_template' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'load_js_template' ) );
	}

	/**
	 * Widget Features Admin Scripts.
	 *
	 * @since unknown
	 */
	public function admin_enqueue_scripts() {
		// Only in widgets.php and customizer.
		global $pagenow;

		if ( ! in_array( $pagenow, array( 'widgets.php', 'customize.php' ), true ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'listify-admin-widget-features', get_template_directory_uri() . '/js/admin/widget-features.js', array( 'underscore', 'jquery', 'jquery-ui-sortable', 'listify-admin-widget-media' ) );
	}

	/**
	 * Widget Front End Callback Function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		// Check widget areas context.
		if ( ! is_singular( 'page' ) ) {
			echo $this->widget_areas_notice();
			return false;
		}

		// Get features.
		$features = isset( $instance['features'] ) ? $instance['features'] : array();

		// Do not display widget if no features defined.
		if ( empty( $features ) ) {
			return;
		}

		// Widget settings.
		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$description = isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : false;
		$align = isset( $instance['text-align'] ) ? esc_attr( $instance['text-align'] ) : 'center';
		$count = count( $features );

		if ( $description && ( isset( $args['id'] ) && 'widget-area-home' === $args['id'] ) ) {
			$args['after_title'] = str_replace( '</div>', '', $args['after_title'] ) . '<p class="home-widget-description">' . $description . '</p></div>';
		}

		ob_start();

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
?>

		<div class="home-features-wrapper row" data-columns>

		<?php
		foreach ( $features as $feature ) {
			if ( is_object( $feature ) ) {
				$feature = json_decode( json_encode( $feature ), true );
			}

			$title = isset( $feature['title'] ) ? $feature['title'] : null;
			$description = isset( $feature['description'] ) ? apply_filters( 'listify_feature_description',
			$feature['description'] ) : null;
			$media = esc_url( $feature['media'] );

			$feature = compact( 'title', 'description', 'media' );

			include( locate_template( array( 'content-home-feature.php', false ) ) );
		}
		?>

		</div>
		<style>
			#<?php echo $this->id; ?> .home-feature {
				text-align: <?php echo esc_attr( $align ); ?>;
			}
		</style>

		<?php
		echo $args['after_widget'];

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}

	/**
	 * Widget Settings HTML Output.
	 *
	 * @since unknown
	 * @param object $widget   Widget.
	 * @param string $key      Field key, value is "features".
	 * @param array  $setting  Setting.
	 * @param array  $instance Instance.
	 * @return void
	 */
	public function output( $widget, $key, $setting, $instance ) {
		$features = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];
		?>
		
<div id="features-<?php echo $widget->id; ?>" class="features-wrap">

	<p><a href="#" class="button-add-feature button button-secondary"><?php _e( 'Add Feature', 'listify' ); ?></a></p>

	<div class="features"></div>

</div>

<script>
	jQuery(document).ready(function($) {
		var feature_template = wp.template( 'feature' );
		<?php foreach ( $features as $order => $feature ) :
			$feature['widget_num'] = $widget->number;
			$feature['order'] = $order;
			?>
			$( '#features-<?php echo $widget->id; ?> .features' ).append( feature_template( <?php echo json_encode( $feature ); ?> ) ).sortable();
		<?php endforeach; ?>
	});
</script>

	<?php
	}

	/**
	 * JS Template & Style.
	 *
	 * @since 2.2.0
	 */
	public function load_js_template() {
		// Only in widgets.php and customizer. And this need to be loaded only once.
		global $pagenow, $_listify_widget_home_features_tmpl_loaded;

		if ( ! in_array( $pagenow, array( 'widgets.php', 'customize.php' ), true ) && isset( $_listify_widget_home_features_tmpl_loaded ) && $_listify_widget_home_features_tmpl_loaded ) {
			return;
		}

		$_listify_widget_home_features_tmpl_loaded = true;

		?>

<script id="tmpl-feature" type="text/template">
	<div class="feature">
		<a href="#" class="button-remove-feature">&nbsp;</a>

		<p>
			<label><?php _e( 'Title:', 'listify' ); ?></label>
			<input name="widget-listify_widget_features[{{data.widget_num}}][features][{{data.order}}][title]" type="text" value="{{data.title}}" class="widefat" />
		</p>

		<p>
			<label><?php _e( 'Image:', 'listify' ); ?></label>
			<input class="widefat listify-widget-media-input" type="url" name="widget-listify_widget_features[{{data.widget_num}}][features][{{data.order}}][media]" value="{{data.media}}" placeholder="http://" />
			<a class="button widget-listify-media-open" data-insert="<?php esc_attr_e( 'Use Image', 'listify' ); ?>" data-title="<?php esc_attr_e( 'Choose an Image', 'listify' ); ?>" href="#"><?php esc_html_e( 'Choose Image', 'listify' ); ?></a> <a class="button listify-widget-media-clear"><?php esc_html_e( 'Clear', 'listify' ); ?></a>
		</p>

		<p>
			<label><?php _e( 'Description:', 'listify' ); ?></label>
			<textarea name="widget-listify_widget_features[{{data.widget_num}}][features][{{data.order}}][description]" rows="3" class="widefat">{{{data.description}}}</textarea>
		</p>
	</div>
</script>

<style>
	.feature {
		border: 1px solid #ddd;
		margin-bottom: 1em;
		padding: 0.5em 1em;
		background: #fff;
		cursor: move;
		position: relative;
	}

	.button-remove-feature {
		position: absolute;
		top: 5px;
		right: 5px;
		text-decoration: none;
	}

	.button-remove-feature:before {
		background: 0 0;
		color: #BBB;
		content: '\f153';
		display: block!important;
		font: 400 13px/1 dashicons;
		speak: none;
		height: 20px;
		margin: 2px 0;
		text-align: center;
		width: 20px;
		-webkit-font-smoothing: antialiased!important;
	}

	.listify-widget-media-input {
		margin-bottom: 5px !important;
	}
</style>

		<?php
	}
}
