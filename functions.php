<?php
/**
 * Raghushree Custom Industrial WordPress Theme — Bootstrap.
 *
 * Loads modular theme files by responsibility without duplicating logic.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$raghushree_includes = array(
	'inc/image-placeholders.php',
	'inc/helpers.php',
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/custom-post-types.php',
	'inc/custom-fields.php',
	'inc/admin-meta-boxes.php',
	'inc/product-master.php',
	'inc/payment-settings.php',
	'inc/payment-compat.php',
	'inc/shipping-quotes.php',
	'inc/template-functions.php',
	'inc/seo-meta.php',
	'inc/schema.php',
	'inc/seed-data.php',
);

foreach ( $raghushree_includes as $raghushree_file ) {
	$raghushree_path = get_template_directory() . '/' . $raghushree_file;
	if ( file_exists( $raghushree_path ) ) {
		require_once $raghushree_path;
	}
}
