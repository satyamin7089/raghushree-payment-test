<?php
/**
 * Plugin Name: Raghushree Payments & Orders
 * Description: Razorpay checkout with Shiprocket rate quotes and manually managed WordPress orders.
 * Version: 0.2.0
 * Requires PHP: 8.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'RGPAY_RATES_ONLY_VERSION', '0.2.0' );

function rgpay_settings() {
	$value = get_option( 'raghushree_payment_settings', array() );
	return is_array( $value ) ? $value : array();
}

/** Fail closed when the theme, payment keys, webhook secret or shipping credentials are not ready. */
function rgpay_checkout_ready() {
	$s = rgpay_settings();
	$mode = isset( $s['mode'] ) ? $s['mode'] : 'test';
	$prefix = 'live' === $mode ? 'rzp_live_' : 'rzp_test_';
	return function_exists( 'raghushree_rates_only_theme_ready' ) && function_exists( 'raghushree_master_variations' ) && function_exists( 'raghushree_fetch_shipping_quote' )
		&& ! empty( $s['checkout_enabled'] ) && ( 'test' === $mode || ( 'live' === $mode && ! empty( $s['allow_live'] ) ) ) && ! empty( $s['razorpay_key_id'] ) && 0 === strpos( $s['razorpay_key_id'], $prefix )
		&& ! empty( $s['razorpay_key_secret'] ) && ! empty( $s['razorpay_webhook_secret'] )
		&& ! empty( $s['shiprocket_email'] ) && ! empty( $s['shiprocket_password'] ) && is_ssl();
}

function rgpay_register_orders() {
	register_post_type( 'rgpay_order', array( 'public' => false, 'show_ui' => false, 'supports' => array( 'title' ) ) );
}
add_action( 'init', 'rgpay_register_orders' );

function rgpay_incompatible_theme_notice() {
	if ( ! current_user_can( 'manage_options' ) || function_exists( 'raghushree_rates_only_theme_ready' ) ) { return; }
	?><div class="notice notice-error"><p>Update the Raghushree theme to the matching rates-only version. Checkout is disabled until both theme and plugin are updated.</p></div><?php
}
add_action( 'admin_notices', 'rgpay_incompatible_theme_notice' );

function rgpay_enqueue() {
	if ( ! rgpay_checkout_ready() || ! ( is_singular( 'products' ) || is_page_template( 'page-templates/template-product-detail.php' ) ) ) { return; }
	wp_enqueue_script( 'razorpay-checkout', 'https://checkout.razorpay.com/v1/checkout.js', array(), null, true );
	wp_enqueue_script( 'raghushree-checkout', plugin_dir_url( __FILE__ ) . 'checkout.js', array( 'razorpay-checkout', 'raghushree-shipping-quote' ), '0.2.0', true );
}
add_action( 'wp_enqueue_scripts', 'rgpay_enqueue', 20 );

/** Server-side, short-lived price and package snapshot. */
function rgpay_quote_snapshot( $quote, $product_id, $option, $pin ) {
	if ( ! rgpay_checkout_ready() ) { return $quote; }
	$packs = raghushree_master_variations( $product_id );
	if ( empty( $packs[ $option ]['enabled'] ) || (int) $quote['product_paise'] !== (int) $packs[ $option ]['price_paise'] ) { return $quote; }
	try { $token = bin2hex( random_bytes( 32 ) ); } catch ( Exception $e ) { return $quote; }
	$snapshot = array( 'product_id' => $product_id, 'option' => $option, 'pin' => $pin, 'quote' => $quote, 'pack' => $packs[ $option ], 'mode' => rgpay_settings()['mode'] ?? 'test', 'pickup_pincode' => rgpay_settings()['pickup_pincode'] ?? '208001', 'quoted_utc' => gmdate( 'Y-m-d H:i:s' ) );
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
	if ( ( $s['mode'] ?? 'test' ) !== $snapshot['mode'] || ( $s['pickup_pincode'] ?? '208001' ) !== ( $snapshot['pickup_pincode'] ?? '' ) ) { rgpay_error( 'Checkout settings changed. Please check shipping again.' ); }
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
	$record = array( 'snapshot' => $snapshot, 'customer' => $customer, 'total_paise' => $total, 'mode' => $snapshot['mode'], 'receipt_hash' => hash( 'sha256', $receipt_token ), 'created_utc' => gmdate( 'Y-m-d H:i:s' ) );
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

/** Razorpay webhook: authenticate raw body and store paid state for manual fulfilment. */
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
	wp_send_json_success( array( 'status' => 'paid', 'message' => 'test' === $record['mode'] ? 'Test payment confirmed. This is a test order; no real shipment will be booked.' : 'Payment confirmed. Your order will be fulfilled manually.' ) );
}
add_action( 'wp_ajax_rgpay_verify_checkout', 'rgpay_verify_checkout' );
add_action( 'wp_ajax_nopriv_rgpay_verify_checkout', 'rgpay_verify_checkout' );

