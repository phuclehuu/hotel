/**
 * Private Messages
 *
 * @since 2.3.0
 */
(function( window, undefined ){

	window.wp = window.wp || {};
	var $ = window.jQuery;
	var $document = $(document);

	/**
	 * Bind items to to the DOM.
	 *
	 * @since 2.3.0
	 */
	$(function() {

		/**
		 * Using wp_editor() inside a popup causes all sorts of issues (uneditable content, unusable buttons, etc).
		 * Remove the TinyMCE instance from this textarea then create it again to avoid glitches.
		 */
		$document.on( 'listifyInlinePopupOpen', function(e) {
			if ( $( '#pm_message' ).length ) {
				wp.editor.remove( 'pm_message' );
				wp.editor.initialize( 'pm_message', {
					tinymce: {
						plugins: 'link, lists, paste',
						toolbar1: 'bold,italic,bullist,numlist,link,unlink,undo,redo',
					}
				} );

				// Remove tabindex to avoid interfering with Link popup.
				$( '.mfp-wrap' ).attr( 'tabindex', false );
			}
		});

	});

})( window );
