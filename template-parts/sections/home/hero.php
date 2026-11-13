<?php
/**
 * Homepage Section 1: Full-Width Hero Banner (Curved Industrial Split Layout).
 *
 * Full-bleed edge-to-edge banner with container-aligned left content preserving
 * Raghushree's verified homepage copy, CTAs, and polymer family tags; right side
 * displays the industrial facility image separated by responsive crimson/white
 * SVG wave ribbons and verified quick-contact actions.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$front_id     = (int) get_option( 'page_on_front' );
$company      = raghushree_get_company_info();
$whatsapp_url = raghushree_get_whatsapp_url( 'Hello Raghushree team, I would like to enquire about plastic raw materials.' );
?>
<section class="section section--hero home-hero" id="home-hero">
	<div class="home-banner">
		<!-- 1. Left / Top Content Aligned to Global Container -->
		<div class="container home-banner__inner">
			<div class="home-banner__content">
				<div class="eyebrow"><?php esc_html_e( 'Manufacturer, Exporter & Supplier of Plastic Raw Materials', 'raghushree' ); ?></div>

				<h1 class="home-banner__title">
					<?php esc_html_e( 'Dependable Polymer Raw Materials for Industrial Manufacturing', 'raghushree' ); ?>
				</h1>

				<p class="home-banner__desc">
					<?php esc_html_e( 'Raghushree supplies Polypropylene (PP), HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymer grades—supporting manufacturers, contract moulders, packaging converters, and distributors across India and global export markets.', 'raghushree' ); ?>
				</p>

				<div class="home-banner__actions">
					<?php
					get_template_part(
						'template-parts/components/buttons',
						null,
						array(
							'buttons' => array(
								array(
									'label'   => __( 'Explore Products', 'raghushree' ),
									'url'     => raghushree_get_products_url(),
									'variant' => 'primary',
									'icon'    => 'arrow-right',
								),
								array(
									'label'   => __( 'Request a Quote', 'raghushree' ),
									'url'     => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Request a Quotation' ) ),
									'variant' => 'dark',
								),
							),
						)
					);
					?>
				</div>

				<div class="home-banner__highlights" aria-label="<?php esc_attr_e( 'Supported Polymer Families', 'raghushree' ); ?>">
					<span class="tag">PP</span>
					<span class="tag">HDPE</span>
					<span class="tag">LDPE</span>
					<span class="tag">LLDPE</span>
					<span class="tag">PVC</span>
					<span class="tag">PET</span>
					<span class="tag tag--accent"><?php esc_html_e( 'Recycled & Reprocessed Polymers', 'raghushree' ); ?></span>
				</div>
			</div>
		</div>

		<!-- 2. Desktop Vertical S-Curve SVG Separator -->
		<svg class="home-banner__desktop-wave" viewBox="0 0 1400 560" preserveAspectRatio="none" aria-hidden="true" focusable="false">
			<defs>
				<linearGradient id="raghushreeHomeWaveDesktop" x1="0%" y1="0%" x2="100%" y2="100%">
					<stop offset="0%" stop-color="#E63946" />
					<stop offset="50%" stop-color="#C5303C" />
					<stop offset="100%" stop-color="#A62530" />
				</linearGradient>
			</defs>
			<path d="M 0,0 L 830,0 C 720,90 690,210 810,330 C 900,420 870,500 930,560 L 0,560 Z" fill="url(#raghushreeHomeWaveDesktop)" />
			<path d="M 0,0 L 790,0 C 680,90 650,210 770,330 C 860,420 830,500 890,560 L 0,560 Z" fill="#FFFFFF" />
		</svg>

		<!-- 3. Right / Bottom Full-Bleed Industrial Image & Mobile Horizontal Wave -->
		<div class="home-banner__media">
			<svg class="home-banner__mobile-wave" viewBox="0 0 1000 120" preserveAspectRatio="none" aria-hidden="true" focusable="false">
				<defs>
					<linearGradient id="raghushreeHomeWaveMobile" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#E63946" />
						<stop offset="50%" stop-color="#C5303C" />
						<stop offset="100%" stop-color="#A62530" />
					</linearGradient>
				</defs>
				<path d="M0,0 L1000,0 L1000,80 C600,150 400,-10 0,40 Z" fill="url(#raghushreeHomeWaveMobile)" />
				<path d="M0,0 L1000,0 L1000,50 C600,120 400,-40 0,10 Z" fill="#FFFFFF" />
			</svg>

			<?php
			raghushree_render_image(
				'home_hero',
				array(
					'post_id'  => $front_id,
					'size'     => 'raghushree-hero',
					'sizes'    => '(min-width: 1024px) 58vw, 100vw',
					'priority' => true,
					'class'    => 'home-banner__img',
				)
			);
			?>

			<div class="home-banner__quick-badge" aria-label="<?php esc_attr_e( 'Direct Contact Channels', 'raghushree' ); ?>">
				<?php if ( ! empty( $company['phone_href'] ) ) : ?>
					<a class="home-banner__quick-btn" href="<?php echo esc_url( $company['phone_href'] ); ?>" aria-label="<?php esc_attr_e( 'Call Raghushree Sales Desk', 'raghushree' ); ?>">
						<?php echo raghushree_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $whatsapp_url ) ) : ?>
					<a class="home-banner__quick-btn" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'WhatsApp Raghushree Sales Desk', 'raghushree' ); ?>">
						<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
				<?php if ( ! empty( $company['email'] ) ) : ?>
					<a class="home-banner__quick-btn" href="mailto:<?php echo esc_attr( $company['email'] ); ?>" aria-label="<?php esc_attr_e( 'Email Raghushree Marketing Desk', 'raghushree' ); ?>">
						<?php echo raghushree_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
