<?php
/** Admin-only payment and shipping settings and diagnostics. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function raghushree_payment_settings_menu() {
	add_submenu_page( 'edit.php?post_type=products', __( 'Payment & Shipping Setup', 'raghushree' ), __( 'Payment & Shipping Setup', 'raghushree' ), 'manage_options', 'raghushree-payment-setup', 'raghushree_payment_settings_render' );
}
add_action( 'admin_menu', 'raghushree_payment_settings_menu' );

function raghushree_payment_settings_fields() {
	return array(
		'razorpay_key_id' => __( 'Razorpay Key ID', 'raghushree' ),
		'razorpay_key_secret' => __( 'Razorpay Key Secret', 'raghushree' ),
		'razorpay_webhook_secret' => __( 'Razorpay Webhook Secret', 'raghushree' ),
		'shiprocket_email' => __( 'Shiprocket API user email', 'raghushree' ),
		'shiprocket_password' => __( 'Shiprocket API user password', 'raghushree' ),
	);
}

function raghushree_payment_settings_save() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Permission denied.', 'raghushree' ) ); }
	check_admin_referer( 'raghushree_payment_settings_save' );
	$old = get_option( 'raghushree_payment_settings', array() );
	$settings = is_array( $old ) ? $old : array();
	$settings['mode'] = isset( $_POST['mode'] ) && 'live' === $_POST['mode'] ? 'live' : 'test';
	$pickup_pincode = isset( $_POST['pickup_pincode'] ) && is_scalar( $_POST['pickup_pincode'] ) ? trim( wp_unslash( $_POST['pickup_pincode'] ) ) : '';
	if ( ! preg_match( '/^[1-9][0-9]{5}$/D', $pickup_pincode ) ) { wp_die( esc_html__( 'Please enter a valid six-digit pickup PIN code.', 'raghushree' ), 400 ); }
	$settings['pickup_pincode'] = $pickup_pincode;
	$settings['checkout_enabled'] = isset( $_POST['checkout_enabled'] );
	$settings['allow_live'] = isset( $_POST['allow_live'] );
	$settings['allow_tax_live'] = isset( $_POST['allow_tax_live'] );
	foreach ( raghushree_payment_settings_fields() as $key => $label ) {
		if ( isset( $_POST['clear'][ $key ] ) ) { unset( $settings[ $key ] ); continue; }
		$value = isset( $_POST[ $key ] ) && is_scalar( $_POST[ $key ] ) ? trim( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( '' !== $value && strlen( $value ) <= 512 && ! preg_match( '/[\x00-\x1F\x7F]/', $value ) ) {
			$settings[ $key ] = $value;
		}
	}
	// Keep credentials out of the autoloaded options cache; they remain in the database.
	if ( false === get_option( 'raghushree_payment_settings', false ) ) { add_option( 'raghushree_payment_settings', $settings, '', false ); }
	else { update_option( 'raghushree_payment_settings', $settings, false ); }
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'raghushree-payment-setup', 'saved' => '1' ), admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_raghushree_payment_settings_save', 'raghushree_payment_settings_save' );

/** Convert internal diagnostic codes to fixed administrator-facing guidance. */
function raghushree_shiprocket_diagnostic_message( $code, $status = 0 ) {
	$messages = array(
		'config' => __( 'Shiprocket API-user credentials are not saved.', 'raghushree' ),
		'product' => __( 'Use the ID of a published product or Product Detail page.', 'raghushree' ),
		'variation' => __( 'The selected pack is not enabled on this product.', 'raghushree' ),
		'pincode' => __( 'Enter a valid six-digit delivery PIN.', 'raghushree' ),
		'pickup' => __( 'Save a valid pickup PIN in Payment & Shipping Setup.', 'raghushree' ),
		'package' => __( 'Save a positive packed weight and all three dimensions for this pack.', 'raghushree' ),
		'auth_network' => __( 'WordPress could not connect to Shiprocket authentication. Check outbound HTTPS, DNS, TLS and the hosting firewall.', 'raghushree' ),
		'auth_http' => __( 'Shiprocket authentication returned an HTTP error. Check API-user credentials and account access; ask your host or Shiprocket support if it persists.', 'raghushree' ),
		'auth_response' => __( 'Shiprocket authentication returned an unexpected response without a usable token.', 'raghushree' ),
		'courier_network' => __( 'WordPress could not connect to Shiprocket courier serviceability. Check outbound HTTPS, DNS, TLS and the hosting firewall.', 'raghushree' ),
		'courier_http' => __( 'Shiprocket courier serviceability returned an HTTP error. Check the API user Courier access, PINs and package details; ask Shiprocket support if it persists.', 'raghushree' ),
		'courier_response' => __( 'Shiprocket courier serviceability returned an unexpected response.', 'raghushree' ),
		'no_rate' => __( 'No usable prepaid courier rate was returned. Check serviceability for these PINs and package.', 'raghushree' ),
		'weight_limit' => __( 'The selected packet quantity exceeds 30 kg. Contact us for a custom quote.', 'raghushree' ),
	);
	$message = isset( $messages[ $code ] ) ? $messages[ $code ] : __( 'The test could not be completed. Check the saved configuration and try again.', 'raghushree' );
	if ( in_array( $code, array( 'auth_http', 'courier_http' ), true ) && $status >= 100 && $status <= 599 ) { $message .= ' ' . sprintf( __( 'HTTP status: %d.', 'raghushree' ), $status ); }
	return $message;
}

