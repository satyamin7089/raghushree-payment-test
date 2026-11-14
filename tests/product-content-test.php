<?php
// Mocked rendering tests: only owner-entered product details and images appear.
define( 'ABSPATH', __DIR__ );
function get_queried_object_id() { return 42; }
function raghushree_get_product_data( $id ) { return $GLOBALS['product']; }
function raghushree_get_enquiry_url( $args ) { return '/enquire'; }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['meta'][$key] ?? ''; }
function get_post_thumbnail_id( $id ) { return $GLOBALS['featured']; }
function wp_attachment_is_image( $id ) { return in_array( $id, array( 11, 22 ), true ); }
function get_template_part( $name, $slug = null, $args = array() ) { $GLOBALS['parts'][$name] = $args; }
function get_post_field( $field, $id ) { return $GLOBALS['content']; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function __( $text, $domain = null ) { return $text; }
function esc_html_e( $text, $domain = null ) { echo esc_html( $text ); }
function esc_url( $url ) { return $url; }
function apply_filters( $name, $content ) { return $content; }
function wp_kses_post( $content ) { return $content; }
function check( $value, $message ) { if ( ! $value ) { throw new Exception( $message ); } }
function render_product() { $GLOBALS['parts'] = array(); ob_start(); include __DIR__ . '/../template-parts/sections/product/detail-content.php'; return ob_get_clean(); }
$GLOBALS['product'] = array( 'id' => 42, 'title' => 'Actual product', 'short_desc' => 'Owner introduction', 'image_slot' => 'polymer_hdpe', 'gallery_ids' => array( 11, 22, 999 ), 'categories' => array( 'x' ), 'primary_cat' => 'Our category', 'polymer_code' => '', 'grades_notes' => '', 'grades_rows' => array(), 'apps_rows' => array(), 'industries' => array(), 'processing' => array(), 'packaging' => '', 'pack_size' => '', 'moq' => '', 'availability' => '', 'tech_specs' => array(), 'datasheet' => '', 'faqs' => array() );
$GLOBALS['content'] = '<p>My own overview.</p>'; $GLOBALS['featured'] = 11; $GLOBALS['meta'] = array( '_raghushree_highlights' => "Own point 1\nOwn point 2" );
$html = render_product(); $hero = $GLOBALS['parts']['template-parts/components/page-banner'];
check( $hero['points'] === array( 'Own point 1', 'Own point 2' ), 'owner highlights used' );
check( count( $hero['slider_slides'] ) === 2 && $hero['slider_slides'][1]['attachment_id'] === 22, 'featured duplication and invalid images skipped' );
check( strpos( $html, 'My own overview.' ) !== false && strpos( $html, 'Shop-Floor Conversion' ) === false && strpos( $html, 'id="grades"' ) === false, 'only authored content, no generic extras' );
check( isset( $GLOBALS['parts']['template-parts/components/product-checkout'] ), 'checkout retained' );
$GLOBALS['product']['tech_specs'] = array( array( 'Owner property', 'Value', 'Reference' ) );
$GLOBALS['product']['packaging'] = 'A bag'; $html = render_product();
check( strpos( $html, 'Owner property' ) !== false && strpos( $html, 'A bag' ) !== false, 'saved structured fields displayed' );
$GLOBALS['featured'] = 0; $GLOBALS['product']['gallery_ids'] = array( 22 ); $html = render_product(); $hero = $GLOBALS['parts']['template-parts/components/page-banner'];
check( $hero['slider_slides'][0]['attachment_id'] === 22 && ! $hero['empty_image'], 'gallery works without featured image' );
$GLOBALS['product']['gallery_ids'] = array(); $GLOBALS['content'] = ''; $html = render_product();
check( $GLOBALS['parts']['template-parts/components/page-banner']['empty_image'] && strpos( $html, 'id="overview"' ) === false, 'empty product does not invent content or photo' );
echo "Product content smoke tests passed.\n";
