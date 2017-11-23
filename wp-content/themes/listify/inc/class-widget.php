<?php
/**
 * Widget Base Class
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Widget
 * @author Astoundify
 */
class Listify_Widget extends WP_Widget {

	/**
	 * Widget ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $widget_id;

	/**
	 * Widget description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $widget_description;

	/**
	 * Widget name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $widget_name;

	/**
	 * Widget areas this widget can appear on.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $widget_areas = array();

	/**
	 * Notice to display when a widget is in the incorrect location.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $widget_notice;

	/**
	 * Widget settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $settings = array();

	/**
	 * Widget control.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $control_ops = array();

	/**
	 * Enable selective refresh.
	 *
	 * @since 1.10.0
	 * @var bool
	 */
	public $selective_refresh = true;

	/**
	 * Register a widget.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => $this->widget_id,
			'description' => $this->widget_description,
			'customize_selective_refresh' => true,
		);

		parent::__construct( $this->widget_id, $this->widget_name, $widget_ops, $this->control_ops );

		// Register Scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Display a notice if widget is in the wrong area.
		add_action( 'admin_print_styles', array( $this, 'widget_screen_notice_css' ) );
		add_action( 'customize_controls_print_styles', array( $this, 'widget_customizer_notice_css' ) );
	}

	/**
	 * Admin Scripts
	 *
	 * @since 2.2.0
	 */
	public function admin_scripts() {

		// Upload script, require `wp_enqueue_media()`.
		wp_register_script( 'listify-admin-widget-media', get_template_directory_uri() . '/js/admin/widget-media.js', array( 'jquery' ) );
	}

