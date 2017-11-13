window.wp = window.wp || {};

/**
 * Map returned data.
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
	api.MapServices = [];

	/**
	 * A generic mapping service.
	 *
	 * @since 2.0.0
	 */
	api.MapService = api.Class.extend({

		/**
		 * DOM element wrapping the canvas.
		 *
		 * @since 2.0.0
		 */
		$mapWrapper: $( '.job_listings-map-wrapper' ),

		/**
		 * Map canvas.
		 *
		 * @since 2.0.0
		 */
		canvas: null,

		/**
		 * If this is the first load.
		 *
		 * @since 2.0.0
		 */
		firstLoad: true,

		/**
		 * Map canvas states.
		 *
		 * @since 2.0.0
		 */
		activeCanvas: $.Deferred(),

		/**
		 * Markers
		 *
		 * @since 2.0.0
		 */
		markers: [],

		/**
		 * Manage marker clusters.
		 *
		 * @since 2.0.0
		 */
		clusterer: null,

		/**
		 * Bounds of active markers.
		 *
		 * @since 2.0.0
		 */
		bounds: null,

		/**
		 * Geocoder.
		 *
		 * @since 2.0.0
		 */
		geocoder: null,

		/**
		 * The template for the InfoBubble content.
		 *
		 * @since 2.0.0
		 */
		infoBubbleTemplate: wp.template( 'infoBubbleTemplate' ),

		/**
		 * The template for the RichMarker content.
		 *
		 * @since 2.0.0
		 */
		markerTemplate: wp.template( 'pinTemplate' ),

		/**
		 * @param {object} options
		 */
		initialize: function( options ) {
			$.extend( this, options || {} );

			var self = this;

			// Create a map.
			this.drawMap();

			if ( this.canvas ) {
				$(window).on( 'resize', function() {
					self.reDraw();
				} );
			}
		},

		/**
		 * Create a map canvas.
		 *
		 * @since 2.0.0
		 */
		drawMap: function() {
			if ( ! this.activeCanvas.state( 'resolved' ) ) {
				this.activeCanvas.resolve();

				if ( window.console && api.settings.scriptDebug ) {
					console.log( 'Map Service ready...' );
				}
			}

			// Create clusterer if enabled.
			if ( 1 == api.settings.mapService.useClusters ) {
				this.createClusterer();
			}
		},

		/**
		 * Plot markers.
		 *
		 * @since 2.0.0
		 *
		 * @param {Array} listings
		 */
		addMarkers: function( listings ) {
			if ( window.console && api.settings.scriptDebug ) {
				console.log( 'Adding markers...' );
			}

			var self = this;

			_.each( listings, function( marker, i ) {
				self.addMarker( marker );
			} );
		},

		/**
		 * Add a single marker.
		 *
		 * @since 2.0.0
		 *
		 * @param {object} marker
		 */
		addMarker: function( marker ) {},

		/**
		 * Reset markers.
		 *
		 * @since 2.0.0
		 */
		resetView: function() {},

		/**
		 * Show a default view.
		 *
		 * @since 2.0.0
		 */
		showDefaultView: function() {},

		/**
		 * Fit bounds.
		 *
		 * @since 2.0.0
		 */
		fitBounds: function() {},

		/**
		 * Redraw map.
		 *
		 * @since 2.0.0
		 */
		reDraw: function() {
			this.fitBounds();
		},

		/**
		 * Autocomplete.
		 *
		 * @since 2.0.0
		 */
		autoComplete: null,

		/**
		 * Geocode an address.
		 *
		 * @since 2.0.0
		 *
		 * @param {string} address
		 */
		geocode: null,

		/**
		 * Create a cluster manager.
		 *
		 * @since 2.0.0
		 */
		createClusterer: function() {},

		/**
		 * Maybe show a list of marker details in a cluster overlay.
		 *
		 * @since 2.0.0
		 */
		maybeClusterOverlay: function( cluster ) {},

		/**
		 * Get map canvas.
		 *
		 * @since 2.2.0
		 */
		getCanvas: function() {},

		/**
		 * Get current center latitude.
		 *
		 * @since 2.2.0
		 */
		getCenterLat: function() {},

		/**
		 * Get current center longitude.
		 *
		 * @since 2.2.0
		 */
		getCenterLng: function() {},

		/**
		 * Get north east latitide.
		 *
		 * @since 2.2.0
		 */
		getNeLat: function() {},

		/**
		 * Get north east longitude.
		 *
		 * @since 2.2.0
		 */
		getNeLng: function() {},

		/**
		 * Get north east longitude.
		 *
		 * @since 2.2.0
		 */
		getRadius: function() {
			var mapUnit = api.mapUnit;
			var EarthRadius = 'mi' === mapUnit ? 3959 : 6371;
			var lat1 = this.getCenterLat() / 57.2958; 
			var lng1 = this.getCenterLng() / 57.2958;
			var lat2 = this.getNeLat() / 57.2958;
			var lng2 = this.getNeLng() / 57.2958;
			var radius = EarthRadius * Math.acos( Math.sin(lat1) * Math.sin(lat2) + Math.cos(lat1) * Math.cos(lat2) * Math.cos(lng2 - lng1) );
			if ( radius < 1 ) {
				return 1;
			}
			return radius;
		},

	});

	/**
	 * Map with Google
	 *
	 * @since 2.0.0
	 */
	api.MapServices.GoogleMaps = api.MapService.extend({

		/**
		 * @param {object} options
		 * @since 2.0.0
		 */
		initialize: function( options ) {
			api.MapService.prototype.initialize.call( this, options );

			/**
			 * @since 2.0.0
			 */
			this.bounds = new google.maps.LatLngBounds();

			/**
			 * @since 2.0.0
			 */
			this.geocoder = new google.maps.Geocoder();

			/**
			 * @since 2.0.0
			 */
			InfoBubble.prototype.getAnchorHeight_ = function() {
				return 55;
			};

			this.infoBubble = new InfoBubble({
				backgroundClassName: 'map-marker-info',
				borderRadius: 4,
				padding: 15,
				borderColor: '#ffffff',
				shadowStyle: 0,
				minHeight: 120,
				maxHeight: 800,
				minWidth: 225,
				maxWidth: 275,
				hideCloseButton: true,
				flat: true,
				anchor: RichMarkerPosition.BOTTOM,
				disableAutoPan: '1' != api.settings.mapService.autoPan
			});

			var self = this;

			google.maps.event.addDomListener( window, 'load', function() {
				self.drawMap();
			});
		},

		/**
		 * Create a map canvas.
		 *
		 * @since 2.0.0
		 */
		drawMap: function() {
			// Only if a map canvas exists.
			if ( ! document.getElementById( 'job_listings-map-canvas' ) ) {
				return;
			}

			this.canvas = new google.maps.Map( document.getElementById( 'job_listings-map-canvas' ), {
				center: new google.maps.LatLng( api.settings.mapService.center[0], api.settings.mapService.center[1] ),
				zoom: parseInt( api.settings.mapService.zoom ),
				maxZoom: parseInt( api.settings.mapService.maxZoom ),
				minZoom: parseInt( api.settings.mapService.maxZoomOut ),
				scrollwheel: api.settings.mapService.googlemaps.scrollwheel,
				styles: api.settings.mapService.googlemaps.styles,
				zoomControlOptions: {
					position: google.maps.ControlPosition.RIGHT_BOTTOM
				},
				streetViewControl: true,
				streetViewControlOptions: {
					position: google.maps.ControlPosition.RIGHT_BOTTOM
				}
			});

			api.MapService.prototype.drawMap.call( this );
		},

		/**
		 * Plot markers.
		 *
		 * @since 2.0.0
		 */
		addMarkers: function( listings ) {
			this.resetView();

			if ( ! listings || 0 === listings.length ) {
				return this.showDefaultView();
			} else {
				api.MapService.prototype.addMarkers.call( this, listings );

				if ( 
					( this.firstLoad && api.settings.mapService.autofit ) ||
					( ! this.firstLoad )
				) {
					this.fitBounds();
				}

				// Display markers.
				if ( this.clusterer ) {
					this.clusterer.addMarkers( this.markers );
				}

				this.firstLoad = false;
			}
		},

		/**
		 * Add a single marker.
		 *
		 * @since 2.0.0
		 */
		addMarker: function( data ) {
			var self = this;

			if ( null === data.location.lat ) {
				return;
			}

			var marker = new RichMarker({
				content: self.markerTemplate( data ),
				flat: true,
				draggable: false,
				position: new google.maps.LatLng( data.location.lat, data.location.lng ),
				data: data
			});

			if ( ! this.clusterer ) {
				marker.setMap( this.canvas );
			}

			// Extend active bounds.
			this.bounds.extend( marker.getPosition() );

			// Add to list.
			this.markers.push( marker );

			// Open an InfoBubble instance.
			google.maps.event.addListener( marker, api.settings.mapService.googlemaps.infoBubbleTrigger, function() {
				// Already open on this marker. Bail.
				if ( self.infoBubble.get( 'isOpen' ) && self.infoBubble.get( 'anchor' ) == marker ) {
					return;
				}

				self.infoBubble.setContent( self.infoBubbleTemplate( data ) );
				self.infoBubble.open( self.canvas, this );
			} );

			// Close info bubble on map click.
			google.maps.event.addListener( this.canvas, 'click', function() {
				self.infoBubble.close();
			});
		},

		/**
		 * Reset markers.
		 *
		 * @todo Don't remove these markers but instead keep active
		 * and hidden to avoid having to redraw them all.
		 *
		 * @since 2.0.0
		 */
		resetView: function() {
			_.each( this.markers, function( marker ) {
				marker.setMap( null );
			} );

			this.bounds = new google.maps.LatLngBounds();
			this.markers = [];

			if ( this.clusterer ) {
				this.clusterer.clearMarkers();
			}

			this.infoBubble.close();
		},

		/**
		 * Show a default view.
		 *
		 * This is usually called when a search has no results.
		 *
		 * @since 2.0.0
		 */
		showDefaultView: function() {
			var coords = api.controllers.dataService.getDefaultCoordinates();

			this.canvas.setZoom( parseInt( api.settings.mapService.zoom ) );
			this.canvas.setCenter( new google.maps.LatLng( coords.lat, coords.lng ) );
		},

		/**
		 * Fitbounds
		 *
		 * @since 2.0.0
		 */
		fitBounds: function() {
			api.MapService.prototype.fitBounds.call( this );

			this.canvas.fitBounds( this.bounds );
		},

		/**
		 * Redraw
		 *
		 * @since 2.0.0
		 */
		reDraw: function() {
			google.maps.event.trigger( this.canvas, 'resize' );
			api.MapService.prototype.reDraw.call( this );
		},

		/**
		 * Autocomplete.
		 *
		 * @since 2.0.0
		 */
		autoComplete: function( field ) {
			var self = this;

			var $field = $( '#' + field );

			if ( 0 === $field.length ) {
				return;
			}

			var autocomplete = new google.maps.places.Autocomplete( document.getElementById( field ), api.settings.mapService.googlemaps.autoCompleteArgs );

			if ( this.canvas ) {
				autocomplete.bindTo( 'bounds', this.canvas );
			}

			// Remove normal change event that submits form.
			$field.unbind( 'change' );

			// Submit an autocomplete event on enter.
			$field.keypress(function( e ) {
				if ( e.which == 13 ) {
					api.controllers.dataService.update();
				}
			});

			autocomplete.addListener( 'place_changed', function() {
				var place = autocomplete.getPlace();

				// A location with coordinates was found.
				if ( place.geometry ) {
					api.controllers.dataService.isAddressGeocoded = true;
					api.controllers.dataService.radiusSearch( place.geometry.location.lat(), place.geometry.location.lng() );

					// It still needs to be geocoded again.
				} else {
					api.controllers.dataService.isAddressGeocoded = false;
					self.geocode( place.name );
				}
			} );
		},

		/**
		 * Geocode an address.
		 *
		 * @since 2.0.0
		 */
		geocode: function( address ) {
			var self = this;

			this.isAddressGeocoding = true;

			this.geocoder.geocode({
				address: address
			}, function( results, status ) {
				if ( status == google.maps.GeocoderStatus.OK ) {
					var loc = results[0].geometry.location;

					api.controllers.dataService.prevAddress = address;
					api.controllers.dataService.isAddressGeocoded = true;
					api.controllers.dataService.$address.val( results[0].formatted_address );

					api.controllers.dataService.radiusSearch( loc.lat(), loc.lng() );
				} else {
					api.controllers.dataService.isAddressGeocoded = false;
				}

				this.isAddressGeocoding = false;
			} );
		},

		/**
		 * Create a cluster manager.
		 *
		 * @since 2.0.0
		 */
		createClusterer: function() {
			this.clusterer = new MarkerClusterer( this.canvas, this.makers, {
				gridSize: api.settings.mapService.gridSize, 
				maxZoom: api.settings.mapService.maxZoom,
				ignoreHiddenMarkers: true,
				cssClass: 'cluster',
				imagePath: '',
				width: 53,
				height: 53
			} );

			var self = this;

			google.maps.event.addListener( this.clusterer, 'clusterclick', function( cluster ) {
				self.maybeClusterOverlay( cluster );
			} );
		},

		/**
		 * Maybe show a list of marker details in a cluster overlay.
		 *
		 * @since 2.0.0
		 */
		maybeClusterOverlay: function( cluster ) {
			var self = this;
			var markers = cluster.getMarkers();
			var zoom = this.canvas.getZoom();

			if ( zoom < ( api.settings.mapService.maxZoom ) ) {
				return;
			}

			var content = _.map( markers, function( marker ) {
				return self.infoBubbleTemplate( marker.data );
			} );

			// @todo don't assume this library is loaded
			$.magnificPopup.open({
				type: 'inline',
				items: {
					src: '<div class="cluster-overlay popup"><ul><li>' + content.join( '</li><li>' ) + '</li></ul></div>',
				},
				callbacks: {
					close: function() {
						// This is janky but only way I can get clusters to show up again.
						// @todo investigate better fix.
						self.canvas.setZoom( zoom - 1 );
						self.canvas.setZoom( zoom );
					}
				}
			});
		},

		/**
		 * Get map canvas.
		 *
		 * @since 2.2.0
		 */
		getCanvas: function() {
			return this.canvas;
		},

		/**
		 * Get current center latitude.
		 *
		 * @since 2.2.0
		 */
		getCenterLat: function() {
			return this.getCanvas().getBounds().getCenter().lat();
		},

		/**
		 * Get current center longitude.
		 *
		 * @since 2.2.0
		 */
		getCenterLng: function() {
			return this.getCanvas().getBounds().getCenter().lng();
		},

		/**
		 * Get north east latitide.
		 *
		 * @since 2.2.0
		 */
		getNeLat: function() {
			return this.getCanvas().getBounds().getNorthEast().lat();
		},

		/**
		 * Get north east longitude.
		 *
		 * @since 2.2.0
		 */
		getNeLng: function() {
			return this.getCanvas().getBounds().getNorthEast().lng();
		},

	});

	/**
	 * Map with Mapbox
	 *
	 * @since 2.0.0
	 */
	api.MapServices.Mapbox = api.MapService.extend({

		/**
		 * @param {object} options
		 * @since 2.0.0
		 */
		initialize: function( options ) {
			api.MapService.prototype.initialize.call( this, options );

			/**
			 * @since 2.0.0
			 */
			this.bounds = new L.latLngBounds();

			/**
			 * @since 2.0.0
			 */
			this.geocoder = new window.GeoSearch.OpenStreetMapProvider();
		},

		/**
		 * Create a map canvas.
		 *
		 * @since 2.0.0
		 */
		drawMap: function() {
			// Only if a map canvas exists.
			if ( ! document.getElementById( 'job_listings-map-canvas' ) ) {
				return;
			}

			this.canvas = L.map( 'job_listings-map-canvas' ).setView( [ 
				api.settings.mapService.center[0], 
				api.settings.mapService.center[1] 
			], api.settings.mapService.zoom );

			// Add tile and attribution.
			L.tileLayer( api.settings.mapService.mapbox.tileUrl, {
				attribution: 'Map data &copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery &copy; <a href="https://mapbox.com">Mapbox</a>',
				maxZoom: api.settings.mapService.maxZoom,
			}).addTo( this.canvas );

			// Disable scrollwheel zoom if needed.
			if ( ! api.settings.mapService.googlemaps.scrollwheel ) {
				this.canvas.scrollWheelZoom.disable();
			}

			api.MapService.prototype.drawMap.apply( this );
		},

		/**
		 * Plot markers.
		 *
		 * @since 2.0.0
		 */
		addMarkers: function( listings ) {
			this.resetView();

			if ( ! listings || 0 === listings.length ) {
				return this.showDefaultView();
			} else {
				api.MapService.prototype.addMarkers.call( this, listings );

				this.canvas.addLayer( this.markers );

				if ( 
					( this.firstLoad && api.settings.mapService.autofit ) ||
					( ! this.firstLoad )
				) {
					this.fitBounds();
				}
			}

			this.firstLoad = false;
		},

		/**
		 * Add a single marker.
		 *
		 * @since 2.0.0
		 */
		addMarker: function( data ) {
			if ( null === data.location.lat ) {
				return;
			}

			var icon = L.divIcon({
				iconSize: [30, 30],
				iconAnchor: [15, 30],
				className: '',
				html: this.markerTemplate( data ),
			});

			var marker = L.marker( [ data.location.lat, data.location.lng ], {
				icon: icon,
				data: data
			} );

			marker.bindPopup( this.infoBubbleTemplate( data ), {
				closeButton: false,
				autoPan: '1' == api.settings.mapService.autoPan,
				offset: [ 0, -60 ],
				maxHeight: 800,
				minWidth: 225,
				maxWidth: 275,
				className: 'map-marker-info'
			} );

			if ( this.clusterer ) {
				this.markers.addLayer( marker );
			} else {
				this.markers.push( marker );
				marker.addTo( this.canvas );
			}

			// Extend active bounds.
			this.bounds.extend( marker.getLatLng() );
		},

		/**
		 * Reset markers.
		 *
		 * @todo Don't remove these markers but instead keep active
		 * and hidden to avoid having to redraw them all.
		 *
		 * @since 2.0.0
		 */
		resetView: function() {
			this.bounds = new L.latLngBounds();

			if ( this.clusterer ) {
				this.markers.clearLayers();
			} else {
				_.each( this.markers, function( marker ) {
					marker.remove();
				} );

				this.markers = [];
			}
		},

		/**
		 * Show a default view.
		 *
		 * This is usually called when a search has no results.
		 *
		 * @since 2.0.0
		 */
		showDefaultView: function() {
			var coords = api.controllers.dataService.getDefaultCoordinates();

			this.canvas.setZoom( api.settings.mapService.zoom );
			this.canvas.setView( L.latLng( coords.lat, coords.lng ) );
		},

		/**
		 * Fitbounds
		 *
		 * @since 2.0.0
		 */
		fitBounds: function() {
			if ( this.bounds.isValid() ) {
				api.MapService.prototype.fitBounds.call( this );

				this.canvas.fitBounds( this.bounds );
			}
		},

		/**
		 * Redraw
		 *
		 * @since 2.0.0
		 */
		reDraw: function() {
			this.canvas.invalidateSize();
			api.MapService.prototype.reDraw.call( this );
		},

		/**
		 * Geocode an address.
		 *
		 * @since 2.0.0
		 */
		geocode: function( address ) {
			var self = this;

			this.isAddressGeocoding = true;

			this.geocoder
				.search({
					query: address
				})
				.then( function( result ) {
					if ( result.length >= 1 ) {
						var loc = result[0];

						api.controllers.dataService.prevAddress = loc.label;
						api.controllers.dataService.$address.val( loc.label );
						api.controllers.dataService.isAddressGeocoded = true;

						api.controllers.dataService.radiusSearch( loc.y, loc.x );
					} else {
						api.controllers.dataService.isAddressGeocoded = false;
					}

					this.isAddressGeocoding = false;
				} );
		},

		/**
		 * Create a cluster manager.
		 *
		 * @since 2.0.0
		 */
		createClusterer: function() {
			this.markers = L.markerClusterGroup({
				maxClusterRadius: api.settings.mapService.gridSize,
				showCoverageOnHover: false,
				iconCreateFunction: function( cluster ) {
					return L.divIcon({
						html: '<div class="cluster">' + cluster.getChildCount() + '</div>',
						className: ''
					});
				}
			});

			var self = this;

			this.markers.on( 'clusterclick', function( a ) {
				self.maybeClusterOverlay( a.layer );
			} );

			this.clusterer = true;
		},

		/**
		 * Maybe show a list of marker details in a cluster overlay.
		 *
		 * @since 2.0.0
		 */
		maybeClusterOverlay: function( cluster ) {
			var self = this;
			var markers = cluster.getAllChildMarkers();
	
			cluster.zoomToBounds();

			var zoom = this.canvas.getZoom();

			if ( zoom != api.settings.mapService.maxZoom ) {
				return;
			}

			var content = _.map( markers, function( marker ) {
				return self.infoBubbleTemplate( marker.options.data );
			} );

			// @todo don't assume this library is loaded
			$.magnificPopup.open({
				type: 'inline',
				items: {
					src: '<div class="cluster-overlay popup"><ul><li>' + content.join( '</li><li>' ) + '</li></ul></div>',
				},
				callbacks: {
					close: function() {
						cluster.unspiderfy();
					}
				}
			});
		},

		/**
		 * Autocomplete is not supported for Mapbox.
		 *
		 * @since 2.0.0
		 *
		 * @param {string} Input field ID
		 */
		autoComplete: null,

		/**
		 * Get map canvas.
		 *
		 * @since 2.2.0
		 */
		getCanvas: function() {
			return this.canvas;
		},

		/**
		 * Get current center latitude.
		 *
		 * @since 2.2.0
		 */
		getCenterLat: function() {
			return this.getCanvas().getBounds().getCenter().lat;
		},

		/**
		 * Get current center longitude.
		 *
		 * @since 2.2.0
		 */
		getCenterLng: function() {
			return this.getCanvas().getBounds().getCenter().lng;
		},

		/**
		 * Get north east latitide.
		 *
		 * @since 2.2.0
		 */
		getNeLat: function() {
			return this.getCanvas().getBounds().getNorthEast().lat;
		},

		/**
		 * Get north east longitude.
		 *
		 * @since 2.2.0
		 */
		getNeLng: function() {
			return this.getCanvas().getBounds().getNorthEast().lng;
		},

	});


	// Choose a map service.
	if ( 'googlemaps' == api.settings.mapService.service ) {
		api.controllers.mapService = new api.MapServices.GoogleMaps();
	} else {
		api.controllers.mapService = new api.MapServices.Mapbox();
	}

	// Add auto complete if available.
	if ( api.controllers.mapService.autoComplete && api.settings.mapService.googlemaps.autoComplete ) {
		api.controllers.mapService.autoComplete( api.controllers.dataService.autoComplete );
	}

	if ( '' !== api.settings.displayMap ) {

		/**
		* When using a map alongside results only plot the initial markers
		* and results once both services are ready.
		*
		* @since 2.0.0
		*/
		$.when( api.controllers.mapService.activeCanvas, api.controllers.dataService.activeResponse ).done(function() {
			$(document).trigger( 'listifyActiveResultsMap' );

			if ( window.console && api.settings.scriptDebug ) {
				console.log( 'Data Service and Map Service are ready...' );
			}

			// Initiate Map
			api.controllers.mapService.addMarkers( api.controllers.dataService.response.listings );
		});

	}

	/**
	 * Some simple UI stuff bound to DOM.
	 *
	 * @since 2.0.0
	 */
	$(document).ready(function() {

		// Map resizing.
		var $body = $( 'body' );
		var $window = $(window);
		var $map = $( '.job_listings-map-wrapper' );
		var $siteHeader = $( '.site-header' );
		var $adminBar = $( '#wpadminbar' );

		var resizeMap = function() {
			if ( $body.hasClass( 'fixed-map' ) ) {
				$map.css( 'height', $window.outerHeight() - $siteHeader.outerHeight() - $adminBar.outerHeight() );
			}
		};

		resizeMap();

		$window.on( 'resize', resizeMap );

	});

})( window );
