<?php
/**
 * Menu widgets
 *
 * @uses $wp_customize
 * @since 1.7.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'nav-menu-width', array(
	'default' => 'fixed',
) );

$wp_customize->add_control( 'nav-menu-width', array(
	'label' => __( 'Menu Width', 'listify' ),
	'description' => __( 'Primary and secondary menu widths on large devices.', 'listify' ),
	'type' => 'select',
	'choices' => array(
		'fixed' => __( 'Fixed', 'listify' ),
		'full' => __( 'Full Width', 'listify' ),
	),
	'priority' => 5,
	'section' => 'nav-menus',
) );
