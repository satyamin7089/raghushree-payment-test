<?php
/**
 * Custom Post Types (`products`, `raghushree_enquiry`) & Polymer Taxonomies.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Products CPT, Polymer Taxonomies, and Admin Enquiry Log CPT.
 *
 * @return void
 */
function raghushree_register_content_types() {
	// 1. Products Custom Post Type (owns /products/ archive + /products/{slug}/ single)
	$product_labels = array(
		'name'               => __( 'Products', 'raghushree' ),
		'singular_name'      => __( 'Product', 'raghushree' ),
		'menu_name'          => __( 'Products', 'raghushree' ),
		'add_new'            => __( 'Add Polymer Record', 'raghushree' ),
		'add_new_item'       => __( 'Add New Polymer Product', 'raghushree' ),
		'edit_item'          => __( 'Edit Polymer Product', 'raghushree' ),
		'new_item'           => __( 'New Polymer Product', 'raghushree' ),
		'view_item'          => __( 'View Product', 'raghushree' ),
		'search_items'       => __( 'Search Products', 'raghushree' ),
		'not_found'          => __( 'No polymer records found.', 'raghushree' ),
		'not_found_in_trash' => __( 'No polymer records found in Trash.', 'raghushree' ),
	);

	register_post_type(
		'products',
		array(
			'labels'             => $product_labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_rest'       => true,
			'has_archive'        => 'products',
			'rewrite'            => array(
				'slug'       => 'products',
				'with_front' => false,
			),
			'menu_icon'          => 'dashicons-networking',
			'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
		)
	);

	// 2. Polymer Category Taxonomy (PP, HDPE, LDPE, LLDPE, PVC, PET, Recycled)
	register_taxonomy(
		'polymer_category',
		array( 'products' ),
		array(
			'labels'            => array(
				'name'          => __( 'Polymer Categories', 'raghushree' ),
				'singular_name' => __( 'Polymer Category', 'raghushree' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'polymer-category',
				'with_front' => false,
			),
		)
	);

	// 3. Material Grade Taxonomy (Assigned ONLY when verified for a polymer)
	register_taxonomy(
		'material_grade',
		array( 'products' ),
		array(
			'labels'            => array(
				'name'          => __( 'Material Grades', 'raghushree' ),
				'singular_name' => __( 'Material Grade', 'raghushree' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'material-grade',
				'with_front' => false,
			),
		)
	);

	// 4. Material Form Taxonomy (Granules, Powder, etc. — assigned ONLY when verified)
	register_taxonomy(
		'material_form',
		array( 'products' ),
		array(
			'labels'            => array(
				'name'          => __( 'Material Forms', 'raghushree' ),
				'singular_name' => __( 'Material Form', 'raghushree' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'material-form',
				'with_front' => false,
			),
		)
	);

	// 5. Manufacturing Applications Taxonomy
	register_taxonomy(
		'manufacturing_application',
		array( 'products' ),
		array(
			'labels'            => array(
				'name'          => __( 'Applications', 'raghushree' ),
				'singular_name' => __( 'Application', 'raghushree' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_admin_column' => false,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'application',
				'with_front' => false,
			),
		)
	);

	// 6. Private Admin Enquiry Log CPT (stores verified website enquiries safely)
	register_post_type(
		'raghushree_enquiry',
		array(
			'labels'          => array(
				'name'          => __( 'Enquiries', 'raghushree' ),
				'singular_name' => __( 'Enquiry', 'raghushree' ),
				'menu_name'     => __( 'Enquiries', 'raghushree' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'capability_type' => 'post',
			'capabilities'    => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'    => true,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title' ),
		)
	);
}
add_action( 'init', 'raghushree_register_content_types' );

/**
 * Filter the native `products` archive query for ordering and server-side search/filters.
 *
 * @param WP_Query $query Current query instance.
 * @return void
 */
function raghushree_filter_products_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_post_type_archive( 'products' ) || $query->is_tax( array( 'polymer_category', 'material_grade', 'material_form' ) ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			)
		);

		$search_q = isset( $_GET['product_q'] ) ? sanitize_text_field( wp_unslash( $_GET['product_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $search_q ) {
			$query->set( 's', $search_q );
		}

		$tax_query = array();
		$cat_slug  = isset( $_GET['polymer_cat'] ) ? sanitize_title( wp_unslash( $_GET['polymer_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $cat_slug ) {
			$tax_query[] = array(
				'taxonomy' => 'polymer_category',
				'field'    => 'slug',
				'terms'    => $cat_slug,
			);
		}

		$grade_slug = isset( $_GET['material_grade'] ) ? sanitize_title( wp_unslash( $_GET['material_grade'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $grade_slug ) {
			$tax_query[] = array(
				'taxonomy' => 'material_grade',
				'field'    => 'slug',
				'terms'    => $grade_slug,
			);
		}

		$form_slug = isset( $_GET['material_form'] ) ? sanitize_title( wp_unslash( $_GET['material_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $form_slug ) {
			$tax_query[] = array(
				'taxonomy' => 'material_form',
				'field'    => 'slug',
				'terms'    => $form_slug,
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
		}
		if ( ! empty( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query );
		}
	}
}
add_action( 'pre_get_posts', 'raghushree_filter_products_archive_query' );
