<?php
/** Admin-only credential storage for a future payment integration. */
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
	if ( in_array( $service, array( 'razorpay', 'shiprocket' ), true ) && in_array( $connection, array( 'success', 'failed', 'mode_mismatch' ), true ) ) :
		$message = 'success' === $connection ? __( 'Authentication test succeeded. This does not test checkout, the webhook or order creation.', 'raghushree' ) : ( 'mode_mismatch' === $connection ? __( 'The Razorpay key does not match the selected test/live mode.', 'raghushree' ) : __( 'Authentication test failed. Check saved credentials, API permissions and server connectivity.', 'raghushree' ) );
		?><div class="notice <?php echo 'success' === $connection ? 'notice-success' : 'notice-error'; ?>"><p><?php echo esc_html( ucfirst( $service ) . ': ' . $message ); ?></p></div><?php
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
	<?php endforeach; ?></div>
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
	} elseif ( 'shiprocket' === $service && ! empty( $settings['shiprocket_email'] ) && ! empty( $settings['shiprocket_password'] ) ) {
		$response = wp_remote_post( 'https://apiv2.shiprocket.in/v1/external/auth/login', array(
			'timeout' => 15, 'redirection' => 0,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body' => wp_json_encode( array( 'email' => $settings['shiprocket_email'], 'password' => $settings['shiprocket_password'] ) ),
		) );
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( is_array( $body ) && ! empty( $body['token'] ) ) { $result = 'success'; }
		}
	}
	wp_safe_redirect( add_query_arg( array( 'post_type' => 'products', 'page' => 'raghushree-payment-setup', 'connection' => $result, 'service' => $service ), admin_url( 'edit.php' ) ) );
	exit;
}
add_action( 'admin_post_raghushree_test_service_connection', 'raghushree_test_service_connection' );
