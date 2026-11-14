<?php
/**
 * Structured Product Data Accessors & Line-Delimited Field Parsers.
 *
 * One product record powers listing cards, detail templates, related products,
 * internal links, and SEO metadata without duplicating information.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parse pipe-delimited multi-line admin strings ("Column 1 | Column 2 | Column 3").
 *
 * @param string $raw      Raw newline-delimited string.
 * @param int    $min_cols Minimum columns expected per row.
 * @return array<int, array<int, string>>
 */
function raghushree_parse_pipe_rows( $raw, $min_cols = 1 ) {
	$rows = array();
	if ( empty( $raw ) || ! is_string( $raw ) ) {
		return $rows;
	}

	$lines = preg_split( '/\r\n|\r|\n/', trim( $raw ) );
	if ( ! is_array( $lines ) ) {
		return $rows;
	}

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) >= $min_cols && '' !== $parts[0] ) {
			$rows[] = $parts;
		}
	}

	return $rows;
}

/**
 * Retrieve complete, sanitized product data for any `products` post.
 * Never fabricates missing grades, specs, datasheets, MOQ, or availability.
 *
 * @param int $post_id Product post ID.
 * @return array<string, mixed>
 */
function raghushree_get_product_data( $post_id ) {
	$post_id = absint( $post_id );
	$post    = get_post( $post_id );
	if ( ! ( $post instanceof WP_Post ) ) {
		return array();
	}

	$categories   = get_the_terms( $post_id, 'polymer_category' );
	$grades_terms = get_the_terms( $post_id, 'material_grade' );
	$forms_terms  = get_the_terms( $post_id, 'material_form' );
	$apps_terms   = get_the_terms( $post_id, 'manufacturing_application' );

	$primary_cat  = ( is_array( $categories ) && ! empty( $categories ) ) ? $categories[0]->name : 'Polymer Raw Material';
	$polymer_code = get_post_meta( $post_id, '_raghushree_polymer_code', true );
	$image_slot   = get_post_meta( $post_id, '_raghushree_image_slot', true );
	if ( empty( $image_slot ) ) {
		$image_slot = 'polymer_hdpe';
	}

	$short_desc = get_post_meta( $post_id, '_raghushree_short_desc', true );
	if ( empty( $short_desc ) ) {
		$short_desc = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 26 );
	}

	// Structured multi-line custom fields
	$grades_notes   = get_post_meta( $post_id, '_raghushree_grades_notes', true );
	$grades_rows    = raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_grades_rows', true ), 2 );
	$apps_rows      = raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_applications_rows', true ), 2 );
	$industries     = raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_industries_rows', true ), 1 );
	$processing     = raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_processing_rows', true ), 2 );
	$tech_specs     = raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_tech_specs', true ), 2 );
	$sections       = raghushree_product_sections( $post_id );
	$apps_rows      = $sections['applications'];
	$tech_specs     = $sections['specifications'];
	$faq_rows       = $sections['faqs'];

	$faqs = array();
	foreach ( $faq_rows as $f_row ) {
		$faqs[] = array(
			'question' => $f_row[0],
			'answer'   => $f_row[1],
		);
	}

	$datasheet_url  = esc_url( (string) get_post_meta( $post_id, '_raghushree_datasheet_url', true ) );
	$packaging      = trim( (string) get_post_meta( $post_id, '_raghushree_packaging', true ) );
	$pack_size      = trim( (string) get_post_meta( $post_id, '_raghushree_pack_size', true ) );
	$moq            = trim( (string) get_post_meta( $post_id, '_raghushree_moq', true ) );
	$availability   = trim( (string) get_post_meta( $post_id, '_raghushree_availability', true ) );

	$gallery_raw = trim( (string) get_post_meta( $post_id, '_raghushree_gallery_ids', true ) );
	$gallery_ids = array();
	if ( '' !== $gallery_raw ) {
		foreach ( explode( ',', $gallery_raw ) as $gid ) {
			$gid = absint( trim( $gid ) );
			if ( $gid > 0 ) {
				$gallery_ids[] = $gid;
			}
		}
	}

	$related_raw = trim( (string) get_post_meta( $post_id, '_raghushree_related_ids', true ) );
	$related_ids = array();
	if ( '' !== $related_raw ) {
		foreach ( explode( ',', $related_raw ) as $rid ) {
			$rid = absint( trim( $rid ) );
			if ( $rid > 0 && $rid !== $post_id ) {
				$related_ids[] = $rid;
			}
		}
	}

	return array(
		'id'           => $post_id,
		'title'        => get_the_title( $post_id ),
		'permalink'    => get_permalink( $post_id ),
		'polymer_code' => $polymer_code,
		'primary_cat'  => $primary_cat,
		'categories'   => is_array( $categories ) ? $categories : array(),
		'grades_terms' => is_array( $grades_terms ) ? $grades_terms : array(),
		'forms_terms'  => is_array( $forms_terms ) ? $forms_terms : array(),
		'apps_terms'   => is_array( $apps_terms ) ? $apps_terms : array(),
		'image_slot'   => $image_slot,
		'short_desc'   => $short_desc,
		'grades_notes' => $grades_notes,
		'grades_rows'  => $grades_rows,
		'apps_rows'    => $apps_rows,
		'features'     => $sections['features'],
		'industries'   => $industries,
		'processing'   => $processing,
		'tech_specs'   => $tech_specs,
		'datasheet'    => $datasheet_url,
		'packaging'    => $packaging,
		'pack_size'    => $pack_size,
		'moq'          => $moq,
		'availability' => $availability,
		'gallery_ids'  => $gallery_ids,
		'related_ids'  => $related_ids,
		'faqs'         => $faqs,
	);
}
