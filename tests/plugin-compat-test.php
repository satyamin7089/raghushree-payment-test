<?php
// The new plugin must not open checkout when the old theme remains installed.
define( 'ABSPATH', __DIR__ );
function add_action( ...$args ) {} function add_filter( ...$args ) {}
function get_option( $key, $default = false ) { return array( 'mode' => 'test', 'checkout_enabled' => true, 'razorpay_key_id' => 'rzp_test_example', 'razorpay_key_secret' => 'secret', 'razorpay_webhook_secret' => 'secret', 'shiprocket_email' => 'user', 'shiprocket_password' => 'password' ); }
function is_ssl() { return true; }
function raghushree_rates_only_theme_ready() { return true; } // Previous theme lacks the GST-pricing marker.
function raghushree_master_variations( $id ) { return array(); }
function raghushree_fetch_shipping_quote( ...$args ) {}
require __DIR__ . '/../integrations/raghushree-payments/raghushree-payments.php';
if ( rgpay_checkout_ready() ) { throw new Exception( 'Checkout must be disabled with the old theme.' ); }
echo "Plugin compatibility smoke test passed.\n";
