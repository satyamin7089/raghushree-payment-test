<?php
// Standalone mocked WordPress tests for the administrator-only diagnostics.
define( 'ABSPATH', __DIR__ );
define( 'MINUTE_IN_SECONDS', 60 );
$GLOBALS['allowed'] = true;
$GLOBALS['valid_nonce'] = true;
$GLOBALS['transients'] = array();
$GLOBALS['quote_calls'] = 0;
$GLOBALS['quote_result'] = array( 'courier_name' => 'Example Courier', 'rate_paise' => 4450 );
function add_action( ...$args ) {}
function __( $text, $domain = null ) { return $text; }
function esc_html__( $text, $domain = null ) { return $text; }
function current_user_can( $capability ) { return $GLOBALS['allowed'] && $capability === 'manage_options'; }
function wp_die( $message ) { throw new Exception( 'denied' ); }
function check_admin_referer( $action ) { if ( ! $GLOBALS['valid_nonce'] ) { throw new Exception( 'nonce' ); } }
function wp_unslash( $value ) { return $value; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $value ) ); }
function get_current_user_id() { return 123; }
function set_transient( $key, $value, $ttl ) { $GLOBALS['transients'][$key] = $value; }
function get_option( $key, $default = false ) { return array( 'shiprocket_email' => 'fake', 'shiprocket_password' => 'fake' ); }
function admin_url( $path ) { return '/wp-admin/' . $path; }
function add_query_arg( $args, $url ) { return $url . '?' . http_build_query( $args ); }
function wp_safe_redirect( $url ) { throw new Exception( 'redirect' ); }
class WP_Error { private $code; private $data; function __construct( $code, $message, $data = null ) { $this->code = $code; $this->data = $data; } function get_error_code() { return $this->code; } function get_error_data() { return $this->data; } }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function raghushree_fetch_shipping_quote( $id, $option, $pin ) { $GLOBALS['quote_calls']++; $GLOBALS['input'] = array( $id, $option, $pin ); return $GLOBALS['quote_result']; }
function raghushree_shiprocket_authenticate( $settings ) { return $GLOBALS['auth_result']; }
require __DIR__ . '/../inc/payment-settings.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
function invoke( $function, $expected ) { try { $function(); throw new Exception( 'no redirect' ); } catch ( Exception $e ) { if ( $e->getMessage() !== $expected ) { throw $e; } } }
$_POST = array( 'product_id' => '42', 'option' => 'sample', 'pincode' => '226001' );
$GLOBALS['allowed'] = false;
invoke( 'raghushree_test_courier_rate', 'denied' );
check( $GLOBALS['quote_calls'] === 0, 'unauthorized user did not call provider' );
$GLOBALS['allowed'] = true; $GLOBALS['valid_nonce'] = false;
invoke( 'raghushree_test_courier_rate', 'nonce' );
check( $GLOBALS['quote_calls'] === 0, 'invalid nonce did not call provider' );
$GLOBALS['valid_nonce'] = true;
invoke( 'raghushree_test_courier_rate', 'redirect' );
check( $GLOBALS['input'] === array( 42, 'sample', '226001' ), 'quote inputs' );
check( $GLOBALS['transients']['raghushree_sr_courier_123'] === array( 'success' => true, 'courier' => 'Example Courier', 'rate_paise' => 4450 ), 'safe quote result' );
$GLOBALS['quote_result'] = new WP_Error( 'courier_http', 'private response', 403 );
invoke( 'raghushree_test_courier_rate', 'redirect' );
check( $GLOBALS['transients']['raghushree_sr_courier_123'] === array( 'success' => false, 'code' => 'courier_http', 'status' => 403 ), 'only safe error data saved' );
$_POST = array( 'service' => 'shiprocket' );
$GLOBALS['auth_result'] = new WP_Error( 'auth_http', 'private response', 401 );
invoke( 'raghushree_test_service_connection', 'redirect' );
check( $GLOBALS['transients']['raghushree_sr_auth_123'] === array( 'code' => 'auth_http', 'status' => 401 ), 'authentication error saved safely' );
echo "Courier diagnostics smoke tests passed.\n";
