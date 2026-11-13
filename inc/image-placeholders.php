<?php
/**
 * Centralized Temporary Unsplash Image Placeholder Registry & Image Renderer.
 *
 * IMPORTANT IMAGE POLICY:
 * - Zero AI-generated images are used anywhere in this theme.
 * - Every temporary placeholder below is a genuine Unsplash photograph of
 *   industrial polymer raw materials, processing machinery, or warehousing.
 * - Placeholders are never presented as Raghushree's own plant or staff.
 * - Uploading a Featured Image on any Product/Page or setting a Media override
 *   in Appearance -> Customize -> Raghushree Media Placeholders automatically
 *   replaces the placeholder without editing any code.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return the centralized map of temporary Unsplash placeholder images.
 *
 * @return array<string, array<string, mixed>>
 */
function raghushree_get_placeholder_registry() {
	return array(
		'home_hero'         => array(
			'local_file' => 'assets/images/bg-cta-3.jpg',
			'base_url'   => 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
			'width'      => 1500,
			'height'     => 670,
			'alt'        => 'Industrial thermoplastic polymer processing and raw material production facility',
			'label'      => 'Homepage Hero Image',
		),
		'home_about'        => array(
			'base_url' => 'https://images.unsplash.com/photo-1730705788367-dbd288c40ee7',
			'width'    => 1200,
			'height'   => 840,
			'alt'      => 'Representative thermoplastic injection moulding machines in an industrial production facility',
			'label'    => 'Homepage About Split Image',
		),
		'home_quality'      => array(
			'base_url' => 'https://images.unsplash.com/photo-1777464375799-768774f30542',
			'width'    => 1200,
			'height'   => 840,
			'alt'      => 'Representative plastic processing machinery with hopper dryers and polymer raw material bags',
			'label'    => 'Homepage Quality & Supply Image',
		),
		'mega_menu_feature' => array(
			'base_url' => 'https://images.unsplash.com/photo-1675176785803-bffbbb0cd2f4',
			'width'    => 800,
			'height'   => 450,
			'alt'      => 'Representative extruded polymer winding spools on an industrial production line',
			'label'    => 'Header Mega Menu Feature Image',
		),
		'about_hero'        => array(
			'base_url' => 'https://images.unsplash.com/photo-1717386255773-1e3037c81788',
			'width'    => 1400,
			'height'   => 920,
			'alt'      => 'Representative automated industrial manufacturing and material forming production line',
			'label'    => 'About Page Hero Image',
		),
		'about_overview'    => array(
			'base_url' => 'https://images.unsplash.com/photo-1777464375799-768774f30542',
			'width'    => 1200,
			'height'   => 840,
			'alt'      => 'Representative plastic processing line with raw material hoppers and industrial equipment',
			'label'    => 'About Company Overview Image',
		),
		'about_quality'     => array(
			'base_url' => 'https://images.unsplash.com/photo-1781330173666-11f8818d6eb2',
			'width'    => 1200,
			'height'   => 840,
			'alt'      => 'Representative industrial testing and process control instrumentation',
			'label'    => 'About Quality Commitment Image',
		),
		'about_supply'      => array(
			'base_url' => 'https://images.unsplash.com/photo-1689942010216-dc412bb1e7a9',
			'width'    => 1200,
			'height'   => 840,
			'alt'      => 'Representative industrial warehouse pallet racking and raw material logistics handling',
			'label'    => 'About Supply & Distribution Image',
		),
		'products_hero'     => array(
			'base_url' => 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
			'width'    => 1280,
			'height'   => 800,
			'alt'      => 'Representative stacked polymer resin bags ready for industrial manufacturing supply',
			'label'    => 'Products Listing Hero Image',
		),
		'polymer_pp'        => array(
			'base_url' => 'https://images.unsplash.com/photo-1675176785803-bffbbb0cd2f4',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative polypropylene tape and raffia extrusion winding machinery',
			'label'    => 'Polypropylene (PP) Placeholder',
		),
		'polymer_hdpe'      => array(
			'base_url' => 'https://images.unsplash.com/photo-1785900976288-31f0f42a7364',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative palletized high-density polyethylene resin sacks for industrial moulding and extrusion',
			'label'    => 'HDPE Placeholder',
		),
		'polymer_ldpe'      => array(
			'base_url' => 'https://images.unsplash.com/photo-1675176785665-8d6aef6b7d8a',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative extruded polyethylene film rolls in an industrial production hall',
			'label'    => 'LDPE Placeholder',
		),
		'polymer_lldpe'     => array(
			'base_url' => 'https://images.unsplash.com/photo-1675176788145-928336d4ba14',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative extruded linear low-density polyethylene spools and flexible packaging material',
			'label'    => 'LLDPE Placeholder',
		),
		'polymer_pvc'       => array(
			'base_url' => 'https://images.unsplash.com/photo-1779718811629-92d973f0badb',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative extruded rigid plastic industrial pipes manufactured from polymer compounds',
			'label'    => 'PVC Placeholder',
		),
		'polymer_pet'       => array(
			'base_url' => 'https://images.unsplash.com/photo-1536939459926-301728717817',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative blow-moulded clear PET packaging containers on a production lot',
			'label'    => 'PET Placeholder',
		),
		'polymer_recycled'  => array(
			'base_url' => 'https://images.unsplash.com/photo-1760307837830-5d5d31fcf25f',
			'width'    => 1000,
			'height'   => 625,
			'alt'      => 'Representative sorted polyolefin plastic feedstock prepared for recycling and reprocessing',
			'label'    => 'Recycled & Reprocessed Polymers Placeholder',
		),
		'app_injection'     => array(
			'base_url' => 'https://images.unsplash.com/photo-1730705788367-dbd288c40ee7',
			'width'    => 800,
			'height'   => 450,
			'alt'      => 'Representative industrial injection moulding machinery line',
			'label'    => 'Application: Injection Moulding',
		),
		'app_blow'          => array(
			'base_url' => 'https://images.unsplash.com/photo-1777464375799-768774f30542',
			'width'    => 800,
			'height'   => 450,
			'alt'      => 'Representative hollow container blow moulding equipment and hopper dryers',
			'label'    => 'Application: Blow Moulding',
		),
		'app_extrusion'     => array(
			'base_url' => 'https://images.unsplash.com/photo-1787672357830-ef4281855d47',
			'width'    => 800,
			'height'   => 450,
			'alt'      => 'Representative continuous industrial extrusion and profile forming rollers',
			'label'    => 'Application: Extrusion',
		),
		'app_film'          => array(
			'base_url' => 'https://images.unsplash.com/photo-1675176785665-8d6aef6b7d8a',
			'width'    => 800,
			'height'   => 450,
			'alt'      => 'Representative industrial packaging film rolls in a manufacturing hall',
			'label'    => 'Application: Film & Packaging',
		),
	);
}

