<?php
/**
 * Edit Ratings In WP Admin
 *
 * @since 2.0.0
 */
class Listify_Ratings_Edit {

	/**
	 * Class Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		/* Bail if WPJM Reviews active or rating not enabled (via customizer) */
		if ( listify_has_integration( 'wp-job-manager-reviews' ) || ! get_theme_mod( 'listing-ratings', true ) ) {
			return;
		}

		/* Add comment meta box to edit rating */
		add_action( 'add_meta_boxes_comment', array( $this, 'add_rating_meta_box' ) );

		/* Update rating */
		add_action( 'edit_comment', array( $this, 'edit_rating' ), 10, 2 );
	}


	/**
	 * Add Rating Meta Box In Comment Edit Screen
	 *
	 * @since 2.0.0
	 */
	public function add_rating_meta_box( $comment ) {

		/* Check user caps */
		if ( ! current_user_can( 'edit_comment', $comment->comment_ID ) ) {
			return;
		}

		/* Post ID */
		$post_id = $comment->comment_post_ID; // post ID

		/* Only job listing */
		if ( 'job_listing' != get_post_type( $post_id ) ) {
			return;
		}

		/* Add meta box */
		add_meta_box(
			$id         = 'listify_comment_rating',
			$title      = __( 'Listing Rating', 'listify' ),
			$callback   = array( $this, 'rating_meta_box' ),
			$screen     = 'comment',
			$context    = 'normal' // only "normal" is valid for comment.
		);
	}

	/**
	 * Rating Meta Box Callback
	 *
	 * @since 2.0.0
	 */
	public function rating_meta_box( $comment, $box ) {

		/* Post ID */
		$post_id = $comment->comment_post_ID; // post ID

		/* Only top level comment can have rating */
		if ( $comment->comment_parent ) {
			echo wpautop( __( 'Only top level comment have rating.', 'listify' ) );
			return;
		}

		$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
		$rating = in_array( $rating, array( '1', '2', '3', '4', '5' ) ) ? $rating : '0';
		?>
		<select id="listify-comment-rating" name="comment_rating">
			<option value="0" <?php selected( '0', $rating ); ?>><?php esc_html_e( 'No rating', 'listify' ); ?></option>
			<option value="1" <?php selected( '1', $rating ); ?>>&#9733; &#9734; &#9734; &#9734; &#9734; <?php esc_html_e( '(1 Star)', 'listify' ); ?></option>
			<option value="2" <?php selected( '2', $rating ); ?>>&#9733; &#9733; &#9734; &#9734; &#9734; <?php esc_html_e( '(2 Star)', 'listify' ); ?></option>
			<option value="3" <?php selected( '3', $rating ); ?>>&#9733; &#9733; &#9733; &#9734; &#9734; <?php esc_html_e( '(3 Star)', 'listify' ); ?></option>
			<option value="4" <?php selected( '4', $rating ); ?>>&#9733; &#9733; &#9733; &#9733; &#9734; <?php esc_html_e( '(4 Star)', 'listify' ); ?></option>
			<option value="5" <?php selected( '5', $rating ); ?>>&#9733; &#9733; &#9733; &#9733; &#9733; <?php esc_html_e( '(5 Star)', 'listify' ); ?></option>
		</select>
		<?php
		wp_nonce_field( 'listify_star_ratings_edit', '_listify_rating_edit_nonce' );
	}

	/**
	 * Edit Rating
	 *
	 * @since 2.0.0
	 */
	public function edit_rating( $comment_id, $data ) {

		/* Check user caps */
		if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
			return $comment_id;
		}

		/* Check nonce */
		if ( ! isset( $_POST['_listify_rating_edit_nonce'] ) || ! wp_verify_nonce( $_POST['_listify_rating_edit_nonce'], 'listify_star_ratings_edit' ) ) {
			return $comment_id;
		}

		/* Rating */
		$rating = isset( $_POST['comment_rating'] ) ? $_POST['comment_rating'] : false;
		$rating = ( $rating && in_array( $rating, array( '1', '2', '3', '4', '5' ) ) ) ? $rating : false;

		/* Post ID */
		$comment = get_comment( $comment_id );
		$post_id = $comment->comment_post_ID;

		/* Delete rating */
		if ( false === $rating ) {
			$rating_deleted = delete_comment_meta( $comment_id, 'rating' );
			if ( $rating_deleted ) {
				do_action( 'listify_comment_rating_updated', $comment_id, $post_id, $rating, $action = 'remove' );
			}
		} // End if().

		else {
			$rating_updated = update_comment_meta( $comment_id, 'rating', $rating );

			/* Successfully add rating: add custom hook to update post meta */
			if ( $rating_updated ) {
				do_action( 'listify_comment_rating_updated', $comment_id, $post_id, $rating, $action = 'add' );
			}
		}

	}

}

new Listify_Ratings_Edit;
