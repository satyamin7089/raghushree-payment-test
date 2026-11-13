<?php
/**
 * Contact Page Sections 1–3: Hero, Contact Information & 11-Field Industrial RFQ Form.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company       = raghushree_get_company_info();
$whatsapp_url  = raghushree_get_whatsapp_url();
$state         = raghushree_get_enquiry_state();
$catalog_items = raghushree_get_catalog_items( 12 );

// Support safe URL query prefill when buyers click "Request a Quote" or "Enquire" from any page
$prefill_type     = isset( $_GET['enquiry_type'] ) ? sanitize_text_field( wp_unslash( $_GET['enquiry_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$prefill_material = isset( $_GET['material'] ) ? sanitize_text_field( wp_unslash( $_GET['material'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$vals   = $state['values'];
$errors = $state['errors'];

$val_name    = isset( $vals['name'] ) ? $vals['name'] : '';
$val_company = isset( $vals['company'] ) ? $vals['company'] : '';
$val_email   = isset( $vals['email'] ) ? $vals['email'] : '';
$val_phone   = isset( $vals['phone'] ) ? $vals['phone'] : '';
$val_country = isset( $vals['country'] ) ? $vals['country'] : 'India';
$val_type    = isset( $vals['enquiry_type'] ) ? $vals['enquiry_type'] : $prefill_type;
$val_mat     = isset( $vals['material'] ) ? $vals['material'] : $prefill_material;
$val_grade   = isset( $vals['grade'] ) ? $vals['grade'] : '';
$val_qty     = isset( $vals['quantity'] ) ? $vals['quantity'] : '';
$val_app     = isset( $vals['application'] ) ? $vals['application'] : '';
$val_msg     = isset( $vals['message'] ) ? $vals['message'] : '';

$enquiry_types = array(
	'Request a Quotation',
	'Material Availability',
	'Sample Request',
	'Technical Consultation',
	'Bulk Order Enquiry',
	'Distributor Enquiry',
);

$material_options = array();
if ( ! empty( $catalog_items ) ) {
	foreach ( $catalog_items as $c_item ) {
		$material_options[] = $c_item['title'];
	}
} else {
	$material_options = array(
		'Polypropylene (PP)',
		'High-Density Polyethylene (HDPE)',
		'Low-Density Polyethylene (LDPE)',
		'Linear Low-Density Polyethylene (LLDPE)',
		'Polyvinyl Chloride (PVC)',
		'Polyethylene Terephthalate (PET)',
		'Recycled & Reprocessed Polymers',
	);
}
$material_options[] = 'Multiple Polymers / Custom Requirement';

$app_options = array(
	'Injection Moulding',
	'Blow Moulding',
	'Film & Packaging',
	'Extrusion',
	'Pipes & Fittings',
	'Electrical & Electronics',
	'Household & Consumer Products',
	'Distribution / Resale',
	'Other Industrial Application',
);
?>
<!-- Section 1: Contact Hero -->
<?php
get_template_part(
	'template-parts/components/page-banner',
	null,
	array(
		'id'            => 'contact-hero',
		'eyebrow'       => __( 'Contact Sales & Technical Desk', 'raghushree' ),
		'title'         => __( 'Request a Polymer Quotation, Sample or Availability Check', 'raghushree' ),
		'description'   => __( 'Connect directly with Raghushree Plast Products Pvt. Ltd. for bulk raw material quotations, polymer grade selection, sample evaluation requests, export enquiries, or distributor partnerships.', 'raghushree' ),
		'primary_cta'   => array(
			'label' => __( 'Submit Material Enquiry', 'raghushree' ),
			'url'   => '#enquiry-form',
		),
		'secondary_cta' => array(
			'kicker' => __( 'Polymer Portfolio', 'raghushree' ),
			'label'  => __( 'Explore Polymer Catalog', 'raghushree' ),
			'url'    => raghushree_get_products_url(),
			'icon'   => 'layers',
		),
		'image_slot'    => 'contact_hero',
		'post_id'       => get_queried_object_id(),
		'caption_title' => __( 'Direct Commercial & RFQ Support', 'raghushree' ),
		'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
	)
);
?>

<!-- Section 2: Verified Contact Information -->
<section class="section section--compact surface--pearl" id="contact-information">
	<div class="container">
		<div class="contact-cards">
			<?php if ( ! empty( $company['full_address'] ) ) : ?>
				<div class="contact-card">
					<div class="contact-card__icon">
						<?php echo raghushree_icon( 'map-pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="contact-card__label"><?php esc_html_e( 'Registered Address', 'raghushree' ); ?></div>
					<div class="contact-card__value"><?php echo esc_html( $company['legal_name'] ); ?></div>
					<p><?php echo esc_html( $company['full_address'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $company['phone'] ) && ! empty( $company['phone_href'] ) ) : ?>
				<div class="contact-card">
					<div class="contact-card__icon">
						<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="contact-card__label"><?php esc_html_e( 'Sales Telephone', 'raghushree' ); ?></div>
					<div class="contact-card__value">
						<a href="<?php echo esc_url( $company['phone_href'] ); ?>"><?php echo esc_html( $company['phone'] ); ?></a>
					</div>
					<p><?php esc_html_e( 'Direct telephone line for domestic & international business enquiries.', 'raghushree' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $company['email'] ) ) : ?>
				<div class="contact-card">
					<div class="contact-card__icon">
						<?php echo raghushree_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="contact-card__label"><?php esc_html_e( 'Marketing & RFQ Email', 'raghushree' ); ?></div>
					<div class="contact-card__value">
						<a href="mailto:<?php echo esc_attr( $company['email'] ); ?>"><?php echo esc_html( $company['email'] ); ?></a>
					</div>
					<p><?php esc_html_e( 'Send detailed purchase schedules, technical requirements, or export RFQs.', 'raghushree' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $whatsapp_url ) ) : ?>
				<div class="contact-card">
					<div class="contact-card__icon">
						<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<div class="contact-card__label"><?php esc_html_e( 'WhatsApp Business Chat', 'raghushree' ); ?></div>
					<div class="contact-card__value">
						<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $company['whatsapp'] ); ?>
						</a>
					</div>
					<p><?php esc_html_e( 'Start a quick WhatsApp conversation with our commercial desk.', 'raghushree' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<!-- Section 3: 11-Field Industrial Enquiry Form -->
<section class="section surface--tint" id="enquiry-form">
	<div class="container">
		<div class="enquiry-layout">
			<div class="enquiry-form-card">
				<div class="eyebrow"><?php esc_html_e( 'Business Enquiry & RFQ Form', 'raghushree' ); ?></div>
				<h2><?php esc_html_e( 'Submit Your Material Specification', 'raghushree' ); ?></h2>
				<p style="margin-bottom: var(--space-6);">
					<?php esc_html_e( 'Complete the details below so our sales and technical team can confirm verified polymer grade availability, forms, and commercial pricing.', 'raghushree' ); ?>
				</p>

				<?php if ( $state['submitted'] && ! empty( $state['message'] ) ) : ?>
					<div class="form-notice <?php echo $state['success'] ? 'form-notice--success' : 'form-notice--error'; ?>" role="alert">
						<?php echo esc_html( $state['message'] ); ?>
					</div>
				<?php endif; ?>

				<form id="raghushree-enquiry-form" method="post" action="<?php echo esc_url( raghushree_get_page_url( 'contact' ) . '#enquiry-form' ); ?>" novalidate>
					<?php wp_nonce_field( 'raghushree_submit_enquiry', 'raghushree_enquiry_nonce' ); ?>
					<input type="hidden" name="raghushree_enquiry_action" value="submit_rfq" />

					<!-- Accessible Anti-Spam Honeypot -->
					<div class="raghushree-hp" aria-hidden="true">
						<label for="company_website_url"><?php esc_html_e( 'Leave this field blank', 'raghushree' ); ?></label>
						<input type="text" id="company_website_url" name="company_website_url" value="" tabindex="-1" autocomplete="off" />
					</div>

					<div class="form-grid form-grid--2">
						<!-- 1. Name -->
						<div class="form-field<?php echo isset( $errors['name'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_name">
								<?php esc_html_e( 'Full Name', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<input class="form-input" type="text" id="enquiry_name" name="enquiry_name" value="<?php echo esc_attr( $val_name ); ?>" autocomplete="name" required <?php echo isset( $errors['name'] ) ? 'aria-invalid="true"' : ''; ?> />
							<?php if ( isset( $errors['name'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['name'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 2. Company Name -->
						<div class="form-field<?php echo isset( $errors['company'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_company">
								<?php esc_html_e( 'Company Name', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<input class="form-input" type="text" id="enquiry_company" name="enquiry_company" value="<?php echo esc_attr( $val_company ); ?>" autocomplete="organization" required <?php echo isset( $errors['company'] ) ? 'aria-invalid="true"' : ''; ?> />
							<?php if ( isset( $errors['company'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['company'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 3. Email -->
						<div class="form-field<?php echo isset( $errors['email'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_email">
								<?php esc_html_e( 'Business Email', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<input class="form-input" type="email" id="enquiry_email" name="enquiry_email" value="<?php echo esc_attr( $val_email ); ?>" autocomplete="email" required <?php echo isset( $errors['email'] ) ? 'aria-invalid="true"' : ''; ?> />
							<?php if ( isset( $errors['email'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['email'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 4. Phone / WhatsApp -->
						<div class="form-field<?php echo isset( $errors['phone'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_phone">
								<?php esc_html_e( 'Phone / WhatsApp Number', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<input class="form-input" type="tel" id="enquiry_phone" name="enquiry_phone" value="<?php echo esc_attr( $val_phone ); ?>" autocomplete="tel" placeholder="+91 ..." required <?php echo isset( $errors['phone'] ) ? 'aria-invalid="true"' : ''; ?> />
							<?php if ( isset( $errors['phone'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['phone'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 5. Country -->
						<div class="form-field<?php echo isset( $errors['country'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_country">
								<?php esc_html_e( 'Country / Market', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<input class="form-input" type="text" id="enquiry_country" name="enquiry_country" value="<?php echo esc_attr( $val_country ); ?>" autocomplete="country-name" required <?php echo isset( $errors['country'] ) ? 'aria-invalid="true"' : ''; ?> />
							<?php if ( isset( $errors['country'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['country'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 6. Enquiry Type -->
						<div class="form-field<?php echo isset( $errors['enquiry_type'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_type">
								<?php esc_html_e( 'Enquiry Type', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<select class="form-select" id="enquiry_type" name="enquiry_type" required <?php echo isset( $errors['enquiry_type'] ) ? 'aria-invalid="true"' : ''; ?>>
								<option value=""><?php esc_html_e( 'Select enquiry type', 'raghushree' ); ?></option>
								<?php foreach ( $enquiry_types as $etype ) : ?>
									<option value="<?php echo esc_attr( $etype ); ?>" <?php selected( $val_type, $etype ); ?>>
										<?php echo esc_html( $etype ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php if ( isset( $errors['enquiry_type'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['enquiry_type'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 7. Material -->
						<div class="form-field<?php echo isset( $errors['material'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_material">
								<?php esc_html_e( 'Material / Polymer Category', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<select class="form-select" id="enquiry_material" name="enquiry_material" required <?php echo isset( $errors['material'] ) ? 'aria-invalid="true"' : ''; ?>>
								<option value=""><?php esc_html_e( 'Select polymer material', 'raghushree' ); ?></option>
								<?php foreach ( $material_options as $m_opt ) : ?>
									<option value="<?php echo esc_attr( $m_opt ); ?>" <?php selected( $val_mat, $m_opt ); ?>>
										<?php echo esc_html( $m_opt ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<?php if ( isset( $errors['material'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['material'] ); ?></span>
							<?php endif; ?>
						</div>

						<!-- 8. Grade / Specification -->
						<div class="form-field">
							<label class="form-label" for="enquiry_grade">
								<?php esc_html_e( 'Preferred Grade / Form / Specification', 'raghushree' ); ?>
							</label>
							<input class="form-input" type="text" id="enquiry_grade" name="enquiry_grade" value="<?php echo esc_attr( $val_grade ); ?>" placeholder="<?php esc_attr_e( 'e.g. Granules, Powder, Reprocessed, MFI target', 'raghushree' ); ?>" />
						</div>

						<!-- 9. Estimated Quantity -->
						<div class="form-field">
							<label class="form-label" for="enquiry_quantity">
								<?php esc_html_e( 'Estimated Quantity', 'raghushree' ); ?>
							</label>
							<input class="form-input" type="text" id="enquiry_quantity" name="enquiry_quantity" value="<?php echo esc_attr( $val_qty ); ?>" placeholder="<?php esc_attr_e( 'e.g. Trial sample, MT / kg per month', 'raghushree' ); ?>" />
						</div>

						<!-- 10. Application -->
						<div class="form-field">
							<label class="form-label" for="enquiry_application">
								<?php esc_html_e( 'Manufacturing Application', 'raghushree' ); ?>
							</label>
							<select class="form-select" id="enquiry_application" name="enquiry_application">
								<option value=""><?php esc_html_e( 'Select primary application (optional)', 'raghushree' ); ?></option>
								<?php foreach ( $app_options as $a_opt ) : ?>
									<option value="<?php echo esc_attr( $a_opt ); ?>" <?php selected( $val_app, $a_opt ); ?>>
										<?php echo esc_html( $a_opt ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>

						<!-- 11. Message -->
						<div class="form-field form-field--full<?php echo isset( $errors['message'] ) ? ' form-field--error' : ''; ?>">
							<label class="form-label" for="enquiry_message">
								<?php esc_html_e( 'Requirement Details & Message', 'raghushree' ); ?><span class="form-label__req" aria-hidden="true">*</span>
							</label>
							<textarea class="form-textarea" id="enquiry_message" name="enquiry_message" placeholder="<?php esc_attr_e( 'Share details on your end-product, colour/natural preference, processing equipment, delivery location, or questions for our team.', 'raghushree' ); ?>" required <?php echo isset( $errors['message'] ) ? 'aria-invalid="true"' : ''; ?>><?php echo esc_textarea( $val_msg ); ?></textarea>
							<?php if ( isset( $errors['message'] ) ) : ?>
								<span class="form-error" role="alert"><?php echo esc_html( $errors['message'] ); ?></span>
							<?php endif; ?>
						</div>
					</div>

					<div style="margin-top: var(--space-5);">
						<button type="submit" class="btn btn--primary">
							<span><?php esc_html_e( 'Submit Business Enquiry', 'raghushree' ); ?></span>
							<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</button>
					</div>
				</form>
			</div>

			<aside class="enquiry-sidebar" aria-label="<?php esc_attr_e( 'Enquiry Guidance', 'raghushree' ); ?>">
				<div class="enquiry-sidebar__box">
					<h3><?php esc_html_e( 'What Helps Us Quote Faster', 'raghushree' ); ?></h3>
					<ul class="enquiry-checklist">
						<li>
							<?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Polymer family (PP, HDPE, LDPE, LLDPE, PVC, PET, or Recycled)', 'raghushree' ); ?></span>
						</li>
						<li>
							<?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Processing method (injection, blow moulding, film, pipe extrusion)', 'raghushree' ); ?></span>
						</li>
						<li>
							<?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Preferred form (plastic granules or powder where applicable)', 'raghushree' ); ?></span>
						</li>
						<li>
							<?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Estimated trial or bulk quantity and destination city/port', 'raghushree' ); ?></span>
						</li>
					</ul>
				</div>

				<div class="enquiry-sidebar__box enquiry-sidebar__box--dark surface--ink">
					<h3><?php esc_html_e( 'Direct Sales Assistance', 'raghushree' ); ?></h3>
					<p><?php esc_html_e( 'Prefer speaking directly with our commercial team in Kanpur? Reach out via telephone or WhatsApp.', 'raghushree' ); ?></p>
					<div style="margin-top: var(--space-4); display: flex; flex-direction: column; gap: var(--space-2);">
						<?php if ( ! empty( $whatsapp_url ) ) : ?>
							<a class="btn btn--primary btn--full" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php esc_html_e( 'WhatsApp Sales Team', 'raghushree' ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( ! empty( $company['phone_href'] ) ) : ?>
							<a class="btn btn--on-dark btn--full" href="<?php echo esc_url( $company['phone_href'] ); ?>">
								<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<span><?php echo esc_html( $company['phone'] ); ?></span>
							</a>
						<?php endif; ?>
					</div>
				</div>
				<div class="enquiry-sidebar__media">
					<?php raghushree_render_image( 'contact_hero', array( 'sizes' => '(min-width: 900px) 35vw, 100vw', 'priority' => false ) ); ?>
				</div>
			</aside>
		</div>
	</div>
</section>