	/**
	 * Update widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $new_instance New widget settings.
	 * @param array $old_instance Old widget settings.
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( ! $this->settings ) {
			return $instance;
		}

		do_action( 'listify_widget_update_before', $instance, $new_instance, $this );

		foreach ( $this->settings as $key => $setting ) {
			switch ( $setting['type'] ) {
				case 'textarea' :
					if ( current_user_can( 'unfiltered_html' ) ) {
						$instance[ $key ] = $new_instance[ $key ];
					} else {
						$instance[ $key ] = wp_kses_data( $new_instance[ $key ] );
					}
					break;
				case 'multicheck' :
					$instance[ $key ] = maybe_serialize( $new_instance[ $key ] );
					break;
				case 'text' :
				case 'checkbox' :
				case 'select' :
				case 'number' :
				case 'colorpicker' :
					$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
					break;
				default :
					$instance[ $key ] = apply_filters( 'listify_widget_update_type_' . $setting['type'], $new_instance[ $key ], $key, $setting );
					break;
			}
		}

		do_action( 'listify_widget_update_after', $instance, $new_instance, $this );

		return $instance;
	}

	/**
	 * Display the widget form settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current widget instance.
	 */
	function form( $instance ) {
		// Display widget areas notice if available.
		echo $this->widget_areas_notice(); // WPCS: XSS ok.

		// Bail if no settings.
		if ( ! $this->settings ) {
			return;
		}

		foreach ( $this->settings as $key => $setting ) {
			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting['std'];

			switch ( $setting['type'] ) {
				case 'description' :
?>

<p class="description"><?php echo wp_kses_post( $value ); ?></p>

<?php
				break;
				case 'text' :
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo $setting['label']; ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" />
</p>

<?php
				break;
				case 'checkbox' :
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>">
		<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="1" <?php checked( 1, esc_attr( $value ) ); ?>/>
		<?php echo esc_attr( $setting['label'] ); ?>
	</label>
</p>

<?php
				break;
				case 'multicheck' :
					$value = maybe_unserialize( $value );

					if ( ! is_array( $value ) ) {
						$value = array();
					}

					$value = array_map( 'absint', $value );
?>

<p><?php echo esc_attr( $setting['label'] ); ?></p>
<p>
	<?php foreach ( $setting['options'] as $id => $label ) : ?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>[]" value="<?php echo esc_attr( $id ); ?>" <?php if ( in_array( (int) $id, $value, true ) ) : ?>checked="checked"<?php endif; ?>/>
			<?php echo esc_attr( $label ); ?><br />
		</label>
	<?php endforeach; ?>
</p>

<?php
				break;
				case 'multicheck-term' :

					// Get value.
					$value = maybe_unserialize( $value );
					if ( ! is_array( $value ) ) {
						$value = array();
					}
					$value = array_map( 'absint', $value ); // Sanitize.

					// Get terms in taxonomy.
					$_terms = array();
					$terms = listify_get_terms( array(
						'taxonomy' => $setting['options'],
					) );
					if ( $terms && ! is_wp_error( $terms ) && is_array( $terms ) ) {
						foreach ( $terms as $term ) {
							$_terms[ $term->term_id ] = $term->name;
						}
					}

?>

<p><?php echo esc_attr( $setting['label'] ); ?></p>
<p>
	<?php foreach ( $_terms as $id => $label ) : ?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>[]" value="<?php echo esc_attr( $id ); ?>" <?php if ( in_array( (int) $id, $value, true ) ) : ?>checked="checked"<?php endif; ?>/>
			<?php echo esc_attr( $label ); ?><br />
		</label>
	<?php endforeach; ?>
</p>

<?php
				break;
				case 'select' :
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_attr( $setting['label'] ); ?></label>
	<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>">
		<?php foreach ( $setting['options'] as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_attr( $label ); ?></option>
		<?php endforeach; ?>
	</select>
</p>

<?php
				break;
				case 'select-taxonomy' :

					// Get all public taxonomies object.
					$taxonomies = get_taxonomies( array(
						'public' => true,
					), 'objects' );

					// Create simple array.
					$_taxonomies = array();
					foreach ( $taxonomies as $taxonomy ) {
						$_taxonomies[ $taxonomy->name ] = $taxonomy->labels->name;
					}
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_attr( $setting['label'] ); ?></label>
	<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>">
		<?php foreach ( $_taxonomies as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_attr( $label ); ?></option>
		<?php endforeach; ?>
	</select>
</p>

<?php
				break;
				case 'number' :
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_attr( $setting['label'] ); ?></label>
	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="number" step="<?php echo esc_attr( $setting['step'] ); ?>" min="<?php echo esc_attr( $setting['min'] ); ?>" max="<?php echo esc_attr( $setting['max'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
</p>

<?php
				break;
				case 'textarea' :
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_attr( $setting['label'] ); ?></label>
	<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" rows="<?php echo isset( $setting['rows'] ) ? absint( $setting['rows'] ) : 3; ?>"><?php echo esc_html( $value ); ?></textarea>
</p>

<?php
				break;
				case 'colorpicker' :
						wp_enqueue_script( 'wp-color-picker' );
						wp_enqueue_style( 'wp-color-picker' );
?>

<p style="margin-bottom: 0;">
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_attr( $setting['label'] ); ?></label>
</p>

<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" data-default-color="<?php echo esc_attr( $value ); ?>" value="<?php echo esc_attr( $value ); ?>" />

<script>
	jQuery(document).ready(function($) {
		$( 'input[name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>"]' ).wpColorPicker();
	});
</script>

<?php
				break;
				case 'image' :
					wp_enqueue_media();
					wp_enqueue_script( 'listify-admin-widget-media' );
?>

<p>
	<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_attr( $setting['label'] ); ?></label>

	<input style="margin-top:5px;" class="widefat listify-widget-media-input" type="url" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="http://" />

	<a class="button widget-listify-media-open" data-insert="<?php esc_attr_e( 'Use Image', 'listify' ); ?>" data-title="<?php esc_attr_e( 'Choose an Image', 'listify' ); ?>" href="#"><?php esc_html_e( 'Choose Image', 'listify' ); ?></a> <a class="button listify-widget-media-clear"><?php esc_html_e( 'Clear', 'listify' ); ?></a>
</p>

<?php
				break;
				default :
					do_action( 'listify_widget_type_' . $setting['type'], $this, $key, $setting, $instance );
				break;
			} // End switch().
		} // End foreach().
	}

	/**
	 * Get a list of icons to choose from.
	 *
	 * @todo call this directly in settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_icon_list() {
		return Listify_Customizer::$icons->get_all_icons();
	}

	/**
	 * Widget Areas Notice HTML
	 *
	 * @since 2.0.0
	 */
	public function widget_areas_notice() {
		if ( $this->widget_notice && current_user_can( 'edit_theme_options' ) ) {
			return '<div class="widget-areas-notice ' . esc_attr( $this->widget_id ) . '">' . wpautop( $this->widget_notice ) . '</div>';
		}

		return false;
	}

	/**
	 * Widget Screen Notice CSS
	 *
	 * @since 2.0.0
	 */
	public function widget_screen_notice_css() {
		global $hook_suffix, $_widget_notice_css;

		// This hook is loaded multiple times in widget.php, limit only once.
		$_widget_notice_css = is_array( $_widget_notice_css ) ? $_widget_notice_css : array();

		// Only load if needed.
		if ( 'widgets.php' === $hook_suffix && ! isset( $_widget_notice_css[ $this->widget_id ] ) && $this->widget_areas && $this->widget_notice ) {
			$_widget_notice_css[ $this->widget_id ] = $this->widget_id; // Add in global as identifier.
?>

<style id="widget-areas-notice-<?php echo esc_attr( $this->widget_id ); ?>" type="text/css">
	.widget-areas-notice.<?php echo esc_attr( $this->widget_id ); ?>{
		display: block;
		color: red;
	}

<?php
foreach ( $this->widget_areas as $sidebar_id ) {
	if ( 'widget-area-page' === $sidebar_id ) {
		printf( 'div[id^="%s"] .widget-areas-notice.%s { display: none; }', esc_attr( $sidebar_id ), esc_attr( $this->widget_id ) );
	} else {
		printf( '#%s .widget-areas-notice.%s { display: none; }', esc_attr( $sidebar_id ), esc_attr( $this->widget_id ) );
	}
}
?>

</style>

<?php
		}
	}

	/**
	 * Widget Customizer Notice CSS
	 *
	 * @since 2.0.0
	 */
	public function widget_customizer_notice_css() {
		global $_widget_notice_css;

		/* This hook is loaded multiple times in widget.php, limit only once. */
		$_widget_notice_css = is_array( $_widget_notice_css ) ? $_widget_notice_css : array();

		if ( ! isset( $_widget_notice_css[ $this->widget_id ] ) && $this->widget_areas && $this->widget_notice ) {
			$_widget_notice_css[ $this->widget_id ] = $this->widget_id; // Add in global as identifier.
?>

<style id="widget-areas-notice-<?php echo esc_attr( $this->widget_id ); ?>" type="text/css">
	.widget-areas-notice.<?php echo esc_attr( $this->widget_id ); ?>{
		display: block;
		color: red;
	}

<?php
foreach ( $this->widget_areas as $sidebar_id ) {
	if ( 'widget-area-page' === $sidebar_id ) {
		printf( 'div[id^="sub-accordion-section-sidebar-widgets-%s"] .widget-areas-notice.%s { display: none; }', esc_attr( $sidebar_id ), esc_attr( $this->widget_id ) );
	} else {
		printf( '#sub-accordion-section-sidebar-widgets-%s .widget-areas-notice.%s { display: none; }', esc_attr( $sidebar_id ), esc_attr( $this->widget_id ) );
	}
}
?>
</style>

<?php
		}
	}

	/**
	 * Display the widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {}
}
