<?php
/**
 * Handle individual listing data.
 *
 * This class implements WordPress-level data management
 * but does not interface with any 3rd party plugins directly.
 *
 * @since 2.0.0
 *
 * @package Listify
 * @category Listing
 * @author Astoundify
 */
abstract class Listify_Listing {

	/**
	 * The associated WordPress post object.
	 *
	 * @since 2.0.0
	 * @var WP_Post $post
	 */
	protected $post;

	/**
	 * Load a new instance of a listing.
	 *
	 * @since 2.0.0
	 *
	 * @param null|int|WP_Post $post Current object.
	 */
	public function __construct( $post ) {
		if ( ! $post ) {
			$this->post = get_post();
		} elseif ( is_int( $post ) ) {
			$this->post = get_post( $post );
		} elseif ( is_a( $post, 'WP_Post' ) ) {
			$this->post = $post;
		}
	}

	/**
	 * Listing ID
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->get_object()->ID;
	}

	/**
	 * Listing price
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_price() {
		return get_post_meta( $this->get_object()->ID,'price' , true);
	}

	/**
	 * Listing amenity
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_amenity_icon() {
		//Victor
		$amenity_str = '';
		$amenity = get_the_terms($this->get_object()->ID,'job_listing_category'); //array object
		
		foreach ($amenity as $k=>$item) {
			if($k>2) break;
			$icons = get_option("templtax_".$item->term_id);
			$img = '';
			if(is_array($icons)){
				foreach($icons as $size => $attach_id) {
					if($attach_id > 0) { 
						$img = wp_get_attachment_image($attach_id,'templ_icon_small');
					}
				}
			}

			if($img!='' && $item->term_type == 'templ_upload_img'){
				$amenity_str .= '<a href="javascript:void(0)"'. ' title="'.$item->name.'">'.$img .'</a>' .' ';
			} else {
				if($item->term_font_icon != '0'){
					$amenity_str .= '<a href="javascript:void(0)"'. ' title="'.$item->name.'">' .$item->term_font_icon.'</a>' .' ';
				}
			}
			//$amenity_str .= $item->name;
			//$amenity_str.='</span>';
		}
		return $amenity_str;
	}

	/**
	 * Listing person
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_person() {
		return get_post_meta( $this->get_object()->ID,'person' , true);
	}

	/**
	 * Listing bedroom
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_bedroom() {
		return get_post_meta( $this->get_object()->ID,'bedroom' , true);
	}

	/**
	 * Listing bathroom
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_bathroom() {
		return get_post_meta( $this->get_object()->ID,'bathroom' , true);
	}

	/**
	 * Associated listing object
	 *
	 * @since 2.0.0
	 */
	public function get_object() {
		return $this->post;
	}

	/**
	 * Status
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_status() {
		return $this->get_object()->post_status;
	}

	/**
	 * Title
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_title() {
		return get_the_title( $this->get_id() );
	}

	/**
	 * Short Description
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_short_description() {
		return wp_trim_words( $this->get_object()->post_content, 55 );
	}

	/**
	 * Permalink
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_permalink() {
		return get_permalink( $this->get_id() );
	}

	/**
	 * Featured Image
	 *
	 * @since 2.0.0
	 *
	 * @param string $size Size of the featured image to load.
	 * @return string $url
	 */
	public function get_featured_image( $size = 'large' ) {
		$image = null;
		$size = apply_filters( 'listify_listing_featured_image_size', $size );
		$featured = wp_get_attachment_image_src( get_post_thumbnail_id( $this->get_id() ), $size );

		if ( is_array( $featured ) ) {
			$image = $featured;
		}

		if ( ! $image && true === apply_filters( 'listify_listing_cover_use_gallery_images', '__return_false' ) ) {
			$image = listify_get_cover_from_group( array(
				'object_ids' => array( $this->get_id() ),
				'size' => $size,
			) );
		}

		/**
		 * Hackery to ensure the current global $post object is the current object.
		 *
		 * @since 2.0.4
		 */
		global $post;

		// Save old global (not the current object).
		$old = $post;

		// Set $post to this object.
		$post = $this->get_object();

		// Backwards compatibility for old filter.
		$image = apply_filters( 'listify_cover_image', $image, array(
			'size' => $size,
		) );

		// Reset global to whatever it was.
		$post = $old;

		return is_array( $image ) ? $image[0] : $image;
	}

