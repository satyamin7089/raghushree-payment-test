<?php
/**
 * Homepage Section 2: Shop the Polymer Packets (3 × 3 image-first grid).
 *
 * Nine published `products` records are shown as image-led tiles with their
 * lowest enabled packet price. The "Explore All" button sits after the grid.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog_items = raghushree_get_catalog_items( 9 );
?>
<section class="section surface--pearl" id="product-categories">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Shop Polymer Packets', 'raghushree' ),
				'title'       => __( 'Buy Polymer Raw Material Packets Online', 'raghushree' ),
				'description' => __( 'Pick a polymer, choose the packet size and order online. Prices appear before GST and shipping, and larger loads can be quoted on request.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<?php if ( ! empty( $catalog_items ) ) : ?>
			<div class="shop-grid">
				<?php foreach ( $catalog_items as $prod ) : ?>
					<?php
					get_template_part(
						'template-parts/components/product-tile',
						null,
						array( 'product' => $prod )
					);
					?>
				<?php endforeach; ?>
			</div>

			<div class="shop-grid__footer">
				<a class="btn btn--dark" href="<?php echo esc_url( raghushree_get_products_url() ); ?>">
					<span><?php esc_html_e( 'Explore All', 'raghushree' ); ?></span>
					<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
