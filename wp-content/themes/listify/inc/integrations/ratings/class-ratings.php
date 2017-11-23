<?php
/**
 * Listify Ratings
 */
class Listify_Ratings extends Listify_Integration {

	/**
	 * Class Constructor
	 */
	public function __construct() {

		/* Include Files */
		$this->includes = array(
			'class-comments.php',
			'class-ratings-edit.php',
		);

		/* Integration ID */
		$this->integration = 'ratings';

		/* Load Parent Constructor */
		parent::__construct();
	}

	/**
	 * Setup Function
	 */
	public function setup_actions() {

		/* Bail if WPJM Reviews active or rating not enabled (via customizer) */
		if ( listify_has_integration( 'wp-job-manager-reviews' ) || ! get_theme_mod( 'listing-ratings', true ) ) {
			return;
		}

		/* Add rating field in comment form */
		add_action( 'comment_form_top', array( $this, 'add_rating_field' ) );

		/* Save Comment Rating */
		add_action( 'comment_post', array( $this, 'save_comment_rating' ), 10, 2 );

		/* Add rating when comment status changed */
		add_action( 'transition_comment_status', array( $this, 'update_comment_status_update_post_rating' ), 10, 3 );

		/* Update Post Ratings Data when new rating submitted */
		add_action( 'listify_comment_rating_updated', array( $this, 'update_post_ratings_data' ), 10, 4 );

		/* Update Post Rating (Average) */
		add_action( 'listify_post_ratings_data_updated', array( $this, 'update_post_rating' ), 10, 3 );

		/* Add rating in comment content */
		add_filter( 'get_comment_text', array( $this, 'display_comment_rating' ), 10, 3 );

		/* Filter rating count, average, best, worst, in listing data */
		add_filter( 'listify_get_listing_rating_count' , array( $this, 'get_rating_count' ) , 10, 2 );
		add_filter( 'listify_get_listing_rating_average' , array( $this, 'get_rating_average' ) , 10, 2 );
		add_filter( 'listify_get_listing_rating_best' , array( $this, 'get_rating_best' ) , 10, 2 );
		add_filter( 'listify_get_listing_rating_worst' , array( $this, 'get_rating_worst' ) , 10, 2 );
	}


	/**
	 * Add rating field in comment form
	 * The input field is added via javascript when the form submitted.
	 * The field name is 'comment_rating'.
	 *
	 * @see inc/integrations/wp-job-manager/js/listing/app.coffee (line 45)
	 * @since 2.0.0
	 */
	public function add_rating_field() {

		/* Only in job_listing post type */
		if ( ! is_singular( 'job_listing' ) ) {
			return;
		}

		/* Rating Stars */
		$default_rating = apply_filters( 'listify_ratings_default_rating', 3 );
?>
<p class="star-rating-wrapper comment-form-rating comment-form-rating--listify">
	<span class="star-rating-label"><?php _e( 'Your Rating', 'listify' ); ?>
	<span class="stars">
		<?php for ( $i = 5; $i > 0; $i-- ) : ?>
			<?php printf( '<a class="star' . ( $i == $default_rating ? ' active' : '' ) . '" href="#" data-rating="%d"></a>', $i ); ?>
		<?php endfor; ?>
	</span>
	<?php wp_nonce_field( 'listify_star_ratings', '_listify_rating_nonce' ); ?>
</p>
<?php
	}


	/**
	 * Save Comment Rating
	 *
	 * @since 2.0.0
	 *
	 * @param int $comment_id
	 * @param int $comment_approved 1 for approved, 0 if comment not approved.
	 */
	public function save_comment_rating( $comment_id, $comment_approved ) {

		/* Check nonce */
		if ( ! isset( $_POST['_listify_rating_nonce'] ) || ! wp_verify_nonce( $_POST['_listify_rating_nonce'], 'listify_star_ratings' ) ) {
			return $comment_id;
		}

		/* Get comment data */
		$comment = get_comment( $comment_id );

		/* Only top level comment can have rating */
		if ( $comment->comment_parent ) {
			return $comment_id;
		}

		/* Only job listing */
		$post_id = $comment->comment_post_ID; // post ID
		if ( 'job_listing' != get_post_type( $post_id ) ) {
			return $comment_id;
		}

		/* Check submitted rating */
		$rating = isset( $_POST['comment_rating'] ) ? $_POST['comment_rating'] : false;
		$rating = ( $rating && in_array( $rating, array( '1', '2', '3', '4', '5' ) ) ) ? $rating : false;
		if ( false === $rating ) {
			return $comment_id;
		}

		/* Save rating as comment meta */
		$rating_updated = update_comment_meta( $comment_id, 'rating', $rating );

		/* Successfully add rating and comment is approved: add custom hook to update post meta */
		if ( $rating_updated && $comment_approved ) {
			do_action( 'listify_comment_rating_updated', $comment_id, $post_id, $rating, $action = 'add' );
		}
	}

