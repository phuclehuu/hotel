/**
 * Review Gallery PopUp (Magnific PopUp)
 *
 * @since 2.2.0
 */
(function( window, undefined ){

	window.wp = window.wp || {};
	var document = window.document;
	var $ = window.jQuery;

	/**
	 * Bind items to to the DOM.
	 *
	 * @since 2.2.0
	 */
	$(function() {

		// Each gallery as it's own popup group.
		$( '.listify-gallery-review' ).each( function() {
			$( this ).magnificPopup({
				tClose: listifySettings.l10n.magnific.tClose,
				tLoading: listifySettings.l10n.magnific.tLoading,
				mainClass: 'listify-wpjm-reviews-gallery-popup',
				delegate: 'a',
				type: 'image',
				gallery: {
					enabled: true,
				}
			});
		});

	});

})( window );
