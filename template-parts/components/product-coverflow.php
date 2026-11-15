<?php
/**
 * Consumer Product Coverflow — centre-focused image slider.
 *
 * The active slide is sharp and centred; the neighbouring slides sit behind it,
 * scaled down, blurred and dimmed. Clicking the centre "+" opens the product
 * page. Arrows, dots, keyboard arrows, swipe and reduced-motion are supported,
 * and without JavaScript the first slide simply renders centred and legible.
 *
 * Args:
 * - id (string) Optional section anchor id.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args         = ( isset( $args ) && is_array( $args ) ) ? $args : array();
$section_id   = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'shop-showcase';
$slides_items = raghushree_get_catalog_items( 9 );
$slide_total  = count( $slides_items );

if ( empty( $slides_items ) ) {
	return;
}
?>
<section
	class="section surface--ink shopflow"
	id="<?php echo esc_attr( $section_id ); ?>"
	data-shopflow
	aria-roledescription="<?php esc_attr_e( 'carousel', 'raghushree' ); ?>"
	aria-label="<?php esc_attr_e( 'Product image showcase', 'raghushree' ); ?>"
>
	<div class="container shopflow__head">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow' => __( 'Product Showcase', 'raghushree' ),
				'title'   => __( 'Explore Every Polymer Packet', 'raghushree' ),
				'align'   => 'center',
			)
		);
		?>
	</div>

	<div class="shopflow__stage">
		<button type="button" class="shopflow__arrow shopflow__arrow--prev" data-shopflow-prev aria-label="<?php esc_attr_e( 'Previous product', 'raghushree' ); ?>">
			<?php echo raghushree_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<div class="shopflow__viewport" data-shopflow-viewport>
			<?php foreach ( $slides_items as $index => $item ) : ?>
				<?php
				$is_active   = 0 === $index;
				$item_price  = raghushree_product_price_summary( (int) $item['id'] );
				$kicker_bits = array();

				if ( ! empty( $item['primary_cat'] ) ) {
					$kicker_bits[] = $item['primary_cat'];
				}
				$kicker_bits[] = ! empty( $item_price['has_price'] )
					/* translators: %s: formatted packet price, e.g. ₹1,250. */
					? sprintf( __( 'From %s', 'raghushree' ), $item_price['display'] )
					: __( 'Price on request', 'raghushree' );
				?>
				<article
					class="shopflow__slide<?php echo $is_active ? ' is-active' : ''; ?>"
					data-shopflow-slide
					data-index="<?php echo esc_attr( (string) $index ); ?>"
					role="group"
					aria-roledescription="<?php esc_attr_e( 'slide', 'raghushree' ); ?>"
					aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total slides, 3: product title. */ __( '%1$d of %2$d: %3$s', 'raghushree' ), $index + 1, $slide_total, $item['title'] ) ); ?>"
					aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>"
				>
					<a
						class="shopflow__link"
						href="<?php echo esc_url( $item['permalink'] ); ?>"
						<?php echo $is_active ? '' : ' tabindex="-1"'; ?>
						aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product title. */ __( 'Open the %s product page', 'raghushree' ), $item['title'] ) ); ?>"
					>
						<?php
						raghushree_render_image(
							$item['image_slot'],
							array(
								'post_id'  => $item['id'],
								'size'     => 'raghushree-hero',
								'sizes'    => '(min-width: 900px) 520px, 84vw',
								'priority' => $is_active,
								'class'    => 'shopflow__img',
							)
						);
						?>
						<span class="shopflow__scrim" aria-hidden="true"></span>
						<span class="shopflow__plus" aria-hidden="true">
							<?php echo raghushree_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span class="shopflow__caption">
							<span class="shopflow__kicker"><?php echo esc_html( implode( ' · ', $kicker_bits ) ); ?></span>
							<span class="shopflow__title"><?php echo esc_html( $item['title'] ); ?></span>
						</span>
					</a>
				</article>
			<?php endforeach; ?>
		</div>

		<button type="button" class="shopflow__arrow shopflow__arrow--next" data-shopflow-next aria-label="<?php esc_attr_e( 'Next product', 'raghushree' ); ?>">
			<?php echo raghushree_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>

	<div class="shopflow__dots" role="tablist" aria-label="<?php esc_attr_e( 'Choose a product to show', 'raghushree' ); ?>">
		<?php foreach ( $slides_items as $index => $item ) : ?>
			<button
				type="button"
				class="shopflow__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
				role="tab"
				data-shopflow-dot="<?php echo esc_attr( (string) $index ); ?>"
				aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
				aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product title. */ __( 'Show %s', 'raghushree' ), $item['title'] ) ); ?>"
			></button>
		<?php endforeach; ?>
	</div>
</section>
