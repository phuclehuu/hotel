<?php
/**
 * Custom Logo.
 *
 * @uses $wp_customize
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) || ! $wp_customize instanceof WP_Customize_Manager ) {
	exit; // Exit if accessed directly.
}

$wp_customize->get_control( 'custom_logo' )->priority = 50;
$wp_customize->get_control( 'custom_logo' )->transport = 'postMessage';

if ( ! isset( $wp_customize->selective_refresh ) ) {
	return;
}

$wp_customize->selective_refresh->add_partial( 'custom_logo', array(
	'selector' => '.site-branding',
	'settings' => array( 'custom_logo' ),
	'render_callback' => 'listify_partial_site_branding',
) );
