<?php
/**
 * Homepage Secondary Logo
 *
 * @uses $wp_customize
 * @since 1.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
