<?php
// Isolated PHP smoke tests: WordPress, Razorpay and Shiprocket calls are mocked.
define( 'ABSPATH', __DIR__ );
$GLOBALS['meta'] = array(); $GLOBALS['options'] = array(); $GLOBALS['scheduled'] = array(); $GLOBALS['requests'] = array(); $GLOBALS['shiprocket_fail'] = false;
function add_action( ...$args ) {} function add_filter( ...$args ) {}
function get_option( $key, $default = false ) { return $GLOBALS['options'][$key] ?? $default; }
function add_option( $key, $value, $deprecated = '', $autoload = null ) { if ( isset( $GLOBALS['options'][$key] ) ) return false; $GLOBALS['options'][$key] = $value; return true; }
function delete_option( $key ) { unset( $GLOBALS['options'][$key] ); }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['meta'][$id][$key] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][$id][$key] = $value; return true; }
function add_post_meta( $id, $key, $value, $unique = false ) { if ( $unique && isset( $GLOBALS['meta'][$id][$key] ) ) return false; $GLOBALS['meta'][$id][$key] = $value; return true; }
function get_posts( $args ) { foreach ( $GLOBALS['meta'] as $id => $m ) if ( ($m['_rgpay_razorpay_order'] ?? '') === $args['meta_value'] ) return array( $id ); return array(); }
function wp_next_scheduled( $hook, $args ) { return in_array( $args[0], $GLOBALS['scheduled'], true ); }
function wp_schedule_single_event( $time, $hook, $args ) { $GLOBALS['scheduled'][] = $args[0]; return true; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
class WP_Error {} class WP_REST_Response { public $data, $status; function __construct( $data, $status ) { $this->data = $data; $this->status = $status; } }
class Request { private $body, $signature; function __construct( $body, $sig ) { $this->body = $body; $this->signature = $sig; } function get_body() { return $this->body; } function get_header( $name ) { return $this->signature; } }
function wp_remote_post( $url, $args ) { $GLOBALS['requests'][] = array( $url, $args ); if ( strpos( $url, '/auth/login' ) !== false ) return array( 'code' => 200, 'body' => '{"token":"fake"}' ); if ( $GLOBALS['shiprocket_fail'] ) return new WP_Error(); return array( 'code' => 200, 'body' => '{"order_id":1234,"shipment_id":5678}' ); }
function wp_remote_retrieve_response_code( $response ) { return $response['code']; }
function wp_remote_retrieve_body( $response ) { return $response['body']; }
function wp_json_encode( $body ) { return json_encode( $body ); }
function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); }
function absint( $value ) { return abs( (int) $value ); }
function get_date_from_gmt( $value, $format ) { return '2026-11-13 12:00'; }
function wp_mail( ...$args ) { return true; }
require __DIR__ . '/../integrations/raghushree-payments/raghushree-payments.php';
function check( $condition, $message ) { if ( ! $condition ) throw new Exception( $message ); }
$record = array( 'mode' => 'test', 'total_paise' => 14450, 'created_utc' => '2026-11-13 12:00:00', 'pickup_location' => 'Primary', 'shiprocket_reference' => '20261113120000-2',
 'customer' => array( 'first_name'=>'A', 'last_name'=>'B', 'email'=>'a@example.com', 'phone'=>'9876543210', 'address'=>'Road 1', 'address_2'=>'', 'city'=>'Lucknow', 'state'=>'UP', 'pincode'=>'226001' ),
 'snapshot' => array( 'product_id'=>42, 'option'=>'10kg', 'pack'=>array( 'weight_kg'=>'10.5', 'length_cm'=>'30', 'width_cm'=>'20', 'height_cm'=>'10' ), 'quote'=>array( 'product_name'=>'PP', 'option_name'=>'10 kg', 'product_paise'=>10000, 'rate_paise'=>4450 ) ) );