/**
 * Build an optimized Unsplash URL with automatic modern format negotiation (WebP/AVIF).
 *
 * @param string $base_url Base Unsplash photo URL without query string.
 * @param int    $width    Target pixel width.
 * @param int    $height   Optional target pixel height.
 * @return string
 */
function raghushree_build_unsplash_url( $base_url, $width = 1000, $height = 0 ) {
	$args = array(
		'auto' => 'format',
		'fit'  => 'crop',
		'w'    => absint( $width ),
		'q'    => 80,
	);
	if ( $height > 0 ) {
		$args['h'] = absint( $height );
	}
	return add_query_arg( $args, $base_url );
}

/**
 * Return the resolved image URL for Open Graph / Schema / templates.
 *
 * @param string $slot_key Placeholder key.
 * @param int    $post_id  Optional post ID to check Featured Image first.
 * @return string
 */
function raghushree_get_image_url( $slot_key, $post_id = 0 ) {
	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$featured_url = get_the_post_thumbnail_url( $post_id, 'raghushree-hero' );
		if ( $featured_url ) {
			return $featured_url;
		}
	}

	$customizer_attachment_id = absint( get_theme_mod( 'raghushree_media_' . $slot_key, 0 ) );
	if ( $customizer_attachment_id ) {
		$custom_url = wp_get_attachment_image_url( $customizer_attachment_id, 'raghushree-hero' );
		if ( $custom_url ) {
			return $custom_url;
		}
	}

	$registry = raghushree_get_placeholder_registry();
	if ( isset( $registry[ $slot_key ] ) ) {
		return raghushree_build_unsplash_url(
			$registry[ $slot_key ]['base_url'],
			$registry[ $slot_key ]['width'],
			$registry[ $slot_key ]['height']
		);
	}

	return raghushree_build_unsplash_url( $registry['home_hero']['base_url'], 1200, 800 );
}

