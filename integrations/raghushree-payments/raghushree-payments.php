<?php
/**
 * Plugin Name: Raghushree Payments & Orders
 * Description: Custom Razorpay and Shiprocket order integration for the Raghushree product master.
 * Version: 0.1.0
 * Requires PHP: 8.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function rgpay_settings() {
	$value = get_option( 'raghushree_payment_settings', array() );
	return is_array( $value ) ? $value : array();
}

/** Fail closed when the theme, keys, webhook secret or pickup name are not ready. */
function rgpay_checkout_ready() {
	$s = rgpay_settings();
	$mode = isset( $s['mode'] ) ? $s['mode'] : 'test';
	$prefix = 'live' === $mode ? 'rzp_live_' : 'rzp_test_';
	return function_exists( 'raghushree_master_variations' ) && function_exists( 'raghushree_fetch_shipping_quote' )
		&& ! empty( $s['checkout_enabled'] ) && ( 'test' === $mode || ( 'live' === $mode && ! empty( $s['allow_live'] ) ) ) && ! empty( $s['razorpay_key_id'] ) && 0 === strpos( $s['razorpay_key_id'], $prefix )
		&& ! empty( $s['razorpay_key_secret'] ) && ! empty( $s['razorpay_webhook_secret'] )
		&& ! empty( $s['shiprocket_email'] ) && ! empty( $s['shiprocket_password'] ) && ! empty( $s['pickup_location'] ) && is_ssl();
}

function rgpay_register_orders() {
	register_post_type( 'rgpay_order', array( 'public' => false, 'show_ui' => false, 'supports' => array( 'title' ) ) );
}
add_action( 'init', 'rgpay_register_orders' );

function rgpay_enqueue() {
	if ( ! rgpay_checkout_ready() || ! ( is_singular( 'products' ) || is_page_template( 'page-templates/template-product-detail.php' ) ) ) { return; }
	wp_enqueue_script( 'razorpay-checkout', 'https://checkout.razorpay.com/v1/checkout.js', array(), null, true );
	wp_enqueue_script( 'raghushree-checkout', plugin_dir_url( __FILE__ ) . 'checkout.js', array( 'razorpay-checkout', 'raghushree-shipping-quote' ), '0.1.0', true );
}
add_action( 'wp_enqueue_scripts', 'rgpay_enqueue', 20 );

/** Server-side, short-lived price and package snapshot. */
function rgpay_quote_snapshot( $quote, $product_id, $option, $pin ) {
	if ( ! rgpay_checkout_ready() ) { return $quote; }
	$packs = raghushree_master_variations( $product_id );
	if ( empty( $packs[ $option ]['enabled'] ) || (int) $quote['product_paise'] !== (int) $packs[ $option ]['price_paise'] ) { return $quote; }
	try { $token = bin2hex( random_bytes( 32 ) ); } catch ( Exception $e ) { return $quote; }
	$snapshot = array( 'product_id' => $product_id, 'option' => $option, 'pin' => $pin, 'quote' => $quote, 'pack' => $packs[ $option ], 'mode' => rgpay_settings()['mode'] ?? 'test', 'pickup_pincode' => rgpay_settings()['pickup_pincode'] ?? '208001', 'pickup_location' => rgpay_settings()['pickup_location'] ?? '' );
	if ( set_transient( 'rgpay_q_' . hash( 'sha256', $token ), $snapshot, 15 * MINUTE_IN_SECONDS ) ) { $quote['checkout_token'] = $token; }
	return $quote;
}
add_filter( 'raghushree_quote_result', 'rgpay_quote_snapshot', 10, 4 );

function rgpay_error( $message, $status = 400 ) { wp_send_json_error( array( 'message' => $message ), $status ); }
function rgpay_post_value( $key ) { return isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? trim( wp_unslash( (string) $_POST[ $key ] ) ) : ''; }
function rgpay_limit( $value, $length ) { return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $length ) : substr( $value, 0, $length ); }
function rgpay_post_text( $key, $max = 100 ) { return rgpay_limit( sanitize_text_field( rgpay_post_value( $key ) ), $max ); }

