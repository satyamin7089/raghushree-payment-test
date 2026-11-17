<?php
/** Product banners request uncropped images; other banners keep their usual size. */
define( 'ABSPATH', __DIR__ );
set_error_handler( function ( $severity, $message, $file, $line ) { throw new ErrorException( $message, 0, $severity, $file, $line ); } );
function __( $text, $domain = null ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_attr( $text ) { return esc_html( $text ); }
function esc_url( $text ) { return esc_html( $text ); }
function esc_html_e( $text, $domain = null ) { echo esc_html( $text ); }
function esc_attr_e( $text, $domain = null ) { echo esc_attr( $text ); }
function wp_kses_post( $text ) { return $text; }
function raghushree_icon( $name, $class = '' ) { return ''; }
function raghushree_render_image( $slot, $args ) { $GLOBALS['image_args'][] = $args; echo '<img alt="test">'; }
function check_banner( $condition, $message ) { if ( ! $condition ) { throw new RuntimeException( $message ); } }
function render_banner( $args ) {
 $GLOBALS['image_args'] = array();
 ob_start(); include dirname( __DIR__ ) . '/template-parts/components/page-banner.php';
 return ob_get_clean();
}
$base = array( 'id' => 'product-hero', 'title' => 'Product', 'post_id' => 10 );
$html = render_banner( $base + array( 'slider_slides' => array( array( 'slot' => 'product' ) ) ) );
check_banner( count( $GLOBALS['image_args'] ) === 1 && $GLOBALS['image_args'][0]['size'] === 'full', 'Single image must use the uncropped original.' );
check_banner( substr_count( $html, 'page-banner-card__product-image' ) === 1, 'Single image must have one square wrapper.' );
$html = render_banner( $base + array( 'slider_slides' => array( array( 'slot' => 'product' ), array( 'attachment_id' => 22 ) ) ) );
check_banner( count( $GLOBALS['image_args'] ) === 2 && $GLOBALS['image_args'][0]['size'] === 'full' && $GLOBALS['image_args'][1]['size'] === 'full', 'Every gallery slide must use the uncropped original.' );
check_banner( substr_count( $html, 'page-banner-card__product-image' ) === 1 && substr_count( $html, 'data-fade-slide=' ) === 2, 'Gallery should contain exactly one wrapper and two slides.' );
$html = render_banner( $base + array( 'empty_image' => true ) );
check_banner( count( $GLOBALS['image_args'] ) === 0 && strpos( $html, 'product-image-empty' ) !== false, 'Missing product image should show the empty state.' );
$html = render_banner( array( 'id' => 'about-hero', 'title' => 'About' ) );
check_banner( $GLOBALS['image_args'][0]['size'] === 'raghushree-hero' && strpos( $html, 'page-banner-card__product-image' ) === false, 'Non-product banners should remain unchanged.' );
echo "Product image banner tests passed.\n";
