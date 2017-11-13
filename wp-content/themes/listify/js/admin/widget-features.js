/**
 * Widget Features (Admin).
 *
 * @since 2.2.0
 */
(function( window, undefined ){

	window.wp = window.wp || {};
	var document = window.document;
	var $ = window.jQuery;

	// Template.
	var featureTemplate = wp.template( 'feature' );

	/**
	 * Bind items to to the DOM.
	 *
	 * @since 2.2.0
	 */
	$(function() {

		// Add new feature.
		$( document ).on( 'click', '.button-add-feature', function(e) {
			e.preventDefault();

			// El.
			var thisform = $( this ).parents( '.widget-inside' );
			var features_div = $( this ).parents( '.features-wrap' ).find( '.features' );

			// Current widget data.
			var widget_num = thisform.find( 'input[name="multi_number"]' ).val();
			if ( ! widget_num ) {
				widget_num = thisform.find( 'input[name="widget_number"]' ).val();
			}

			// Add features.
			features_div.append( featureTemplate( {
				widget_num  : widget_num,
				order       : features_div.find( '.feature' ).length + 1,
				title       : '',
				media       : '',
				description : '',
			} ) ).sortable();
		} );

		// Remove feature.
		$( document ).on( 'click', '.button-remove-feature', function(e) {
			e.preventDefault();
			$( this ).parents( '.feature' ).remove();
		} );

	});

})( window );
