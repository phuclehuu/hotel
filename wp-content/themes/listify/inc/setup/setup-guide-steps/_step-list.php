<?php
/**
 * Steps for the setup guide.
 *
 * @since 1.5.0
 */

global $tgmpa;

/** Create the steps */
$steps = array();

if ( ! wp_get_theme()->parent() ) {
	$steps['child-theme'] = array(
		'title' => __( 'Install a Child Theme', 'listify' ),
		'completed' => wp_get_theme()->parent(),
	);
}

$steps['theme-updater'] = array(
	'title' => __( 'Enable Automatic Updates', 'listify' ),
	'completed' => 'n/a',
);

$steps['install-plugins'] = array(
	'title' => __( 'Install Required Plugins', 'listify' ),
	'completed' => class_exists( 'WP_Job_Manager' ) && class_exists( 'WooCommerce' ),
);

if ( current_user_can( 'import' ) ) {
	$steps['import-content'] = array(
		'title' => __( 'Choose Site Content', 'listify' ),
		'completed' => get_option( 'page_for_posts' ),
	);
}

$steps['google-maps'] = array(
	'title' => __( 'Setup Google Maps', 'listify' ),
	'completed' => listify_get_google_maps_api_key(),
);

$steps['customize-theme'] = array(
	'title' => __( 'Customize Your Site', 'listify' ),
	'completed' => 'n/a',
);

$steps['support-us'] = array(
	'title' => __( 'Get Involved', 'listify' ),
	'completed' => 'n/a',
);

return $steps;