/** Orders API: all amounts and details come from the saved server quote, never the browser. */
function rgpay_start_checkout() {
	check_ajax_referer( 'raghushree_shipping_quote', 'nonce' );
	if ( ! rgpay_checkout_ready() ) { rgpay_error( 'Checkout is currently unavailable.' ); }
	$token = rgpay_post_value( 'quote_token' );
	if ( ! preg_match( '/^[a-f0-9]{64}$/D', $token ) ) { rgpay_error( 'Please check shipping again.' ); }
	$hash = hash( 'sha256', $token );
	$snapshot = get_transient( 'rgpay_q_' . $hash );
	if ( ! is_array( $snapshot ) || ! isset( $snapshot['quote'], $snapshot['pack'], $snapshot['product_id'], $snapshot['option'], $snapshot['pin'], $snapshot['mode'] ) ) { rgpay_error( 'Your shipping quote expired. Please check shipping again.' ); }
	$s = rgpay_settings();
	if ( ( $s['mode'] ?? 'test' ) !== $snapshot['mode'] || ( $s['pickup_pincode'] ?? '208001' ) !== ( $snapshot['pickup_pincode'] ?? '' ) || ( $s['pickup_location'] ?? '' ) !== ( $snapshot['pickup_location'] ?? '' ) ) { rgpay_error( 'Checkout settings changed. Please check shipping again.' ); }
	$post = get_post( $snapshot['product_id'] );
	$packs = raghushree_master_variations( $snapshot['product_id'] );
	if ( ! $post || 'publish' !== $post->post_status || empty( $packs[ $snapshot['option'] ]['enabled'] ) || $packs[ $snapshot['option'] ] !== $snapshot['pack'] ) { rgpay_error( 'Product details changed. Please check shipping again.' ); }
	$customer = array(
		'first_name' => rgpay_post_text( 'first_name', 50 ), 'last_name' => rgpay_post_text( 'last_name', 50 ),
		'email' => sanitize_email( rgpay_post_value( 'email' ) ), 'phone' => rgpay_post_value( 'phone' ),
		'address' => rgpay_post_text( 'address', 100 ), 'address_2' => rgpay_post_text( 'address_2', 100 ),
		'city' => rgpay_post_text( 'city', 30 ), 'state' => rgpay_post_text( 'state', 50 ), 'pincode' => $snapshot['pin'],
	);
	if ( ! $customer['first_name'] || ! $customer['last_name'] || ! is_email( $customer['email'] ) || strlen( $customer['email'] ) > 100 || ! preg_match( '/^[6-9][0-9]{9}$/D', $customer['phone'] ) || ! $customer['address'] || ! $customer['city'] || ! $customer['state'] ) { rgpay_error( 'Please complete the delivery details, including a valid email and 10-digit Indian mobile number.' ); }
	$total = (int) $snapshot['quote']['product_paise'] + (int) $snapshot['quote']['rate_paise'];
	if ( $total < 100 || $total > 2000000000 ) { rgpay_error( 'This total cannot be paid online. Please contact us.' ); }
	// Atomic unique option consumes the token, preventing parallel double submission.
	$claim = 'rgpay_quote_' . $hash;
	if ( ! add_option( $claim, time(), '', false ) ) { rgpay_error( 'This quote has already been used. Please check shipping again.' ); }
	delete_transient( 'rgpay_q_' . $hash );
	wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'rgpay_cleanup_quote_claim', array( $claim ) );
	$order_id = wp_insert_post( array( 'post_type' => 'rgpay_order', 'post_status' => 'publish', 'post_title' => 'Pending order' ), true );
	if ( is_wp_error( $order_id ) || ! $order_id ) { rgpay_error( 'Could not create the order. Please check shipping again.', 500 ); }
	try { $receipt_token = bin2hex( random_bytes( 32 ) ); } catch ( Exception $e ) { rgpay_error( 'Could not start checkout. Please try again.', 500 ); }
	$record = array( 'snapshot' => $snapshot, 'customer' => $customer, 'total_paise' => $total, 'mode' => $snapshot['mode'], 'pickup_location' => $s['pickup_location'], 'receipt_hash' => hash( 'sha256', $receipt_token ), 'created_utc' => gmdate( 'Y-m-d H:i:s' ), 'shiprocket_reference' => gmdate( 'YmdHis' ) . '-' . $order_id );
	if ( ! add_post_meta( $order_id, '_rgpay_record', $record, true ) || ! add_post_meta( $order_id, '_rgpay_status', 'pending', true ) ) { rgpay_error( 'Could not save the order. Please contact us.', 500 ); }
	wp_update_post( array( 'ID' => $order_id, 'post_title' => 'Order #' . $order_id ) );
	$body = array( 'amount' => $total, 'currency' => 'INR', 'receipt' => 'rg-' . gmdate( 'YmdHis' ) . '-' . $order_id, 'notes' => array( 'product' => rgpay_limit( $snapshot['quote']['product_name'] . ' - ' . $snapshot['quote']['option_name'], 240 ), 'internal_order_id' => (string) $order_id ) );
	$response = wp_remote_post( 'https://api.razorpay.com/v1/orders', array( 'timeout' => 20, 'redirection' => 0, 'headers' => array( 'Content-Type' => 'application/json', 'Authorization' => 'Basic ' . base64_encode( $s['razorpay_key_id'] . ':' . $s['razorpay_key_secret'] ) ), 'body' => wp_json_encode( $body ) ) );
	if ( is_wp_error( $response ) || ! in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201 ), true ) ) { update_post_meta( $order_id, '_rgpay_status', 'order_creation_failed' ); rgpay_error( 'Could not start payment. Please check shipping again.', 502 ); }
	$rz = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $rz ) || empty( $rz['id'] ) || ! preg_match( '/^order_[a-zA-Z0-9]+$/D', $rz['id'] ) || (int) ( $rz['amount'] ?? -1 ) !== $total || ( $rz['currency'] ?? '' ) !== 'INR' ) { update_post_meta( $order_id, '_rgpay_status', 'order_creation_failed' ); rgpay_error( 'Could not start payment. Please contact us.', 502 ); }
	if ( ! add_post_meta( $order_id, '_rgpay_razorpay_order', $rz['id'], true ) ) { update_post_meta( $order_id, '_rgpay_status', 'order_creation_failed' ); rgpay_error( 'Could not save payment reference. Please contact us.', 500 ); }
	wp_send_json_success( array( 'key' => $s['razorpay_key_id'], 'razorpay_order_id' => $rz['id'], 'amount' => $total, 'internal_order_id' => $order_id, 'receipt_token' => $receipt_token, 'description' => $snapshot['quote']['product_name'] . ' — ' . $snapshot['quote']['option_name'], 'prefill' => array( 'name' => $customer['first_name'] . ' ' . $customer['last_name'], 'email' => $customer['email'], 'contact' => $customer['phone'] ) ) );
}
add_action( 'wp_ajax_rgpay_start_checkout', 'rgpay_start_checkout' );
add_action( 'wp_ajax_nopriv_rgpay_start_checkout', 'rgpay_start_checkout' );

