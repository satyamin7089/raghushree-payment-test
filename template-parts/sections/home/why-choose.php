<?php
/**
 * Homepage Section 7: Why Choose Raghushree.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$reasons = array(
	array(
		'icon'  => 'shield',
		'title' => __( 'Verified Grade Communication', 'raghushree' ),
		'desc'  => __( 'We communicate material characteristics, polymer origin, and physical form clearly—helping buyers procure with confidence without guesswork.', 'raghushree' ),
	),
	array(
		'icon'  => 'settings',
		'title' => __( 'Process-Focused Consultation', 'raghushree' ),
		'desc'  => __( 'Whether your shop floor runs high-cavity injection moulds, blown film lines, or pipe extruders, we help align raw material selection to your machinery.', 'raghushree' ),
	),
	array(
		'icon'  => 'file-text',
		'title' => __( 'Sample & Trial Support', 'raghushree' ),
		'desc'  => __( 'Production, R&D, and quality control teams can request evaluation samples and discuss processing parameters prior to bulk procurement.', 'raghushree' ),
	),
	array(
		'icon'  => 'truck',
		'title' => __( 'Bulk & Repeat Order Coordination', 'raghushree' ),
		'desc'  => __( 'Structured quotation turnaround and dependable dispatch planning for recurring factory schedules, dealers, and international buyers.', 'raghushree' ),
	),
	array(
		'icon'  => 'layers',
		'title' => __( 'Primary & Reprocessed Options', 'raghushree' ),
		'desc'  => __( 'Access commodity polymers alongside cost-effective recycled and reprocessed polymer grades from a single commercial point of contact.', 'raghushree' ),
	),
	array(
		'icon'  => 'whatsapp',
		'title' => __( 'Direct Sales Accessibility', 'raghushree' ),
		'desc'  => __( 'Fast, direct access to our Kanpur commercial team via structured RFQ forms, email, telephone, and WhatsApp for real-time procurement support.', 'raghushree' ),
	),
);
?>
<section class="section surface--pearl" id="why-choose-raghushree">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Why Choose Raghushree', 'raghushree' ),
				'title'       => __( 'Built Around Industrial Procurement & Production Realities', 'raghushree' ),
				'description' => __( 'Raw material procurement requires accurate specification matching, responsive communication, and reliable commercial execution across every order cycle.', 'raghushree' ),
			)
		);
		?>

		<div class="grid-3">
			<?php foreach ( $reasons as $reason ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'icon'        => $reason['icon'],
						'title'       => $reason['title'],
						'description' => $reason['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