/**
 * No fulfilment worker is registered: Shiprocket is used only for quotes.
 * Old scheduled rgpay_fulfil_order events therefore cannot create orders after upgrade.
 * Existing records/Shiprocket IDs are retained and shown for manual reconciliation.
 */
function rgpay_admin_menu() {
	add_submenu_page( 'edit.php?post_type=products', 'Payment Orders', 'Payment Orders', 'manage_options', 'rgpay-orders', 'rgpay_orders_screen' );
}
add_action( 'admin_menu', 'rgpay_admin_menu' );

/** Manual delivery state is independent of payment state and never calls Shiprocket. */
function rgpay_set_manual_delivery( $id, $status, $courier, $tracking ) {
	if ( get_post_type( $id ) !== 'rgpay_order' || 'paid' !== get_post_meta( $id, '_rgpay_status', true ) || ! in_array( $status, array( 'awaiting', 'fulfilled' ), true ) ) { return false; }
	update_post_meta( $id, '_rgpay_manual_courier', rgpay_limit( sanitize_text_field( $courier ), 100 ) );
	update_post_meta( $id, '_rgpay_manual_tracking', rgpay_limit( sanitize_text_field( $tracking ), 120 ) );
	update_post_meta( $id, '_rgpay_manual_status', $status );
	return get_post_meta( $id, '_rgpay_manual_status', true ) === $status;
}

function rgpay_admin_update_delivery() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Permission denied.' ); }
	$id = absint( rgpay_post_value( 'order_id' ) );
	check_admin_referer( 'rgpay_delivery_' . $id );
	$ok = rgpay_set_manual_delivery( $id, rgpay_post_value( 'delivery_status' ), rgpay_post_value( 'delivery_courier' ), rgpay_post_value( 'delivery_tracking' ) );
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'rgpay-orders', 'delivery_result' => $ok ? 'saved' : 'error' ), admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_rgpay_update_delivery', 'rgpay_admin_update_delivery' );

