<?php
/**
 * Reusable Final Conversion CTA Panel Component.
 *
 * Args:
 * - eyebrow (string)
 * - title (string)
 * - description (string)
 * - material (optional prefill polymer title)
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company     = raghushree_get_company_info();
$eyebrow     = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : __( 'Procurement & Technical Enquiries', 'raghushree' );
$title       = ! empty( $args['title'] ) ? $args['title'] : __( 'Discuss Your Polymer Raw Material Requirements with Raghushree', 'raghushree' );
$description = ! empty( $args['description'] ) ? $args['description'] : __( 'Request a quotation, confirm current polymer grade and form availability, arrange material samples, or discuss domestic and export bulk orders with our sales team.', 'raghushree' );
$material    = ! empty( $args['material'] ) ? $args['material'] : '';

$rfq_params = array( 'enquiry_type' => 'Request a Quotation' );
$wa_msg     = 'Hello Raghushree team, I would like to enquire about plastic raw materials.';
if ( '' !== $material ) {
	$rfq_params['material'] = $material;
	$wa_msg                 = sprintf( 'Hello Raghushree team, I would like to enquire about %s raw material availability and pricing.', $material );
}

$quote_url    = raghushree_get_enquiry_url( $rfq_params );
$whatsapp_url = raghushree_get_whatsapp_url( $wa_msg );
?>
<div class="cta-panel surface--ink reveal">
	<div class="cta-panel__grid">
		<div>
			<div class="eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<h2 class="cta-panel__title"><?php echo esc_html( $title ); ?></h2>
			<p><?php echo esc_html( $description ); ?></p>

			<div class="cta-panel__meta">
				<?php if ( ! empty( $company['phone'] ) && ! empty( $company['phone_href'] ) ) : ?>
					<a href="<?php echo esc_url( $company['phone_href'] ); ?>">
						<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $company['phone'] ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $company['email'] ) ) : ?>
					<a href="mailto:<?php echo esc_attr( $company['email'] ); ?>">
						<?php echo raghushree_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $company['email'] ); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="cta-panel__actions">
			<a class="btn btn--primary" href="<?php echo esc_url( $quote_url ); ?>">
				<span><?php esc_html_e( 'Request a Quotation', 'raghushree' ); ?></span>
				<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
			<?php if ( ! empty( $whatsapp_url ) ) : ?>
				<a class="btn btn--on-dark" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span><?php esc_html_e( 'Start WhatsApp Chat', 'raghushree' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