function rgpay_find_order( $razorpay_order ) {
	$ids = get_posts( array( 'post_type' => 'rgpay_order', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => 2, 'meta_key' => '_rgpay_razorpay_order', 'meta_value' => $razorpay_order, 'no_found_rows' => true ) );
	return count( $ids ) === 1 ? (int) $ids[0] : 0;
}

/** Called only after authenticated captured/paid details match the stored order. */
function rgpay_mark_paid( $order_id, $payment_id ) {
	$existing = get_post_meta( $order_id, '_rgpay_payment_id', true );
	if ( $existing && ! hash_equals( (string) $existing, (string) $payment_id ) ) { return false; }
	if ( ! $existing ) {
		add_post_meta( $order_id, '_rgpay_payment_id', $payment_id, true );
		if ( ! hash_equals( (string) get_post_meta( $order_id, '_rgpay_payment_id', true ), (string) $payment_id ) ) { return false; }
	}
	update_post_meta( $order_id, '_rgpay_status', 'paid' );
	if ( 'paid' !== get_post_meta( $order_id, '_rgpay_status', true ) ) { return false; }
	if ( ! wp_next_scheduled( 'rgpay_fulfil_order', array( $order_id ) ) && ! wp_schedule_single_event( time() + 5, 'rgpay_fulfil_order', array( $order_id ) ) ) { return false; }
	return true;
}

