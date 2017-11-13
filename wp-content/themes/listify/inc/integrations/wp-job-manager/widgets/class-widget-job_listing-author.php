<?php
/**
 * Job Listing: Author
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Listing_Author extends Listify_Widget {

	/**
	 * Listing owner.
	 *
	 * @since 1.8.0
	 */
	public $listing_owner = false;

	public function __construct() {
		$this->widget_description = __( 'Display the listing&#39;s author', 'listify' );

		// this is a typo that has to remain or widgets will be removed from sidebars
		$this->widget_id          = 'listify_widget_panel_listing_auhtor';
		$this->widget_name        = __( 'Listify - Listing: Author', 'listify' );
		$this->widget_areas       = array( 'single-job_listing-widget-area', 'single-job_listing' );
		$this->widget_notice      = __( 'Add this widget only in "Single Listing" widget areas.' );
		$this->settings           = array(
			'image' => array(
				'type' => 'select',
				'std' => 'avatar',
				'label' => __( 'Avatar', 'listify' ),
				'options' => array(
					'avatar' => __( 'Owner Avatar', 'listify' ),
					'logo' => __( 'Company Logo', 'listify' ),
				),
			),
			'display-name' => array(
				'type' => 'select',
				'std' => 'login',
				'label' => __( 'Display Name', 'listify' ),
				'options' => array(
					'nickname' => __( 'Nickname', 'listify' ),
					'login' => __( 'Login Name', 'listify' ),
					'email' => __( 'Email Address', 'listify' ),
					'first_last' => __( 'First + Last', 'listify' ),
					'last_first' => __( 'Last + First', 'listify' ),
				),
			),
			'display-location' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display location', 'listify' ),
			),
			'display-join-date' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display join date', 'listify' ),
			),
			'biography' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display biography', 'listify' ),
			),
			'display-contact' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display contact link', 'listify' ),
			),
			'contact-text' => array(
				'type'  => 'text',
				'std'   => 'Contact',
				'label' => __( 'Contact Button Text:', 'listify' ),
			),
			'display-profile' => array(
				'type'  => 'checkbox',
				'std'   => 1,
				'label' => __( 'Display profile link', 'listify' ),
			),
			'profile-text' => array(
				'type'  => 'text',
				'std'   => 'Profile',
				'label' => __( 'Profile Button Text:', 'listify' ),
			),
		);

		parent::__construct();
	}

	function widget( $args, $instance ) {
		global $job_preview;

		if ( ! is_singular( 'job_listing' ) && ! $job_preview ) {
			echo $this->widget_areas_notice(); // WPCS: XSS ok.
			return false;
		}

		$this->listing_owner = get_post()->post_author;

		if ( ! $this->listing_owner || 0 == $this->listing_owner ) {
			return;
		}

		foreach ( $this->settings as $key => $setting ) {
			$instance[ $key ] = isset( $instance[ $key ] ) ? $instance[ $key ] : $this->settings[ $key ]['std'];
		}

		extract( $args );

		ob_start();

		echo $before_widget;
		?>

		<div class="job_listing-author">
			<div class="job_listing-author-avatar">
				<?php echo $this->get_avatar( $instance ); ?>
			</div>

			<div class="job_listing-author-info">
				<h3 class="widget-title"><?php echo esc_attr( $this->get_name( $instance ) ); ?></h3>
			</div>

			<?php $this->the_location( $instance ); ?>
			<?php $this->the_join_date( $instance ); ?>
			<?php $this->the_biography( $instance ); ?>

			<?php if ( 'preview' != get_post()->post_status ) : ?>
			<div class="job_listing-author-info-more">
				<?php $this->the_contact_button( $instance ); ?>
				<?php $this->the_profile_button( $instance ); ?>
			</div>
			<?php endif; ?>

			<?php do_action( 'listify_widget_job_listing_author_after' ); ?>
		</div>

		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}

	/**
	 * Get the listing owner avatar.
	 *
	 * Can either be the company logo or gravatar depending on
	 * setting selection.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instance
	 * @return string $image
	 */
	public function get_avatar( $instance ) {
		$image = listify_the_listing_secondary_image( null, array(
			'type' => isset( $instance['image'] ) ? $instance['image'] : 'avatar',
			'size' => 'thumbnail',
			'style' => 'circle',
		) );

		return $image;
	}

	/**
	 * Get the listing owner's name.
	 *
	 * First attempt their nicename and fallback to username.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instance
	 * @return string $name
	 */
	public function get_name( $instance ) {
		$format = $instance['display-name'];

		switch ( $format ) {
			case 'nickname':
				$name = get_the_author_meta( 'nickname', $this->listing_owner );
				break;
			case 'email':
				$name = get_the_author_meta( 'email', $this->listing_owner );
				break;
			case 'first_last':
				$name = get_the_author_meta( 'first_name', $this->listing_owner ) . ' ' . get_the_author_meta( 'last_name', $this->listing_owner );
				break;
			case 'last_first':
				$name = get_the_author_meta( 'last_name', $this->listing_owner ) . ' ' . get_the_author_meta( 'first_name', $this->listing_owner );
				break;
			default:
				$name = get_the_author_meta( 'login', $this->listing_owner );
		}

		return $name;
	}

	/**
	 * Get the listing owner's location.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instnace
	 */
	public function the_location( $instance ) {
		$location = isset( $instance['display-location'] ) && 1 == $instance['display-location'] ? true : false;

		if ( ! $location ) {
			return;
		}

		if ( ! listify_has_integration( 'woocommerce' ) ) {
			return;
		}

		$location = get_user_meta( $this->listing_owner, 'shipping_city', true );

		if ( '' == $location ) {
			return;
		}

		$state = get_user_meta( $this->listing_owner, 'shipping_state', true );

		if ( '' != $state ) {
			$location = $location . ', ' . $state;
		}
?>

<div class="job_listing-author-location">
	<?php echo esc_attr( $location ); ?>
</div>

<?php
	}

	/**
	 * Get the listing owner's join date.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instance
	 */
	public function the_join_date( $instance ) {
		$join_date = isset( $instance['display-join-date'] ) && 1 == $instance['display-join-date'] ? true : false;

		if ( ! $join_date ) {
			return;
		}

		$join_date = get_the_author_meta( 'registered', $this->listing_owner );
?>

<div class="job_listing-author-join-date">
	<?php printf( __( 'Member since %s', 'listify' ), date_i18n( 'F Y', strtotime( $join_date ) ) ); ?>
</div>

<?php
	}

	/**
	 * Get the listing owner's biogrpahy.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instnace
	 */
	public function the_biography( $instance ) {
		$biography = isset( $instance['biography'] ) && 1 == $instance['biography'] ? true : false;

		if ( ! $biography ) {
			return;
		}

		$biography = get_the_author_meta( 'description', $this->listing_owner );

		if ( ! $biography || '' == $biography ) {
			return;
		}
?>

<div class="job_listing-author-biography">
	<?php echo wpautop( wptexturize( $biography ) ); ?>
</div>

<?php
	}

	/**
	 * Get the listing owner's contact button.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instance
	 */
	public function the_contact_button( $instance ) {
		$contact = ! isset( $instance['display-contact'] ) || 1 == $instance['display-contact'] ? true : false;
		$contact_text = isset( $instance['contact-text'] ) ? $instance['contact-text'] : 'Contact';
		$apply_method = get_the_job_application_method();

		if ( ! $contact || ! candidates_can_apply() || ! $apply_method ) {
			return;
		}
?>
<a href="#job_listing-author-apply" data-mfp-src=".job_application" class="application_button button button-secondary popup-trigger">
	<?php echo esc_attr( $contact_text ); // Popup Trigger. ?>
</a>
<?php
		get_job_manager_template( 'job-application.php' ); // Hidden, open via popup.
	}

	/**
	 * Get the listing owner's profile button.
	 *
	 * @since 1.8.0
	 *
	 * @param array $instance
	 */
	public function the_profile_button( $instance ) {
		$profile = ! isset( $instance['display-profile'] ) || 1 == $instance['display-profile'] ? true : false;
		$profile_text = isset( $instance['profile-text'] ) ? $instance['profile-text'] : 'Profile';

		if ( ! $profile ) {
			return;
		}
?>

<a href="<?php echo esc_url( get_author_posts_url( $this->listing_owner ) ); ?>" class="button">
	<?php echo esc_attr( $profile_text ); ?>
</a>

<?php
	}
}
