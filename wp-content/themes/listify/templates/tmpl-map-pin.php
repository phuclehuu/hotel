<?php
/**
 * Map marker pin.
 *
 * @since unknown
 * @version 2.3.0
 *
 * @package Listify
 * @category Template
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<script id="tmpl-pinTemplate" type="text/template">

	<div id="listing-{{data.id}}-map-marker" class="map-marker marker-color-{{{ data.mapMarker.term }}} type-{{{ data.mapMarker.term }}} <# if ( data.status.featured ) { #>featured<# } #>">
		<i class="{{{ data.mapMarker.icon }}}"></i>
		<span class="map-marker__shadow"></span>
	</div>

</script>
