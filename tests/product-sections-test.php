<?php
define( 'ABSPATH', __DIR__ );
$GLOBALS['meta'] = array( '_raghushree_applications_rows' => "Water | Transfer", '_raghushree_tech_specs' => "Length | 10 | cm", '_raghushree_product_faqs' => 'Q? | A.' ); $GLOBALS['transients'] = array();
function add_action( ...$args ) {} function __( $text, $domain = null ) { return $text; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); } function esc_attr( $s ) { return esc_html( $s ); } function esc_textarea( $s ) { return esc_html( $s ); } function esc_html_e( $s, $domain = null ) { echo esc_html( $s ); }
function get_post_meta( $id, $key, $single ) { return $GLOBALS['meta'][$key] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['meta'][$key] = $value; }
function wp_unslash( $value ) { return $value; } function sanitize_text_field( $value ) { return trim( strip_tags( $value ) ); } function sanitize_textarea_field( $value ) { return trim( strip_tags( $value ) ); }
function get_current_user_id() { return 1; } function set_transient( $key, $value, $ttl ) { $GLOBALS['transients'][$key] = $value; }
function get_transient( $key ) { return $GLOBALS['transients'][$key] ?? false; } function delete_transient( $key ) { unset( $GLOBALS['transients'][$key] ); }
require __DIR__ . '/../inc/custom-fields.php'; require __DIR__ . '/../inc/product-sections.php';
function check( $condition, $message ) { if ( ! $condition ) throw new Exception( $message ); }
$old = raghushree_product_sections( 42 ); check( $old['applications'][0][0] === 'Water' && $old['faqs'][0][1] === 'A.', 'legacy content loads' );
$_POST = array( 'rg_sections_present' => '1', 'rg_sections_complete' => '1', 'rg_sections' => array( 'features' => array( array( 'Feature', 'Detail with | characters' ) ), 'applications' => array( array( 'Water', 'Use' ) ), 'specifications' => array( array( 'Length', '10', 'cm' ) ), 'faqs' => array( array( 'Q?', 'Answer | here' ) ) ) );
raghushree_save_product_sections( 42 ); $saved = $GLOBALS['meta']['_raghushree_product_sections'];
check( $saved['faqs'][0][1] === 'Answer | here' && $saved['features'][0][1] === 'Detail with | characters', 'structured values preserve literal separators' );
check( $GLOBALS['meta']['_raghushree_product_faqs'] === 'Q? | A.', 'legacy values preserved' );
$_POST['rg_sections']['faqs'][0][1] = ''; raghushree_save_product_sections( 42 );
check( $GLOBALS['meta']['_raghushree_product_sections'] === $saved && isset( $GLOBALS['transients']['raghushree_sections_draft_1_42'] ), 'invalid rows leave saved content intact with editable draft' );
$_POST['rg_sections']['faqs'][0][1] = 'Fixed'; raghushree_save_product_sections( 42 ); check( $GLOBALS['meta']['_raghushree_product_sections']['faqs'][0][1] === 'Fixed' && ! isset( $GLOBALS['transients']['raghushree_sections_draft_1_42'] ), 'corrected draft saves' );
$before = $GLOBALS['meta']; unset( $_POST['rg_sections_complete'] ); raghushree_save_product_sections( 42 ); check( $GLOBALS['meta'] === $before, 'truncated form leaves data intact' );
$_POST['rg_sections_complete'] = '1'; $_POST['rg_section_intros_present'] = '1';
$_POST['rg_section_intros'] = array( 'features' => "Feature intro\nSecond line", 'applications' => 'Applications intro', 'specifications' => 'Technical intro', 'faqs' => '<b>FAQ intro</b>' );
raghushree_save_product_sections( 42 ); $intros = $GLOBALS['meta']['_raghushree_section_intros'];
check( $intros['features'] === "Feature intro\nSecond line" && $intros['faqs'] === 'FAQ intro', 'intros saved as multiline plain text' );
$_POST['rg_section_intros']['features'] = 'Unsaved edit'; $_POST['rg_sections']['faqs'][0][1] = '';
raghushree_save_product_sections( 42 );
check( $GLOBALS['meta']['_raghushree_section_intros'] === $intros && $GLOBALS['transients']['raghushree_sections_draft_1_42']['_intros']['features'] === 'Unsaved edit', 'invalid rows retain saved intros and editable draft' );
$_POST['rg_sections']['faqs'][0][1] = 'Fixed'; unset( $_POST['rg_section_intros']['faqs'] ); $before = $GLOBALS['meta'];
raghushree_save_product_sections( 42 ); check( $GLOBALS['meta'] === $before, 'missing intro fields do not erase data' );
$_POST['rg_section_intros']['faqs'] = 'FAQ intro'; $_POST['rg_section_intros']['features'] = str_repeat( 'x', 4801 );
raghushree_save_product_sections( 42 ); check( $GLOBALS['meta'] === $before, 'oversized intros rejected atomically' );
unset( $_POST['rg_section_intros_present'], $_POST['rg_section_intros'] ); raghushree_save_product_sections( 42 );
check( $GLOBALS['meta']['_raghushree_section_intros'] === $intros, 'older submissions preserve intros' );
ob_start(); raghushree_render_product_sections( 42 ); $editor = ob_get_clean();
foreach ( array( 'features', 'applications', 'specifications', 'faqs' ) as $key ) { check( strpos( $editor, 'name="rg_section_intros[' . $key . ']"' ) !== false, 'editor has intro input: ' . $key ); }
check( strpos( $editor, "Feature intro\nSecond line" ) !== false, 'saved multiline intro restored in editor' );
echo "Product sections smoke tests passed.\n";