function rgpay_entities_match( $record, $rz_order_id, $order, $payment ) {
	return is_array( $record ) && is_array( $order ) && is_array( $payment )
		&& ( $order['id'] ?? '' ) === $rz_order_id && ( $payment['order_id'] ?? '' ) === $rz_order_id
		&& preg_match( '/^pay_[a-zA-Z0-9]+$/D', (string) ( $payment['id'] ?? '' ) )
		&& ( $order['status'] ?? '' ) === 'paid' && ( $payment['status'] ?? '' ) === 'captured'
		&& ( $order['currency'] ?? '' ) === 'INR' && ( $payment['currency'] ?? '' ) === 'INR'
		&& (int) ( $order['amount'] ?? -1 ) === (int) $record['total_paise']
		&& (int) ( $order['amount_paid'] ?? -1 ) === (int) $record['total_paise']
		&& (int) ( $payment['amount'] ?? -1 ) === (int) $record['total_paise'];
}

/** Razorpay webhook: authenticate raw body, store paid state, queue work, respond quickly. */
function rgpay_webhook( $request ) {
	$s = rgpay_settings(); $raw = $request->get_body(); $signature = $request->get_header( 'x-razorpay-signature' );
	if ( empty( $s['razorpay_webhook_secret'] ) || ! is_string( $signature ) || ! hash_equals( hash_hmac( 'sha256', $raw, $s['razorpay_webhook_secret'] ), $signature ) ) { return new WP_REST_Response( array( 'error' => 'Invalid signature' ), 401 ); }
	$event = json_decode( $raw, true );
	if ( ! is_array( $event ) || ( $event['event'] ?? '' ) !== 'order.paid' ) { return new WP_REST_Response( array( 'ok' => true ), 200 ); }
	$order = $event['payload']['order']['entity'] ?? null; $payment = $event['payload']['payment']['entity'] ?? null;
	$rz_order = is_array( $order ) ? ( $order['id'] ?? '' ) : '';
	$id = is_string( $rz_order ) ? rgpay_find_order( $rz_order ) : 0;
	if ( ! $id ) { return new WP_REST_Response( array( 'ok' => true ), 200 ); } // Another integration's order.
	$record = get_post_meta( $id, '_rgpay_record', true );
	if ( ! is_array( $record ) || ( $record['mode'] ?? '' ) !== ( $s['mode'] ?? 'test' ) || ! rgpay_entities_match( $record, $rz_order, $order, $payment ) || ! rgpay_mark_paid( $id, $payment['id'] ) ) { return new WP_REST_Response( array( 'error' => 'Order mismatch; review required' ), 400 ); }
	return new WP_REST_Response( array( 'ok' => true ), 200 );
}
add_action( 'rest_api_init', function () { register_rest_route( 'raghushree/v1', '/razorpay-webhook', array( 'methods' => 'POST', 'callback' => 'rgpay_webhook', 'permission_callback' => '__return_true' ) ); } );

