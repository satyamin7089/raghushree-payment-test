<?php
/**
 * Consumer Product Tile — image-first "packet" tile.
 *
 * Used only by the 3 × 3 shop grids on the homepage and the About page so the
 * enquiry-led `product-card` component (and therefore the Products archive)
 * keeps its existing layout untouched.
 *
 * Args:
 * - product (array) from raghushree_get_product_data()
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args    = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$product = ( ! empty( $args['product'] ) && is_array( $args['product'] ) ) ? $args['product'] : array();

if ( empty( $product['id'] ) || empty( $product['permalink'] ) ) {
	return;
}

$price      = raghushree_product_price_summary( (int) $product['id'] );
$has_price  = ! empty( $price['has_price'] );
$cta_url    = $has_price ? $product['permalink'] . '#product-purchase' : $product['permalink'];
$cta_label  = $has_price ? __( 'Buy now', 'raghushree' ) : __( 'View product', 'raghushree' );
$cta_class  = $has_price ? 'btn--primary' : 'btn--dark';
if ( $has_price ) {
	/* translators: %s: formatted packet price, e.g. ₹1,250. */
	$price_text = sprintf( __( 'From %s', 'raghushree' ), $price['display'] );
	/* translators: %s: enabled packet label, e.g. 25 kg. */
	$price_note = '' !== $price['label'] ? sprintf( __( 'per %s · before GST', 'raghushree' ), $price['label'] ) : __( 'before GST', 'raghushree' );
} else {
	$price_text = __( 'Price on request', 'raghushree' );
	$price_note = __( 'Confirmed with your quotation', 'raghushree' );
}
?>
<article class="shop-tile reveal">
	<a class="shop-tile__media" href="<?php echo esc_url( $product['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		raghushree_render_image(
			$product['image_slot'],
			array(
				'post_id'  => $product['id'],
				'size'     => 'full',
				'sizes'    => '(min-width: 1200px) 380px, (min-width: 900px) 30vw, (min-width: 600px) 46vw, 92vw',
				'priority' => false,
			)
		);
		?>
	</a>

	<div class="shop-tile__body">
		<?php if ( ! empty( $product['primary_cat'] ) ) : ?>
			<span class="shop-tile__tag"><?php echo esc_html( $product['primary_cat'] ); ?></span>
		<?php endif; ?>
		<h3 class="shop-tile__title">
			<a href="<?php echo esc_url( $product['permalink'] ); ?>"><?php echo esc_html( $product['title'] ); ?></a>
		</h3>

		<p class="shop-tile__price">
			<span class="shop-tile__amount"><?php echo esc_html( $price_text ); ?></span>
			<span class="shop-tile__note"><?php echo esc_html( $price_note ); ?></span>
		</p>

		<a class="btn <?php echo esc_attr( $cta_class ); ?> btn--sm btn--full shop-tile__cta" href="<?php echo esc_url( $cta_url ); ?>">
			<span><?php echo esc_html( $cta_label ); ?></span>
			<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
</article>
