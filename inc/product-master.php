<?php
/** Product master for the custom checkout and shipping quote integration. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Defaults apply until an administrator saves product-specific values. */
function raghushree_master_defaults() {
	return array(
		'10kg'   => array( 'label' => '10 kg', 'price_paise' => 10000, 'enabled' => true ),
		'20kg'   => array( 'label' => '20 kg', 'price_paise' => 20000, 'enabled' => true ),
		'30kg'   => array( 'label' => '30 kg', 'price_paise' => 30000, 'enabled' => true ),
		'sample' => array( 'label' => 'Sample', 'price_paise' => 0, 'enabled' => false ),
	);
}

/** Merge individual rows with defaults, including newly introduced options. */
function raghushree_master_variations( $post_id ) {
	$saved = get_post_meta( $post_id, '_raghushree_master_variations', true );
	$saved = is_array( $saved ) ? $saved : array();
	$rows  = array();
	foreach ( raghushree_master_defaults() as $key => $default ) {
		$rows[ $key ] = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? array_merge( $default, $saved[ $key ] ) : $default;
		// Names and the free sample price are controlled by the master, not submitted input.
		$rows[ $key ]['label'] = $default['label'];
		if ( 'sample' === $key ) { $rows[ $key ]['price_paise'] = 0; }
	}
	return $rows;
}

function raghushree_master_box_register() {
	add_meta_box( 'raghushree_master', __( 'Product Master — Packs & Shipping', 'raghushree' ), 'raghushree_master_box_render', 'products', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'raghushree_master_box_register' );

function raghushree_master_page_box_register( $post ) {
	if ( 'page-templates/template-product-detail.php' === get_page_template_slug( $post ) ) {
		add_meta_box( 'raghushree_master', __( 'Product Master — Packs & Shipping', 'raghushree' ), 'raghushree_master_box_render', 'page', 'normal', 'high' );
	}
}
add_action( 'add_meta_boxes_page', 'raghushree_master_page_box_register' );

function raghushree_master_shipping_fields() {
	return array(
		'weight_kg' => __( 'Packed weight (kg)', 'raghushree' ),
		'length_cm' => __( 'Length (cm)', 'raghushree' ),
		'width_cm'  => __( 'Width (cm)', 'raghushree' ),
		'height_cm' => __( 'Height (cm)', 'raghushree' ),
	);
}

function raghushree_master_box_render( $post ) {
	$variations = raghushree_master_variations( $post->ID );
	wp_nonce_field( 'raghushree_master_save', 'raghushree_master_nonce' );
	?>
	<p><?php esc_html_e( 'Enter the actual packed weight and outer dimensions for each option, including the sample. Prices exclude shipping; configure and test checkout separately.', 'raghushree' ); ?></p>
	<div style="overflow-x:auto"><table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Option', 'raghushree' ); ?></th><th><?php esc_html_e( 'Price (INR)', 'raghushree' ); ?></th><?php foreach ( raghushree_master_shipping_fields() as $label ) : ?><th><?php echo esc_html( $label ); ?></th><?php endforeach; ?><th><?php esc_html_e( 'Display', 'raghushree' ); ?></th></tr></thead><tbody>
	<?php foreach ( $variations as $key => $row ) : ?>
		<tr><td><?php echo esc_html( $row['label'] ); ?></td><td>
		<?php if ( 'sample' === $key ) : ?>
			<?php esc_html_e( '₹0 — shipping only', 'raghushree' ); ?>
		<?php else : ?>
			<label><span class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Price for %s', 'raghushree' ), $row['label'] ) ); ?></span><input type="number" min="0.01" max="10000000" step="0.01" required style="width:100px" name="raghushree_master_price[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( number_format( (int) $row['price_paise'] / 100, 2, '.', '' ) ); ?>"></label>
		<?php endif; ?></td>
		<?php foreach ( raghushree_master_shipping_fields() as $field => $label ) : ?>
		<td><label><span class="screen-reader-text"><?php echo esc_html( $row['label'] . ' — ' . $label ); ?></span><input type="number" min="0.001" max="99999" step="0.001" style="width:100px" name="raghushree_master_shipping[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( isset( $row[ $field ] ) ? $row[ $field ] : '' ); ?>"></label></td>
		<?php endforeach; ?>
		<td><label><input type="checkbox" name="raghushree_master_enabled[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( ! empty( $row['enabled'] ) ); ?>> <?php esc_html_e( 'Show', 'raghushree' ); ?></label></td></tr>
	<?php endforeach; ?>
	</tbody></table></div>
	<p class="description"><?php esc_html_e( 'Sample is disabled by default and its product price is always zero. Shipping details can be saved later, but must be complete before shipping quotes or payments are enabled. Pack size is not a material grade; use the existing Material Grades taxonomy for verified grades.', 'raghushree' ); ?></p>
	<?php
}

function raghushree_master_box_save( $post_id ) {
	if ( ! isset( $_POST['raghushree_master_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['raghushree_master_nonce'] ) ), 'raghushree_master_save' ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$prices   = isset( $_POST['raghushree_master_price'] ) && is_array( $_POST['raghushree_master_price'] ) ? wp_unslash( $_POST['raghushree_master_price'] ) : array();
	$enabled  = isset( $_POST['raghushree_master_enabled'] ) && is_array( $_POST['raghushree_master_enabled'] ) ? $_POST['raghushree_master_enabled'] : array();
	$shipping = isset( $_POST['raghushree_master_shipping'] ) && is_array( $_POST['raghushree_master_shipping'] ) ? wp_unslash( $_POST['raghushree_master_shipping'] ) : array();
	$rows = array();
	foreach ( raghushree_master_defaults() as $key => $default ) {
		$paise = 0;
		if ( 'sample' !== $key ) {
			$price = isset( $prices[ $key ] ) && is_scalar( $prices[ $key ] ) ? (string) $prices[ $key ] : '';
			// Parse decimal money without floating-point rounding. Reject the entire update if invalid.
			if ( ! preg_match( '/^(\d{1,8})(?:\.(\d{1,2}))?$/D', $price, $m ) ) { return; }
			$paise = (int) $m[1] * 100 + (int) str_pad( isset( $m[2] ) ? $m[2] : '', 2, '0' );
			if ( $paise < 1 || $paise > 1000000000 ) { return; }
		}
		$rows[ $key ] = array( 'label' => $default['label'], 'price_paise' => $paise, 'enabled' => isset( $enabled[ $key ] ) );
		foreach ( raghushree_master_shipping_fields() as $field => $label ) {
			$value = isset( $shipping[ $key ][ $field ] ) && is_scalar( $shipping[ $key ][ $field ] ) ? trim( (string) $shipping[ $key ][ $field ] ) : '';
			// Blank permits an incomplete draft. Quote creation must reject incomplete data.
			if ( '' !== $value && ( ! preg_match( '/^\d{1,5}(?:\.\d{1,3})?$/D', $value ) || (float) $value <= 0 || (float) $value > 99999 ) ) { return; }
			$rows[ $key ][ $field ] = $value;
		}
	}
	update_post_meta( $post_id, '_raghushree_master_variations', $rows );
}
add_action( 'save_post_products', 'raghushree_master_box_save' );
add_action( 'save_post_page', 'raghushree_master_box_save' );
