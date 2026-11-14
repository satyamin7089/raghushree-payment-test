<?php
/** Product master: polymer pack prices, pipe packets and GST. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function raghushree_master_defaults() {
	return array(
		'5kg' => array( 'label' => '5 kg', 'price_paise' => 0, 'enabled' => false ),
		'10kg' => array( 'label' => '10 kg', 'price_paise' => 0, 'enabled' => false ),
		'15kg' => array( 'label' => '15 kg', 'price_paise' => 0, 'enabled' => false ),
		'20kg' => array( 'label' => '20 kg', 'price_paise' => 0, 'enabled' => false ),
		'25kg' => array( 'label' => '25 kg', 'price_paise' => 0, 'enabled' => false ),
		'sample' => array( 'label' => 'Sample', 'price_paise' => 0, 'enabled' => false ),
	);
}
function raghushree_master_shipping_fields() {
	return array( 'weight_kg' => __( 'Packed weight (kg)', 'raghushree' ), 'length_cm' => __( 'Length (cm)', 'raghushree' ), 'width_cm' => __( 'Width (cm)', 'raghushree' ), 'height_cm' => __( 'Height (cm)', 'raghushree' ) );
}
function raghushree_product_commerce( $post_id ) {
	$saved = get_post_meta( $post_id, '_raghushree_commerce', true );
	$saved = is_array( $saved ) ? $saved : array();
	return array_merge( array( 'type' => 'polymer', 'gst_bps' => 1800, 'hsn' => '', 'packet_label' => '', 'packet_price_paise' => 0, 'packet_rows' => array() ), $saved );
}
/** Prices are exclusive of GST. Old saved packs are retained; never invent new prices. */
function raghushree_master_variations( $post_id ) {
	$commerce = raghushree_product_commerce( $post_id );
	$saved = get_post_meta( $post_id, '_raghushree_master_variations', true );
	$saved = is_array( $saved ) ? $saved : array();
	$defaults = raghushree_master_defaults();
	$rows = array();
	if ( 'pipe' === $commerce['type'] ) {
		foreach ( (array) $commerce['packet_rows'] as $quantity => $measurements ) {
			$qty = (int) $quantity;
			if ( $qty < 1 || ! is_array( $measurements ) ) { continue; }
			$rows['packet_' . $qty] = array_merge( $measurements, array( 'label' => sprintf( '%d %s', $qty, $commerce['packet_label'] ?: 'packet(s)' ), 'quantity' => $qty, 'price_paise' => (int) $commerce['packet_price_paise'] * $qty, 'enabled' => (int) $commerce['packet_price_paise'] > 0 ) );
		}
	} else {
		foreach ( $defaults as $key => $default ) {
			if ( 'sample' === $key ) { continue; }
			$rows[$key] = isset( $saved[$key] ) && is_array( $saved[$key] ) ? array_merge( $default, $saved[$key] ) : $default;
			$rows[$key]['label'] = $default['label'];
		}
	}
	$rows['sample'] = isset( $saved['sample'] ) && is_array( $saved['sample'] ) ? array_merge( $defaults['sample'], $saved['sample'] ) : $defaults['sample'];
	$rows['sample']['label'] = 'Sample'; $rows['sample']['price_paise'] = 0;
	foreach ( $rows as &$row ) { $row['gst_bps'] = (int) $commerce['gst_bps']; $row['hsn'] = (string) $commerce['hsn']; $row['product_type'] = $commerce['type']; }
	unset( $row );
	return $rows;
}
function raghushree_master_box_register() { add_meta_box( 'raghushree_master', __( 'Product Master — Pricing, GST & Shipping', 'raghushree' ), 'raghushree_master_box_render', 'products', 'normal', 'high' ); }
add_action( 'add_meta_boxes', 'raghushree_master_box_register' );
function raghushree_master_page_box_register( $post ) { if ( 'page-templates/template-product-detail.php' === get_page_template_slug( $post ) ) { add_meta_box( 'raghushree_master', __( 'Product Master — Pricing, GST & Shipping', 'raghushree' ), 'raghushree_master_box_render', 'page', 'normal', 'high' ); } }
add_action( 'add_meta_boxes_page', 'raghushree_master_page_box_register' );
function raghushree_master_box_render( $post ) {
	$commerce = raghushree_product_commerce( $post->ID );
	$saved = get_post_meta( $post->ID, '_raghushree_master_variations', true ); $saved = is_array( $saved ) ? $saved : array();
	$lines = array(); foreach ( (array) $commerce['packet_rows'] as $qty => $r ) { $lines[] = implode( ', ', array( $qty, $r['weight_kg'], $r['length_cm'], $r['width_cm'], $r['height_cm'] ) ); }
	wp_nonce_field( 'raghushree_master_save', 'raghushree_master_nonce' );
	?>
	<p><?php esc_html_e( 'Enter prices before GST. New pack sizes remain unavailable until enabled with a price and package measurements. Save your changes after editing.', 'raghushree' ); ?></p>
	<p><label><?php esc_html_e( 'Product type', 'raghushree' ); ?> <select name="rg_product_type"><option value="polymer" <?php selected( $commerce['type'], 'polymer' ); ?>><?php esc_html_e( 'Polymer', 'raghushree' ); ?></option><option value="pipe" <?php selected( $commerce['type'], 'pipe' ); ?>><?php esc_html_e( 'Pipe / packet', 'raghushree' ); ?></option></select></label>
	<label><?php esc_html_e( 'Product GST (%)', 'raghushree' ); ?> <input type="number" name="rg_gst" min="0" max="100" step="0.01" required value="<?php echo esc_attr( number_format( $commerce['gst_bps'] / 100, 2, '.', '' ) ); ?>"></label>
	<label><?php esc_html_e( 'HSN code', 'raghushree' ); ?> <input type="text" name="rg_hsn" maxlength="8" pattern="[0-9]{4,8}" value="<?php echo esc_attr( $commerce['hsn'] ); ?>"></label></p>
	<h3><?php esc_html_e( 'Polymer packs and sample', 'raghushree' ); ?></h3>
	<div style="overflow-x:auto"><table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Option', 'raghushree' ); ?></th><th><?php esc_html_e( 'Price before GST (₹)', 'raghushree' ); ?></th><?php foreach ( raghushree_master_shipping_fields() as $label ) : ?><th><?php echo esc_html( $label ); ?></th><?php endforeach; ?><th><?php esc_html_e( 'Show', 'raghushree' ); ?></th></tr></thead><tbody>
	<?php foreach ( raghushree_master_defaults() as $key => $default ) : $row = isset( $saved[$key] ) && is_array( $saved[$key] ) ? array_merge( $default, $saved[$key] ) : $default; ?>
	<tr><td><?php echo esc_html( $default['label'] ); ?></td><td><?php if ( 'sample' === $key ) : ?>₹0<?php else : ?><input type="number" min="0.01" max="10000000" step="0.01" name="raghushree_master_price[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( ! empty( $row['price_paise'] ) ? number_format( $row['price_paise'] / 100, 2, '.', '' ) : '' ); ?>" style="width:100px"><?php endif; ?></td>
	<?php foreach ( raghushree_master_shipping_fields() as $field => $label ) : ?><td><input type="number" min="0.001" max="99999" step="0.001" name="raghushree_master_shipping[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $row[$field] ?? '' ); ?>" aria-label="<?php echo esc_attr( $default['label'] . ' ' . $label ); ?>" style="width:100px"></td><?php endforeach; ?>
	<td><label><input type="checkbox" name="raghushree_master_enabled[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $row['enabled'] ) ); ?>> <?php esc_html_e( 'Show', 'raghushree' ); ?></label></td></tr><?php endforeach; ?></tbody></table></div>
	<h3><?php esc_html_e( 'Pipe packet pricing and combined shipping dimensions', 'raghushree' ); ?></h3>
	<p><label><?php esc_html_e( 'Packet size / description', 'raghushree' ); ?> <input type="text" name="rg_packet_label" maxlength="80" value="<?php echo esc_attr( $commerce['packet_label'] ); ?>"></label> <label><?php esc_html_e( 'Price per packet before GST (₹)', 'raghushree' ); ?> <input type="number" min="0.01" max="10000000" step="0.01" name="rg_packet_price" value="<?php echo esc_attr( ! empty( $commerce['packet_price_paise'] ) ? number_format( $commerce['packet_price_paise'] / 100, 2, '.', '' ) : '' ); ?>"></label></p>
	<p><?php esc_html_e( 'One line per quantity: number of packets, total packed weight (kg), total length, width, height (cm), separated by commas. Example: 1, 8, 40, 30, 20. Enter actual combined measurements for 2 packets on a separate line. Online checkout is limited to 30 kg total packed weight.', 'raghushree' ); ?></p>
	<textarea name="rg_packet_rows" rows="6" class="large-text code"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea>
	<?php
}
function raghushree_parse_decimal_paise( $value, $max_paise ) {
	if ( ! is_string( $value ) || ! preg_match( '/^(\d{1,8})(?:\.(\d{1,2}))?$/D', $value, $m ) ) { return false; }
	$result = (int) $m[1] * 100 + (int) str_pad( $m[2] ?? '', 2, '0' );
	return $result <= $max_paise ? $result : false;
}
function raghushree_master_invalid() { set_transient( 'raghushree_master_invalid_' . get_current_user_id(), 1, 60 ); }
function raghushree_master_invalid_notice() {
	$key = 'raghushree_master_invalid_' . get_current_user_id();
	if ( get_transient( $key ) ) { delete_transient( $key ); ?><div class="notice notice-error"><p><?php esc_html_e( 'Product Master was not saved. Check GST, HSN, enabled pack prices, and packet measurement rows. The previous master values remain unchanged.', 'raghushree' ); ?></p></div><?php }
}
add_action( 'admin_notices', 'raghushree_master_invalid_notice' );
function raghushree_master_box_save( $post_id ) {
	if ( ! isset( $_POST['raghushree_master_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['raghushree_master_nonce'] ) ), 'raghushree_master_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$type = isset( $_POST['rg_product_type'] ) && is_scalar( $_POST['rg_product_type'] ) ? wp_unslash( $_POST['rg_product_type'] ) : '';
	$gst = isset( $_POST['rg_gst'] ) && is_scalar( $_POST['rg_gst'] ) ? raghushree_parse_decimal_paise( trim( wp_unslash( (string) $_POST['rg_gst'] ) ), 10000 ) : false;
	$hsn = isset( $_POST['rg_hsn'] ) && is_scalar( $_POST['rg_hsn'] ) ? trim( wp_unslash( (string) $_POST['rg_hsn'] ) ) : '';
	if ( ! in_array( $type, array( 'polymer', 'pipe' ), true ) || false === $gst || ( '' !== $hsn && ! preg_match( '/^[0-9]{4,8}$/D', $hsn ) ) ) { raghushree_master_invalid(); return; }
	$prices = isset( $_POST['raghushree_master_price'] ) && is_array( $_POST['raghushree_master_price'] ) ? wp_unslash( $_POST['raghushree_master_price'] ) : array();
	$enabled = isset( $_POST['raghushree_master_enabled'] ) && is_array( $_POST['raghushree_master_enabled'] ) ? $_POST['raghushree_master_enabled'] : array();
	$shipping = isset( $_POST['raghushree_master_shipping'] ) && is_array( $_POST['raghushree_master_shipping'] ) ? wp_unslash( $_POST['raghushree_master_shipping'] ) : array();
	$rows = get_post_meta( $post_id, '_raghushree_master_variations', true ); $rows = is_array( $rows ) ? $rows : array();
	foreach ( raghushree_master_defaults() as $key => $default ) {
		$price = isset( $prices[$key] ) && is_scalar( $prices[$key] ) ? trim( (string) $prices[$key] ) : '';
		$paise = 'sample' === $key || '' === $price ? 0 : raghushree_parse_decimal_paise( $price, 1000000000 );
		if ( false === $paise || ( 'sample' !== $key && isset( $enabled[$key] ) && $paise < 1 ) ) { raghushree_master_invalid(); return; }
		$rows[$key] = array( 'label' => $default['label'], 'price_paise' => $paise, 'enabled' => isset( $enabled[$key] ) );
		foreach ( raghushree_master_shipping_fields() as $field => $label ) {
			$value = isset( $shipping[$key][$field] ) && is_scalar( $shipping[$key][$field] ) ? trim( (string) $shipping[$key][$field] ) : '';
			if ( '' !== $value && ( ! preg_match( '/^\d{1,5}(?:\.\d{1,3})?$/D', $value ) || (float) $value <= 0 || (float) $value > 99999 ) ) { raghushree_master_invalid(); return; }
			$rows[$key][$field] = $value;
		}
	}
	$packet_price = isset( $_POST['rg_packet_price'] ) && is_scalar( $_POST['rg_packet_price'] ) ? trim( wp_unslash( (string) $_POST['rg_packet_price'] ) ) : '';
	$packet_price = '' === $packet_price ? 0 : raghushree_parse_decimal_paise( $packet_price, 1000000000 ); if ( false === $packet_price ) { raghushree_master_invalid(); return; }
	$packet_label = isset( $_POST['rg_packet_label'] ) && is_scalar( $_POST['rg_packet_label'] ) ? sanitize_text_field( wp_unslash( $_POST['rg_packet_label'] ) ) : '';
	if ( strlen( $packet_label ) > 80 ) { raghushree_master_invalid(); return; }
	$raw = isset( $_POST['rg_packet_rows'] ) && is_scalar( $_POST['rg_packet_rows'] ) ? trim( wp_unslash( (string) $_POST['rg_packet_rows'] ) ) : '';
	$packets = array();
	if ( '' !== $raw ) { foreach ( preg_split( '/\R/', $raw ) as $line ) {
		$parts = array_map( 'trim', explode( ',', $line ) );
		if ( count( $parts ) !== 5 || ! preg_match( '/^[1-9][0-9]{0,2}$/D', $parts[0] ) || isset( $packets[(int) $parts[0]] ) ) { raghushree_master_invalid(); return; }
		$values = array(); foreach ( array_values( array_keys( raghushree_master_shipping_fields() ) ) as $i => $field ) { $value = $parts[$i+1]; if ( ! preg_match( '/^\d{1,5}(?:\.\d{1,3})?$/D', $value ) || (float) $value <= 0 || (float) $value > 99999 ) { raghushree_master_invalid(); return; } $values[$field] = $value; }
		$packets[(int) $parts[0]] = $values;
	} }
	ksort( $packets );
	update_post_meta( $post_id, '_raghushree_master_variations', $rows );
	update_post_meta( $post_id, '_raghushree_commerce', array( 'type' => $type, 'gst_bps' => $gst, 'hsn' => $hsn, 'packet_label' => $packet_label, 'packet_price_paise' => $packet_price, 'packet_rows' => $packets ) );
}
add_action( 'save_post_products', 'raghushree_master_box_save' );
add_action( 'save_post_page', 'raghushree_master_box_save' );
