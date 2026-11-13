<?php
/** Shiprocket recommended-courier quotes. This does not accept payments. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Read only a rate for the explicitly recommended courier; never silently choose another. */
function raghushree_recommended_shipping_rate( $data ) {
	if ( ! is_array( $data ) || empty( $data['recommended_courier_company_id'] ) || ! is_scalar( $data['recommended_courier_company_id'] ) || empty( $data['available_courier_companies'] ) || ! is_array( $data['available_courier_companies'] ) ) { return false; }
	$recommended_id = (string) $data['recommended_courier_company_id'];
	foreach ( $data['available_courier_companies'] as $courier ) {
		if ( ! is_array( $courier ) || ! isset( $courier['courier_company_id'], $courier['rate'] ) || ! is_scalar( $courier['courier_company_id'] ) || (string) $courier['courier_company_id'] !== $recommended_id || ! is_numeric( $courier['rate'] ) ) { continue; }
		$rate = (float) $courier['rate'];
		if ( ! is_finite( $rate ) || $rate < 0.005 || $rate > 10000000 ) { return false; }
		return array( 'courier_id' => $recommended_id, 'courier_name' => isset( $courier['courier_name'] ) && is_scalar( $courier['courier_name'] ) ? sanitize_text_field( (string) $courier['courier_name'] ) : '', 'rate_paise' => (int) round( $rate * 100 ) );
	}
	return false;
}

/**
 * Authenticate with the documented Shiprocket API. Error data is restricted to an
 * HTTP status so administrators can troubleshoot without exposing response bodies.
 */
function raghushree_shiprocket_authenticate( $settings ) {
	if ( ! is_array( $settings ) || empty( $settings['shiprocket_email'] ) || empty( $settings['shiprocket_password'] ) ) {
		return new WP_Error( 'config', __( 'Shipping quotes are not configured. Please contact us.', 'raghushree' ) );
	}
	$response = wp_remote_post( 'https://apiv2.shiprocket.in/v1/external/auth/login', array( 'timeout' => 15, 'redirection' => 0, 'headers' => array( 'Content-Type' => 'application/json', 'Accept' => 'application/json' ), 'body' => wp_json_encode( array( 'email' => $settings['shiprocket_email'], 'password' => $settings['shiprocket_password'] ) ) ) );
	if ( is_wp_error( $response ) ) { return new WP_Error( 'auth_network', __( 'Shipping rates are temporarily unavailable. Please try again later.', 'raghushree' ) ); }
	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status ) { return new WP_Error( 'auth_http', __( 'Shipping rates are temporarily unavailable. Please try again later.', 'raghushree' ), $status ); }
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || empty( $body['token'] ) || ! is_string( $body['token'] ) ) { return new WP_Error( 'auth_response', __( 'Shipping rates are temporarily unavailable. Please try again later.', 'raghushree' ) ); }
	return $body['token'];
}

