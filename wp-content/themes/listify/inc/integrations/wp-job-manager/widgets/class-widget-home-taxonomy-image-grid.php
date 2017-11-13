<?php
/**
 * Home: Taxonomy Image Grid
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Taxonomy_Image_Grid extends Listify_Widget {

	/**
	 * Constructor Class
	 * Widget Configuration and Settings
	 */
	public function __construct() {

		/* Widget Config */
		$this->widget_description = __( 'Display a grid of images for a certain taxonomy', 'listify' );
		$this->widget_id          = 'listify_widget_taxonomy_image_grid';
		$this->widget_name        = __( 'Listify - Page: Image Grid', 'listify' );
		$this->widget_areas       = array( 'widget-area-home', 'widget-area-page' ); // valid widget areas
		$this->widget_notice      = __( 'Add this widget only in "Page" widget area.' );

		/* Widget Settings */
		$this->settings = array(
			'title' => array(
				'type'    => 'text',
				'std'     => '',
				'label'   => __( 'Title:', 'listify' ),
			),
			'description' => array(
				'type'    => 'text',
				'std'     => '',
				'label'   => __( 'Description:', 'listify' ),
			),
			'style' => array(
				'label'   => __( 'Style:', 'listify' ),
				'type'    => 'select',
				'std'     => 'tiled',
				'options' => array(
					'tiled'  => __( 'Tiled', 'listify' ),
					'square' => __( 'Square', 'listify' ),
				),
			),
			'taxonomy' => array(
				'label'   => __( 'Taxonomy:', 'listify' ),
				'type'    => 'select-taxonomy',
				'std'     => '',
			),
			'limit' => array(
				'type'    => 'number',
				'std'     => 5,
				'min'     => 1,
				'max'     => 999,
				'step'    => 1,
				'label'   => __( 'Number of terms to show:', 'listify' ),
			),
			'terms' => array(
				'type'    => 'text',
				'std'     => '',
				'label'   => __( 'Term IDs: (optional)', 'listify' ),
			),
			'child_of' => array(
				'type'    => 'text',
				'std'     => '',
				'label'   => __( 'Child of: (Display on child terms of this ID)', 'listify' ),
			),
			'parent' => array(
				'type'    => 'text',
				'std'     => '',
				'label'   => __( 'Parent: (Display only terms with this parent ID)', 'listify' ),
			),
			'order' => array(
				'label'   => __( 'Order:', 'listify' ),
				'type'    => 'select',
				'std'     => 'ASC',
				'options' => array(
					'ASC'  => __( 'Ascending', 'listify' ),
					'DESC' => __( 'Descending', 'listify' ),
				),
			),
			'orderby' => array(
				'label'   => __( 'Order By:', 'listify' ),
				'type'    => 'select',
				'std'     => 'random',
				'options' => array(
					'random' => __( 'Random', 'listify' ),
					'name'   => __( 'Name', 'listify' ),
					'count'  => __( 'Count', 'listify' ),
				),
			),
		);

		parent::__construct();
	}

	/**
	 * widget function.
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

		/* Set Default Widget Instance */
		$defaults = array(
			'title'         => '',
			'description'   => '',
			'style'         => 'tiled',
			'taxonomy'      => '',
			'limit'         => 5,
			'terms'         => '', // comma separated
			'child_of'      => '',
			'order'         => 'ASC',
			'orderby'       => 'random',
		);
		$instance = $this->instance = wp_parse_args( $instance, $defaults );

		/* Extract for easier implementation */
		extract( $args ); // $name, $id, $description, $before_widget, etc

		/* Vars + Sanitize */
		$title         = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$description   = $instance['description'];
		$style         = 'tiled' == $instance['style'] ? 'tiled' : 'square';
		$taxonomy      = $instance['taxonomy'];
		$limit         = absint( $instance['limit'] );
		$terms_include = trim( $instance['terms'] ) ? array_map( 'trim', explode( ',', $instance['terms'] ) ) : array(); // make array
		$child_of      = $instance['child_of'];
		$parent        = ( isset( $instance['parent'] ) && '' !== $instance['parent'] ) ? (int) $instance['parent'] : false;
		$order         = 'ASC' == $instance['order'] ? 'ASC' :  'DESC';
		$orderby       = in_array( $instance['orderby'], array( 'random', 'name', 'count' ) ) ? $instance['orderby'] : 'random';

		if ( $description && ( isset( $args['id'] ) && 'widget-area-home' === $args['id'] ) ) {
			$after_title = str_replace( '</div>', '', $after_title ) . '<p class="home-widget-description">' . $description . '</p></div>';
		}

		/* Bail early if taxonomy not valid */
		if ( ! $taxonomy || is_wp_error( get_taxonomy( $taxonomy ) ) ) {
			return false;
		}

		/* === GET TERMS === */

		/* Get terms */
		$get_terms_args = array(
			'taxonomy' => $taxonomy,
			'number'   => $limit,
			'order'    => $order,
			'orderby'  => in_array( $orderby, array( 'name', 'count' ) ) ? $orderby : 'name', // "random" is not valid get terms orderby.
		);

		if ( $child_of ) {
			$get_terms_args['child_of'] = $child_of;
		}

		if ( ! empty( $terms_include ) ) {
			$get_terms_args['include'] = $terms_include;
			$get_terms_args['orderby'] = 'include';
			$get_terms_args['hide_empty'] = false;
		}

		if ( $parent || 0 === $parent ) {
			$get_terms_args['parent'] = $parent;
		}

		// WP_Term_Query does not support random ordering so we fake it here.
		if ( 'random' === $orderby ) {

			// Get random order.
			$_order = array( 'ASC', 'DESC' );
			shuffle( $_order );
			$get_terms_args['order'] = $_order[0];

			// Shuffle orderby.
			$_orderby = array( 'name', 'slug', 'term_group', 'term_id', 'id', 'description', 'count' );
			shuffle( $_orderby );
			$get_terms_args['orderby'] = $_orderby[0];

			// Load at least 50 terms, so we can randomize better.
			if ( $get_terms_args['number'] < 50 ) {
				$get_terms_args['number'] = 50;
			}
		}

		/* Get the terms */
		$terms = listify_get_terms( $get_terms_args );

		/* Bail if any of the terms is not valid */
		if ( ! $terms || is_wp_error( $terms ) ) {
			return;
		}

		// Randomize if random order selected.
		if ( 'random' === $orderby ) {
			shuffle( $terms );
			$terms = array_slice( $terms, 0, $limit );
		}

		/* Markup Datas */
		$cols      = 'col-xs-12 col-sm-6 col-md-';
		$spans     = $this->get_spans( count( $terms ), $style );

		/* Start output buffering */
		ob_start();

		echo $before_widget;

		if ( $title ) { echo $before_title . $title . $after_title;
		}
