window.wp = window.wp || {};

/**
 * Use returned data (from WP Job Manager or FacetWP) and plot 
 * information on a map (Google Maps) and display a grid of results.
 *
 * @since 2.0.0
 */
(function( window, undefined ){

	window.wp = window.wp || {};
	var document = window.document;
	var $ = window.jQuery;

	var ctor, inherits;

	var api = wp.listifyResults = {};

	// Shared empty constructor function to aid in prototype-chain creation.
	ctor = function() {};

	/**
	 * Helper function to correctly set up the prototype chain, for subclasses.
	 * Similar to `goog.inherits`, but uses a hash of prototype properties and
	 * class properties to be extended.
	 *
	 * @param  object parent      Parent class constructor to inherit from.
	 * @param  object protoProps  Properties to apply to the prototype for use as class instance properties.
	 * @param  object staticProps Properties to apply directly to the class constructor.
	 * @return child              The subclassed constructor.
	 */
	inherits = function( parent, protoProps, staticProps ) {
		var child;

		// The constructor function for the new subclass is either defined by you
		// (the "constructor" property in your `extend` definition), or defaulted
		// by us to simply call `super()`.
		if ( protoProps && protoProps.hasOwnProperty( 'constructor' ) ) {
			child = protoProps.constructor;
		} else {
			child = function() {
				// Storing the result `super()` before returning the value
				// prevents a bug in Opera where, if the constructor returns
				// a function, Opera will reject the return value in favor of
				// the original object. This causes all sorts of trouble.
				var result = parent.apply( this, arguments );
				return result;
			};
		}

		// Inherit class (static) properties from parent.
		$.extend( child, parent );

		// Set the prototype chain to inherit from `parent`, without calling
		// `parent`'s constructor function.
		ctor.prototype  = parent.prototype;
		child.prototype = new ctor();

		// Add prototype properties (instance properties) to the subclass,
		// if supplied.
		if ( protoProps ) {
			$.extend( child.prototype, protoProps );
		}

		// Add static properties to the constructor function, if supplied.
		if ( staticProps ) {
			$.extend( child, staticProps );
		}

		// Correctly set child's `prototype.constructor`.
		child.prototype.constructor = child;

		// Set a convenience property in case the parent's prototype is needed later.
		child.__super__ = parent.prototype;

		return child;
	};

	/**
	 * Base class for object inheritance.
	 */
	api.Class = function( applicator, argsArray, options ) {
		var magic, args = arguments;

		if ( applicator && argsArray && api.Class.applicator === applicator ) {
			args = argsArray;
			$.extend( this, options || {} );
		}

		magic = this;

		/*
		 * If the class has a method called "instance",
		 * the return value from the class' constructor will be a function that
		 * calls the "instance" method.
		 *
		 * It is also an object that has properties and methods inside it.
		 */
		if ( this.instance ) {
			magic = function() {
				return magic.instance.apply( magic, arguments );
			};

			$.extend( magic, this );
		}

		magic.initialize.apply( magic, args );

		return magic;
	};

	/**
	 * Creates a subclass of the class.
	 *
	 * @param  object protoProps  Properties to apply to the prototype.
	 * @param  object staticProps Properties to apply directly to the class.
	 * @return child              The subclass.
	 */
	api.Class.extend = function( protoProps, classProps ) {
		var child = inherits( this, protoProps, classProps );
		child.extend = this.extend;

		return child;
	};

	api.Class.applicator = {};

	/**
	 * Initialize a class instance.
	 *
	 * Override this function in a subclass as needed.
	 */
	api.Class.prototype.initialize = function() {};

	/*
	 * Checks whether a given instance extended a constructor.
	 *
	 * The magic surrounding the instance parameter causes the instanceof
	 * keyword to return inaccurate results; it defaults to the function's
	 * prototype instead of the constructor chain. Hence this function.
	 */
	api.Class.prototype.extended = function( constructor ) {
		var proto = this;

		while ( typeof proto.constructor !== 'undefined' ) {
			if ( proto.constructor === constructor ) {
				return true;
			}

			if ( typeof proto.constructor.__super__ === 'undefined' ) {
				return false;
			}

			proto = proto.constructor.__super__;
		}

		return false;
	};

	/**
	 * Global settings.
	 *
	 * @since 2.0.0
	 */
	api.settings = listifyResults;

	/**
	 * The chosen controllers.
	 *
	 * @since 2.0.0
	 */
	api.controllers = {};

	/**
	 * Toggle between Map and Results view.
	 *
	 * @since 2.1.0
	 */
	var toggleCurrentView = function() {
		var defaultView = api.settings.defaultMobileView;

		$( '.archive-job_listing-toggle' ).on( 'click', function(e) {
			e.preventDefault();

			$( '.archive-job_listing-toggle' ).removeClass( 'active' );
			$(this).toggleClass( 'active' );

			var toggle = $(this).data( 'toggle' );

			if ( 'results' == toggle ) {
				$( '#primary' ).show();
				$( '.job_listings-map-wrapper' ).hide();
			} else {
				$( '#primary' ).hide();
				$( '.job_listings-map-wrapper' ).show();
			}

			if ( api.controllers.mapService && api.controllers.mapService.canvas ) {
				api.controllers.mapService.reDraw();
			}
		});

		if ( $(window).outerWidth() < 992 && $( 'body' ).hasClass( 'fixed-map' ) ) {
			$( '.archive-job_listing-toggle[data-toggle="' + defaultView + '"]' ).trigger( 'click' );
		}
	}

	/**
	 * Wait for dom to be ready.
	 *
	 * @since 2.1.0
	 */
	$(function() {

		// Allow view toggling.
		toggleCurrentView();

		/**
		 * Trigger an active state on the related map marker when
		 * a result is hovered.
		 *
		 * @since 2.0.0
		 */
		$(document).on( 'listifyDataServiceLoaded', function() {
			$( 'li.type-job_listing .content-box' )
				.mouseenter( function() {
					$( '#' + $(this).parent( 'li' ).attr( 'id' ) + '-map-marker' ).addClass( 'map-marker--active' );
				})
				.mouseout( function() {
					$( '#' + $(this).parent( 'li' ).attr( 'id' ) + '-map-marker' ).removeClass( 'map-marker--active' );
				});
		});

	});

})( window );