	/**
	 * The Secondary Image
	 *
	 * Depending on the Customizer Setting "Customize > Listings > Listing Results > Listing Card Avatar"
	 * this will either output the lising owner's avatar (gravatar by default) or the uploaded company logo.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Adjust the output of image.
	 */
	public function get_secondary_image( $args = array() ) {
		$defaults = array(
			'listing' => $this->get_object(),
			'size' => 'thumbnail',
			'type' => get_theme_mod( 'listing-archive-card-avatar', 'avatar' ),
			'style' => get_theme_mod( 'listing-archive-card-avatar-style', 'circle' ),
		);

		if ( is_singular( 'job_listing' ) && ! did_action( 'listify_single_job_listing_cover_end' ) ) {
			$defaults['type'] = get_theme_mod( 'single-listing-secondary-image', 'avatar' );
			$defaults['style'] = get_theme_mod( 'single-listing-secondary-image-style', 'circle' );
		}

		$args = wp_parse_args( $args, $defaults );

		$image = false;

		if ( 'avatar' === esc_attr( $args['type'] ) ) {
			$image = $this->get_company_avatar( $args );
		} else {
			$image = $this->get_company_logo( $args );
		}

		return esc_url( $image );
	}

	/**
	 * Return the URL of the company gravatar (listing owner)
	 *
	 * @since 2.0.0
	 */
	public function get_company_avatar( $args ) {
		$defaults = array(
			'listing' => $this->get_object(),
			'size' => array( 200, 200 ),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( 'thumbnail' === $args['size'] ) {
			$args['size'] = get_option( 'thumbnail_size_w' );
		}

		$gravatar = get_avatar( $args['listing']->post_author, $args['size'] );

		if ( $gravatar ) {
			preg_match( '/src=("|\')(.*?)("|\')/s', $gravatar, $matches );

			$gravatar = isset( $matches[2] ) ? $matches[2] : '';
		}

		return $gravatar;
	}

	/**
	 * Return the URL to the logo
	 *
	 * This is not the "Company Logo" that is default with WP Job Manager. Instead
	 * it is a separate File Upload field that is stored as a plain URL.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args Adjust the output of the image.
	 */
	abstract public function get_company_logo( $args = array() );

	/**
	 * Phone Number
	 *
	 * @since 2.0.0
	 *
	 * @param bool $sanitize Strip non-numeric characters or not.
	 * @return null|string
	 */
	abstract public function get_telephone( $sanitize = false );

	/**
	 * Email
	 *
	 * @since 2.0.0
	 *
	 * @return null|string
	 */
	abstract public function get_email();

	/**
	 * URL
	 *
	 * @since 2.0.0
	 *
	 * @return null|string
	 */
	abstract public function get_url();

	/**
	 * Get location data.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	abstract public function get_location_data();

	/**
	 * Latitude
	 *
	 * @since 2.0.0
	 *
	 * @return float|int
	 */
	public function get_lat() {
		$location_data = $this->get_location_data();

		return isset( $location_data['latitude'] ) ? $location_data['latitude'] : null;
	}

	/**
	 * Longitude
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_lng() {
		$location_data = $this->get_location_data();

		return isset( $location_data['longitude'] ) ? $location_data['longitude'] : null;
	}

	/**
	 * Location
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	abstract public function get_location();

	/**
	 * Get location based on the chosen formatting.
	 *
	 * @since 2.0.0
	 *
	 * @param string $format The output format.
	 * @return string
	 */
	public function get_location_formatted( $format = null ) {
		$location = $this->get_location();

		/* Bail if no location */
		if ( ! $location ) {
			return;
		}

		/* Format not specified, get from theme mod */
		if ( ! $format ) {
			$format = get_theme_mod( 'listing-address-format', 'formatted' );
		}

		/* None as format, display plain location */
		if ( 'none' === $format ) {
			return $location;
		} elseif ( 'coordinates' === $format ) {
			return $this->get_coordinates();
		} elseif ( 'formatted' === $format ) {
			$address = apply_filters( 'listify_formatted_address', $this->get_location_data(), $location, $this->get_object() );

			$output = array(
				sprintf( '<a class="google_map_link" href="%s" target="_blank">', $this->get_map_url() ),
				listify_get_formatted_address( $address ),
				'</a>',
			);

			$output = apply_filters( 'listify_the_location_formatted_parts', $output, $location, $this->get_object() );
			$output = apply_filters( 'listify_the_location_formatted', implode( '', array_map( 'html_entity_decode', $output ) ) );

			return $output;
		}
	}

	/**
	 * Get the coordinates.
	 *
	 * @since 2.0.0
	 *
	 * @return string $coordinates
	 */
	public function get_coordinates() {
		return $this->get_lat() . ',' . $this->get_lng();
	}

	/**
	 * Get best rating.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_rating_best() {
		return apply_filters( 'listify_get_listing_rating_best', 5, $this );
	}

	/**
	 * Get worst rating.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_rating_worst() {
		return apply_filters( 'listify_get_listing_rating_worst', 1, $this );
	}

	/**
	 * Get rating average.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_rating_average() {
		return absint( apply_filters( 'listify_get_listing_rating_average', 0, $this ) );
	}

	/**
	 * Get rating count.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public function get_rating_count() {
		return absint( apply_filters( 'listify_get_listing_rating_count', 0, $this ) );
	}

	/**
	 * Generate a URL to Google based on the location data.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_map_url() {
		$base = 'http://maps.google.com/maps';
		$args = array(
			'daddr' => urlencode( $this->get_coordinates() ),
		);

		return esc_url( add_query_arg( $args, $base ) );
	}

	/**
	 * Map marker term
	 *
	 * @since 2.0.0
	 *
	 * @return null|WP_Term
	 */
	public function get_marker_term() {
		$taxonomy = listify_get_top_level_taxonomy();

		// Use Yoast SEO primary term feature if active.
		if ( function_exists( 'yoast_get_primary_term_id' ) ) {
			$term = get_term( yoast_get_primary_term_id( $taxonomy, $this->get_id() ) );

			if ( ! is_wp_error( $term ) && ! empty( $term ) ) {
				return $term;
			}
		}

		// Get current listing terms.
		$terms = get_the_terms( $this->get_id(), $taxonomy );

		if ( ! empty( $terms ) ) {
			// Look for the deepest term.
			if ( apply_filters( 'listify_map_marker_term_deepest', false ) ) {
				$deepest = false;
				$max = -1;

				foreach ( $terms as $term ) {
					$parents = get_ancestors( $term->term_id, $taxonomy );
					$depth = count( $parents );

					if ( $depth > $max ) {
						$deepest = $term;
						$max = $depth;
					}
				}

				$term = $deepest;
			} else { // Pic the first top level.
				$parent = current( $terms );

				foreach ( $terms as $term ) {
					if ( 0 === $term->parent ) {
						$parent = $term;
						break;
					}
				}

				$term = $parent;
			}
		} else { // Mock a WP_Term object.
			$term = new stdClass();
			$term->term_id = 0;
		}

		return $term;
	}

	/**
	 * Map marker term icon
	 *
	 * @since 2.0.0
	 *
	 * @return null|string
	 */
	public function get_marker_term_icon() {
		$default_icon = get_theme_mod( 'default-marker-icon', 'information-circled' );
		$tax = listify_get_top_level_taxonomy();
		$term = $this->get_marker_term();

		if ( 0 === $term->term_id ) {
			return 'ion-' . $default_icon;
		}

		$icon = get_theme_mod( 'listings-' . $tax . '-' . $term->term_id . '-icon', $default_icon );

		if ( $icon ) {
			$icon = 'ion-' . $icon;
		} else {
			$icon = 'ion-information-circled';
		}

		return apply_filters( 'listify_listing_marker_term_icon', $icon, $term, $default_icon );
	}

	/**
	 * Get Business Hours
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	abstract public function get_business_hours();

	/**
	 * Get Business Hours Timezone
	 *
	 * @since 2.1.0
	 *
	 * @return string
	 */
	abstract public function get_business_hours_timezone();

	/**
	 * Get Business Hours GMT
	 *
	 * @since 2.1.0
	 *
	 * @return int
	 */
	abstract public function get_business_hours_gmt();

	/**
	 * Is Business Open.
	 *
	 * @since 2.1.0
	 *
	 * @return bool|null Null for unknown status.
	 */
	public function is_open() {
		$is_open = false;
		$format = get_option( 'time_format' );

		// Current time.
		$current_day = strtolower( listify_get_current_time( 'D', $this->get_business_hours_gmt() ) );
		$current_time = strtolower( listify_get_current_time( $format, $this->get_business_hours_gmt() ) ); // "8:00 pm".
		$time = DateTime::createFromFormat( $format, $current_time );

		// Opening hours data.
		$opening_hours = $this->get_business_hours();

		// Hour of the day not set.
		if ( ! isset( $opening_hours[ $current_day ] ) ) {
			return $is_open; // Closed.
		}

		// Loop each hour and compare with current time.
		foreach ( $opening_hours[ $current_day ] as $hours ) {
			if ( isset( $hours['open'], $hours['close'] ) && $hours['open'] && $hours['close'] ) {

				$open = DateTime::createFromFormat( $format,  $hours['open'] );
				$close = DateTime::createFromFormat( $format, $hours['close'] );

				// Can't create a valid time.
				if ( ! $close || ! $open ) {
					$is_open = false;
					break;
				}

				// Assume we've moved to the next day if closing time is "before" opening.
				if ( $close < $open ) {
					$close = $close->add( new DateInterval( 'P1D' ) );
				}

				// Open 24 Hours.
				if ( '24h' === $hours['open'] && '24h' === $hours['close'] ) {
					$is_open = true;
					break;
				} elseif ( 'Closed' === $hours['open'] && 'Closed' === $hours['close'] ) { // Closed.
					$is_open = false;
					break;
				} else {

					// Get time format.
					$open = DateTime::createFromFormat( $format,  $hours['open'] );
					$close = DateTime::createFromFormat( $format, $hours['close'] );

					// Unknown status, cannot be converted to time format.
					if ( ! $open || ! $close ) {
						$is_open = null;
						break;
					}

					// Assume we've moved to the next day if closing time is "before" opening.
					if ( $close < $open ) {
						$close = $close->add( new DateInterval( 'P1D' ) );
					}

					if ( $time > $open && $time < $close ) {
						$is_open = true;
						break;
					}
				}
			}
		}

		return apply_filters( 'listify_get_listing_is_open', $is_open, $this );
	}

	/**
	 * Featured?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	abstract public function is_featured();

	/**
	 * Filled?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	abstract public function is_filled();

	/**
	 * Claimed?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	abstract public function is_claimed();

	/**
	 * Favorited?
	 *
	 * @since 2.0.0
	 *
	 * @return bool
	 */
	public function is_favorited() {
		return (bool) apply_filters( 'listify_get_listing_is_favorited', false, $this );
	}

	/**
	 * Generate JSON-LD for a listing.
	 *
	 * @since 2.0.0
	 *
	 * @see http://json-ld.org/
	 * @see https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-structured-data.php
	 *
	 * @return array
	 */
	public function get_json_ld() {
		$markup = array();

		$location_data = $this->get_location_data();

		$markup = array(
			'@context' => 'http://schema.org',
			'@type' => 'Place',
			'@id' => $this->get_permalink(),
			'name' => $this->get_title(),
			'description' => $this->get_short_description(),
			'url' => array(
				'@type' => 'URL',
				'@id' => $this->get_permalink(),
			),
			'hasMap' => $this->get_map_url(),
		);

		// Location.
		if ( $location_data['address_1'] ) {
			$markup['address'] = array(
				'@type' => 'PostalAddress',
			);

			if ( '' !== $location_data['city'] ) {
				$markup['address']['addressLocality'] = $location_data['city'];
			}

			if ( '' !== $location_data['state'] ) {
				$markup['address']['addressRegion'] = $location_data['state'];
			}

			if ( '' !== $location_data['postcode'] ) {
				$markup['address']['postalCode'] = $location_data['postcode'];
			}

			if ( '' !== $location_data['full_country'] ) {
				$markup['address']['addressCountry'] = $location_data['full_country'];
			}

			if ( '' !== $location_data['address_1'] ) {
				$markup['address']['streetAddress'] = $location_data['address_1'] . ( '' !== $location_data['address_2'] ? ( ' ' . $location_data['address_2'] ) : '' );
			}
		}

		// Geolocation.
		if ( $this->get_lat() ) {
			$markup['geo'] = array(
				'@type' => 'GeoCoordinates',
				'latitude' => $this->get_lat(),
				'longitude' => $this->get_lng(),
			);
		}

		// Ratings.
		if ( 0 !== $this->get_rating_count() ) {
			$markup['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $this->get_rating_average(),
				'ratingCount' => absint( $this->get_rating_count() ),
				'bestRating'  => absint( $this->get_rating_best() ),
				'worstRating' => absint( $this->get_rating_worst() ),
			);
		}

		// Image.
		$image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->get_id() ) );

