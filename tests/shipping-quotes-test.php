<?php
// Standalone logic tests with a fake WordPress and fake Shiprocket HTTP responses.
define( 'ABSPATH', __DIR__ );
$GLOBALS['http_calls'] = array();
$GLOBALS['auth_response'] = array( 'code' => 200, 'body' => '{"token":"fake-token"}' );
$GLOBALS['courier_response'] = array( 'code' => 200, 'body' => json_encode( array( 'data' => array( 'recommended_courier_company_id' => 7, 'available_courier_companies' => array( array( 'courier_company_id' => 1, 'rate' => '10.00' ), array( 'courier_company_id' => 7, 'courier_name' => 'Example', 'rate' => '44.50' ) ) ) ) ) );
function add_action( ...$args ) {}
function __( $text, $domain = null ) { return $text; }
function sanitize_text_field( $text ) { return trim( strip_tags( $text ) ); }
class WP_Error { private $code; private $data; public function __construct( $code, $message, $data = null ) { $this->code = $code; $this->data = $data; } public function get_error_code() { return $this->code; } public function get_error_data() { return $this->data; } }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_post( $id ) { return $id === 42 ? (object) array( 'post_status' => 'publish', 'post_type' => 'products' ) : null; }
function get_the_title( $post ) { return 'Example product'; }
function raghushree_master_variations( $id ) { return array( 'sample' => array( 'label' => 'Sample', 'enabled' => true, 'price_paise' => 0, 'weight_kg' => '0.5', 'length_cm' => '10', 'width_cm' => '8', 'height_cm' => '6' ), '10kg' => array( 'label' => '10 kg', 'enabled' => false ) ); }
function raghushree_master_shipping_fields() { return array( 'weight_kg' => 'weight', 'length_cm' => 'length', 'width_cm' => 'width', 'height_cm' => 'height' ); }
function get_option( $name, $default = false ) { return array( 'shiprocket_email' => 'user', 'shiprocket_password' => 'pass', 'pickup_pincode' => '208001' ); }
function wp_json_encode( $body ) { return json_encode( $body ); }
function wp_remote_post( $url, $args ) { $GLOBALS['http_calls'][] = array( $url, $args ); return $GLOBALS['auth_response']; }
function wp_remote_get( $url, $args ) { $GLOBALS['http_calls'][] = array( $url, $args ); return $GLOBALS['courier_response']; }
function wp_remote_retrieve_response_code( $response ) { return $response['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function add_query_arg( $query, $url ) { return $url . '?' . http_build_query( $query ); }
require __DIR__ . '/../inc/shipping-quotes.php';
require __DIR__ . '/../inc/payment-settings.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
// Fields and placement follow Shiprocket's published courier serviceability example.
$documented_data = array( 'available_courier_companies' => array( array( 'courier_company_id' => 43, 'courier_name' => 'Delhivery Surface', 'rate' => 54 ), array( 'courier_company_id' => 225, 'courier_name' => 'India Post', 'rate' => 123.9 ) ), 'recommended_courier_company_id' => 43, 'shiprocket_recommended_courier_id' => 43 );
$documented_rate = raghushree_recommended_shipping_rate( $documented_data );
check( $documented_rate['courier_id'] === '43' && $documented_rate['rate_paise'] === 5400, 'documented serviceability response shape' );
check( false === raghushree_recommended_shipping_rate( array( 'available_courier_companies' => array() ) ), 'must not pick arbitrary courier' );
check( false === raghushree_recommended_shipping_rate( array( 'recommended_courier_company_id' => 3, 'available_courier_companies' => array( array( 'courier_company_id' => 4, 'rate' => '3' ) ) ) ), 'unavailable recommended courier must fail' );
$quote = raghushree_fetch_shipping_quote( 42, 'sample', '226001' );
check( ! is_wp_error( $quote ) && $quote['rate_paise'] === 4450 && $quote['product_paise'] === 0 && $quote['courier_id'] === '7', 'recommended sample quote' );
check( strpos( $GLOBALS['http_calls'][1][0], 'pickup_postcode=208001' ) !== false && strpos( $GLOBALS['http_calls'][1][0], 'cod=0' ) !== false && strpos( $GLOBALS['http_calls'][1][0], 'length=10' ) !== false, 'pickup, prepaid and dimensions passed' );
$calls = count( $GLOBALS['http_calls'] );
check( is_wp_error( raghushree_fetch_shipping_quote( 42, '10kg', '226001' ) ) && count( $GLOBALS['http_calls'] ) === $calls, 'disabled option rejected before API' );
check( is_wp_error( raghushree_fetch_shipping_quote( 42, 'sample', 'abc' ) ) && count( $GLOBALS['http_calls'] ) === $calls, 'invalid pincode rejected before API' );
check( $GLOBALS['http_calls'][0][0] === 'https://apiv2.shiprocket.in/v1/external/auth/login' && json_decode( $GLOBALS['http_calls'][0][1]['body'], true ) === array( 'email' => 'user', 'password' => 'pass' ), 'documented authentication request' );
check( strpos( $GLOBALS['http_calls'][1][0], 'https://apiv2.shiprocket.in/v1/external/courier/serviceability/?' ) === 0 && $GLOBALS['http_calls'][1][1]['headers']['Authorization'] === 'Bearer fake-token', 'documented serviceability request and bearer' );
check( false === raghushree_recommended_shipping_rate( array( 'recommended_courier_company_id' => array( 7 ), 'available_courier_companies' => array( array( 'courier_company_id' => 7, 'rate' => 40 ) ) ) ), 'malformed recommendation rejected' );
check( false === raghushree_recommended_shipping_rate( array( 'recommended_courier_company_id' => 7, 'available_courier_companies' => array( array( 'courier_company_id' => 7, 'rate' => '0.001' ) ) ) ), 'sub-paise rate rejected' );
$GLOBALS['auth_response'] = array( 'code' => 401, 'body' => '{"message":"sensitive server response"}' );
$error = raghushree_fetch_shipping_quote( 42, 'sample', '226001' );
check( is_wp_error( $error ) && $error->get_error_code() === 'auth_http' && $error->get_error_data() === 401, 'authentication status' );
check( strpos( raghushree_shiprocket_diagnostic_message( $error->get_error_code(), $error->get_error_data() ), '401' ) !== false && strpos( raghushree_shiprocket_diagnostic_message( $error->get_error_code(), $error->get_error_data() ), 'sensitive' ) === false, 'safe admin diagnostic' );
$GLOBALS['auth_response'] = new WP_Error( 'transport', 'secret transport details' );
check( raghushree_fetch_shipping_quote( 42, 'sample', '226001' )->get_error_code() === 'auth_network', 'authentication network error' );
$GLOBALS['auth_response'] = array( 'code' => 200, 'body' => '{}' );
check( raghushree_fetch_shipping_quote( 42, 'sample', '226001' )->get_error_code() === 'auth_response', 'missing token' );
$GLOBALS['auth_response'] = array( 'code' => 200, 'body' => '{"token":"fake-token"}' );
$GLOBALS['courier_response'] = array( 'code' => 403, 'body' => '{"message":"sensitive server response"}' );
$error = raghushree_fetch_shipping_quote( 42, 'sample', '226001' );
check( $error->get_error_code() === 'courier_http' && $error->get_error_data() === 403, 'courier permission status' );
$GLOBALS['courier_response'] = new WP_Error( 'transport', 'secret transport details' );
check( raghushree_fetch_shipping_quote( 42, 'sample', '226001' )->get_error_code() === 'courier_network', 'courier network error' );
$GLOBALS['courier_response'] = array( 'code' => 200, 'body' => 'not json' );
check( raghushree_fetch_shipping_quote( 42, 'sample', '226001' )->get_error_code() === 'courier_response', 'malformed response' );
$GLOBALS['courier_response'] = array( 'code' => 200, 'body' => '{"data":{"available_courier_companies":[{"courier_company_id":7,"rate":44}]}}' );
check( raghushree_fetch_shipping_quote( 42, 'sample', '226001' )->get_error_code() === 'no_rate', 'missing recommendation must fail' );
echo "Shipping quote smoke tests passed.\n";
