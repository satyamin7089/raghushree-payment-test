<?php
/**
 * Root Fallback Template (`index.php`) — Required by WordPress Theme Standards.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_front_page() ) {
	get_template_part( 'front-page' );
	return;
}

get_template_part( 'archive' );
