<?php
/**
 * Homepage Section 2: Business Strength / Trust Bar.
 *
 * Uses strictly verified business positioning — zero fabricated claims.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$strengths = array(
	array(
		'icon'  => 'layers',
		'title' => __( 'Broad Polymer Portfolio', 'raghushree' ),
		'desc'  => __( 'Supporting PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymers.', 'raghushree' ),
	),
	array(
		'icon'  => 'box',
		'title' => __( 'Multiple Material Forms', 'raghushree' ),
		'desc'  => __( 'Available in plastic granules, plastic powder, and verified reprocessed or recycled grades.', 'raghushree' ),
	),
	array(
		'icon'  => 'settings',
		'title' => __( 'Application-Aligned Guidance', 'raghushree' ),
		'desc'  => __( 'Helping production, R&D, and procurement teams match grades to moulding and extrusion lines.', 'raghushree' ),
	),
	array(
		'icon'  => 'globe',
		'title' => __( 'India & Export Supply', 'raghushree' ),
		'desc'  => __( 'Serving manufacturing hubs across India alongside international export business enquiries.', 'raghushree' ),
	),
);
?>
<section class="section section--compact surface--ink" id="business-strengths" aria-label="<?php esc_attr_e( 'Core Business Strengths', 'raghushree' ); ?>">
	<div class="container">
		<div class="trust-bar__grid">
			<?php foreach ( $strengths as $item ) : ?>
				<div class="trust-item">
					<div class="trust-item__icon">
						<?php echo raghushree_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div>
						<h2 class="trust-item__title"><?php echo esc_html( $item['title'] ); ?></h2>
						<div class="trust-item__desc"><?php echo esc_html( $item['desc'] ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
