<?php
// Standalone logic tests with a fake WordPress and fake Shiprocket HTTP responses.
define( 'ABSPATH', __DIR__ );
$GLOBALS['http_calls'] = array();
function add_action( ...$args ) {}
function __( $text, $domain = null ) { return $text; }
function sanitize_text_field( $text ) { return trim( strip_tags( $text ) ); }
class WP_Error { private $code; public function __construct( $code, $message ) { $this->code = $code; } public function get_error_code() { return $this->code; } }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_post( $id ) { return $id === 42 ? (object) array( 'post_status' => 'publish', 'post_type' => 'products' ) : null; }
function get_the_title( $post ) { return 'Example product'; }
function raghushree_master_variations( $id ) { return array( 'sample' => array( 'label' => 'Sample', 'enabled' => true, 'price_paise' => 0, 'weight_kg' => '0.5', 'length_cm' => '10', 'width_cm' => '8', 'height_cm' => '6' ), '10kg' => array( 'label' => '10 kg', 'enabled' => false ) ); }
function raghushree_master_shipping_fields() { return array( 'weight_kg' => 'weight', 'length_cm' => 'length', 'width_cm' => 'width', 'height_cm' => 'height' ); }
function get_option( $name, $default = false ) { return array( 'shiprocket_email' => 'user', 'shiprocket_password' => 'pass', 'pickup_pincode' => '208001' ); }
function wp_json_encode( $body ) { return json_encode( $body ); }
function wp_remote_post( $url, $args ) { $GLOBALS['http_calls'][] = array( $url, $args ); return array( 'code' => 200, 'body' => '{"token":"fake-token"}' ); }
function wp_remote_get( $url, $args ) { $GLOBALS['http_calls'][] = array( $url, $args ); return array( 'code' => 200, 'body' => json_encode( array( 'data' => array( 'recommended_courier_company_id' => 7, 'available_courier_companies' => array( array( 'courier_company_id' => 1, 'rate' => '10.00' ), array( 'courier_company_id' => 7, 'courier_name' => 'Example', 'rate' => '44.50' ) ) ) ) ) ); }
function wp_remote_retrieve_response_code( $response ) { return $response['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function add_query_arg( $query, $url ) { return $url . '?' . http_build_query( $query ); }
require __DIR__ . '/../inc/shipping-quotes.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
check( false === raghushree_recommended_shipping_rate( array( 'available_courier_companies' => array() ) ), 'must not pick arbitrary courier' );
check( false === raghushree_recommended_shipping_rate( array( 'recommended_courier_company_id' => 3, 'available_courier_companies' => array( array( 'courier_company_id' => 4, 'rate' => '3' ) ) ) ), 'unavailable recommended courier must fail' );
$quote = raghushree_fetch_shipping_quote( 42, 'sample', '226001' );
check( ! is_wp_error( $quote ) && $quote['rate_paise'] === 4450 && $quote['product_paise'] === 0 && $quote['courier_id'] === '7', 'recommended sample quote' );
check( strpos( $GLOBALS['http_calls'][1][0], 'pickup_postcode=208001' ) !== false && strpos( $GLOBALS['http_calls'][1][0], 'cod=0' ) !== false && strpos( $GLOBALS['http_calls'][1][0], 'length=10' ) !== false, 'pickup, prepaid and dimensions passed' );
$calls = count( $GLOBALS['http_calls'] );
check( is_wp_error( raghushree_fetch_shipping_quote( 42, '10kg', '226001' ) ) && count( $GLOBALS['http_calls'] ) === $calls, 'disabled option rejected before API' );
check( is_wp_error( raghushree_fetch_shipping_quote( 42, 'sample', 'abc' ) ) && count( $GLOBALS['http_calls'] ) === $calls, 'invalid pincode rejected before API' );
echo "Shipping quote smoke tests passed.\n";