	/**
	 * Also Update Post Rating When Approving Comment
	 *
	 * @since 2.0.0
	 *
	 * @param string $new_status New comment status.
	 * @param string $old_status Old/edited comment status.
	 * @param object $comment    Comment object.
	 * @return void.
	 */
	public function update_comment_status_update_post_rating( $new_status, $old_status, $comment ) {
		$comment_id = $comment->comment_ID;
		$post_id = $comment->comment_post_ID;

		/* Bail if do not have rating or not top level comment. */
		$rating = get_comment_meta( $comment_id, 'rating', true );
		if ( ! $rating || $comment->comment_parent ) {
			return;
		}

		/* Only job listing */
		if ( 'job_listing' != get_post_type( $post_id ) ) {
			return;
		}

		/* Set to approve */
		if ( 'approve' == $comment_status ) {
			do_action( 'listify_comment_rating_updated', $comment_id, $post_id, $rating, $action = 'add' );
		} else {
			do_action( 'listify_comment_rating_updated', $comment_id, $post_id, $rating, $action = 'remove' );
		}
	}


	/**
	 * Update Post Rating When Comment Updated
	 *
	 * @link https://codex.wordpress.org/Class_Reference/WP_Comment_Query
	 * @since 2.0.0
	 *
	 * @param int    $comment_id
	 * @param int    $post_id
	 * @param int    $rating Comment rating, valid value is 1-5
	 * @param string $action update ratings rata action. valid value are "add", "remove", and "recalculate"
	 */
	public function update_post_ratings_data( $comment_id, $post_id, $rating, $action = 'add' ) {

		/* Ratings Data */
		$ratings_data = get_post_meta( $post_id, 'ratings_data', true );

		/* No ratings data: first time */
		if ( ! $ratings_data || ! is_array( $ratings_data ) || 'recalculate' == $action ) {
			$ratings_data = array();

			/* Comment Query */
			$args = array(
				'post_id'        => $post_id,
				'status'         => 'approve',
				'parent'         => 0,
				'meta_key'       => 'rating',
			);
			$comments_query = new WP_Comment_Query;
			$comments = $comments_query->query( $args );
			if ( $comments ) {
				foreach ( $comments as $comment ) {
					$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
					$rating = in_array( $rating, array( '1', '2', '3', '4', '5' ) ) ? $rating : '1';
					$ratings_data[ $comment->comment_ID ] = $rating;
				}
			}
		} // End if().

		else {
			/* Add rating */
			if ( 'add' == $action ) {
				$ratings_data[ $comment_id ] = $rating;
			} // End if().

			elseif ( 'remove' == $action ) {
				unset( $ratings_data[ $comment_id ] );
			}
		}

		/* Update ratings data */
		$ratings_data_updated = update_post_meta( $post_id, 'ratings_data', $ratings_data );

		/* Successfully update ratings data: add custom hook */
		if ( $ratings_data_updated ) {
			do_action( 'listify_post_ratings_data_updated', $comment_id, $post_id, $ratings_data );
		}
	}

