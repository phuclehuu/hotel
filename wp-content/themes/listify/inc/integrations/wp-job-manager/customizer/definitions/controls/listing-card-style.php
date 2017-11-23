<?php
/**
 * Card Style
 *
 * @uses $wp_customize
 * @since 1.9.0
 */

// not adding for now
return;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wp_customize->add_setting( 'listing-card-style', array(
	'default' => 'default',
	'transport' => 'refresh',
) );

$wp_customize->add_control( new Listify_Customize_Control_ControlGroup(
	$wp_customize,
	'listing-card-style',
	array(
		'label' => __( 'Card Style', 'listify' ),
		'group' => include( get_template_directory() . '/inc/integrations/wp-job-manager/customizer/definitions/control-groups/listing-card-style.php' ),
		'input_type' => 'select',
		'priority' => 5,
		'section' => 'search-results',
	)
) );