function rgpay_orders_screen() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$page = isset( $_GET['order_page'] ) && is_scalar( $_GET['order_page'] ) ? max( 1, absint( $_GET['order_page'] ) ) : 1;
	$query = new WP_Query( array( 'post_type' => 'rgpay_order', 'post_status' => 'publish', 'posts_per_page' => 25, 'paged' => $page, 'orderby' => 'ID', 'order' => 'DESC' ) );
	$result = isset( $_GET['delivery_result'] ) && is_scalar( $_GET['delivery_result'] ) ? sanitize_key( wp_unslash( $_GET['delivery_result'] ) ) : '';
	?>
	<div class="wrap"><h1>Payment Orders</h1>
	<p>Shiprocket is used for shipping quotes only. No orders, courier bookings or pickups are sent to Shiprocket by this version. Only fulfil orders marked paid; test orders are not real payments.</p>
	<div class="notice notice-warning inline"><p>Orders from an older version may already have been sent to Shiprocket, or the outcome may be uncertain. Check the legacy status and Shiprocket before creating a manual shipment, to avoid duplicates.</p></div>
	<?php if ( 'saved' === $result ) : ?><div class="notice notice-success"><p>Manual delivery details saved.</p></div><?php elseif ( 'error' === $result ) : ?><div class="notice notice-error"><p>Could not update delivery. Only paid orders can be updated.</p></div><?php endif; ?>
	<div style="overflow-x:auto"><table class="widefat striped"><thead><tr><th>Order</th><th>Customer / product</th><th>Payment</th><th>Shipping quote</th><th>Manual delivery</th></tr></thead><tbody>
	<?php foreach ( $query->posts as $order ) :
		$id = $order->ID;
		$r = get_post_meta( $id, '_rgpay_record', true );
		if ( ! is_array( $r ) || empty( $r['customer'] ) || empty( $r['snapshot']['quote'] ) ) { continue; }
		$c = $r['customer']; $q = $r['snapshot']['quote']; $pack = $r['snapshot']['pack'] ?? array();
		$payment_status = get_post_meta( $id, '_rgpay_status', true );
		$manual_status = get_post_meta( $id, '_rgpay_manual_status', true );
		if ( ! $manual_status ) { $manual_status = 'awaiting'; }
		$legacy = get_post_meta( $id, '_rgpay_fulfilment', true );
		$legacy_id = get_post_meta( $id, '_rgpay_shiprocket_order', true );
		$legacy_shipment = get_post_meta( $id, '_rgpay_shiprocket_shipment', true );
		$legacy_lock = false !== get_option( 'rgpay_ship_' . $id, false );
		?>
		<tr>
		<td><strong>#<?php echo esc_html( $id ); ?></strong><br><?php echo esc_html( strtoupper( $r['mode'] ?? '' ) ); ?><br><?php echo esc_html( $r['created_utc'] ?? '' ); ?> UTC</td>
		<td><?php echo esc_html( ( $c['first_name'] ?? '' ) . ' ' . ( $c['last_name'] ?? '' ) ); ?><br><?php echo esc_html( ( $c['email'] ?? '' ) . ' / ' . ( $c['phone'] ?? '' ) ); ?><br><?php echo esc_html( ( $c['address'] ?? '' ) . ', ' . ( $c['address_2'] ?? '' ) . ', ' . ( $c['city'] ?? '' ) . ', ' . ( $c['state'] ?? '' ) . ' ' . ( $c['pincode'] ?? '' ) ); ?><br><strong><?php echo esc_html( ( $q['product_name'] ?? '' ) . ' — ' . ( $q['option_name'] ?? '' ) ); ?></strong><br><?php echo esc_html( sprintf( 'Packed: %s kg; %s × %s × %s cm', $pack['weight_kg'] ?? '—', $pack['length_cm'] ?? '—', $pack['width_cm'] ?? '—', $pack['height_cm'] ?? '—' ) ); ?></td>
		<td><strong><?php echo esc_html( $payment_status ); ?></strong><br><?php echo esc_html( sprintf( 'Product: ₹%s', number_format( (int) ( $q['product_paise'] ?? 0 ) / 100, 2 ) ) ); ?><br><?php echo esc_html( sprintf( 'Shipping: ₹%s', number_format( (int) ( $q['rate_paise'] ?? 0 ) / 100, 2 ) ) ); ?><br><?php echo esc_html( sprintf( 'Total: ₹%s', number_format( (int) ( $r['total_paise'] ?? 0 ) / 100, 2 ) ) ); ?><br><?php echo esc_html( get_post_meta( $id, '_rgpay_razorpay_order', true ) ); ?><br><?php echo esc_html( get_post_meta( $id, '_rgpay_payment_id', true ) ); ?></td>
		<td><?php echo esc_html( $q['courier_name'] ?? '—' ); ?><br><?php echo esc_html( 'Courier ID: ' . ( $q['courier_id'] ?? '—' ) ); ?><br><?php echo esc_html( 'Pickup PIN: ' . ( $r['snapshot']['pickup_pincode'] ?? '—' ) ); ?><br><?php echo esc_html( 'Quoted UTC: ' . ( $r['snapshot']['quoted_utc'] ?? 'Not recorded' ) ); ?><?php if ( $legacy || $legacy_id || $legacy_shipment || $legacy_lock ) : ?><hr><strong>Previous Shiprocket integration — check before dispatch:</strong><br><?php echo esc_html( 'Status: ' . ( $legacy ? $legacy : 'unknown' ) ); ?><br><?php echo esc_html( 'Reference: ' . ( $r['shiprocket_reference'] ?? '—' ) ); ?><br><?php echo esc_html( 'Shiprocket order: ' . ( $legacy_id ? $legacy_id : '—' ) ); ?><br><?php echo esc_html( 'Shipment: ' . ( $legacy_shipment ? $legacy_shipment : '—' ) ); ?><?php endif; ?></td>
		<td><?php if ( 'paid' === $payment_status ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="rgpay_update_delivery"><input type="hidden" name="order_id" value="<?php echo esc_attr( $id ); ?>"><?php wp_nonce_field( 'rgpay_delivery_' . $id ); ?>
			<label for="delivery-status-<?php echo esc_attr( $id ); ?>">Status</label><br><select id="delivery-status-<?php echo esc_attr( $id ); ?>" name="delivery_status"><option value="awaiting" <?php selected( $manual_status, 'awaiting' ); ?>>Awaiting manual fulfilment</option><option value="fulfilled" <?php selected( $manual_status, 'fulfilled' ); ?>>Fulfilled manually</option></select><br>
			<label for="delivery-courier-<?php echo esc_attr( $id ); ?>">Courier (optional)</label><br><input id="delivery-courier-<?php echo esc_attr( $id ); ?>" name="delivery_courier" maxlength="100" value="<?php echo esc_attr( get_post_meta( $id, '_rgpay_manual_courier', true ) ); ?>"><br>
			<label for="delivery-tracking-<?php echo esc_attr( $id ); ?>">Tracking reference (optional)</label><br><input id="delivery-tracking-<?php echo esc_attr( $id ); ?>" name="delivery_tracking" maxlength="120" value="<?php echo esc_attr( get_post_meta( $id, '_rgpay_manual_tracking', true ) ); ?>"><br><button class="button" type="submit">Save delivery</button>
		</form><?php else : ?>Not available until payment is confirmed.<?php endif; ?></td>
		</tr>
	<?php endforeach; ?>
	<?php if ( empty( $query->posts ) ) : ?><tr><td colspan="5">No orders found.</td></tr><?php endif; ?>
	</tbody></table></div>
	<?php if ( $query->max_num_pages > 1 ) : ?><div class="tablenav"><div class="tablenav-pages"><?php echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( array( 'post_type' => 'products', 'page' => 'rgpay-orders', 'order_page' => '%#%' ), admin_url( 'edit.php' ) ), 'format' => '', 'current' => $page, 'total' => $query->max_num_pages ) ) ); ?></div></div><?php endif; ?>
	</div><?php
}

add_action( 'rgpay_cleanup_quote_claim', function ( $claim ) { if ( is_string( $claim ) && preg_match( '/^rgpay_quote_[a-f0-9]{64}$/D', $claim ) ) { delete_option( $claim ); } } );
