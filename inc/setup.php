<?php
/**
 * Theme Setup, Image Sizes, Preloads & Customizer Registration.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configure theme supports, image sizes, and navigation locations.
 *
 * @return void
 */
function raghushree_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Responsive industrial image sizes
	add_image_size( 'raghushree-hero', 1400, 920, true );
	add_image_size( 'raghushree-split', 1080, 760, true );
	add_image_size( 'raghushree-card', 800, 500, true );

	register_nav_menus(
		array(
			'primary'         => __( 'Primary Navigation', 'raghushree' ),
			'footer_products' => __( 'Footer Polymer Links', 'raghushree' ),
			'footer_links'    => __( 'Footer Quick Links', 'raghushree' ),
		)
	);
}
add_action( 'after_setup_theme', 'raghushree_theme_setup' );

/**
 * Preload critical self-hosted WOFF2 font files to eliminate FOIT/CLS.
 *
 * @return void
 */
function raghushree_preload_fonts() {
	$fonts = array(
		'IBMPlexSans-SemiBold.woff2',
		'IBMPlexSans-Bold.woff2',
		'Inter-Regular.woff2',
		'Inter-SemiBold.woff2',
	);

	foreach ( $fonts as $font_file ) {
		$font_url = get_template_directory_uri() . '/assets/fonts/' . $font_file;
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n",
			esc_url( $font_url )
		);
	}
}
add_action( 'wp_head', 'raghushree_preload_fonts', 1 );

/**
 * Clean up non-essential WordPress emoji scripts/styles for Core Web Vitals.
 *
 * @return void
 */
function raghushree_optimize_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
}
add_action( 'init', 'raghushree_optimize_head' );

/**
 * Register Customizer controls for Verified Company Details & Media Placeholder Replacement.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 * @return void
 */
function raghushree_customize_register( $wp_customize ) {
	// 1. Verified Company & Contact Section
	$wp_customize->add_section(
		'raghushree_company_section',
		array(
			'title'       => __( 'Raghushree Company & Contact', 'raghushree' ),
			'description' => __( 'Manage verified company credentials, Kanpur office address, phone, WhatsApp, email, and optional social links. Empty fields are automatically hidden.', 'raghushree' ),
			'priority'    => 30,
		)
	);

	$fields = array(
		'raghushree_brand_name'   => array( 'Brand Name', 'Raghushree', 'sanitize_text_field' ),
		'raghushree_legal_name'   => array( 'Registered Legal Name', 'Raghushree Plast Products Pvt. Ltd.', 'sanitize_text_field' ),
		'raghushree_positioning'  => array( 'Business Positioning Line', 'Manufacturer, Exporter & Supplier of Plastic Raw Materials', 'sanitize_text_field' ),
		'raghushree_full_address' => array( 'Full Registered Address', '78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India', 'sanitize_text_field' ),
		'raghushree_street'       => array( 'Street Address', '78/205, Latouche Road, General Ganj', 'sanitize_text_field' ),
		'raghushree_city'         => array( 'City', 'Kanpur', 'sanitize_text_field' ),
		'raghushree_postal'       => array( 'Postal / PIN Code', '208001', 'sanitize_text_field' ),
		'raghushree_state'        => array( 'State', 'Uttar Pradesh', 'sanitize_text_field' ),
		'raghushree_country'      => array( 'Country', 'India', 'sanitize_text_field' ),
		'raghushree_email'        => array( 'Sales & Marketing Email', 'marketing@raghushree.com', 'sanitize_email' ),
		'raghushree_phone'        => array( 'Primary Phone Number', '+91-830-3715-071', 'sanitize_text_field' ),
		'raghushree_whatsapp'     => array( 'WhatsApp Number', '+91-830-3715-071', 'sanitize_text_field' ),
		'raghushree_social_linkedin' => array( 'LinkedIn URL (optional)', '', 'esc_url_raw' ),
		'raghushree_social_facebook' => array( 'Facebook URL (optional)', '', 'esc_url_raw' ),
		'raghushree_social_x'        => array( 'X / Twitter URL (optional)', '', 'esc_url_raw' ),
		'raghushree_social_youtube'  => array( 'YouTube URL (optional)', '', 'esc_url_raw' ),
	);

	foreach ( $fields as $setting_id => $meta ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $meta[1],
				'sanitize_callback' => $meta[2],
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'   => $meta[0],
				'section' => 'raghushree_company_section',
				'type'    => 'text',
			)
		);
	}

	// 2. Centralized Image Replacement Section
	$wp_customize->add_section(
		'raghushree_media_section',
		array(
			'title'       => __( 'Raghushree Image Placeholders', 'raghushree' ),
			'description' => __( 'Replace any temporary Unsplash placeholder photograph with an uploaded WordPress Media Library image.', 'raghushree' ),
			'priority'    => 35,
		)
	);

	foreach ( raghushree_get_placeholder_registry() as $slot_key => $slot_data ) {
		$setting_key = 'raghushree_media_' . $slot_key;
		$wp_customize->add_setting(
			$setting_key,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
			)
		);
		if ( class_exists( 'WP_Customize_Media_Control' ) ) {
			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					$setting_key,
					array(
						'label'     => $slot_data['label'],
						'section'   => 'raghushree_media_section',
						'mime_type' => 'image',
					)
				)
			);
		}
	}
}
add_action( 'customize_register', 'raghushree_customize_register' );
