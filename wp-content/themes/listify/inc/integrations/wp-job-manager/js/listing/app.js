( function() {

	// Bind.
	var __bind = function(fn, me){
		return function(){
			return fn.apply(me, arguments); 
		}; 
	};

	// Add in wp object.
	wp.listify = wp.listify || {};
	wp.listify.listing = {};


	/****************************
	 * LISTING MAPS
	 ****************************/

	var ListifySingleMap;
	ListifySingleMap = ( function() {

		/**
		 * Constructor.
		 */
		function ListifySingleMap() {

			// Get vars needed.
			this.options = listifySingleMap;      // Data from localize script.
			this.canvas  = 'listing-contact-map'; // #listing-contact-map .

			if ( ! document.getElementById( this.canvas ) ) {
				return;
			}

			// Setup map based on selected map provider.
			if ( 'googlemaps' === this.options.provider ) {
				return this.setupGoogleMaps();
			} else if ( 'mapbox' === this.options.provider ) {
				return this.setupMapBox();
			}
		}

		/**
		 * Google Maps Setup.
		 */
		ListifySingleMap.prototype.setupGoogleMaps = function() {

			// Var:
			this.latlng = new google.maps.LatLng( this.options.lat, this.options.lng );

			// Set Map:
			this.map = new google.maps.Map( document.getElementById( this.canvas ), {
				zoom: parseInt( this.options.mapOptions.zoom ),
				center: this.latlng,
				scrollwheel: false,
				styles: this.options.mapOptions.styles,
				streetViewControl: false,
			} );

			// Remove other businesses.
			this.map.setOptions( { styles: [
				{
					featureType: "poi",
					stylers: [
						{
							visibility: "off",
						}
					],
				}
			] } );

			// Set Marker (using RichMarker Library):
			this.marker = new RichMarker( {
				position: this.latlng,
				flat: true,
				draggable: false,
				content: '<div class="map-marker marker-color-' + this.options.term + ' type-' + this.options.term + '"><i class="' + this.options.icon + '"></i></div>'
			} );
			this.marker.setMap( this.map );
		};

		/**
		 * MapBox Setup (Using Leaflet Library)
		 */
		ListifySingleMap.prototype.setupMapBox = function() {

			// Load Map:
			this.map = L.map( this.canvas ).setView( [ this.options.lat, this.options.lng ], parseInt( this.options.mapOptions.zoom ) );

			// Map Style:
			L.tileLayer( this.options.mapOptions.mapboxTileUrl, {
				attribution: 'Map data &copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="https://mapbox.com">Mapbox</a>',
				maxZoom: this.options.mapOptions.maxZoom,
			} ).addTo( this.map );

			// Marker data:
			this.markerTemplate = wp.template( 'pinTemplate' ); // Loaded in footer.
			this.markerTemplateData = {
				mapMarker: {
					term: this.options.term,
					icon: this.options.icon,
				},
				status: {
					featured: false,
				}
			}
			this.markerIcon = L.divIcon( {
				iconSize: [30, 45],
				iconAnchor: [15, 45],
				className: '',
				html: this.markerTemplate( this.markerTemplateData ),
			} );

			// Add marker to map:
			this.marker = L.marker( [ this.options.lat, this.options.lng ], { icon: this.markerIcon } ).addTo( this.map );
		};

		return ListifySingleMap;

	} )(); // end ListifySingleMap;

	// Define Map.
	wp.listify.listing.map = function() {
		return new ListifySingleMap();
	};

	// Load Map.
	wp.listify.listing.map();

	/****************************
	 * COMMENTS
	 ****************************/

	jQuery( function($) {

		var ListifyListingComments;

		ListifyListingComments = ( function() {

			function ListifyListingComments() {

				// Get vars needed: Data from localize script.
				this.options = listifyListingComments;

				this.toggleStars = __bind( this.toggleStars, this );
				this.bindActions = __bind( this.bindActions, this );
				$( '.form-submit' ).append( $( '<input />' ).attr( {
					type: 'hidden',
					id: 'comment_rating',
					name: 'comment_rating',
					value: this.options.defaultRating,
				} ) );
				this.bindActions();
			}

			ListifyListingComments.prototype.bindActions = function() {
				$( '.comment-sorting-filter' ).on( 'change', function( e ) {
					return $( this ).closest( 'form' ).submit();
				} );
				return $( '.comment-form-rating.comment-form-rating--listify .star' ).on( 'click', ( function( _this ) {
					return function(e) {
						e.preventDefault();
						return _this.toggleStars( e.target );
					};
				} )( this ) );
			};

			ListifyListingComments.prototype.toggleStars = function( el ) {
				var rating;
				$( '.comment-form-rating.comment-form-rating--listify .star' ).removeClass( 'active' );
				el = $(el);
				el.addClass( 'active' );
				rating = el.data( 'rating' );
				return $( '#comment_rating' ).val( rating );
			};

			return ListifyListingComments;

		} )();

		return new ListifyListingComments();

	});

	/****************************
	 * GALLERY
	 ****************************/

	jQuery( function($) {

		var ListifyListingGallery;

		ListifyListingGallery = (function() {

			function ListifyListingGallery() {
				this.slick = __bind( this.slick, this );
				this.gallery = __bind( this.gallery, this );
				this.cover = __bind( this.cover, this );
				this.slick();
				this.gallery();
				this.cover();
			}

			ListifyListingGallery.prototype.cover = function() {
				var $container,
					$fixedHeight;
				$fixedHeight = $( '.single-job_listing-cover-gallery' ).outerHeight();
				$container = $( '.single-job_listing-cover-gallery-slick:not(.slick-initialized)' );
				if ( 0 === $container.length ) {
					return;
				}
				$container.on( 'lazyLoaded', function( slick ) {
					$container.fadeIn( 1000 );
					return $container.slick( 'setPosition' );
				} );

				return $container.slick( {
					variableWidth: true,
					centerMode: true,
					slidestoShow: 1,
					dots: true,
					infinite: true,
					lazyLoad: 'ondemand',
					rtl: '1' === listifySettings.is_rtl
				} );
			};

			ListifyListingGallery.prototype.gallery = function() {
				var args,
					preview;

				preview = $( '#job_preview' ).length || $( '.no-gallery-comments' ).length;
				if ( 0 === preview ) {
					return;
				}
				args = {
					tClose: listifySettings.l10n.magnific.tClose,
					tLoading: listifySettings.l10n.magnific.tLoading,
					gallery: {
						enabled: true,
						preload: [1, 1]
					}
				};
				if ( preview ) {
					args.type = 'image';
				} else {
					args.type = 'ajax';
					args.ajax = {
						tError: listifySettings.l10n.magnific.tError,
						settings: {
							type: 'GET',
							data: {
								'view': 'singular'
							}
						}
					};
					args.callbacks = {
						open: function() {
							return $( 'body' ).addClass( 'gallery-overlay' );
						},
						close: function() {
							return $( 'body' ).removeClass( 'gallery-overlay' );
						},
						lazyLoad: function( item ) {
							var $thumb;
							return $thumb = $( item.el ).data( 'src' );
						},
						parseAjax: function( mfpResponse ) {
							return mfpResponse.data = $( mfpResponse.data ).find( '#main' );
						}
					};
				} // end if( preview ).
				return $( '.listing-gallery__item-trigger' ).magnificPopup( args );
			};

			ListifyListingGallery.prototype.slick = function() {
				$( '.listing-gallery' ).slick({
					slidesToShow: 1,
					slidesToScroll: 1,
					arrows: false,
					fade: true,
					adaptiveHeight: true,
					asNavFor: '.listing-gallery-nav',
					rtl: '1' === listifySettings.is_rtl
				});

				return $( '.listing-gallery-nav' ).slick({
					slidesToShow: 7,
					slidesToScroll: 1,
					asNavFor: '.listing-gallery',
					dots: true,
					arrows: false,
					focusOnSelect: true,
					infininte: true,
					rtl: listifySettings.is_rlt,
					responsive: [
						{
							breakpoint: 1200,
							settings: {
								slidesToShow: 5
							}
						}
					]
				});
			};

			return ListifyListingGallery;

		})();

		wp.listify.listing.gallerySlider = function() {
			return new ListifyListingGallery();
		};

		return wp.listify.listing.gallerySlider();

	});

	/****************************
	 * LOCATE ME
	 ****************************/

	jQuery(function($) {

		var listingLocateMe;

		listingLocateMe = ( function() {

			function listingLocateMe() {
				this.find = __bind(this.find, this);
				this.bindActions = __bind(this.bindActions, this);
				this.$directionsLocate = $( '#get-directions-locate-me' );
				this.$directionsSAddr = $( '#get-directions-start' );
				this.bindActions();
			}

			listingLocateMe.prototype.bindActions = function() {
				var self;
				self = this;
				$( '#get-directions' ).on( 'click', (function(_this) {
					return function(e) {
						e.preventDefault();
						return $( '#get-directions-form' ).toggle();
					};
				})(this));

				return this.$directionsLocate.on( 'click', ( function( _this ) {
					return function(e) {
						e.preventDefault();
						self.$directionsLocate.addClass( 'loading' );
						return self.find();
					};
				} )( this ) );
			};

			listingLocateMe.prototype.find = function() {
				var error, self, success;
				self = this;
				if ( ! navigator.geolocation ) {
					return;
				}
				success = function(position) {
					var geocoder, latlng;
					if (position.coords) {
						latlng = new google.maps.LatLng( position.coords.latitude, position.coords.longitude );
						geocoder = new google.maps.Geocoder();
						geocoder.geocode({
							location: latlng
						}, function( result ) {
							return self.$directionsSAddr.val( result[0].formatted_address );
						});
					}
					return self.$directionsLocate.removeClass( 'loading' );
				};
				error = function() {
					return self.$directionsLocate.removeClass( 'loading' );
				};
				return navigator.geolocation.getCurrentPosition( success, error );
			};

			return listingLocateMe;

		})();

		return new listingLocateMe();

	});

}).call(this);