		if ( false !== $image ) {
			$markup['image'] = $image[0];
		}

		// Logo.
		$logo = $this->get_secondary_image();

		if ( false !== $logo ) {
			$markup['logo'] = array(
				'@type' => 'URL',
				'@id' => $logo,
			);
		}

		// Main entity.
		if ( $this->get_url() ) {
			$markup['mainEntityOfPage'] = array(
				'@type' => 'WebPage',
				'@id' => $this->get_url(),
			);
		}

		return apply_filters( 'listify_get_listing_jsonld', $markup, $this );
	}

	/**
	 * Describe the listing data in an organized fashion.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function to_array() {
		/**
		 * Allow plugins and themes to add data describing a listing. This data
		 * is sent to the Javascript result output to be accessed via Javascript
		 * template functions.
		 *
		 * @since 2.0.0
		 *
		 * @param array $data Data about the listing.
		 * @param array $this The current listing.
		 * @return array $data
		 */
		return apply_filters( 'listify_get_listing_to_array', array(
			'id' => $this->get_id(),
			'object' => $this->get_object(),
			'price' => $this->get_price(),
			'amenity' => $this->get_amenity_icon(),
			'person' => $this->get_person(),
			'bathroom' => $this->get_bathroom(),
			'bedroom' => $this->get_bedroom(),
			'status' => array(
				'published' => 'publish' === $this->get_status(),
				'featured' => $this->is_featured(),
				'claimed' => $this->is_claimed(),
				'favorited' => $this->is_favorited(),
				'businessHours' => null === $this->is_open() ? false : ( $this->get_business_hours() ? $this->get_business_hours() : false ),
				'businessIsOpen' => $this->is_open(),
			),
			'permalink' => $this->get_permalink(),
			'title' => $this->get_title(),
			'telephone' => $this->get_telephone(),
			'location' => array(
				'raw' => $this->get_location( 'raw' ),
				'address' => $this->get_location_formatted(),
				'lng' => $this->get_lng(),
				'lat' => $this->get_lat(),
			),
			'featuredImage' => array(
				'url' => $this->get_featured_image(),
			),
			'secondaryImage' => array(
				'url' => esc_url( $this->get_secondary_image() ),
				'type' => esc_attr( get_theme_mod( 'listing-archive-card-avatar', 'avatar' ) ),
				'style' => esc_attr( get_theme_mod( 'listing-archive-card-avatar-style', 'circle' ) ),
				'permalink' => 'avatar' === get_theme_mod( 'listing-archive-card-avatar', 'avatar' ) ? esc_url( get_author_posts_url( $this->get_object()->post_author ) ) : null,
			),
			'mapMarker' => array(
				'term' => $this->get_marker_term()->term_id,
				'icon' => $this->get_marker_term_icon(),
				'target' => get_theme_mod( 'listing-archive-window', false ) && ! is_front_page() ? '_blank' : '',
			),
			'styles' => array(
				'featuredStyle' => get_theme_mod( 'listing-archive-feature-style', 'outline' ),
				'cardClasses' => listify_get_listing_card_class( $this->get_id() ),
			),
			'i18n' => array(
				'featured' => _x( 'Featured', 'featured listing', 'listify' ),
			),
			'reviews' => array(
				'average' => $this->get_rating_average(),
				'count' => $this->get_rating_count(),
				'stars' => array(
					'full' => floor( $this->get_rating_average() ),
					'half' => ceil( $this->get_rating_average() - floor( $this->get_rating_average() ) ),
					'empty' => $this->get_rating_best() - floor( $this->get_rating_average() ) - ceil( $this->get_rating_average() - floor( $this->get_rating_average() ) ),
				),
				'i18n' => array(
					// Translators: %d Number of stars.
					'totalStars' => sprintf( _n( '%d Star', '%d Stars', $this->get_rating_average(), 'listify' ), $this->get_rating_average() ),
				),
			),
			'json_ld' => $this->get_json_ld(),

			// @todo figure out a better way to pass these conditionals
			'cardDisplay' => array(
				'title' => get_theme_mod( 'listing-card-display-title', true ),
				'address' => get_theme_mod( 'listing-card-display-location', true ),
				'telephone' => get_theme_mod( 'listing-card-display-phone', true ),
				'rating' => get_theme_mod( 'listing-card-display-rating', true ) && ( listify_has_integration( 'ratings' ) || listify_has_integration( 'wp-job-manager-reviews' ) ),
				'secondaryImage' => get_theme_mod( 'listing-card-display-secondary-image', true ),
				'claimed' => get_theme_mod( 'listing-card-display-claim-badge', true ),
				'target' => get_theme_mod( 'listing-archive-window', false )
			),

			// @todo this probably should be added from somewhere else
			'currentUser' => array(
				'id' => get_current_user_id(),
			),
		), $this );
	}

}