/** Fetch a quote using stored credentials and a validated product snapshot. */
function raghushree_fetch_shipping_quote( $product_id, $option, $destination ) {
	$post = get_post( $product_id );
	if ( ! $post || 'publish' !== $post->post_status || ! in_array( $post->post_type, array( 'products', 'page' ), true ) || ( 'page' === $post->post_type && 'page-templates/template-product-detail.php' !== get_page_template_slug( $post ) ) ) { return new WP_Error( 'product', __( 'This product is not available.', 'raghushree' ) ); }
	$variations = raghushree_master_variations( $product_id );
	if ( ! isset( $variations[ $option ] ) || empty( $variations[ $option ]['enabled'] ) ) { return new WP_Error( 'variation', __( 'This option is not available.', 'raghushree' ) ); }
	if ( ! preg_match( '/^[1-9][0-9]{5}$/D', $destination ) ) { return new WP_Error( 'pincode', __( 'Enter a valid six-digit delivery PIN code.', 'raghushree' ) ); }
	$pack = $variations[ $option ];
	foreach ( raghushree_master_shipping_fields() as $field => $label ) {
		if ( ! isset( $pack[ $field ] ) || ! is_numeric( $pack[ $field ] ) || (float) $pack[ $field ] <= 0 || (float) $pack[ $field ] > 99999 ) { return new WP_Error( 'package', __( 'Shipping details are not yet configured for this option. Please contact us.', 'raghushree' ) ); }
	}
	$settings = get_option( 'raghushree_payment_settings', array() );
	if ( ! is_array( $settings ) || empty( $settings['shiprocket_email'] ) || empty( $settings['shiprocket_password'] ) ) { return new WP_Error( 'config', __( 'Shipping quotes are not configured. Please contact us.', 'raghushree' ) ); }
	$pickup = isset( $settings['pickup_pincode'] ) ? $settings['pickup_pincode'] : '208001';
	if ( ! preg_match( '/^[1-9][0-9]{5}$/D', $pickup ) ) { return new WP_Error( 'pickup', __( 'Pickup details are not configured. Please contact us.', 'raghushree' ) ); }
	$token = raghushree_shiprocket_authenticate( $settings );
	if ( is_wp_error( $token ) ) { return $token; }
	$query = array(
		'pickup_postcode' => $pickup, 'delivery_postcode' => $destination, 'cod' => 0,
		'weight' => $pack['weight_kg'], 'length' => $pack['length_cm'], 'breadth' => $pack['width_cm'], 'height' => $pack['height_cm'],
		'declared_value' => number_format( (int) $pack['price_paise'] / 100, 2, '.', '' ),
	);
	$response = wp_remote_get( add_query_arg( $query, 'https://apiv2.shiprocket.in/v1/external/courier/serviceability/' ), array( 'timeout' => 15, 'redirection' => 0, 'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json' ) ) );
	if ( is_wp_error( $response ) ) { return new WP_Error( 'courier_network', __( 'Shipping rates are temporarily unavailable. Please try again later.', 'raghushree' ) ); }
	$status = (int) wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status ) { return new WP_Error( 'courier_http', __( 'Shipping rates are temporarily unavailable. Please try again later.', 'raghushree' ), $status ); }
	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || ! isset( $body['data'] ) || ! is_array( $body['data'] ) ) { return new WP_Error( 'courier_response', __( 'Shipping rates are temporarily unavailable. Please try again later.', 'raghushree' ) ); }
	$rate = raghushree_recommended_shipping_rate( isset( $body['data'] ) ? $body['data'] : null );
	if ( false === $rate ) { return new WP_Error( 'no_rate', __( 'No recommended prepaid shipping rate is available for this PIN code. Please contact us.', 'raghushree' ) ); }
	return array( 'rate_paise' => $rate['rate_paise'], 'courier_name' => $rate['courier_name'], 'courier_id' => $rate['courier_id'], 'product_paise' => (int) $pack['price_paise'], 'product_name' => get_the_title( $post ), 'option_name' => $pack['label'] );
}

function raghushree_ajax_shipping_quote() {
	check_ajax_referer( 'raghushree_shipping_quote', 'nonce' );
	// A modest per-client throttle; a production deployment should also use edge rate limiting.
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'rsq_' . substr( hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) ), 0, 32 );
	$count = (int) get_transient( $key );
	if ( $count >= 20 ) { wp_send_json_error( array( 'message' => __( 'Too many requests. Please try again later.', 'raghushree' ) ), 429 ); }
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
	$id = isset( $_POST['product_id'] ) && is_scalar( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$option = isset( $_POST['option'] ) && is_scalar( $_POST['option'] ) ? sanitize_key( wp_unslash( $_POST['option'] ) ) : '';
	$pin = isset( $_POST['pincode'] ) && is_scalar( $_POST['pincode'] ) ? trim( wp_unslash( $_POST['pincode'] ) ) : '';
	$quote = raghushree_fetch_shipping_quote( $id, $option, $pin );
	if ( is_wp_error( $quote ) ) { wp_send_json_error( array( 'message' => $quote->get_error_message() ), 400 ); }
	$quote = apply_filters( 'raghushree_quote_result', $quote, $id, $option, $pin );
	wp_send_json_success( $quote );
}
add_action( 'wp_ajax_raghushree_shipping_quote', 'raghushree_ajax_shipping_quote' );
add_action( 'wp_ajax_nopriv_raghushree_shipping_quote', 'raghushree_ajax_shipping_quote' );
