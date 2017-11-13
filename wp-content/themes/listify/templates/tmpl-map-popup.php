<?php
/**
 * Map marker pin popup.
 *
 * @since unknown
 * @version 2.2.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<script id="tmpl-infoBubbleTemplate" type="text/template">

	<# if ( data.featuredImage ) { #>
		<span style="background-image: url({{data.featuredImage.url}})" class="list-cover has-image"></span>
	<# } #>

	<# if ( data.title ) { #>
		<h3>
			<a href="{{data.permalink}}" target="{{data.mapMarker.target}}">
				{{{data.title}}}
			</a>
		</h3>
	<# } #>

	<# if ( data.cardDisplay.rating && data.reviews ) { #>
		<div class="listing-stars">
			<# if ( data.reviews ) { #>
				<# for ( var i = 1; i <= data.reviews.stars.full; i++ ) { #>
					<span class="listing-star listing-star--full"></span>
				<# } for ( var i = 1; i <= data.reviews.stars.half; i++ ) { #>
					<span class="listing-star listing-star--half"></span>
				<# } for ( var i = 1; i <= data.reviews.stars.empty; i++ ) { #>
					<span class="listing-star listing-star--empty"></span>
				<# } #>
			<# } #>
		</div>
	<# } #>

	<# if ( data.status.businessHours ) { #>
		<# if ( data.status.businessIsOpen ) { #>
			<div class="listing-business-hour-status" data-status="open">
				<?php esc_html_e( 'Now Open', 'listify' ); ?>
			</div>
		<# } else { #>
			<div class="listing-business-hour-status" data-status="closed">
				<?php esc_html_e( 'Closed', 'listify' ); ?>
			</div>
		<# } #>
	<# } #>

	<# if ( data.location.raw ) { #>
		<span class="address">{{{data.location.raw}}}</span>
	<# } #>

	<# if ( data.permalink ) { #>
		<a href="{{data.permalink}}" class="job_listing-clickbox"></a>
	<# } #>

</script>
