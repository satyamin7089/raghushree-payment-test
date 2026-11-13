<?php
/**
 * Contact Page Sections 4–9: Material Enquiry Options, Supported Enquiry Types,
 * Direct Phone/WhatsApp Channels, Location & Coverage, FAQs & Final CTA.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company       = raghushree_get_company_info();
$whatsapp_url  = raghushree_get_whatsapp_url();
$catalog_items = raghushree_get_catalog_items( 7 );

$supported_types = array(
	array(
		'icon'  => 'file-text',
		'title' => __( 'Commercial Quotations', 'raghushree' ),
		'desc'  => __( 'Prompt pricing proposals for verified polymer grades, granules, or powder based on volume and destination.', 'raghushree' ),
	),
	array(
		'icon'  => 'layers',
		'title' => __( 'Material Availability Checks', 'raghushree' ),
		'desc'  => __( 'Confirm current supply of specific polymer categories, reprocessed/recycled grades, or colour classifications.', 'raghushree' ),
	),
	array(
		'icon'  => 'box',
		'title' => __( 'Sample Evaluation Requests', 'raghushree' ),
		'desc'  => __( 'Arrange evaluation material for production trials, laboratory checks, and quality control sign-off.', 'raghushree' ),
	),
	array(
		'icon'  => 'settings',
		'title' => __( 'Technical Consultation', 'raghushree' ),
		'desc'  => __( 'Discuss melt flow behaviour, moulding/extrusion compatibility, and polymer form suitability.', 'raghushree' ),
	),
	array(
		'icon'  => 'truck',
		'title' => __( 'Bulk & Repeat Order Planning', 'raghushree' ),
		'desc'  => __( 'Coordinate scheduled dispatches and full-load raw material procurement for ongoing plant production.', 'raghushree' ),
	),
	array(
		'icon'  => 'globe',
		'title' => __( 'Distributor & Export Enquiries', 'raghushree' ),
		'desc'  => __( 'Connect regarding regional dealer distribution in India or international polymer export shipments.', 'raghushree' ),
	),
);

$contact_faqs = array(
	array(
		'question' => __( 'What information should I include when requesting a polymer quotation?', 'raghushree' ),
		'answer'   => __( 'Please specify the polymer category (PP, HDPE, LDPE, LLDPE, PVC, PET, or recycled/reprocessed), preferred physical form or grade requirement, manufacturing application, estimated quantity, and delivery city or country.', 'raghushree' ),
	),
	array(
		'question' => __( 'Can I enquire about multiple polymer categories in a single submission?', 'raghushree' ),
		'answer'   => __( 'Yes. Select "Multiple Polymers / Custom Requirement" in the Material field and list each polymer grade and estimated quantity in your message.', 'raghushree' ),
	),
	array(
		'question' => __( 'Do you support WhatsApp for quick procurement communication?', 'raghushree' ),
		'answer'   => __( 'Yes. Buyers can initiate a direct WhatsApp conversation with our commercial team for quick availability questions or follow-ups alongside formal email quotations.', 'raghushree' ),
	),
	array(
		'question' => __( 'Where is Raghushree located?', 'raghushree' ),
		'answer'   => sprintf(
			/* translators: 1: Legal company name, 2: Verified address */
			__( '%1$s is located at %2$s, serving industrial customers across India and international export markets.', 'raghushree' ),
			$company['legal_name'],
			$company['full_address']
		),
	),
);
?>
<!-- Section 4: Product and Material Enquiry Options -->
<section class="section surface--white" id="material-enquiry-options">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Material Enquiry Options', 'raghushree' ),
				'title'       => __( 'Enquire Directly by Polymer Category', 'raghushree' ),
				'description' => __( 'Review individual polymer pages or pre-select a material category to open a targeted quotation request.', 'raghushree' ),
			)
		);
		?>
		<div class="grid-4">
			<?php foreach ( $catalog_items as $c_item ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'meta'        => $c_item['polymer_code'],
						'title'       => $c_item['title'],
						'description' => $c_item['short_desc'],
						'link_label'  => __( 'View Polymer Details', 'raghushree' ),
						'link_url'    => $c_item['permalink'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Section 5: Types of Enquiries Supported -->
<section class="section surface--pearl" id="supported-enquiries">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Supported Business Enquiries', 'raghushree' ),
				'title'       => __( 'Dedicated Support Across Every Stage of Procurement', 'raghushree' ),
			)
		);
		?>
		<div class="grid-3">
			<?php foreach ( $supported_types as $stype ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'icon'        => $stype['icon'],
						'title'       => $stype['title'],
						'description' => $stype['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- Section 6: Phone and WhatsApp Contact Options -->
<section class="section surface--white" id="phone-whatsapp-options">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow' => __( 'Direct Channels', 'raghushree' ),
				'title'   => __( 'Telephone & WhatsApp Sales Coordination', 'raghushree' ),
			)
		);
		?>
		<div class="direct-channels">
			<?php if ( ! empty( $company['phone'] ) && ! empty( $company['phone_href'] ) ) : ?>
				<div class="channel-panel">
					<div>
						<div class="eyebrow"><?php esc_html_e( 'Telephone Desk', 'raghushree' ); ?></div>
						<h3><?php esc_html_e( 'Speak with Our Commercial Team', 'raghushree' ); ?></h3>
						<p><?php esc_html_e( 'Discuss urgent raw material requirements, bulk order dispatches, or grade specifications directly by phone.', 'raghushree' ); ?></p>
					</div>
					<div>
						<a class="btn btn--dark" href="<?php echo esc_url( $company['phone_href'] ); ?>">
							<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php echo esc_html( $company['phone'] ); ?></span>
						</a>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $whatsapp_url ) ) : ?>
				<div class="channel-panel">
					<div>
						<div class="eyebrow"><?php esc_html_e( 'Instant Messaging', 'raghushree' ); ?></div>
						<h3><?php esc_html_e( 'Start a WhatsApp Conversation', 'raghushree' ); ?></h3>
						<p><?php esc_html_e( 'Share your required polymer grade, target application, and quantity on WhatsApp for fast sales coordination.', 'raghushree' ); ?></p>
					</div>
					<div>
						<a class="btn btn--primary" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Message on WhatsApp', 'raghushree' ); ?></span>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- Section 7: Verified Location and Coverage Information -->
