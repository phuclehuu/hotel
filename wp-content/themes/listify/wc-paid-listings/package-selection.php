<?php
/**
 * Package Selection.
 * Shows packages selection to purchase.
 *
 * @version 2.2.0
 * @since 2.2.0
 *
 * @var array $packages      WC Products.
 * @var array $user_packages User Packages.
 *
 * @package Listing Payments
 * @category Template
 * @author Astoundify
 */

get_job_manager_template(
	$template_name  = 'package-selection.php',
	$args           = array(
		'packages'      => $packages,
		'user_packages' => $user_packages,
	),
	$template_path  = 'listing-payments'
);
