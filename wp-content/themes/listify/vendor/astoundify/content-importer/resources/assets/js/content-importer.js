/**
 * Functions for ajaxified content importing inside the WordPress admin.
 *
 * @version 1.0.0
 *
 * @package Astoundify_ContentImporter
 */

/**
 * @param {jQuery} $ jQuery object.
 * @param {object} wp WP object.
 */
(function( $, wp ) {
	var $window = $( window );

	/**
	 * The Astoundify_ContentImporter object.
	 *
	 * @since 1.0.0
	 * @type {object}
	 */
	var Astoundify_ContentImporter = Astoundify_ContentImporter || {};

	/**
	 * Whether an import is currently processing.
	 *
	 * @since 1.0.0
	 * @type {bool}
	 */
	Astoundify_ContentImporter.importRunning = false;

	/**
	 * The form that triggers an import process.
	 *
	 * @todo Make this dynamic
	 *
	 * @since 1.0.0
	 * @type {bool}
	 */
	Astoundify_ContentImporter.$div = $( '#astoundify-ci' );

	/**
	 * Warn users they will cancel the import if they leave the page.
	 *
	 * @todo This string should be translatable.
	 */
	Astoundify_ContentImporter.beforeUnload = function() {
		if ( Astoundify_ContentImporter.importRunning ) {
			return 'Please do not leave while an import is in progress.';
		}
	};

	/**
	 * Stage an import process.
	 *
	 * @todo Make DOM traverssing more dynamic.
	 *
	 * - Hides any import groups that do not have any items
	 * - Adds an active spinner to groups that need processing
	 * - Resets the initial processed count to 0
	 * - Resets the total count to the new value
	 *
	 * @since 1.0.0
	 *
	 * @param {Array} groups
	 */
	Astoundify_ContentImporter.stageImport = function( groups ) {
		_.each( groups, function(items, type) {
			var total = items.length;

			if ( 0 === total ) {
				$( '#import-type-' + type ).hide();
			} else {
				Astoundify_ContentImporter.typeElement( type, 'spinner' ).addClass( 'is-active' );
				Astoundify_ContentImporter.typeElement( type, 'processed' ).text(0);
				Astoundify_ContentImporter.typeElement( type, 'total' ).text(total);
			}
		});
	};

	/**
	 * Iterate import process.
	 *
	 * @todo Make DOM traverssing more dynamic.
	 *
	 * @since 1.0.0
	 *
	 * @param {Array} items
	 * @param {string} iterate_action Only one of two "import" or "reset"
	 */
	Astoundify_ContentImporter.runIterateImport = function( groups, iterate_action ) {

		// Reset error + status.
		$( '#import-status' ).html( '' );
		$( '#import-errors' ).html( '' );

		// Notify that an import is running
		Astoundify_ContentImporter.importRunning = true;

		var dfd = $.Deferred().resolve();
		var filteredItems = [];

		// Split out object types to single iteration items.
		_.each( groups, function( group, type ) {
			if ( 'object' !== type ) {
				filteredItems.push( group );
			} else {
				_.each( group, function( item ) {
					filteredItems.push([item]);
				} );
			}
		} );

		// Remove any empty items.
		filteredItems = _.reject( filteredItems, function( items ) {
			return items.length === 0;
		} );


		// Iterate sorted items.
		_.each( filteredItems, function( items ) {
			dfd = dfd.then( function() {
				return Astoundify_ContentImporter.runImport( items, iterate_action );
			} );
		});

	};

	/**
	 * Import process.
	 *
	 * @since 2.0.0
	 */
	Astoundify_ContentImporter.runImport = function( items, iterate_action ) {
		return $.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: {
				action: 'astoundify_ci_iterate_item',
				iterate_action: iterate_action,
				items: items,
			},
			dataType: 'json',
			success: function( responses ) {
				if ( false === responses.success ) {
					return;
				}

				// Loop each item responses.
				_.each( responses, function( response, i ) {

					var type = response.item.type;
					var total = parseInt( Astoundify_ContentImporter.typeElement( type, 'total' ).text() );
					var processed = parseInt( Astoundify_ContentImporter.typeElement( type, 'processed' ).text() );

					// Add processed:
					Astoundify_ContentImporter.typeElement( type, 'processed' ).text( processed + 1 );
					processed = parseInt( Astoundify_ContentImporter.typeElement( type, 'processed' ).text() ); // Reset processed.

					// Log error.
					if ( response.success === false ) {
						$( '#import-errors' ).show().prepend( '<li>' + response.data + '</li>' );
					}

					// Remove spinner when done.
					if ( processed === total ) {
						Astoundify_ContentImporter.typeElement( type, 'spinner' ).removeClass( 'is-active' );

						// Done?
						var all_done = true;

						$( '.import-type' ).each( function( i, val ) {
							var processed = parseInt( $( this ).find( '.processed-count' ).text() );
							var total = parseInt( $( this ).find( '.total-count' ).text() );

							if ( processed !== total ) {
								all_done = false;
							}
						});

						if ( true === all_done ) {
							Astoundify_ContentImporter.importRunning = false;
						}

					}

				});

			},
			error: function( XMLHttpRequest, textStatus, errorThrown ) { 
				if ( window.console ) {
					console.log( textStatus + ': ' + errorThrown );
				}
			},
		} );
	};

	/**
	 * Get an import group type element.
	 *
	 * @since 1.0.0
	 * 
	 * @param {string} type
	 * @param {string} element
	 */
	Astoundify_ContentImporter.typeElement = function( type, element ) {
		return $( '#' + type + '-' + element );
	};

	/**
	 * Alert users before leaving the page
	 *
	 * @since 1.0.0
	 */
	$window.bind( 'beforeunload', Astoundify_ContentImporter.beforeUnload );

	/**
	 * Bind actions to DOM
	 *
	 * @since 1.0.0
	 */
	jQuery(document).ready(function($) {

		/**
		 * When a processing action buttin is clicked perform an action.
		 *
		 * @since 1.0.0
		 */
		Astoundify_ContentImporter.$div.find( 'a.button-import' ).on( 'click', function(e) {
			e.preventDefault();

			var $button = $(this);

			return $.ajax({
				type: 'POST',
				url: ajaxurl, 
				data: {
					action: 'astoundify_ci',
					security: astoundifyContentImporter.nonces.stage,
					page: astoundifyContentImporter.page,
					files: astoundifyContentImporterFiles,
					pack: $( 'input[name=astoundify_ci_pack]' ).val(),
				},
				dataType: 'json',
				success: function(response) {
					if ( response.success ) {
						$( '#plugins-to-import' ).hide();
						$( '#import-summary' ).slideDown( "slow" );

						groups = response.data.groups;
						items = response.data.items;

						// these should be callbacks
						Astoundify_ContentImporter.stageImport( groups );
						Astoundify_ContentImporter.runIterateImport( groups, $button.data( 'action' ) );
					} else {
						$( '#plugins-to-import' ).hide();
						$( '#import-errors' ).show().html( '<li>' + response.data + '</li>' );
					}
				}
			});
		});
	});

})( jQuery, window.wp );
