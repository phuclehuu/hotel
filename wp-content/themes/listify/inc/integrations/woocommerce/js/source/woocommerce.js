(function($) {
	'use strict';

	var listifyWooCommerce = {
		cache: {
			$document: $(document),
			$window: $(window)
		},

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			var self = this;

			$(function() {
				self.initRatings();
				self.initPackageSelection();
			});
		},

		initRatings: function() {
			$( '.comment-form-rating' ).on( 'hover click', '.stars span a', function() {
				$(this)
					.siblings()
						.removeClass( 'hover' )
						.end()
					.prevAll()
						.addClass( 'hover' );
			});
		},

		initPackageSelection: function() {
			var selectedPackage = $( '#listify_selected_package' );
			
			if ( selectedPackage.length == 0 ) {
				return;
			}

			// don't redirect if the page is showing an error
			if ( $( '.job-manager-error' ).length ) {
				return;
			}
			
			var value = selectedPackage.val();

			$( '.job_listing_packages' ).find( '#package-' + value ).attr( 'checked', 'checked' );
			$( '#job_package_selection' ).submit();
		}
	};

	listifyWooCommerce.init();

})(jQuery);