/**
 * Render a responsive image using WordPress Media Library first, with centralized
 * Unsplash placeholder fallback.
 *
 * @param string               $slot_key Centralized registry key.
 * @param array<string, mixed> $args     Optional arguments: post_id, attachment_id, size, sizes, priority, alt, class.
 * @return void
 */
function raghushree_render_image( $slot_key, $args = array() ) {
	$defaults = array(
		'post_id'       => 0,
		'attachment_id' => 0,
		'size'          => 'raghushree-split',
		'sizes'         => '(min-width: 1200px) 600px, (min-width: 900px) 48vw, 100vw',
		'priority'      => false,
		'alt'           => '',
		'class'         => '',
	);
	$args     = wp_parse_args( $args, $defaults );

	$attachment_id = absint( $args['attachment_id'] );
	if ( ! $attachment_id && ! empty( $args['post_id'] ) && has_post_thumbnail( $args['post_id'] ) ) {
		$attachment_id = (int) get_post_thumbnail_id( $args['post_id'] );
	}
	if ( ! $attachment_id ) {
		$attachment_id = absint( get_theme_mod( 'raghushree_media_' . $slot_key, 0 ) );
	}

	// 1. Native WordPress Media Library output when replaced by admin
	if ( $attachment_id ) {
		$img_attrs = array(
			'sizes'    => $args['sizes'],
			'decoding' => 'async',
			'loading'  => $args['priority'] ? 'eager' : 'lazy',
		);
		if ( $args['priority'] ) {
			$img_attrs['fetchpriority'] = 'high';
		}
		if ( ! empty( $args['class'] ) ) {
			$img_attrs['class'] = $args['class'];
		}
		if ( ! empty( $args['alt'] ) ) {
			$img_attrs['alt'] = $args['alt'];
		}

		$wp_img = wp_get_attachment_image( $attachment_id, $args['size'], false, $img_attrs );
		if ( $wp_img ) {
			echo $wp_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by wp_get_attachment_image.
			return;
		}
	}

	// 2. Centralized Theme Asset or Temporary Unsplash Placeholder Fallback
	$registry = raghushree_get_placeholder_registry();
	$item     = isset( $registry[ $slot_key ] ) ? $registry[ $slot_key ] : $registry['home_hero'];
	$width    = (int) $item['width'];
	$height   = (int) $item['height'];
	$alt      = ! empty( $args['alt'] ) ? $args['alt'] : $item['alt'];

	if ( ! empty( $item['local_file'] ) && file_exists( get_template_directory() . '/' . ltrim( $item['local_file'], '/' ) ) ) {
		$local_src = get_template_directory_uri() . '/' . ltrim( $item['local_file'], '/' );
		printf(
			'<img src="%1$s" width="%2$d" height="%3$d" alt="%4$s"%5$s decoding="async" loading="%6$s"%7$s />',
			esc_url( $local_src ),
			absint( $width ),
			absint( $height ),
			esc_attr( $alt ),
			! empty( $args['class'] ) ? ' class="' . esc_attr( $args['class'] ) . '"' : '',
			$args['priority'] ? 'eager' : 'lazy',
			$args['priority'] ? ' fetchpriority="high"' : ''
		);
		return;
	}

	$base_url = $item['base_url'];
	$ratio    = $height > 0 ? ( $height / $width ) : 0.65;

	$widths  = array( 480, 768, 1080, 1440 );
	$srcset  = array();
	foreach ( $widths as $w ) {
		$h        = (int) round( $w * $ratio );
		$srcset[] = esc_url( raghushree_build_unsplash_url( $base_url, $w, $h ) ) . ' ' . $w . 'w';
	}

	$src = raghushree_build_unsplash_url( $base_url, $width, $height );

	printf(
		'<img src="%1$s" srcset="%2$s" sizes="%3$s" width="%4$d" height="%5$d" alt="%6$s"%7$s decoding="async" loading="%8$s"%9$s />',
		esc_url( $src ),
		esc_attr( implode( ', ', $srcset ) ),
		esc_attr( $args['sizes'] ),
		absint( $width ),
		absint( $height ),
		esc_attr( $alt ),
		! empty( $args['class'] ) ? ' class="' . esc_attr( $args['class'] ) . '"' : '',
		$args['priority'] ? 'eager' : 'lazy',
		$args['priority'] ? ' fetchpriority="high"' : ''
	);
}
