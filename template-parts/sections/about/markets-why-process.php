<?php
/**
 * About Page Sections 9–12: Markets & Global Reach, Why Choose Raghushree,
 * Customer Enquiry Process & Final CTA.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$why_items = array(
	array(
		'icon'  => 'layers',
		'title' => __( 'Multi-Polymer Coverage', 'raghushree' ),
		'desc'  => __( 'PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymers managed through one responsive supplier.', 'raghushree' ),
	),
	array(
		'icon'  => 'shield',
		'title' => __( 'Honest Commercial Representation', 'raghushree' ),
		'desc'  => __( 'Zero exaggerated claims—every quotation reflects verified material availability, grade form, and dispatch terms.', 'raghushree' ),
	),
	array(
		'icon'  => 'box',
		'title' => __( 'Flexible Buyer Engagement', 'raghushree' ),
		'desc'  => __( 'Supporting trial sample evaluations, bulk factory orders, repeat procurement schedules, and distributor enquiries.', 'raghushree' ),
	),
	array(
		'icon'  => 'whatsapp',
		'title' => __( 'Responsive Communication', 'raghushree' ),
		'desc'  => __( 'Direct coordination via email, phone, WhatsApp, and structured RFQ submissions from our Kanpur office.', 'raghushree' ),
	),
);

$enquiry_steps = array(
	array(
		'num'   => '01',
		'title' => __( 'Share Specification', 'raghushree' ),
		'desc'  => __( 'Tell us your polymer category, preferred grade or form, application, and estimated quantity.', 'raghushree' ),
	),
	array(
		'num'   => '02',
		'title' => __( 'Availability Review', 'raghushree' ),
		'desc'  => __( 'We check current verified grades and recommend compatible options or evaluation samples.', 'raghushree' ),
	),
	array(
		'num'   => '03',
		'title' => __( 'Commercial Quote', 'raghushree' ),
		'desc'  => __( 'Receive clear pricing, packaging details, MOQ confirmation, and dispatch terms.', 'raghushree' ),
	),
	array(
		'num'   => '04',
		'title' => __( 'Dispatch & Support', 'raghushree' ),
		'desc'  => __( 'Orders are coordinated smoothly for domestic Indian delivery or international export shipment.', 'raghushree' ),
	),
);
?>
<!-- Section 9: Markets and Global Reach -->
<section class="section surface--tint" id="markets-and-reach">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Markets & Global Reach', 'raghushree' ),
				'title'       => __( 'Serving Core Indian Industrial Hubs & International Export Buyers', 'raghushree' ),
				'description' => __( 'From Uttar Pradesh and pan-India manufacturing corridors to overseas industrial importers, Raghushree welcomes domestic and global polymer enquiries.', 'raghushree' ),
			)
		);
		?>
		<div class="market-regions">
			<div class="market-card">
				<h3><?php esc_html_e( 'Domestic Market — India', 'raghushree' ); ?></h3>
				<p><?php esc_html_e( 'Supporting plastic product manufacturers, moulding units, pipe extruders, film converters, and polymer dealers across major Indian industrial regions.', 'raghushree' ); ?></p>
				<div class="market-card__list">
					<span class="tag tag--accent"><?php esc_html_e( 'Bulk Factory Supply', 'raghushree' ); ?></span>
					<span class="tag"><?php esc_html_e( 'Repeat Scheduled Orders', 'raghushree' ); ?></span>
					<span class="tag"><?php esc_html_e( 'Dealer & Distributor Enquiries', 'raghushree' ); ?></span>
				</div>
			</div>
			<div class="market-card">
				<h3><?php esc_html_e( 'International Export Enquiries', 'raghushree' ); ?></h3>
				<p><?php esc_html_e( 'Handling global business enquiries from overseas converters, industrial buyers, and raw material distributors seeking Indian polymer supply.', 'raghushree' ); ?></p>
				<div class="market-card__list">
					<span class="tag tag--accent"><?php esc_html_e( 'Export Quotations', 'raghushree' ); ?></span>
					<span class="tag"><?php esc_html_e( 'Container & Bulk Enquiries', 'raghushree' ); ?></span>
					<span class="tag"><?php esc_html_e( 'Specification Verification', 'raghushree' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Section 10: Why Choose Raghushree -->
<section class="section surface--white" id="about-why-choose">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Why Choose Raghushree', 'raghushree' ),
				'title'       => __( 'Dependable Execution for Procurement & Manufacturing Leaders', 'raghushree' ),
			)
		);
		?>
		<div class="grid-4">
			<?php foreach ( $why_items as $w ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'icon'        => $w['icon'],
						'title'       => $w['title'],
						'description' => $w['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Section 11: Customer Enquiry Process -->
<section class="section surface--ink" id="customer-enquiry-process">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Customer Enquiry Process', 'raghushree' ),
				'title'       => __( 'How Industrial Buyers Work with Raghushree', 'raghushree' ),
				'description' => __( 'A clear four-stage engagement process designed for purchasing managers, production heads, and business directors.', 'raghushree' ),
			)
		);
		?>
		<div class="process-grid process-grid--4">
			<?php foreach ( $enquiry_steps as $st ) : ?>
				<div class="process-card reveal">
					<span class="process-card__step"><?php echo esc_html( $st['num'] ); ?></span>
					<h3 class="process-card__title"><?php echo esc_html( $st['title'] ); ?></h3>
					<p><?php echo esc_html( $st['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Section 12: Final CTA -->
<section class="section surface--pearl" id="about-final-cta">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/cta-banner',
			null,
			array(
				'eyebrow'     => __( 'Connect with Raghushree', 'raghushree' ),
				'title'       => __( 'Start a Conversation About Your Polymer Raw Material Needs', 'raghushree' ),
				'description' => __( 'Reach out to our Kanpur commercial team to verify grade availability, request evaluation samples, or obtain a bulk order quotation.', 'raghushree' ),
			)
		);
		?>
	</div>
</section>
