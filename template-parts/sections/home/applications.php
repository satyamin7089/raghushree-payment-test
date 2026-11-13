<?php
/**
 * Homepage Section: Supporting Core Plastic Processing & Conversion Methods
 * ("Useful detail, on the other side." — 4 Interactive 180° Flip Cards).
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part(
	'template-parts/components/flip-cards-section',
	null,
	array(
		'id'      => 'manufacturing-applications',
		'surface' => 'surface--white',
	)
);
