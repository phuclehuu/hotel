<?php
/**
 * Listing Card Styles
 *
 * @package Listify
 * @subpackage Customize
 * @since 1.8.0
 */

$groups = array(
	'default' => array(
		'title' => __( 'Default', 'listify' ),
		'controls' => array(
			'listing-card-display-title' => true,
			'listing-card-display-location' => true,
			'listing-card-display-phone' => true,
			'listing-card-display-rating' => true,
			'listing-card-display-bookmarks' => true,
			'listing-card-display-booking-attribute' => false,
			'listing-card-display-price' => false,
		),
	),
	'style-1' => array(
		'title' => __( 'Alternate 1', 'listify' ),
		'controls' => array(
			'listing-card-display-title' => true,
			'listing-card-display-location' => true,
			'listing-card-display-phone' => true,
			'listing-card-display-rating' => true,
			'listing-card-display-bookmarks' => true,
			'listing-card-display-booking-attribute' => true,
			'listing-card-display-price' => true,
		),
	),
);

return $groups;
