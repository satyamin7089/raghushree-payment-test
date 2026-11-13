<?php
/**
 * Conditional Stylesheet & Deferred Script Loading.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve asset file version via filemtime for reliable cache invalidation.
 *
 * @param string $relative_path Path relative to theme root.
 * @return string
 */
function raghushree_asset_version( $relative_path ) {
	$full_path = get_template_directory() . '/' . ltrim( $relative_path, '/' );
	if ( file_exists( $full_path ) ) {
		return (string) filemtime( $full_path );
	}
	return '1.1.0';
}

/**
 * Enqueue global and conditionally-loaded page-specific CSS & JS.
 *
 * @return void
 */
function raghushree_enqueue_assets() {
	$uri = get_template_directory_uri();

	// 1. Shared Foundational Stylesheets
	wp_enqueue_style(
		'raghushree-global',
		$uri . '/assets/css/global.css',
		array(),
		raghushree_asset_version( 'assets/css/global.css' )
	);

	wp_enqueue_style(
		'raghushree-components',
		$uri . '/assets/css/components.css',
		array( 'raghushree-global' ),
		raghushree_asset_version( 'assets/css/components.css' )
	);

	wp_enqueue_style(
		'raghushree-header',
		$uri . '/assets/css/header.css',
		array( 'raghushree-global', 'raghushree-components' ),
		raghushree_asset_version( 'assets/css/header.css' )
	);

	wp_enqueue_style(
		'raghushree-footer',
		$uri . '/assets/css/footer.css',
		array( 'raghushree-global', 'raghushree-components' ),
		raghushree_asset_version( 'assets/css/footer.css' )
	);

	wp_enqueue_style(
		'raghushree-interactive-sections',
		$uri . '/assets/css/interactive-sections.css',
		array( 'raghushree-components' ),
		raghushree_asset_version( 'assets/css/interactive-sections.css' )
	);

	// 2. Shared Deferred Vanilla JS
	wp_enqueue_script(
		'raghushree-global',
		$uri . '/assets/js/global.js',
		array(),
		raghushree_asset_version( 'assets/js/global.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	wp_enqueue_script(
		'raghushree-navigation',
		$uri . '/assets/js/navigation.js',
		array(),
		raghushree_asset_version( 'assets/js/navigation.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$is_about          = is_page_template( 'page-templates/template-about.php' ) || is_page( 'about' );
	$is_single_product = is_singular( 'products' ) || is_page_template( 'page-templates/template-product-detail.php' );

	// 3. Conditional Page-Specific Styles & Scripts
	if ( is_front_page() ) {
		wp_enqueue_style(
			'raghushree-home',
			$uri . '/assets/css/home.css',
			array( 'raghushree-components', 'raghushree-interactive-sections' ),
			raghushree_asset_version( 'assets/css/home.css' )
		);
	}

	if ( $is_about ) {
		wp_enqueue_style(
			'raghushree-about',
			$uri . '/assets/css/about.css',
			array( 'raghushree-components', 'raghushree-interactive-sections' ),
			raghushree_asset_version( 'assets/css/about.css' )
		);
	}

	// GSAP + ScrollTrigger for the 5-Slide Scroll-Pinned Visual Product Story (Home & About)
	if ( is_front_page() || $is_about ) {
		wp_enqueue_script(
			'raghushree-gsap',
			$uri . '/assets/vendor/gsap/gsap.min.js',
			array(),
			raghushree_asset_version( 'assets/vendor/gsap/gsap.min.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
		wp_enqueue_script(
			'raghushree-scrolltrigger',
			$uri . '/assets/vendor/gsap/ScrollTrigger.min.js',
			array( 'raghushree-gsap' ),
			raghushree_asset_version( 'assets/vendor/gsap/ScrollTrigger.min.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	// Interactive Sections Engine (5-Slide Story, 180° Flip Cards, Single-Product Fade Slider)
	if ( is_front_page() || $is_about || $is_single_product ) {
		$deps = ( is_front_page() || $is_about ) ? array( 'raghushree-gsap', 'raghushree-scrolltrigger' ) : array();
		wp_enqueue_script(
			'raghushree-interactive-sections',
			$uri . '/assets/js/interactive-sections.js',
			$deps,
			raghushree_asset_version( 'assets/js/interactive-sections.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	if ( is_page_template( 'page-templates/template-contact.php' ) || is_page( 'contact' ) ) {
		wp_enqueue_style(
			'raghushree-contact',
			$uri . '/assets/css/contact.css',
			array( 'raghushree-components' ),
			raghushree_asset_version( 'assets/css/contact.css' )
		);
		wp_enqueue_script(
			'raghushree-contact',
			$uri . '/assets/js/contact.js',
			array(),
			raghushree_asset_version( 'assets/js/contact.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	$is_products_listing = is_post_type_archive( 'products' )
		|| is_tax( array( 'polymer_category', 'material_grade', 'material_form', 'manufacturing_application' ) )
		|| is_page_template( 'page-templates/template-products.php' );

	if ( $is_products_listing ) {
		wp_enqueue_style(
			'raghushree-products',
			$uri . '/assets/css/products.css',
			array( 'raghushree-components' ),
			raghushree_asset_version( 'assets/css/products.css' )
		);
		wp_enqueue_script(
			'raghushree-products',
			$uri . '/assets/js/products.js',
			array(),
			raghushree_asset_version( 'assets/js/products.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	if ( $is_single_product ) {
		wp_enqueue_style(
			'raghushree-product-single',
			$uri . '/assets/css/product-single.css',
			array( 'raghushree-components', 'raghushree-interactive-sections' ),
			raghushree_asset_version( 'assets/css/product-single.css' )
		);
		wp_enqueue_script(
			'raghushree-product-single',
			$uri . '/assets/js/product-single.js',
			array(),
			raghushree_asset_version( 'assets/js/product-single.js' ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'raghushree_enqueue_assets' );

/** Final shared typography layer, loaded after page-specific styles. */
function raghushree_enqueue_typography() {
  wp_enqueue_style( 'raghushree-typography', get_template_directory_uri() . '/assets/css/typography.css', array( 'raghushree-components' ), raghushree_asset_version( 'assets/css/typography.css' ) );
}
add_action( 'wp_enqueue_scripts', 'raghushree_enqueue_typography', 99 );
