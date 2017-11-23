<?php
/**
 * Copyright
 *
 * @uses $wp_customize
 * @since 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// as seen on title
$wp_customize->add_setting( 'copyright-text', array(
	'default' => sprintf( __( 'Copyright %1$s &copy; %2$s. All Rights Reserved', 'listify' ), get_bloginfo( 'name' ), date( 'Y' ) ),
	'transport' => 'postMessage',
) );

$wp_customize->add_control( 'copyright-text', array(
	'label' => __( 'Copyright Text', 'listify' ),
	'type' => 'text',
	'priority' => 30,
	'section' => 'content-footer',
) );

if ( ! isset( $wp_customize->selective_refresh ) ) {
	return;
}

$wp_customize->selective_refresh->add_partial( 'copyright-text', array(
	'selector' => '.site-info',
	'settings' => array( 'copyright-text' ),
	'render_callback' => 'listify_partial_copyright_text',
) );