	/**
	 * Update Post Rating (Average)
	 *
	 * @since 2.0.0
	 *
	 * @param int   $comment_id
	 * @param int   $post_id
	 * @param array $ratings_data Approved comments ratings data, using comment ID as array keys and rating as value.
	 */
	public function update_post_rating( $comment_id, $post_id, $ratings_data ) {
		$count = count( $ratings_data );
		$total = 0;

		foreach ( $ratings_data as $rating ) {
			$total = $total + $rating;
		}

		// Avoid division by zero error.
		if ( 0 === $count ) {
			$rating = 0;
		} else {
			$rating = $total / $count; // average rating.
		}

		/* Update rating average count */
		$rating_updated = update_post_meta( $post_id, 'rating', $rating );

		/* Successfully update rating average (display): add custom hook */
		if ( $rating_updated ) {
			do_action( 'listify_post_rating_updated', $comment_id, $post_id, $ratings_data, $rating );
		}
	}


	/**
	 * Display Comment Rating before comment content
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Comment Content/Comment Text
	 * @param object $comment
	 * @param array  $arg Optional additional filter args.
	 */
	public function display_comment_rating( $content, $comment, $arg ) {

		/* Only if rating available */
		$rating = get_comment_meta( $comment->comment_ID, 'rating', true );
		if ( ! $rating ) {
			return $content;
		}

		/* Sanitize rating */
		$rating = in_array( $rating, array( '1', '2', '3', '4', '5' ) ) ? $rating : '1';

		/* Only top level comment can have rating */
		if ( $comment->comment_parent ) {
			return $content;
		}

		/* Only job listing */
		$post_id = $comment->comment_post_ID; // post ID
		if ( 'job_listing' != get_post_type( $post_id ) ) {
			return $content;
		}

		/* Stars Rating HTML */
		$stars  = '<p class="comment-stars">';
		for ( $i = 1 ; $i <= 5 ; $i++ ) {
			if ( $i <= $rating ) {
				$stars .= '<span class="comment-star comment-star--full"></span>';
			} else {
				$stars .= '<span class="comment-star comment-star--empty"></span>';
			}
		}
		$stars .= '</p>';

		/* Add rating before content */
		return $stars . $content;
	}

	/**
	 * Get Rating Count
	 * This filter is defined in "inc/class-listing.php"
	 *
	 * @since 2.0.0
	 *
	 * @param int    $count Comment Count
	 * @param object $listing Listing object Listify_Listing
	 */
	public function get_rating_count( $count, $listing ) {

		/* Bail if no comment */
		if ( ! $listing->get_object()->comment_count ) {
			return $count;
		}

		/* Get ratings data */
		$post_id = $listing->get_id();
		$ratings_data = get_post_meta( $post_id, 'ratings_data', true );

		/* No ratings data: recalculate */
		if ( ! $ratings_data ) {
			$this->update_post_ratings_data( false, $post_id, false, 'recalculate' );
		}

		/* Try again */
		$ratings_data = get_post_meta( $post_id, 'ratings_data', true );
		$ratings_data = is_array( $ratings_data ) ? $ratings_data : array();

		return count( $ratings_data );
	}

	/**
	 * Get Rating Average
	 *
	 * This filter is defined in "inc/class-listing.php"
	 *
	 * @since 2.0.0
	 *
	 * @param int    $average Comment average
	 * @param object $listing Listing object Listify_Listing
	 */
	public function get_rating_average( $average, $listing ) {
		$rating = $listing->get_object()->rating ? $listing->get_object()->rating : 0;
		return $rating;
	}

	/**
	 * Get Best Rating
	 *
	 * @since 2.0.0
	 *
	 * @param int    $best    Best Rating.
	 * @param object $listing Listing object Listify_Listing.
	 */
	public function get_rating_best( $best, $listing ) {
		$ratings_data = get_post_meta( $listing->get_id(), 'ratings_data', true );
		if ( $ratings_data ) {
			$best = max( $ratings_data );
		}
		return $best;
	}

	/**
	 * Get Worst Rating
	 *
	 * @since 2.0.0
	 *
	 * @param int    $worst   Worst Rating.
	 * @param object $listing Listing object Listify_Listing.
	 */
	public function get_rating_worst( $worst, $listing ) {
		$ratings_data = get_post_meta( $listing->get_id(), 'ratings_data', true );
		if ( $ratings_data ) {
			$worst = min( $ratings_data );
		}
		return $worst;
	}

}

$_GLOBALS['listify_ratings'] = new Listify_Ratings;