$order = array( 'id'=>'order_Abc123', 'status'=>'paid', 'currency'=>'INR', 'amount'=>14450, 'amount_paid'=>14450 );
$payment = array( 'id'=>'pay_Xyz123', 'order_id'=>'order_Abc123', 'status'=>'captured', 'currency'=>'INR', 'amount'=>14450 );
check( rgpay_entities_match( $record, 'order_Abc123', $order, $payment ), 'valid captured payment' );
$bad = $payment; $bad['amount'] = 1; check( ! rgpay_entities_match( $record, 'order_Abc123', $order, $bad ), 'wrong amount rejected' );
$bad = $payment; $bad['status'] = 'authorized'; check( ! rgpay_entities_match( $record, 'order_Abc123', $order, $bad ), 'uncaptured rejected' );
$bad = $order; $bad['currency'] = 'USD'; check( ! rgpay_entities_match( $record, 'order_Abc123', $bad, $payment ), 'wrong currency rejected' );
$GLOBALS['options']['raghushree_payment_settings'] = array( 'mode'=>'test', 'razorpay_webhook_secret'=>'secret', 'shiprocket_email'=>'user', 'shiprocket_password'=>'pass' );
$GLOBALS['meta'][1] = array( '_rgpay_record'=>$record, '_rgpay_razorpay_order'=>'order_Abc123', '_rgpay_status'=>'pending' );
$raw = json_encode( array( 'event'=>'order.paid', 'payload'=>array( 'order'=>array('entity'=>$order), 'payment'=>array('entity'=>$payment) ) ) );
check( rgpay_webhook( new Request( $raw, 'bad' ) )->status === 401 && $GLOBALS['meta'][1]['_rgpay_status'] === 'pending', 'bad webhook signature rejected' );
$sig = hash_hmac( 'sha256', $raw, 'secret' );
check( rgpay_webhook( new Request( $raw, $sig ) )->status === 200 && $GLOBALS['meta'][1]['_rgpay_status'] === 'paid', 'signed webhook marks paid' );
rgpay_webhook( new Request( $raw, $sig ) ); check( count( $GLOBALS['scheduled'] ) === 1, 'duplicate webhook does not schedule twice' );
rgpay_fulfil_order( 1 ); check( $GLOBALS['meta'][1]['_rgpay_fulfilment'] === 'test_simulated' && count( $GLOBALS['requests'] ) === 0, 'test payment never calls Shiprocket' );
$record['mode'] = 'live'; $GLOBALS['options']['raghushree_payment_settings']['mode'] = 'live'; $GLOBALS['meta'][2] = array( '_rgpay_record'=>$record, '_rgpay_status'=>'paid' );
rgpay_fulfil_order( 2 ); check( $GLOBALS['meta'][2]['_rgpay_fulfilment'] === 'created' && $GLOBALS['meta'][2]['_rgpay_shiprocket_order'] === '1234', 'live order creation' );
$payload = json_decode( $GLOBALS['requests'][1][1]['body'], true );
check( $payload['payment_method'] === 'Prepaid' && $payload['sub_total'] == 100 && $payload['shipping_charges'] == 44.5 && $payload['order_items'][0]['name'] === 'PP - 10 kg', 'live payload' );
rgpay_fulfil_order( 2 ); check( count( $GLOBALS['requests'] ) === 2, 'duplicate processing makes no second request' );
$GLOBALS['shiprocket_fail'] = true; $GLOBALS['meta'][3] = array( '_rgpay_record'=>$record, '_rgpay_status'=>'paid' ); rgpay_fulfil_order( 3 );
check( $GLOBALS['meta'][3]['_rgpay_fulfilment'] === 'uncertain_check_shiprocket', 'ambiguous Shiprocket response flagged' );
rgpay_fulfil_order( 3 ); check( count( $GLOBALS['requests'] ) === 4, 'ambiguous request not repeated' );
echo "Payment flow smoke tests passed.\n";
