<?php
// Isolated PHP smoke tests: WordPress, Razorpay and Shiprocket calls are mocked.
define( 'ABSPATH', __DIR__ );
$GLOBALS['meta'] = array(); $GLOBALS['options'] = array(); $GLOBALS['scheduled'] = array(); $GLOBALS['requests'] = array(); $GLOBALS['actions'] = array();
function add_action( $hook, $callback, ...$args ) { $GLOBALS['actions'][$hook][] = $callback; } function add_filter( ...$args ) {}
function current_user_can( $cap ) { return ! empty( $GLOBALS['admin_allowed'] ); }
function wp_die( $message ) { throw new Exception( $message ); }
function check_admin_referer( $action ) { if ( empty( $GLOBALS['nonce_valid'] ) ) { throw new Exception( 'invalid nonce' ); } }
function wp_unslash( $value ) { return $value; }
function get_post_type( $id ) { return isset( $GLOBALS['meta'][$id] ) ? 'rgpay_order' : false; }
function do_action( $hook, ...$args ) { foreach ( $GLOBALS['actions'][$hook] ?? array() as $callback ) { call_user_func( $callback, ...$args ); } }
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
function wp_remote_post( $url, $args ) { $GLOBALS['requests'][] = array( $url, $args ); if ( strpos( $url, '/auth/login' ) !== false ) return array( 'code' => 200, 'body' => '{"token":"fake"}' ); return array( 'code' => 200, 'body' => '{"order_id":1234,"shipment_id":5678}' ); }
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
rgpay_webhook( new Request( $raw, $sig ) ); check( count( $GLOBALS['scheduled'] ) === 0, 'paid webhook does not schedule shipment creation' );
check( ! isset( $GLOBALS['actions']['rgpay_fulfil_order'] ), 'legacy shipment worker is not registered' );
do_action( 'rgpay_fulfil_order', 1 ); check( count( $GLOBALS['requests'] ) === 0, 'legacy queued action does not call Shiprocket' );
check( rgpay_set_manual_delivery( 1, 'fulfilled', 'My Courier', 'TRACK-123' ), 'manual delivery save' );
check( $GLOBALS['meta'][1]['_rgpay_manual_status'] === 'fulfilled' && $GLOBALS['meta'][1]['_rgpay_manual_courier'] === 'My Courier' && $GLOBALS['meta'][1]['_rgpay_manual_tracking'] === 'TRACK-123', 'manual data stored' );
rgpay_webhook( new Request( $raw, $sig ) ); check( $GLOBALS['meta'][1]['_rgpay_manual_status'] === 'fulfilled', 'duplicate webhook preserves manual delivery status' );
check( ! rgpay_set_manual_delivery( 1, 'unknown', '', '' ), 'invalid delivery status rejected' );
$_POST = array( 'order_id'=>1, 'delivery_status'=>'awaiting', 'delivery_courier'=>'', 'delivery_tracking'=>'' );
$GLOBALS['admin_allowed'] = false;
try { rgpay_admin_update_delivery(); throw new Exception( 'unauthorised update accepted' ); } catch ( Exception $e ) { check( $e->getMessage() === 'Permission denied.', 'admin permission required' ); }
$GLOBALS['admin_allowed'] = true; $GLOBALS['nonce_valid'] = false;
try { rgpay_admin_update_delivery(); throw new Exception( 'invalid nonce accepted' ); } catch ( Exception $e ) { check( $e->getMessage() === 'invalid nonce', 'admin nonce required' ); }
check( $GLOBALS['meta'][1]['_rgpay_manual_status'] === 'fulfilled', 'denied admin actions do not alter the order' );
$GLOBALS['meta'][4] = array( '_rgpay_record'=>$record, '_rgpay_status'=>'pending' );
check( ! rgpay_set_manual_delivery( 4, 'fulfilled', '', '' ) && empty( $GLOBALS['meta'][4]['_rgpay_manual_status'] ), 'unpaid order cannot be fulfilled' );
$record['mode'] = 'live'; $GLOBALS['options']['raghushree_payment_settings']['mode'] = 'live';
$GLOBALS['meta'][2] = array( '_rgpay_record'=>$record, '_rgpay_razorpay_order'=>'order_Live123', '_rgpay_status'=>'pending' );
$live_order = $order; $live_order['id'] = 'order_Live123'; $live_payment = $payment; $live_payment['order_id'] = 'order_Live123';
$live_raw = json_encode( array( 'event'=>'order.paid', 'payload'=>array( 'order'=>array('entity'=>$live_order), 'payment'=>array('entity'=>$live_payment) ) ) );
check( rgpay_webhook( new Request( $live_raw, hash_hmac( 'sha256', $live_raw, 'secret' ) ) )->status === 200, 'live payment webhook accepted' );
check( $GLOBALS['meta'][2]['_rgpay_status'] === 'paid' && count( $GLOBALS['requests'] ) === 0 && count( $GLOBALS['scheduled'] ) === 0, 'live paid order stored without shipment API or queue' );
$GLOBALS['meta'][3] = array( '_rgpay_record'=>$record, '_rgpay_status'=>'paid', '_rgpay_fulfilment'=>'uncertain_check_shiprocket', '_rgpay_shiprocket_order'=>'old-id' );
do_action( 'rgpay_fulfil_order', 3 );
check( $GLOBALS['meta'][3]['_rgpay_fulfilment'] === 'uncertain_check_shiprocket' && $GLOBALS['meta'][3]['_rgpay_shiprocket_order'] === 'old-id' && count( $GLOBALS['requests'] ) === 0, 'legacy uncertain order preserved and not resent' );
echo "Payment flow smoke tests passed.\n";
