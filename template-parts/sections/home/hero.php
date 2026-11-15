<?php
/**
 * Homepage Section 1: Full-Width Hero Banner Carousel (Curved Industrial Split Layout).
 *
 * Two banners share the exact same layout and rotate every 12 seconds:
 * 01 — agriculture & irrigation pipes (Kisan-facing copy and photograph)
 * 02 — industrial polymer raw materials (existing homepage copy)
 *
 * Left/right arrow buttons only (no dots). The carousel pauses on hover and
 * keyboard focus, supports the arrow keys, respects prefers-reduced-motion and
 * degrades to the first banner when JavaScript is unavailable.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$front_id     = (int) get_option( 'page_on_front' );
$company      = raghushree_get_company_info();
$whatsapp_url = raghushree_get_whatsapp_url( 'Hello Raghushree team, I would like to enquire about plastic raw materials.' );

$banners = array(
	array(
		'eyebrow'  => __( 'Agriculture & Irrigation Pipes', 'raghushree' ),
		'title'    => __( 'Pipes That Keep Farms Watered Through Every Season', 'raghushree' ),
		'desc'     => __( 'PVC garden pipes, suction pipes, lay-flat tubes and HDPE lines for irrigation, borewell and water supply — supplied from Kanpur to farms, dealers and agriculture distributors across India.', 'raghushree' ),
		'slot'     => 'home_hero_pipes',
		'post_id'  => 0,
		'alt'      => __( 'Representative irrigation pipes laid out for farm water supply', 'raghushree' ),
		'priority' => true,
		'buttons'  => array(
			array(
				'label'   => __( 'Explore Pipe Range', 'raghushree' ),
				'url'     => raghushree_get_products_url( array( 'polymer_cat' => 'pvc-tubing' ) ),
				'variant' => 'primary',
				'icon'    => 'arrow-right',
			),
			array(
				'label'   => __( 'Get a Pipe Quote', 'raghushree' ),
				'url'     => raghushree_get_enquiry_url(
					array(
						'material'     => 'PVC & HDPE Irrigation Pipes',
						'enquiry_type' => 'Request a Quotation',
					)
				),
				'variant' => 'dark',
			),
		),
		'tags'     => array(
			__( 'PVC Garden Pipes', 'raghushree' ),
			__( 'PVC Suction Pipes', 'raghushree' ),
			__( 'Lay Flat Pipes', 'raghushree' ),
			__( 'Hose Pipes', 'raghushree' ),
			__( 'HDPE Pipes', 'raghushree' ),
			__( 'Farm Irrigation', 'raghushree' ),
		),
	),
	array(
		'eyebrow'  => __( 'Manufacturer, Exporter & Supplier of Plastic Raw Materials', 'raghushree' ),
		'title'    => __( 'Dependable Polymer Raw Materials for Industrial Manufacturing', 'raghushree' ),
		'desc'     => __( 'Raghushree supplies Polypropylene (PP), HDPE, LDPE, LLDPE, PVC, PET, and recycled & reprocessed polymer grades—supporting manufacturers, contract moulders, packaging converters, and distributors across India and global export markets.', 'raghushree' ),
		'slot'     => 'home_hero',
		'post_id'  => $front_id,
		'alt'      => '',
		'priority' => false,
		'buttons'  => array(
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
		'tags'     => array(
			'PP',
			'HDPE',
			'LDPE',
			'LLDPE',
			'PVC',
			'PET',
			__( 'Recycled & Reprocessed Polymers', 'raghushree' ),
		),
	),
);

$banner_total = count( $banners );
?>
<section
	class="section section--hero home-hero"
	id="home-hero"
	data-home-hero
	aria-roledescription="<?php esc_attr_e( 'carousel', 'raghushree' ); ?>"
	aria-label="<?php esc_attr_e( 'Raghushree homepage banners', 'raghushree' ); ?>"
>
	<div class="home-hero__track" data-home-hero-track>
		<?php foreach ( $banners as $index => $banner ) : ?>
			<?php
			$is_active    = 0 === $index;
			$title_tag    = 0 === $index ? 'h1' : 'h2';
			$gradient_id  = 'raghushreeHomeWaveDesktop' . ( $index + 1 );
			$mobile_id    = 'raghushreeHomeWaveMobile' . ( $index + 1 );
			$last_tag_key = count( $banner['tags'] ) - 1;
			?>
			<div
				class="home-banner<?php echo $is_active ? ' is-active' : ''; ?>"
				data-home-hero-slide
				data-index="<?php echo esc_attr( (string) $index ); ?>"
				role="group"
				aria-roledescription="<?php esc_attr_e( 'slide', 'raghushree' ); ?>"
				aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total banners, 3: banner heading. */ __( '%1$d of %2$d: %3$s', 'raghushree' ), $index + 1, $banner_total, $banner['title'] ) ); ?>"
				aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>"
				<?php echo $is_active ? '' : ' inert'; ?>
			>
				<!-- 1. Left / Top Content Aligned to Global Container -->
				<div class="container home-banner__inner">
					<div class="home-banner__content">
						<div class="eyebrow"><?php echo esc_html( $banner['eyebrow'] ); ?></div>

						<<?php echo $title_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed h1/h2 value. ?> class="home-banner__title">
							<?php echo esc_html( $banner['title'] ); ?>
						</<?php echo $title_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

						<p class="home-banner__desc">
							<?php echo esc_html( $banner['desc'] ); ?>
						</p>

						<div class="home-banner__actions">
							<?php
							get_template_part(
								'template-parts/components/buttons',
								null,
								array( 'buttons' => $banner['buttons'] )
							);
							?>
						</div>

						<div class="home-banner__highlights" aria-label="<?php esc_attr_e( 'Supported Materials', 'raghushree' ); ?>">
							<?php foreach ( $banner['tags'] as $tag_index => $tag_label ) : ?>
								<span class="tag<?php echo $tag_index === $last_tag_key ? ' tag--accent' : ''; ?>"><?php echo esc_html( $tag_label ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<!-- 2. Desktop Vertical S-Curve SVG Separator -->
				<svg class="home-banner__desktop-wave" viewBox="0 0 1400 560" preserveAspectRatio="none" aria-hidden="true" focusable="false">
					<defs>
						<linearGradient id="<?php echo esc_attr( $gradient_id ); ?>" x1="0%" y1="0%" x2="100%" y2="100%">
							<stop offset="0%" stop-color="#E63946" />
							<stop offset="50%" stop-color="#C5303C" />
							<stop offset="100%" stop-color="#A62530" />
						</linearGradient>
					</defs>
					<path d="M 0,0 L 830,0 C 720,90 690,210 810,330 C 900,420 870,500 930,560 L 0,560 Z" fill="url(#<?php echo esc_attr( $gradient_id ); ?>)" />
					<path d="M 0,0 L 790,0 C 680,90 650,210 770,330 C 860,420 830,500 890,560 L 0,560 Z" fill="#FFFFFF" />
				</svg>

				<!-- 3. Right / Bottom Full-Bleed Image & Mobile Horizontal Wave -->
				<div class="home-banner__media">
					<svg class="home-banner__mobile-wave" viewBox="0 0 1000 120" preserveAspectRatio="none" aria-hidden="true" focusable="false">
						<defs>
							<linearGradient id="<?php echo esc_attr( $mobile_id ); ?>" x1="0%" y1="0%" x2="100%" y2="100%">
								<stop offset="0%" stop-color="#E63946" />
								<stop offset="50%" stop-color="#C5303C" />
								<stop offset="100%" stop-color="#A62530" />
							</linearGradient>
						</defs>
						<path d="M0,0 L1000,0 L1000,80 C600,150 400,-10 0,40 Z" fill="url(#<?php echo esc_attr( $mobile_id ); ?>)" />
						<path d="M0,0 L1000,0 L1000,50 C600,120 400,-40 0,10 Z" fill="#FFFFFF" />
					</svg>

					<?php
					raghushree_render_image(
						$banner['slot'],
						array(
							'post_id'  => $banner['post_id'],
							'size'     => 'raghushree-hero',
							'sizes'    => '(min-width: 1024px) 58vw, 100vw',
							'priority' => $banner['priority'],
							'class'    => 'home-banner__img',
							'alt'      => $banner['alt'],
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
		<?php endforeach; ?>
	</div>

	<!-- Arrow controls only — no dots, as requested -->
	<div class="home-hero__controls">
		<button type="button" class="home-hero__arrow" data-home-hero-prev aria-label="<?php esc_attr_e( 'Previous banner', 'raghushree' ); ?>">
			<?php echo raghushree_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
		<button type="button" class="home-hero__arrow" data-home-hero-next aria-label="<?php esc_attr_e( 'Next banner', 'raghushree' ); ?>">
			<?php echo raghushree_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>
</section>