/** Browser response verification, with server-to-server status checks. */
function rgpay_verify_checkout() {
	check_ajax_referer( 'raghushree_shipping_quote', 'nonce' );
	$id = absint( rgpay_post_value( 'internal_order_id' ) ); $token = rgpay_post_value( 'receipt_token' );
	$record = get_post_meta( $id, '_rgpay_record', true ); $rz_order = get_post_meta( $id, '_rgpay_razorpay_order', true );
	$payment_id = rgpay_post_value( 'razorpay_payment_id' ); $response_order = rgpay_post_value( 'razorpay_order_id' ); $sig = rgpay_post_value( 'razorpay_signature' ); $s = rgpay_settings();
	if ( ! is_array( $record ) || ! preg_match( '/^[a-f0-9]{64}$/D', $token ) || ! hash_equals( $record['receipt_hash'], hash( 'sha256', $token ) ) || $rz_order !== $response_order || ! preg_match( '/^pay_[a-zA-Z0-9]+$/D', $payment_id ) || empty( $s['razorpay_key_secret'] ) || ( $record['mode'] ?? '' ) !== ( $s['mode'] ?? 'test' ) || ! hash_equals( hash_hmac( 'sha256', $rz_order . '|' . $payment_id, $s['razorpay_key_secret'] ), $sig ) ) { rgpay_error( 'Payment could not be verified. Please contact us with your payment reference.', 400 ); }
	$headers = array( 'Authorization' => 'Basic ' . base64_encode( $s['razorpay_key_id'] . ':' . $s['razorpay_key_secret'] ), 'Accept' => 'application/json' );
	$payment_response = wp_remote_get( 'https://api.razorpay.com/v1/payments/' . rawurlencode( $payment_id ), array( 'timeout' => 15, 'redirection' => 0, 'headers' => $headers ) );
	$order_response = wp_remote_get( 'https://api.razorpay.com/v1/orders/' . rawurlencode( $rz_order ), array( 'timeout' => 15, 'redirection' => 0, 'headers' => $headers ) );
	if ( is_wp_error( $payment_response ) || is_wp_error( $order_response ) || 200 !== wp_remote_retrieve_response_code( $payment_response ) || 200 !== wp_remote_retrieve_response_code( $order_response ) ) { wp_send_json_success( array( 'status' => 'pending', 'message' => 'Payment confirmation is pending. Do not pay again; please contact us if it does not update.' ) ); }
	$payment = json_decode( wp_remote_retrieve_body( $payment_response ), true ); $order = json_decode( wp_remote_retrieve_body( $order_response ), true );
	if ( ! rgpay_entities_match( $record, $rz_order, $order, $payment ) || ( $payment['id'] ?? '' ) !== $payment_id ) { wp_send_json_success( array( 'status' => 'pending', 'message' => 'Payment confirmation is pending. Do not pay again; please contact us if it does not update.' ) ); }
	if ( ! rgpay_mark_paid( $id, $payment_id ) ) { rgpay_error( 'Payment needs manual review. Please contact us.', 400 ); }
	wp_send_json_success( array( 'status' => 'paid', 'message' => 'test' === $record['mode'] ? 'Test payment confirmed. No real Shiprocket order is created in test mode.' : 'Payment confirmed. Your order is being processed.' ) );
}
add_action( 'wp_ajax_rgpay_verify_checkout', 'rgpay_verify_checkout' );
add_action( 'wp_ajax_nopriv_rgpay_verify_checkout', 'rgpay_verify_checkout' );

