window.wp = window.wp || {};

/**
 * Display returned data.
 *
 * @since 2.0.0
 */
(function( window, undefined ){

	window.wp = window.wp || {};
	var document = window.document;
	var $ = window.jQuery;

	var api = wp.listifyResults || {};

	/**
	 * @since 2.0.0
	 */
	api.DataServices = [];

	/**
	 * A generic data service.
	 *
	 * @since 2.0.0
	 */
	api.DataService = api.Class.extend({

		/**
		 * The response data from the service.
		 *
		 * @since 2.0.0
		 */
		response: null,

		/**
		 * Manage the state of the response data.
		 *
		 * @since 2.0.0
		 */
		activeResponse: $.Deferred(),

		/**
		 * The template the result data is passed to.
		 *
		 * @since 2.0.0
		 */
		resultTemplate: wp.template( 'listingCard' ),

		/**
		 * The template to show when no results are found.
		 *
		 * @since 2.0.0
		 */
		noResultsTemplate: wp.template( 'noResults' ),

		/**
		 * The area the listing results are appended to.
		 *
		 * @since 2.0.0
		 */
		resultsContainer: 'ul.job_listings',

		/**
		 * @param {object} options
		 */
		initialize: function( options ) {
			$.extend( this, options || {} );
		},

		/**
		 * Update the data service.
		 *
		 * @since 2.0.0
		 */
		update: function() {},

		/**
		 * Get default coordinates.
		 *
		 * This is related to the data service so the last searched location
		 * can be used when available.
		 *
		 * @since 2.0.0
		 */
		getDefaultCoordinates: function() {},

		/**
		 * Add active listings to the current view.
		 *
		 * @since 2.0.0
		 */
		addResults: function( resultsContainer ) {
			if ( window.console && api.settings.scriptDebug ) {
				console.log( 'Adding results...' );
			}

			var self = this;
			var defaultContainer = this.resultsContainer;

			if ( ! _.isUndefined( resultsContainer ) ) {
				this.resultsContainer = resultsContainer;
			}

			this.resetResults();

			var $resultsContainer = $( this.resultsContainer );

			if ( $resultsContainer.length === 0 ) {
				return;
			}

			if ( ! _.isUndefined( api.controllers.dataService.response.listings ) && 0 !== api.controllers.dataService.response.listings.length ) {
				// Clear results container.
				$resultsContainer
					.html( '' )
					.addClass( 'listing-cards-anchor--active' )

				_.each( api.controllers.dataService.response.listings, function( listing ) {
					$resultsContainer.append( self.resultTemplate( listing ) );
				} );
			}

			// Reset container for multiple items on the page.
			this.resultsContainer = defaultContainer;
		},

		/**
		 * Reset results in current view.
		 *
		 * @since 2.0.0
		 */
		resetResults: function() {
			this.setFound();

			$( this.resultsContainer )
				.removeClass( 'listing-cards-anchor--active' )
				.html( this.noResultsTemplate( { 
					noResults: api.settings.i18n.noResults 
				} ) );
		},

		/**
		 * Set the amount of listings in the current view.
		 *
		 * @since 2.0.0
		 */
		setFound: function() {
			var found = ! _.isUndefined( api.controllers.dataService.response.found_posts ) ? api.controllers.dataService.response.found_posts : 0;
			$( '.results-found' ).text( api.settings.i18n.resultsFound.replace( '%d', found ) );
		}

	});

	/**
	 * WP Job Manager
	 *
	 * @since 2.0.0
	 */
	api.DataServices.WPJobManager = api.DataService.extend({

		/**
		 * If the WP Job Manager search address has geolocation data.
		 *
		 * @since 2.0.0
		 */
		isAddressGeocoded: false,

		/**
		 * Track the previous address string to see if it has changed
		 * when the form submits.
		 *
		 * @since 2.0.0
		 */
		prevAddress: $( '#search_location' ).val(),

		/**
		 * WP Job Manager DOM.
		 *
		 * @since 2.0.0
		 */
		$target: $( 'div.job_listings' ),
		$form: $( 'form.job_filters' ),
		$address: $( '#search_location' ),
		$lat: $( '#search_lat' ),
		$lng: $( '#search_lng' ),
		$radius: $( '#search_radius' ),

		// Used by getElementById
		autoComplete: 'search_location',

		/**
		 * Manage the global state based on the returned response.
		 *
		 * @since 2.0.0
		 */
		initialize: function( options ) {
			api.DataService.prototype.initialize.call( options );

			/**
			 * The area the listing results are appended to.
			 *
			 * @since 2.0.0
			 */
			this.resultsContainer = this.$target.children( 'ul.job_listings' );

			// Don't let the form submit.
			this.$form.on( 'submit', function( e ) {
				e.preventDefault();
			} );

			// Search by radius.
			this.radiusSlider();

			// Auto locate the user.
			this.locateMe();

			if ( 0 === this.$target.length ) {
				return;
			}

			var self = this;

			this.$target.on( 'updated_results', function( event, data ) {
				// Set the returned data and alert that the data is ready to be used.
				self.response = data;

				// First run, just notify it is complete.
				if ( ! self.activeResponse.state( 'resolved' ) ) {
					if ( window.console && api.settings.scriptDebug ) {
						console.log( 'Data Service ready...' );
					}

					self.activeResponse.resolve();
				} else {
					// Add results to view.
					self.addResults();

					// Add results to map
					if ( ! _.isUndefined( api.controllers.mapService ) ) {
						api.controllers.mapService.addMarkers( self.response.listings );
					}
				}

				$(document).trigger( 'listifyDataServiceLoaded' );
			} );

			// Before a search starts.
			this.$target.on( 'update_results', function(e) {
				self.maybeReverseGeocode();
			} );
		},

		/**
		 * Update the data service (resubmit the form).
		 *
		 * @since 2.0.0
		 */
		update: function() {
			this.$target.triggerHandler( 'update_results', [ 1, false ] );
		},

		/**
		 * Maybe reverse geocode a prefilled location.
		 *
		 * Current Map Service must implement a geocode method.
		 *
		 * @since 2.0.0
		 */
		maybeReverseGeocode: function() {
			if ( ! api.controllers.mapService ) {
				return;
			}

			if ( ! api.controllers.mapService.geocode ) {
				return;
			}

			if ( '' === this.$address.val() ) {
				return;
			}

			if ( this.$address.val() != this.prevAddress ) {
				this.isAddressGeocoded = false;
			}

			if ( this.isAddressGeocoded || this.isAddressGeocoding ) {
				return;
			}

			api.controllers.mapService.geocode( this.$address.val() );
		},

		/**
		 * Get the default view. Use either the last geocoded search
		 * if available, or the set option coordinates.
		 *
		 * @since 2.0.0
		 */
		getDefaultCoordinates: function() {
			if ( this.$lat.val() && '0' != this.$lat.val() ) {
				return {
					lat: this.$lat.val(),
					lng: this.$lng.val()
				};
			} else {
				return {
					lat: api.settings.mapService.center[0],
					lng: api.settings.mapService.center[1]
				};
			}
		},

		/**
		 * Enable radius searching for this service.
		 *
		 * @since 2.0.0
		 */
		radiusSearch: function( lat, lng ) {
			this.$lat.val( lat );
			this.$lng.val( lng );

			/**
			 * The form technically updates on enter keypress however this can
			 * happen before the input values are updated.
			 *
			 * Run it again to abort the current XHR and use the proper values.
			 */
			this.update();
		},

		/**
		 * Implement a slider to adjust the search radius.
		 *
		 * @since 2.0.0
		 */
		radiusSlider: function() {
			var self = this;

			$( '.search-radius-slider div' ).each(function( k ) {
				var slider = noUiSlider.create( this, {
					format: wNumb({
						decimals: 0,
					}),
					step: 1,
					start: [ api.settings.dataService.wpjobmanager.searchRadiusDefault ],
					range: {
						'min': parseInt( api.settings.dataService.wpjobmanager.searchRadiusMin ),
						'max': parseInt( api.settings.dataService.wpjobmanager.searchRadiusMax )
					}
				});

				slider.on( 'set', self.updateRadius );

				slider.on( 'set', function() {
					self.update();
				});
			});
		},

		/**
		 * Update the label and form value when the slider changes.
		 *
		 * @since 2.0.0
		 */
		updateRadius: function( values, handle ) {
			$( '.radi' ).text( values[0] );
			$( '#search_radius' ).val( values[0] );
		},

		/**
		 * Manage events.
		 *
		 * @since 2.0.0
		 */
		locateMe: function() {
			if ( ! navigator.geolocation ) {
				return;
			}

			var self = this;

			// Add the icon.
			$( '.search_location' ).append( '<i class="locate-me"></i>' );

			// Handle click event.
			$( '.locate-me' ).on( 'click', function( e ) {
				e.preventDefault();

				$(this).addClass( 'loading' );

				navigator.geolocation.getCurrentPosition( self.locateMeSuccess, null, {
					enableHighAccuracy: true
				});
			});
		},

		/**
		 * When the location is found.
		 *
		 * @since 2.0.0
		 */
		locateMeSuccess: function( position ) {
			var lat = position.coords.latitude;
			var lng = position.coords.longitude;

			if ( api.controllers.mapService.geocode ) {
				api.controllers.mapService.geocode( lat + ', ' + lng );
			}

			$( '.locate-me' ).removeClass( 'loading' );
		},

		/**
		 * Search This Location.
		 *
		 * @since 2.2.0
		 */
		searchThisLocation: function() {
			var centerLat = api.controllers.mapService.getCenterLat();
			var centerLng = api.controllers.mapService.getCenterLng();
			var radius = api.controllers.mapService.getRadius();

			// Update input to search current map location.
			this.$lat.val( centerLat );
			this.$lng.val( centerLng );
			this.$radius.val( radius );

			// Update results.
			this.update();
		},

	});

	/**
	 * FacetWP
	 *
	 * @since 2.0.0
	 */
	api.DataServices.FacetWP = api.DataService.extend({
		/**
		 * The area the listing results are appended to.
		 *
		 * @since 2.0.0
		 */
		resultsContainer: '.facetwp_job_listings ul.job_listings',

		/**
		 * @since 2.0.0
		 */
		initialize: function( options ) {
			api.DataService.prototype.initialize.call( options );

			var self = this;

			$( document ).on( 'facetwp-loaded', function( event, data ) {
				// Avoid updating other FacetWP templates.
				if ( _.isUndefined( FWP.settings.listify ) ) {
					return;
				}

				// Record returned data and alert that the data is ready to be used.
				self.response = FWP.settings.listify;

				// First run, just notify of response.
				if ( ! self.activeResponse.state( 'resolved' ) ) {
					if ( window.console && api.settings.scriptDebug ) {
						console.log( 'Data Service ready...' );
					}

					self.activeResponse.resolve();
				} else {
					// Add results to view.
					self.addResults();

					// Add markers to the map.
					if ( ! _.isUndefined( api.controllers.mapService ) ) {
						api.controllers.mapService.addMarkers( self.response.listings );
					}
				}

				$(document).trigger( 'listifyDataServiceLoaded' );
			} );
		},

		/**
		 * Get the default view. Use either the last geocoded search
		 * if available, or the set option coordinates.
		 *
		 * @since 2.0.0
		 */
		getDefaultCoordinates: function() {
			var lat = api.settings.mapService.center[0];
			var lng = api.settings.mapService.center[1];

			if ( $( '.facetwp-type-proximity' ).length > 0 ) {
				var facet = $( '.facetwp-type-proximity' ).data( 'name' );

				if ( FWP.facets[ facet ].length ) {
					lat = FWP.facets[facet][0];
					lng = FWP.facets[facet][1];
				}
			}

			return {
				lat: lat,
				lng: lng
			};
		},


		/**
		 * Search This Location for FacetWP.
		 *
		 * @since 2.3.0
		 */
		searchThisLocation: function() {
			var centerLat = api.controllers.mapService.getCenterLat();
			var centerLng = api.controllers.mapService.getCenterLng();
			var radius = api.controllers.mapService.getRadius();

			$( '#facetwp-radius' ).append( $('<option>', {
				value: radius,
				text : radius,
			} ) );

			$( '#facetwp-radius' ).val( radius );
			$( '.facetwp-lat' ).attr( 'value', centerLat );
			$( '.facetwp-lng' ).attr( 'value', centerLng );

			FWP.refresh();
		},

	});

	// Choose data service.
	if ( 'facetwp' == api.settings.dataService.service ) {
		api.controllers.dataService = new api.DataServices.FacetWP();
	} else {
		api.controllers.dataService = new api.DataServices.WPJobManager();
	}

	// Add listings once loaded.
	$(document).on( 'listifyDataServiceLoaded', function() {
		api.controllers.dataService.addResults();
	});

	// Search this location.
	// @link https://stackoverflow.com/questions/3525670
	$( '#search-this-location' ).click( function(e) {
		e.preventDefault();
		api.controllers.dataService.searchThisLocation();
	} );

})( window );
