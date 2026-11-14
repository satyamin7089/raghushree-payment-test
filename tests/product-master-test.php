<?php
// Standalone product master tests with mocked WordPress storage.
define( 'ABSPATH', __DIR__ ); $GLOBALS['mock_meta'] = array();
function set_transient( ...$args ) {} function get_current_user_id() { return 1; } function add_action( ...$args ) {} function __( $value, $domain = null ) { return $value; }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['mock_meta'][$id][$key] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['mock_meta'][$id][$key] = $value; }
function wp_verify_nonce( $value, $action ) { return $value === 'valid'; } function sanitize_text_field( $value ) { return $value; }
function wp_unslash( $value ) { return $value; } function wp_is_post_revision( $id ) { return false; } function current_user_can( ...$args ) { return true; }
require __DIR__ . '/../inc/product-master.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
$default = raghushree_master_variations( 42 );
check( isset( $default['5kg'], $default['10kg'], $default['15kg'], $default['20kg'], $default['25kg'] ) && ! isset( $default['30kg'] ) && ! $default['5kg']['enabled'], 'five packs disabled by default, no 30kg' );
check( $default['sample']['price_paise'] === 0 && $default['sample']['gst_bps'] === 1800, 'free sample and default GST' );
$GLOBALS['mock_meta'][42]['_raghushree_master_variations'] = array( '10kg' => array( 'price_paise' => 12345, 'enabled' => true ), '30kg' => array( 'price_paise' => 30000, 'enabled' => true ) );
$old = raghushree_master_variations( 42 ); check( $old['10kg']['price_paise'] === 12345 && ! isset( $old['30kg'] ), 'preserve saved pack and hide 30kg' );
$_POST = array( 'raghushree_master_nonce' => 'valid', 'rg_product_type' => 'pipe', 'rg_gst' => '12.50', 'rg_hsn' => '3917', 'rg_packet_label' => 'packet', 'rg_packet_price' => '100.25', 'rg_packet_rows' => "1, 8, 40, 30, 20\n2, 17, 60, 40, 25", 'raghushree_master_price' => array( '10kg' => '100.25' ), 'raghushree_master_enabled' => array( '10kg' => '1', 'sample' => '1' ), 'raghushree_master_shipping' => array( 'sample' => array( 'weight_kg' => '0.250', 'length_cm' => '10', 'width_cm' => '5', 'height_cm' => '4' ) ) );
raghushree_master_box_save( 42 ); $saved = $GLOBALS['mock_meta'][42]; $packs = raghushree_master_variations( 42 );
check( $packs['packet_2']['price_paise'] === 20050 && $packs['packet_2']['weight_kg'] === '17' && $packs['packet_2']['gst_bps'] === 1250 && $packs['packet_2']['hsn'] === '3917', 'two packet independent measurements and GST' );
check( $packs['sample']['price_paise'] === 0 && $packs['sample']['enabled'], 'sample remains free' );
check( isset( $saved['_raghushree_master_variations']['30kg'] ), 'old 30kg data retained' );
$_POST['rg_packet_rows'] = "1, 8, 40, 30, 20\n1, 9, 40, 30, 20"; raghushree_master_box_save( 42 ); check( $GLOBALS['mock_meta'][42] === $saved, 'duplicate packet quantity rejected' );
$_POST['rg_packet_rows'] = '1, -1, 40, 30, 20'; raghushree_master_box_save( 42 ); check( $GLOBALS['mock_meta'][42] === $saved, 'invalid measurements rejected' );
$_POST['rg_packet_rows'] = '1, 8, 40, 30, 20'; $_POST['rg_gst'] = '101'; raghushree_master_box_save( 42 ); check( $GLOBALS['mock_meta'][42] === $saved, 'invalid GST rejected' );
echo "Product master smoke tests passed.\n";
