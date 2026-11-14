<?php
/** Purchase controls embedded below product description and points. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$id = (int) $args['product_id']; $enquire = $args['enquire_url'];
$commerce = raghushree_product_commerce( $id ); $packs = raghushree_master_variations( $id );
$available = array_filter( $packs, function ( $row ) { return ! empty( $row['enabled'] ); } );
?>
<div class="product-purchase-hero" id="product-purchase">
	<p class="purchase-eyebrow"><?php esc_html_e( 'Order online', 'raghushree' ); ?></p>
	<?php if ( ! empty( $available ) ) : ?>
	<form class="product-quote" data-endpoint="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-product="<?php echo esc_attr( $id ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'raghushree_shipping_quote' ) ); ?>" data-type="<?php echo esc_attr( $commerce['type'] ); ?>" data-packs="<?php echo esc_attr( wp_json_encode( $available ) ); ?>">
		<div class="purchase-price"><span><?php esc_html_e( 'Product price', 'raghushree' ); ?></span><strong data-purchase-price>—</strong><small><?php esc_html_e( 'before GST and shipping', 'raghushree' ); ?></small></div>
		<?php if ( 'pipe' === $commerce['type'] ) : ?>
		<label for="packet-quantity"><?php esc_html_e( 'Number of packets', 'raghushree' ); ?></label>
		<div class="packet-stepper"><button type="button" data-qty-minus aria-label="<?php esc_attr_e( 'Decrease packet quantity', 'raghushree' ); ?>">−</button><input id="packet-quantity" name="packet_quantity" type="number" min="1" max="999" value="1" inputmode="numeric"><button type="button" data-qty-plus aria-label="<?php esc_attr_e( 'Increase packet quantity', 'raghushree' ); ?>">+</button></div>
		<input type="hidden" name="option" value="packet_1">
		<?php if ( ! empty( $available['sample'] ) ) : ?><label class="purchase-sample"><input type="checkbox" data-sample> <?php esc_html_e( 'Order free sample instead', 'raghushree' ); ?></label><?php endif; ?>
		<small><?php echo esc_html( $commerce['packet_label'] ?: __( 'One quantity is one packet.', 'raghushree' ) ); ?></small>
		<?php else : ?>
		<label for="quote-pack"><?php esc_html_e( 'Choose pack size', 'raghushree' ); ?></label>
		<select id="quote-pack" name="option" required><?php foreach ( $available as $key => $pack ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $pack['label'] ); ?></option><?php endforeach; ?></select>
		<?php endif; ?>
		<p class="purchase-quantity-note" data-purchase-note><?php echo esc_html( 'pipe' === $commerce['type'] ? __( 'Over 30 kg? Contact us for a custom quote.', 'raghushree' ) : __( '30 kg or more? Contact us for a custom quote.', 'raghushree' ) ); ?></p>
		<div class="purchase-actions"><a class="btn btn--primary" href="#product-delivery"><?php esc_html_e( 'Buy now', 'raghushree' ); ?></a><a class="btn btn--dark" href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'Enquire now', 'raghushree' ); ?></a></div>
		<div id="product-delivery" class="purchase-delivery"><label for="quote-pincode"><?php esc_html_e( 'Check delivery', 'raghushree' ); ?></label><div class="purchase-delivery__controls"><input id="quote-pincode" name="pincode" type="text" inputmode="numeric" pattern="[1-9][0-9]{5}" maxlength="6" placeholder="<?php esc_attr_e( 'Enter delivery PIN', 'raghushree' ); ?>" required><button type="submit"><?php esc_html_e( 'Check', 'raghushree' ); ?></button></div><p class="product-quote__result" role="status" aria-live="polite"></p></div>
	</form>
	<?php else : ?><p><?php esc_html_e( 'Pricing is being confirmed. Please contact us for a quote.', 'raghushree' ); ?></p><a class="btn btn--dark" href="<?php echo esc_url( $enquire ); ?>"><?php esc_html_e( 'Enquire now', 'raghushree' ); ?></a><?php endif; ?>
</div>
