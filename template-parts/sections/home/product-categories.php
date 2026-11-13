<?php
/**
 * Homepage Section 4: Product Categories (Dynamic WordPress `products` CPT).
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog_items = raghushree_get_catalog_items( 7 );
?>
<section class="section surface--pearl" id="product-categories">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Polymer Portfolio', 'raghushree' ),
				'title'       => __( 'Plastic Raw Material Categories Supplied by Raghushree', 'raghushree' ),
				'description' => __( 'Explore our core polymer families including PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymers. Select any material to review processing applications or submit a grade enquiry.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<?php if ( ! empty( $catalog_items ) ) : ?>
			<div class="grid-3">
				<?php foreach ( $catalog_items as $prod ) : ?>
					<?php
					get_template_part(
						'template-parts/components/product-card',
						null,
						array( 'product' => $prod )
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div style="margin-top: var(--space-7); text-align: center;">
			<a class="btn btn--dark" href="<?php echo esc_url( raghushree_get_products_url() ); ?>">
				<span><?php esc_html_e( 'View Complete Polymer Catalog', 'raghushree' ); ?></span>
				<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</section>
