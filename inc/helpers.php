<?php
/**
 * Verified Company Configuration, URL Helpers & Inline SVG Icon Library.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieve verified company & contact details (editable via Theme Customizer).
 * Never fabricates missing social links or unverified claims.
 *
 * @return array<string, mixed>
 */
function raghushree_get_company_info() {
	$phone_raw    = get_theme_mod( 'raghushree_phone', '+91-830-3715-071' );
	$whatsapp_raw = get_theme_mod( 'raghushree_whatsapp', '+91-830-3715-071' );
	$phone_digits = preg_replace( '/[^0-9+]/', '', (string) $phone_raw );
	$wa_digits    = preg_replace( '/[^0-9]/', '', (string) $whatsapp_raw );

	$socials = array();
	$social_keys = array(
		'linkedin' => 'LinkedIn',
		'facebook' => 'Facebook',
		'x'        => 'X',
		'youtube'  => 'YouTube',
	);
	foreach ( $social_keys as $key => $label ) {
		$url = trim( (string) get_theme_mod( 'raghushree_social_' . $key, '' ) );
		if ( ! empty( $url ) ) {
			$socials[ $key ] = array(
				'label' => $label,
				'url'   => $url,
			);
		}
	}

	return array(
		'brand_name'     => get_theme_mod( 'raghushree_brand_name', 'Raghushree' ),
		'legal_name'     => get_theme_mod( 'raghushree_legal_name', 'Raghushree Plast Products Pvt. Ltd.' ),
		'positioning'    => get_theme_mod( 'raghushree_positioning', 'Manufacturer, Exporter & Supplier of Plastic Raw Materials' ),
		'street_address' => get_theme_mod( 'raghushree_street', '78/205, Latouche Road, General Ganj' ),
		'city'           => get_theme_mod( 'raghushree_city', 'Kanpur' ),
		'postal_code'    => get_theme_mod( 'raghushree_postal', '208001' ),
		'state'          => get_theme_mod( 'raghushree_state', 'Uttar Pradesh' ),
		'country'        => get_theme_mod( 'raghushree_country', 'India' ),
		'country_code'   => 'IN',
		'full_address'   => get_theme_mod(
			'raghushree_full_address',
			'78/205, Latouche Road, General Ganj, Kanpur-208001, U.P., India'
		),
		'email'          => sanitize_email( get_theme_mod( 'raghushree_email', 'marketing@raghushree.com' ) ),
		'phone'          => $phone_raw,
		'phone_href'     => ! empty( $phone_digits ) ? 'tel:' . $phone_digits : '',
		'whatsapp'       => $whatsapp_raw,
		'whatsapp_digits'=> $wa_digits,
		'whatsapp_url'   => ! empty( $wa_digits ) ? 'https://wa.me/' . $wa_digits : '',
		'socials'        => $socials,
	);
}

/**
 * Build a WhatsApp conversation URL with an optional pre-filled message.
 *
 * @param string $message Optional pre-filled buyer message.
 * @return string
 */
function raghushree_get_whatsapp_url( $message = '' ) {
	$info = raghushree_get_company_info();
	if ( empty( $info['whatsapp_url'] ) ) {
		return '';
	}
	if ( empty( $message ) ) {
		$message = 'Hello Raghushree team, I would like to enquire about plastic raw materials.';
	}
	return add_query_arg( 'text', rawurlencode( $message ), $info['whatsapp_url'] );
}

/**
 * Resolve a page URL dynamically by slug without hardcoding site URLs.
 *
 * @param string $slug Page slug ('about', 'contact', etc.).
 * @return string
 */
function raghushree_get_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post ) {
		return get_permalink( $page->ID );
	}
	return home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * Return the dynamic Products catalog archive URL.
 *
 * @param array<string, string> $query_args Optional filter query arguments.
 * @return string
 */
function raghushree_get_products_url( $query_args = array() ) {
	$archive_url = get_post_type_archive_link( 'products' );
	if ( ! $archive_url ) {
		$archive_url = raghushree_get_page_url( 'products' );
	}
	if ( ! empty( $query_args ) ) {
		return add_query_arg( $query_args, $archive_url );
	}
	return $archive_url;
}

/**
 * Return the Contact / Enquiry form URL with optional prefilled query parameters.
 *
 * @param array<string, string> $params Optional prefill arguments: material, enquiry_type, application.
 * @return string
 */
function raghushree_get_enquiry_url( $params = array() ) {
	$url = raghushree_get_page_url( 'contact' );
	if ( ! empty( $params ) ) {
		$url = add_query_arg( $params, $url );
	}
	return $url . '#enquiry-form';
}

/**
 * Return crisp inline SVG icons (zero CSS border triangles).
 *
 * @param string $name  Icon identifier.
 * @param string $class Optional extra CSS class.
 * @return string
 */
function raghushree_icon( $name, $class = '' ) {
	$cls  = trim( 'svg-icon ' . $class );
	$attr = 'class="' . esc_attr( $cls ) . '" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"';

	switch ( $name ) {
		case 'arrow-right':
			return '<svg ' . $attr . '><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
		case 'arrow-left':
			return '<svg ' . $attr . '><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>';
		case 'chevron-down':
			return '<svg ' . $attr . '><polyline points="6 9 12 15 18 9"/></svg>';
		case 'chevron-left':
			return '<svg ' . $attr . '><polyline points="15 18 9 12 15 6"/></svg>';
		case 'chevron-right':
			return '<svg ' . $attr . '><polyline points="9 18 15 12 9 6"/></svg>';
		case 'refresh':
			return '<svg ' . $attr . '><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>';
		case 'plus':
			return '<svg ' . $attr . '><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>';
		case 'menu':
			return '<svg ' . $attr . '><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
		case 'close':
			return '<svg ' . $attr . '><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
		case 'phone':
			return '<svg ' . $attr . '><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.58 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
		case 'mail':
			return '<svg ' . $attr . '><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>';
		case 'map-pin':
			return '<svg ' . $attr . '><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>';
		case 'whatsapp':
			return '<svg ' . $attr . '><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>';
		case 'layers':
			return '<svg ' . $attr . '><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>';
		case 'box':
			return '<svg ' . $attr . '><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>';
		case 'shield':
			return '<svg ' . $attr . '><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>';
		case 'globe':
			return '<svg ' . $attr . '><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>';
		case 'settings':
			return '<svg ' . $attr . '><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';
		case 'check':
			return '<svg ' . $attr . '><polyline points="20 6 9 17 4 12"/></svg>';
		case 'file-text':
			return '<svg ' . $attr . '><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>';
		case 'truck':
			return '<svg ' . $attr . '><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
		case 'search':
			return '<svg ' . $attr . '><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
		default:
			return '<svg ' . $attr . '><circle cx="12" cy="12" r="9"/></svg>';
	}
}