?>
		<div class="row">

<?php
		/* Start Term Loop */
		$count = 0;
foreach ( $terms as $term ) :

	/* Get term thumbnail */
	$thumbnail = get_term_meta( $term->term_id, 'thumbnail_id', true );

	/* Term has no thumbnail */
	if ( ! $thumbnail ) {

		/* Get posts IDs attached to term */
		$cover_objects = get_objects_in_term( $term->term_id, $taxonomy );

		/**
				 * Format Div Attr with Image Background
		 *
				 * @see listify_cover() in functions.php
				 */
		$image = apply_filters(
			'listify_cover',
			$cols . ' entry-cover image-grid-cover',
			array(
				'object_ids' => $cover_objects,
				'term'       => $term,
				'taxonomy'   => $taxonomy,
			)
		);
	} // End if().

	else {
		$image = apply_filters(
			'listify_cover',
			$cols . ' entry-cover image-grid-cover',
			array(
				'images' => array( $thumbnail ),
			)
		);
	}
?>

<div id="image-grid-term-<?php echo $term->slug; ?>" class="<?php echo $cols . $spans[ $count ]; ?> image-grid-item">
<div <?php echo $image ?>>
	<a href="<?php echo esc_url( get_term_link( $term, $taxonomy ) ); ?>" class="image-grid-clickbox"></a>
	<a href="<?php echo esc_url( get_term_link( $term, $taxonomy ) ); ?>" class="cover-wrapper"><?php echo $term->name; ?></a>
</div>
</div>

<?php
/* End term loop */
$count++;
		endforeach;
?>

		</div>

<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}

	/**
	 * Helper to get spans markup
	 * if using tiled, will randomize the span to get dynamic markup
	 *
	 * @since 2.0.0
	 * @return array
	 */
	private function get_spans( $total, $style ) {
		$col_count = 0;
		$spans = array();

		/* Square Style: Fixed span */
		if ( 'square' == $style ) {
			for ( $i = 0; $i < $total; $i++ ) {
				$spans[ $i ] = apply_filters( 'listify_image_grid_square_columns', 4 );
			}
		} // End if().

		else {
			for ( $i = 0; $i < $total; $i++ ) {
				$span = 4;
				if ( $i == 0 ) {
					$span = 8;
				} elseif ( $i == $total - 1 ) {
					$span = 12 - $col_count;
				} elseif ( $i == rand( 1, $total ) ) {
					$span = 6;
				}
				$col_count = $col_count + $span;
				if ( $col_count > 12 ) {
					$span = 12 - $col_count + $span;
				}
				if ( $span < 4 ) {
					$spans[ $i - 1 ] = $spans[ $i - 1 ] - 1;
					$span = 3;
				}
				if ( $col_count >= 12 ) {
					$col_count = 0;
				}
				$spans[ $i ] = $span;
			} // End for().
		} // end tiled style

		return $spans;
	}

}
