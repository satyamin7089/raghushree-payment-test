<?php
// Checkout creation test with fake WordPress, server-side quote and Razorpay API.
define('ABSPATH', __DIR__); define('HOUR_IN_SECONDS',3600);
$GLOBALS['options'] = array('raghushree_payment_settings'=>array('mode'=>'test', 'checkout_enabled'=>true, 'razorpay_key_id'=>'rzp_test_abc', 'razorpay_key_secret'=>'secret', 'razorpay_webhook_secret'=>'webhook', 'shiprocket_email'=>'email', 'shiprocket_password'=>'password', 'pickup_location'=>'Primary', 'pickup_pincode'=>'208001'));
$GLOBALS['meta']=array(); $GLOBALS['transients']=array(); $GLOBALS['requests']=array();
function add_action(...$x) {} function add_filter(...$x) {} function is_ssl(){return true;}
function get_option($k,$default=false){return $GLOBALS['options'][$k]??$default;} function add_option($k,$v,$d='',$a=null){if(isset($GLOBALS['options'][$k]))return false;$GLOBALS['options'][$k]=$v;return true;}
function get_transient($k){return $GLOBALS['transients'][$k]??false;} function delete_transient($k){unset($GLOBALS['transients'][$k]);} function wp_schedule_single_event(...$x){return true;}
function check_ajax_referer(...$x){} function wp_unslash($x){return $x;} function sanitize_text_field($x){return trim(strip_tags($x));} function sanitize_email($x){return filter_var($x,FILTER_SANITIZE_EMAIL);} function is_email($x){return filter_var($x,FILTER_VALIDATE_EMAIL);}
function get_post($id){return (object)array('post_status'=>'publish','post_type'=>'products');} function raghushree_master_variations($id){return array('10kg'=>$GLOBALS['pack']);} function raghushree_fetch_shipping_quote(...$x){}
function wp_insert_post($post,$err){return 77;} function is_wp_error($x){return $x instanceof WP_Error;} class WP_Error{}
function add_post_meta($id,$key,$value,$unique=false){$GLOBALS['meta'][$id][$key]=$value;return true;} function update_post_meta($id,$key,$value){$GLOBALS['meta'][$id][$key]=$value;return true;} function wp_update_post($p){}
function wp_json_encode($x){return json_encode($x);} function wp_remote_post($url,$args){$GLOBALS['requests'][]=array($url,$args);return array('code'=>200,'body'=>json_encode(array('id'=>'order_ABC123','amount'=>14450,'currency'=>'INR')));} function wp_remote_retrieve_response_code($r){return $r['code'];} function wp_remote_retrieve_body($r){return $r['body'];}
class Sent extends Exception { public $data; public $ok; function __construct($ok,$data){$this->ok=$ok;$this->data=$data;} } function wp_send_json_error($data,$status=0){throw new Sent(false,$data);} function wp_send_json_success($data){throw new Sent(true,$data);}
require __DIR__.'/../integrations/raghushree-payments/raghushree-payments.php';
function check($ok,$message){if(!$ok)throw new Exception($message);}
$GLOBALS['pack']=array('label'=>'10 kg','enabled'=>true,'price_paise'=>10000,'weight_kg'=>'10','length_cm'=>'30','width_cm'=>'20','height_cm'=>'10');
$token=str_repeat('a',64);$hash=hash('sha256',$token);
$GLOBALS['transients']['rgpay_q_'.$hash]=array('product_id'=>42,'option'=>'10kg','pin'=>'226001','mode'=>'test','pickup_pincode'=>'208001','pickup_location'=>'Primary','pack'=>$GLOBALS['pack'],'quote'=>array('product_paise'=>10000,'rate_paise'=>4450,'product_name'=>'PP','option_name'=>'10 kg','courier_name'=>'Example'));
$_POST=array('quote_token'=>$token,'first_name'=>'A','last_name'=>'B','email'=>'a@example.com','phone'=>'9876543210','address'=>'Street','city'=>'Lucknow','state'=>'UP','amount'=>'1');
try { rgpay_start_checkout(); throw new Exception('No response'); } catch(Sent $result){check($result->ok && $result->data['amount']===14450,'server quote sets total');}
$sent=json_decode($GLOBALS['requests'][0][1]['body'],true);check($sent['amount']===14450 && $sent['notes']['product']==='PP - 10 kg','order uses server amount and dynamic title');
check($GLOBALS['meta'][77]['_rgpay_record']['customer']['pincode']==='226001','server quote binds delivery pincode');
try { rgpay_start_checkout(); throw new Exception('No response'); } catch(Sent $result){check(!$result->ok && count($GLOBALS['requests'])===1,'replay rejected');}
echo "Checkout creation smoke tests passed.\n";