/** Never automatically repeat a create request once it might have reached Shiprocket. */
function rgpay_fulfil_order( $order_id ) {
	$order_id = absint( $order_id );
	$record = get_post_meta( $order_id, '_rgpay_record', true );
	if ( ! is_array( $record ) || 'paid' !== get_post_meta( $order_id, '_rgpay_status', true ) ) { return; }
	$lock = 'rgpay_ship_' . $order_id;
	if ( ! add_option( $lock, gmdate( 'c' ), '', false ) ) { return; }
	if ( 'test' === $record['mode'] ) {
		update_post_meta( $order_id, '_rgpay_fulfilment', 'test_simulated' );
		return; // Razorpay test payments must never create real Shiprocket orders.
	}
	$s = rgpay_settings();
	if ( ( $s['mode'] ?? 'test' ) !== 'live' || empty( $s['shiprocket_email'] ) || empty( $s['shiprocket_password'] ) || empty( $record['pickup_location'] ) || empty( $record['shiprocket_reference'] ) ) {
		update_post_meta( $order_id, '_rgpay_fulfilment', 'configuration_error' );
		delete_option( $lock );
		wp_mail( get_option( 'admin_email' ), 'Paid order needs attention', 'Order #' . $order_id . ' is paid but Shiprocket configuration is missing or the mode changed. Check the Orders screen.' );
		return;
	}
	$auth = wp_remote_post( 'https://apiv2.shiprocket.in/v1/external/auth/login', array( 'timeout' => 15, 'redirection' => 0, 'headers' => array( 'Content-Type' => 'application/json' ), 'body' => wp_json_encode( array( 'email' => $s['shiprocket_email'], 'password' => $s['shiprocket_password'] ) ) ) );
	$auth_body = ! is_wp_error( $auth ) && 200 === wp_remote_retrieve_response_code( $auth ) ? json_decode( wp_remote_retrieve_body( $auth ), true ) : null;
	if ( ! is_array( $auth_body ) || empty( $auth_body['token'] ) || ! is_string( $auth_body['token'] ) ) {
		update_post_meta( $order_id, '_rgpay_fulfilment', 'authentication_error' );
		delete_option( $lock ); // No create request was sent, so an admin may safely retry.
		wp_mail( get_option( 'admin_email' ), 'Paid order needs attention', 'Order #' . $order_id . ' is paid but Shiprocket authentication failed. Check the Orders screen.' );
		return;
	}
	$c = $record['customer']; $snapshot = $record['snapshot']; $pack = $snapshot['pack']; $quote = $snapshot['quote'];
	$payload = array(
		'order_id' => $record['shiprocket_reference'], 'order_date' => get_date_from_gmt( $record['created_utc'], 'Y-m-d H:i' ), 'pickup_location' => $record['pickup_location'],
		'billing_customer_name' => $c['first_name'], 'billing_last_name' => $c['last_name'], 'billing_address' => $c['address'], 'billing_address_2' => $c['address_2'],
		'billing_city' => $c['city'], 'billing_state' => $c['state'], 'billing_pincode' => $c['pincode'], 'billing_country' => 'India', 'billing_email' => $c['email'], 'billing_phone' => $c['phone'], 'shipping_is_billing' => true,
		'order_items' => array( array( 'name' => rgpay_limit( $quote['product_name'] . ' - ' . $quote['option_name'], 120 ), 'sku' => 'RG-' . $snapshot['product_id'] . '-' . $snapshot['option'], 'units' => 1, 'selling_price' => (int) $quote['product_paise'] / 100 ) ),
		'payment_method' => 'Prepaid', 'shipping_charges' => (int) $quote['rate_paise'] / 100, 'sub_total' => (int) $quote['product_paise'] / 100,
		'length' => (float) $pack['length_cm'], 'breadth' => (float) $pack['width_cm'], 'height' => (float) $pack['height_cm'], 'weight' => (float) $pack['weight_kg'],
	);
	// Persist the attempt before sending; a timeout or crash requires manual reconciliation.
	update_post_meta( $order_id, '_rgpay_fulfilment', 'sending_check_shiprocket' );
	if ( 'sending_check_shiprocket' !== get_post_meta( $order_id, '_rgpay_fulfilment', true ) ) { wp_mail( get_option( 'admin_email' ), 'Paid order needs attention', 'Could not record the Shiprocket send attempt for order #' . $order_id . '. No request was sent. Check the Orders screen.' ); return; }
	$response = wp_remote_post( 'https://apiv2.shiprocket.in/v1/external/orders/create/adhoc', array( 'timeout' => 25, 'redirection' => 0, 'headers' => array( 'Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $auth_body['token'] ), 'body' => wp_json_encode( $payload ) ) );
	$result = ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), array( 200, 201 ), true ) ? json_decode( wp_remote_retrieve_body( $response ), true ) : null;
	if ( is_array( $result ) && ! empty( $result['order_id'] ) && ! empty( $result['shipment_id'] ) ) {
		update_post_meta( $order_id, '_rgpay_shiprocket_order', sanitize_text_field( (string) $result['order_id'] ) );
		update_post_meta( $order_id, '_rgpay_shiprocket_shipment', sanitize_text_field( (string) $result['shipment_id'] ) );
		update_post_meta( $order_id, '_rgpay_fulfilment', 'created' );
		return; // The merchant processes courier selection, AWB and pickup in Shiprocket.
	}
	update_post_meta( $order_id, '_rgpay_fulfilment', 'uncertain_check_shiprocket' );
	wp_mail( get_option( 'admin_email' ), 'Paid order needs Shiprocket review', 'Order #' . $order_id . ' is paid but the Shiprocket create response was not confirmed. Check Shiprocket for reference ' . $record['shiprocket_reference'] . ' before taking any action; do not blindly retry.' );
}
add_action( 'rgpay_fulfil_order', 'rgpay_fulfil_order' );

function rgpay_admin_menu() {
	add_submenu_page( 'edit.php?post_type=products', 'Payment Orders', 'Payment Orders', 'manage_options', 'rgpay-orders', 'rgpay_orders_screen' );
}
add_action( 'admin_menu', 'rgpay_admin_menu' );

function rgpay_admin_process() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Permission denied.' ); }
	$id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
	check_admin_referer( 'rgpay_process_' . $id );
	if ( get_post_type( $id ) === 'rgpay_order' && get_post_meta( $id, '_rgpay_status', true ) === 'paid' ) { rgpay_fulfil_order( $id ); }
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'rgpay-orders' ), admin_url( 'edit.php' ) ) ); exit;
}
add_action( 'admin_post_rgpay_process', 'rgpay_admin_process' );