<section class="section surface--tint" id="location-and-coverage">
	<div class="container">
		<div class="market-regions">
			<div class="market-card">
				<div class="eyebrow"><?php esc_html_e( 'Verified Office Location', 'raghushree' ); ?></div>
				<h3><?php echo esc_html( $company['legal_name'] ); ?></h3>
				<p><?php echo esc_html( $company['full_address'] ); ?></p>
				<?php if ( ! empty( $company['email'] ) ) : ?>
					<p style="margin-top: var(--space-3);">
						<strong><?php esc_html_e( 'Email:', 'raghushree' ); ?></strong>
						<a href="mailto:<?php echo esc_attr( $company['email'] ); ?>"><?php echo esc_html( $company['email'] ); ?></a>
					</p>
				<?php endif; ?>
			</div>

			<div class="market-card">
				<div class="eyebrow"><?php esc_html_e( 'Supply Coverage', 'raghushree' ); ?></div>
				<h3><?php esc_html_e( 'Domestic India & Global Export Enquiries', 'raghushree' ); ?></h3>
				<p><?php esc_html_e( 'We coordinate polymer raw material supply across Indian manufacturing states as well as international export destinations based on verified commercial terms.', 'raghushree' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- Section 8: Contact FAQs -->
<section class="section surface--white" id="contact-faqs">
	<div class="container--narrow">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow' => __( 'Enquiry FAQs', 'raghushree' ),
				'title'   => __( 'Frequently Asked Procurement Questions', 'raghushree' ),
				'align'   => 'center',
			)
		);

		get_template_part(
			'template-parts/components/faq',
			null,
			array(
				'faqs'      => $contact_faqs,
				'id_prefix' => 'contact-faq',
			)
		);
		?>
	</div>
</section>

<!-- Section 9: Final CTA -->
<section class="section surface--pearl" id="contact-final-cta">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/cta-banner',
			null,
			array(
				'eyebrow'     => __( 'Explore Polymer Portfolio', 'raghushree' ),
				'title'       => __( 'Review Polymer Categories Before Submitting Your RFQ', 'raghushree' ),
				'description' => __( 'Browse our complete catalog of PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymer raw materials.', 'raghushree' ),
			)
		);
		?>
	</div>
</section>
