(function($) {
	'use strict';

	var listifyWPJobManager = {
		cache: {
			$body: $( 'body' ),
			$document: $( document ),
			$window: $( window )
		},

		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			var self = this;

			$(function() {
				self.cache.$target = $( '.job_listings' );
				self.cache.$body = $( 'body' );
				self.cache.$html = $( 'html' );

				self.initHeader();
				self.initFilters();
				self.initSearch();
				self.submitButton();
				self.initTimePickers();
				self.initTabbedListings();
				self.initBusinessHours();
				self.initApply();
				self.previewListing();
				self.filterTags();

				// Turn category dropdown in to Chosen if available.
				if ( $.isFunction( $.fn.chosen ) ) {
					$( '.job-manager-category-dropdown' ).chosen({
						search_contains: true
					});
				}
			});

			this.cache.$window.on( 'resize', function() {
				self.initHeader();
			});
		},

		initHeader: function() {
			var $body = this.cache.$body;
			var $window = this.cache.$window;

			var isFixedHeader = $body.hasClass( 'fixed-header' );
			var isTransparentHeader = $body.hasClass( 'site-header--transparent' );

			var $siteHeader = $( '.site-header' );
			var siteHeaderHeight = $( '.site-header' ).outerHeight();

			var isMobile = $window.outerWidth() <= 992;

			if ( isMobile ) {
				$body.css( 'padding-top', 0 );
				return;
			}

			if ( isFixedHeader && ! isTransparentHeader ) {
				$body.css( 'padding-top', siteHeaderHeight );
			}
		},

		initSearch: function() {
			$( '.search-overlay-toggle[data-toggle]' ).click(function(e) {
				e.preventDefault();

				$( $(this).data( 'toggle' ) )
					.toggleClass( 'active' );
			});

			$( '.listify_widget_search_listings form.job_filters' )
				.removeClass( 'job_filters' )
				.addClass( 'job_search_form' )
				.prop( 'action', listifySettings.archiveurl );

			$( 'button.update_results' ).on( 'click', function() {
				$(this).parent( 'form' ).submit();
			});

			$( 'form.job_search_form input' ).keypress(function(event) {
				if ( event.which == 13 ) {
					event.preventDefault();
					$( 'form.job_search_form' ).submit();
				}
			});

			$( 'form.job_search_form' ).on( 'submit', function(e) {
				var info = $(this).serialize();

				window.location.href = listifySettings.archiveurl + '?' + info;
			});
		},

		initFilters: function() {
			var filters = [ $( 'ul.job_types' ), $( '.filter_by_tag' ) ];

			$.each(filters, function(i, el) {
				if ( el.outerHeight() > 140 ) {
					el.addClass( 'too-tall' );
				}
			});

			if ( $( '.home' ).find( '.job_types' ) && ! $( '.home .job_types' ).is( ':visible' ) ) {
				$( '.home input[name="filter_job_type[]"]' ).remove();
			}
		},

		submitButton: function() {
			$( '.update_results' ).on( 'click', function(e) {
				e.preventDefault();

				$( 'div.job_listings' ).trigger( 'update_results', [1, false] );
			});
		},

		initTimePickers: function() {
			$( '.timepicker' ).timepicker({
				timeFormat: listifySettings.l10n.timeFormat,
				noneOption: {
					label: listifySettings.l10n.closed,
					value: listifySettings.l10n.closed
				}
			});
		},

		initTabbedListings: function() {
			var $tabWrapper = $( '.tabbed-listings-tabs-wrapper' );
			var $buttonsWrapper = $( '.tabbed-listings-tabs' );

			$tabWrapper.find( '> div' ).hide().filter( ':first-child' ).show();
			$buttonsWrapper.find( 'li:first-child a' ).addClass( 'active' );

			$buttonsWrapper.on( 'click', 'li:not(:last-child) a', function(e) {
				e.preventDefault();

				$buttonsWrapper.find( 'li a' ).removeClass( 'active' );
				$(this).addClass( 'active' );

				var activeTab = $(this).attr( 'href' );

				$( this ).parents( '.listify_widget_tabbed_listings' ).find( '.listings-tab' ).hide().filter( activeTab ).show();
			});
		},

		initBusinessHours: function() {
			$( '.fieldset-job_hours label' ).click(function(e) {
				e.preventDefault();

				$(this)
					.parent()
					.toggleClass( 'open' )
					.end()
					.next()
					.toggle();
			});
		},

		initApply: function() {
			$( '.job_application.application' ).addClass( 'popup' );
		},

		previewListing: function() {
			if ( $( '.job_listing_preview' ).length ) {
				$( '#main' ).addClass( 'preview-listing' );

				$( '.job_listing_preview.single_job_listing' )
					.removeClass( 'single_job_listing' )
					.addClass( 'single-job_listing' );
			}
		},

		filterTags: function() {
			$('.filter_by_tag').contents().filter(function() {
				return this.nodeType === 3;
			}).each(function() {
				this.nodeValue = $.trim(this.nodeValue);
			}).wrap('<span class="filter-label"></span>');
		}
	};

	listifyWPJobManager.init();

})(jQuery);