function rgpay_orders_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$orders = get_posts( array( 'post_type' => 'rgpay_order', 'post_status' => 'publish', 'posts_per_page' => 100, 'orderby' => 'ID', 'order' => 'DESC' ) );
	?><div class="wrap"><h1>Payment Orders</h1><p>Test payments do not create real Shiprocket orders. Live paid orders are sent to Shiprocket once; courier selection and pickup are handled there. If a send is uncertain, check Shiprocket before creating anything manually. Configure a real server cron for WordPress scheduled events.</p><table class="widefat striped"><thead><tr><th>Order</th><th>Customer / product</th><th>Payment</th><th>Shiprocket</th><th>Action</th></tr></thead><tbody><?php
	foreach ( $orders as $order ) {
		$id = $order->ID; $r = get_post_meta( $id, '_rgpay_record', true );
		if ( ! is_array( $r ) ) { continue; }
		$c = $r['customer']; $q = $r['snapshot']['quote']; $fulfil = get_post_meta( $id, '_rgpay_fulfilment', true ); $locked = false !== get_option( 'rgpay_ship_' . $id, false );
		?><tr><td>#<?php echo esc_html( $id ); ?><br><?php echo esc_html( $r['mode'] ); ?><br><?php echo esc_html( $r['created_utc'] ); ?> UTC</td><td><?php echo esc_html( $c['first_name'] . ' ' . $c['last_name'] ); ?><br><?php echo esc_html( $c['email'] . ' / ' . $c['phone'] ); ?><br><?php echo esc_html( $c['address'] . ', ' . $c['address_2'] . ', ' . $c['city'] . ', ' . $c['state'] . ' ' . $c['pincode'] ); ?><br><?php echo esc_html( $q['product_name'] . ' — ' . $q['option_name'] ); ?></td><td><?php echo esc_html( get_post_meta( $id, '_rgpay_status', true ) ); ?><br>₹<?php echo esc_html( number_format( $r['total_paise'] / 100, 2 ) ); ?><br><?php echo esc_html( get_post_meta( $id, '_rgpay_razorpay_order', true ) ); ?><br><?php echo esc_html( get_post_meta( $id, '_rgpay_payment_id', true ) ); ?></td><td><?php echo esc_html( $fulfil ? $fulfil : ( $locked ? 'locked — check Shiprocket before action' : 'not yet sent' ) ); ?><br><?php echo esc_html( 'Reference: ' . ( $r['shiprocket_reference'] ?? '' ) ); ?><br><?php echo esc_html( get_post_meta( $id, '_rgpay_shiprocket_order', true ) ); ?><br><?php echo esc_html( get_post_meta( $id, '_rgpay_shiprocket_shipment', true ) ); ?><br><?php echo esc_html( 'Quoted: ' . $q['courier_name'] . ' / ₹' . number_format( $q['rate_paise'] / 100, 2 ) ); ?></td><td><?php if ( 'paid' === get_post_meta( $id, '_rgpay_status', true ) && ! $locked && in_array( $fulfil, array( '', 'configuration_error', 'authentication_error' ), true ) ) : ?><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="rgpay_process"><input type="hidden" name="order_id" value="<?php echo esc_attr( $id ); ?>"><?php wp_nonce_field( 'rgpay_process_' . $id ); ?><button type="submit" class="button">Process paid order</button></form><?php endif; ?></td></tr><?php
	}
	?></tbody></table></div><?php
}

add_action( 'rgpay_cleanup_quote_claim', function ( $claim ) { if ( is_string( $claim ) && preg_match( '/^rgpay_quote_[a-f0-9]{64}$/D', $claim ) ) { delete_option( $claim ); } } );
