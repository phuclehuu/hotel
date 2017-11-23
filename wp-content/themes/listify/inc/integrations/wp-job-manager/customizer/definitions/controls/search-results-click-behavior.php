<?php
/**
 * Click behavior.
 *
 * @uses $wp_customize
 * @since 1.5.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-archive-window', array(
	'default' => false,
) );

$wp_customize->add_control( 'listing-archive-window', array(
	'label' => __( 'Open listings in a new tab/window', 'listify' ),
	'type' => 'checkbox',
	'priority' => 50,
	'section' => 'search-results',
) );
