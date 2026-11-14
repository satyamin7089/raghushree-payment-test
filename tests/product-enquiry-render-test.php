<?php
define( 'ABSPATH', __DIR__ );
function raghushree_get_enquiry_state() { return array( 'submitted' => true, 'success' => false, 'message' => 'Please review.', 'errors' => array( 'email' => 'Invalid email' ), 'values' => array( 'name' => 'Buyer' ) ); }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); } function esc_attr( $s ) { return esc_html( $s ); } function esc_textarea( $s ) { return esc_html( $s ); } function esc_url( $s ) { return esc_html( $s ); } function __( $s, $domain = null ) { return $s; } function esc_html_e( $s, $domain = null ) { echo esc_html( $s ); } function esc_attr_e( $s, $domain = null ) { echo esc_attr( $s ); } function get_permalink( $id ) { return '/product/'; } function wp_nonce_field( ...$args ) { echo '<input name="raghushree_enquiry_nonce" value="nonce">'; }
$args = array( 'product_id' => 42, 'product_title' => 'A & B' ); ob_start(); include __DIR__ . '/../template-parts/components/product-enquiry.php'; $html = ob_get_clean();
function check( $v, $message ) { if ( ! $v ) throw new Exception( $message ); }
check( strpos( $html, 'name="enquiry_material" value="A &amp; B"' ) !== false && strpos( $html, 'name="raghushree_enquiry_nonce"' ) !== false, 'product and nonce included safely' );
check( strpos( $html, 'Invalid email' ) !== false && strpos( $html, 'aria-invalid="true"' ) !== false && strpos( $html, 'value="Buyer"' ) !== false, 'validation message and values retained' );
echo "Product enquiry render smoke tests passed.\n";