function raghushree_payment_settings_render() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	$settings = get_option( 'raghushree_payment_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();
	?>
	<div class="wrap"><h1><?php esc_html_e( 'Payment & Shipping Setup', 'raghushree' ); ?></h1>
	<?php if ( isset( $_GET['saved'] ) ) : ?><div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', 'raghushree' ); ?></p></div><?php endif; ?>
	<?php
	// Only display fixed messages; API responses and credentials are never displayed.
	$connection = isset( $_GET['connection'] ) && is_scalar( $_GET['connection'] ) ? sanitize_key( wp_unslash( $_GET['connection'] ) ) : '';
	$service = isset( $_GET['service'] ) && is_scalar( $_GET['service'] ) ? sanitize_key( wp_unslash( $_GET['service'] ) ) : '';
	if ( 'shiprocket' === $service && 'failed' === $connection ) {
		$diagnostic = get_transient( 'raghushree_sr_auth_' . get_current_user_id() );
		delete_transient( 'raghushree_sr_auth_' . get_current_user_id() );
	}
	if ( in_array( $service, array( 'razorpay', 'shiprocket' ), true ) && in_array( $connection, array( 'success', 'failed', 'mode_mismatch' ), true ) ) :
		$message = 'success' === $connection ? __( 'Authentication test succeeded. This does not test checkout, the webhook or order creation.', 'raghushree' ) : ( 'mode_mismatch' === $connection ? __( 'The Razorpay key does not match the selected test/live mode.', 'raghushree' ) : __( 'Authentication test failed. Check saved credentials, API permissions and server connectivity.', 'raghushree' ) );
		if ( 'shiprocket' === $service && 'failed' === $connection && is_array( $diagnostic ?? null ) ) { $message = raghushree_shiprocket_diagnostic_message( $diagnostic['code'] ?? '', (int) ( $diagnostic['status'] ?? 0 ) ); }
		?><div class="notice <?php echo 'success' === $connection ? 'notice-success' : 'notice-error'; ?>"><p><?php echo esc_html( ucfirst( $service ) . ': ' . $message ); ?></p></div><?php
	endif;
	?>
	<?php
	$courier_test = get_transient( 'raghushree_sr_courier_' . get_current_user_id() );
	delete_transient( 'raghushree_sr_courier_' . get_current_user_id() );
	if ( is_array( $courier_test ) ) :
		$success = ! empty( $courier_test['success'] );
		$message = $success ? sprintf( __( 'Courier rate test succeeded: %1$s, estimated shipping ₹%2$s. No shipment was created.', 'raghushree' ), $courier_test['courier'] ?: __( 'Lowest available courier', 'raghushree' ), number_format_i18n( (int) $courier_test['rate_paise'] / 100, 2 ) ) : raghushree_shiprocket_diagnostic_message( $courier_test['code'] ?? '', (int) ( $courier_test['status'] ?? 0 ) );
		?><div class="notice <?php echo $success ? 'notice-success' : 'notice-error'; ?>"><p><?php echo esc_html( $message ); ?></p></div><?php
	endif;
	?>
	<div class="notice notice-warning"><p><?php esc_html_e( 'Checkout is disabled by default. Install and activate the updated Raghushree Payments & Orders plugin, configure the webhook, then test on staging before enabling it. Shiprocket is used for rates only; orders are managed manually in WordPress in both modes.', 'raghushree' ); ?></p></div>
	<p><strong><?php esc_html_e( 'Razorpay webhook URL:', 'raghushree' ); ?></strong> <code><?php echo esc_html( rest_url( 'raghushree/v1/razorpay-webhook' ) ); ?></code> — <?php esc_html_e( 'Subscribe to order.paid and set the same webhook secret below.', 'raghushree' ); ?></p>
	<p><?php esc_html_e( 'Credentials are stored in the WordPress database, not encrypted by this theme. Restrict admin and database access, use HTTPS and protect backups. Blank fields keep existing values; stored values are never shown again.', 'raghushree' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
		<input type="hidden" name="action" value="raghushree_payment_settings_save"><?php wp_nonce_field( 'raghushree_payment_settings_save' ); ?>
		<table class="form-table" role="presentation"><tr><th><label for="raghushree-mode"><?php esc_html_e( 'Razorpay mode', 'raghushree' ); ?></label></th><td><select id="raghushree-mode" name="mode"><option value="test" <?php selected( isset( $settings['mode'] ) ? $settings['mode'] : 'test', 'test' ); ?>><?php esc_html_e( 'Test', 'raghushree' ); ?></option><option value="live" <?php selected( isset( $settings['mode'] ) ? $settings['mode'] : 'test', 'live' ); ?>><?php esc_html_e( 'Live', 'raghushree' ); ?></option></select><p class="description"><?php esc_html_e( 'Do not mix test and live credentials. Replace Razorpay keys when switching modes.', 'raghushree' ); ?></p></td></tr>
		<tr><th><label for="pickup-pincode"><?php esc_html_e( 'Pickup PIN code', 'raghushree' ); ?></label></th><td><input id="pickup-pincode" name="pickup_pincode" type="text" inputmode="numeric" pattern="[1-9][0-9]{5}" maxlength="6" required value="<?php echo esc_attr( isset( $settings['pickup_pincode'] ) ? $settings['pickup_pincode'] : '208001' ); ?>"><p class="description"><?php esc_html_e( 'Origin PIN used for Shiprocket rate quotes. A PIN alone does not create or book a pickup.', 'raghushree' ); ?></p></td></tr>
		<tr><th><?php esc_html_e( 'Enable checkout', 'raghushree' ); ?></th><td><label><input type="checkbox" name="checkout_enabled" value="1" <?php checked( ! empty( $settings['checkout_enabled'] ) ); ?>> <?php esc_html_e( 'Enable after setup and testing', 'raghushree' ); ?></label><p class="description"><?php esc_html_e( 'Shiprocket supplies quotes only. No Shiprocket order or pickup is created; arrange delivery yourself. Test mode does not charge real money. Configure the webhook before enabling.', 'raghushree' ); ?></p></td></tr>
		<tr><th><?php esc_html_e( 'Live payment safety', 'raghushree' ); ?></th><td><label><input type="checkbox" name="allow_live" value="1" <?php checked( ! empty( $settings['allow_live'] ) ); ?>> <?php esc_html_e( 'I have completed staging tests and explicitly allow live payments', 'raghushree' ); ?></label><p class="description"><?php esc_html_e( 'Leave unchecked during testing. Both this and Enable checkout are required in live mode.', 'raghushree' ); ?></p></td></tr>
		<tr><th><?php esc_html_e( 'GST live-pricing safety', 'raghushree' ); ?></th><td><label><input type="checkbox" name="allow_tax_live" value="1" <?php checked( ! empty( $settings['allow_tax_live'] ) ); ?>> <?php esc_html_e( 'I have verified product and shipping GST treatment and tested the new totals on staging', 'raghushree' ); ?></label><p class="description"><?php esc_html_e( 'Required for live checkout after this pricing upgrade. Confirm whether Shiprocket rates already include GST before charging additional shipping GST.', 'raghushree' ); ?></p></td></tr>
		<?php foreach ( raghushree_payment_settings_fields() as $key => $label ) : ?>
		<tr><th><label for="setting-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td><input class="regular-text" type="password" autocomplete="new-password" id="setting-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="" placeholder="<?php echo esc_attr( ! empty( $settings[ $key ] ) ? __( 'Saved — enter a new value to replace', 'raghushree' ) : __( 'Not set', 'raghushree' ) ); ?>"> <?php if ( ! empty( $settings[ $key ] ) ) : ?><label><input type="checkbox" name="clear[<?php echo esc_attr( $key ); ?>]" value="1"> <?php esc_html_e( 'Clear', 'raghushree' ); ?></label><?php endif; ?></td></tr>
		<?php endforeach; ?></table>
		<?php submit_button(); ?>
	</form>
	<h2><?php esc_html_e( 'Test saved credentials', 'raghushree' ); ?></h2>
	<p><?php esc_html_e( 'Save changes first. These tests only authenticate; they do not create orders, test webhooks, or enable checkout.', 'raghushree' ); ?></p>
	<?php foreach ( array( 'razorpay' => 'Razorpay', 'shiprocket' => 'Shiprocket' ) as $service_key => $service_label ) : ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline-block;margin-right:10px">
		<input type="hidden" name="action" value="raghushree_test_service_connection"><input type="hidden" name="service" value="<?php echo esc_attr( $service_key ); ?>"><?php wp_nonce_field( 'raghushree_test_service_connection' ); ?>
		<?php submit_button( sprintf( __( 'Test %s', 'raghushree' ), $service_label ), 'secondary', 'submit', false ); ?>
	</form>
	<?php endforeach; ?>
	<h2><?php esc_html_e( 'Test Shiprocket courier rate', 'raghushree' ); ?></h2>
	<p><?php esc_html_e( 'Save credentials and pickup PIN first. Enter a published product ID (the number after post= in its edit URL), an enabled pack with saved dimensions, and a delivery PIN. This contacts your configured Shiprocket account for the lowest available prepaid rate only; it does not create an order or pickup. The Courier API-user module may be required; confirm permissions with Shiprocket if access is denied.', 'raghushree' ); ?></p>
	<?php $diagnostic_products = get_posts( array( 'post_type' => 'products', 'post_status' => 'publish', 'posts_per_page' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ); ?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="raghushree_test_courier_rate"><?php wp_nonce_field( 'raghushree_test_courier_rate' ); ?>
		<div class="raghushree-courier-pickers" hidden><p><label for="sr-product-picker"><?php esc_html_e( 'Choose a product', 'raghushree' ); ?></label> <select id="sr-product-picker"><option value=""><?php esc_html_e( 'Select a product', 'raghushree' ); ?></option><?php foreach ( $diagnostic_products as $product ) : $packs = array_filter( raghushree_master_variations( $product->ID ), function ( $pack ) { return ! empty( $pack['enabled'] ); } ); $choices = array(); foreach ( $packs as $key => $pack ) { $choices[] = array( 'key' => $key, 'label' => $pack['label'] ); } ?><option value="<?php echo esc_attr( $product->ID ); ?>" data-packs="<?php echo esc_attr( wp_json_encode( $choices ) ); ?>"><?php echo esc_html( get_the_title( $product ) ); ?></option><?php endforeach; ?></select>
		<label for="sr-pack-picker"><?php esc_html_e( 'Choose a pack', 'raghushree' ); ?></label> <select id="sr-pack-picker"><option value=""><?php esc_html_e( 'Select a product first', 'raghushree' ); ?></option></select></p><p class="description"><?php esc_html_e( 'The first 200 published products are listed. If yours is not listed, enter its ID and pack key below.', 'raghushree' ); ?></p></div>
		<label for="sr-product-id"><?php esc_html_e( 'Product ID', 'raghushree' ); ?></label> <input id="sr-product-id" name="product_id" type="number" min="1" required>
		<label for="sr-option"><?php esc_html_e( 'Pack', 'raghushree' ); ?></label> <input id="sr-option" name="option" type="text" placeholder="10kg or packet_1" required>
		<label for="sr-destination"><?php esc_html_e( 'Delivery PIN', 'raghushree' ); ?></label> <input id="sr-destination" name="pincode" type="text" inputmode="numeric" pattern="[1-9][0-9]{5}" maxlength="6" required>
		<?php submit_button( __( 'Test courier rate', 'raghushree' ), 'secondary', 'submit', false ); ?>
	</form></div>
	<?php
}

/** Verify saved API credentials without creating a payment or a shipment. */
function raghushree_test_service_connection() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Permission denied.', 'raghushree' ) ); }
	check_admin_referer( 'raghushree_test_service_connection' );
	$service = isset( $_POST['service'] ) && is_scalar( $_POST['service'] ) ? sanitize_key( wp_unslash( $_POST['service'] ) ) : '';
	$settings = get_option( 'raghushree_payment_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();
	$result = 'failed';
	if ( 'razorpay' === $service && ! empty( $settings['razorpay_key_id'] ) && ! empty( $settings['razorpay_key_secret'] ) ) {
		$mode = isset( $settings['mode'] ) ? $settings['mode'] : 'test';
		$prefix = 'live' === $mode ? 'rzp_live_' : 'rzp_test_';
		if ( 0 === strpos( $settings['razorpay_key_id'], $prefix ) ) {
			$response = wp_remote_get( 'https://api.razorpay.com/v1/orders?count=1', array(
				'timeout' => 15, 'redirection' => 0,
				'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $settings['razorpay_key_id'] . ':' . $settings['razorpay_key_secret'] ) ),
			) );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) { $result = 'success'; }
		} else { $result = 'mode_mismatch'; }
	} elseif ( 'shiprocket' === $service ) {
		$token = raghushree_shiprocket_authenticate( $settings );
		if ( is_wp_error( $token ) ) {
			set_transient( 'raghushree_sr_auth_' . get_current_user_id(), array( 'code' => $token->get_error_code(), 'status' => (int) $token->get_error_data() ), 5 * MINUTE_IN_SECONDS );
		} else { $result = 'success'; }
	}
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'raghushree-payment-setup', 'connection' => $result, 'service' => $service ), admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_raghushree_test_service_connection', 'raghushree_test_service_connection' );

/** Exercise the same prepaid serviceability path as the product page, without starting checkout. */
function raghushree_test_courier_rate() {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Permission denied.', 'raghushree' ) ); }
	check_admin_referer( 'raghushree_test_courier_rate' );
	$id = isset( $_POST['product_id'] ) && is_scalar( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$option = isset( $_POST['option'] ) && is_scalar( $_POST['option'] ) ? sanitize_key( wp_unslash( $_POST['option'] ) ) : '';
	$pin = isset( $_POST['pincode'] ) && is_scalar( $_POST['pincode'] ) ? trim( wp_unslash( $_POST['pincode'] ) ) : '';
	$quote = raghushree_fetch_shipping_quote( $id, $option, $pin );
	$result = is_wp_error( $quote ) ? array( 'success' => false, 'code' => $quote->get_error_code(), 'status' => (int) $quote->get_error_data() ) : array( 'success' => true, 'courier' => $quote['courier_name'], 'rate_paise' => $quote['rate_paise'] );
	set_transient( 'raghushree_sr_courier_' . get_current_user_id(), $result, 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'raghushree-payment-setup' ), admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_raghushree_test_courier_rate', 'raghushree_test_courier_rate' );

/** Enhance the optional courier diagnostic without changing its server-side checks. */
function raghushree_courier_test_admin_assets( $hook ) {
	if ( ! isset( $_GET['page'] ) || ! is_scalar( $_GET['page'] ) || 'raghushree-payment-setup' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) { return; }
	wp_enqueue_script( 'raghushree-courier-test', get_template_directory_uri() . '/assets/js/courier-test-admin.js', array(), raghushree_asset_version( 'assets/js/courier-test-admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'raghushree_courier_test_admin_assets' );
