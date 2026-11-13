<?php
/**
 * About Page Sections 1–3:
 * - Section 1: About Hero Banner
 * - Section 2: Three-Column Company Overview (Col 1: Two Stacked Images | Col 2: Content | Col 3: Two Stacked Images)
 * - Section 3: Business Approach
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company = raghushree_get_company_info();
$post_id = get_queried_object_id();
?>
<!-- Section 1: About Hero -->
<?php
get_template_part(
	'template-parts/components/page-banner',
	null,
	array(
		'id'            => 'about-hero',
		'eyebrow'       => __( 'About Raghushree', 'raghushree' ),
		'title'         => __( 'Industrial Plastic Raw Materials Backed by Practical Commercial Reliability', 'raghushree' ),
		'description'   => sprintf(
			/* translators: %s: Legal company name */
			__( '%s is an Indian manufacturer, exporter, and supplier of thermoplastic raw materials—serving plastic product manufacturers, contract moulders, packaging converters, and distributors.', 'raghushree' ),
			$company['legal_name']
		),
		'primary_cta'   => array(
			'label' => __( 'Explore Polymer Portfolio', 'raghushree' ),
			'url'   => raghushree_get_products_url(),
		),
		'secondary_cta' => array(
			'kicker' => __( 'Bulk Order Enquiries', 'raghushree' ),
			'label'  => __( 'Contact Sales Desk', 'raghushree' ),
			'url'    => raghushree_get_page_url( 'contact' ),
			'icon'   => 'phone',
		),
		'image_slot'    => 'about_hero',
		'post_id'       => $post_id,
		'caption_title' => __( 'Industrial Polymer Raw Material Supply', 'raghushree' ),
		'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
	)
);
?>

<!-- Section 2: Three-Column Company Overview (2 Stacked Images | Editorial Content | 2 Stacked Images) -->
<section class="section surface--white" id="company-overview">
	<div class="container">
		<div class="about-triptych">
			<!-- Column 1: Two Stacked Industrial Photographs -->
			<div class="about-triptych__stack">
				<div class="about-triptych__media reveal">
					<?php
					raghushree_render_image(
						'about_overview',
						array(
							'sizes'    => '(min-width: 1024px) 380px, (min-width: 640px) 48vw, 100vw',
							'priority' => false,
						)
					);
					?>
					<span class="about-triptych__caption"><?php esc_html_e( 'Thermoplastic Processing Support', 'raghushree' ); ?></span>
				</div>

				<div class="about-triptych__media reveal">
					<?php
					raghushree_render_image(
						'app_injection',
						array(
							'sizes'    => '(min-width: 1024px) 380px, (min-width: 640px) 48vw, 100vw',
							'priority' => false,
						)
					);
					?>
					<span class="about-triptych__caption"><?php esc_html_e( 'Moulding & Extrusion Alignment', 'raghushree' ); ?></span>
				</div>
			</div>

			<!-- Column 2: Center Editorial Content -->
			<div class="about-triptych__center">
				<div class="eyebrow"><?php esc_html_e( 'Company Overview', 'raghushree' ); ?></div>
				<h2><?php esc_html_e( 'Rooted in Kanpur, Serving Industrial Polymer Buyers Across Markets', 'raghushree' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: 1: Legal company name, 2: Registered address */
						esc_html__( 'Headquartered at %2$s, %1$s focuses on supplying dependable plastic raw materials tailored to real shop-floor conversion needs.', 'raghushree' ),
						esc_html( $company['legal_name'] ),
						esc_html( $company['full_address'] )
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'We bridge material availability and manufacturing demand across commodity thermoplastics as well as recycled and reprocessed polymer categories—helping buyers maintain continuity across single orders, trial evaluations, and ongoing supply schedules.', 'raghushree' ); ?>
				</p>

				<div class="about-triptych__facts">
					<div class="about-triptych__fact">
						<span class="about-triptych__fact-label"><?php esc_html_e( 'Legal Entity', 'raghushree' ); ?></span>
						<strong class="about-triptych__fact-val"><?php echo esc_html( $company['legal_name'] ); ?></strong>
					</div>
					<div class="about-triptych__fact">
						<span class="about-triptych__fact-label"><?php esc_html_e( 'Operations Scope', 'raghushree' ); ?></span>
						<strong class="about-triptych__fact-val"><?php esc_html_e( 'Manufacturer, Exporter & Supplier', 'raghushree' ); ?></strong>
					</div>
					<div class="about-triptych__fact">
						<span class="about-triptych__fact-label"><?php esc_html_e( 'Commercial Base', 'raghushree' ); ?></span>
						<strong class="about-triptych__fact-val"><?php esc_html_e( 'General Ganj, Kanpur-208001, U.P., India', 'raghushree' ); ?></strong>
					</div>
				</div>

				<div class="about-triptych__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( raghushree_get_products_url() ); ?>">
						<span><?php esc_html_e( 'Explore Polymer Portfolio', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<a class="btn btn--secondary" href="<?php echo esc_url( raghushree_get_page_url( 'contact' ) ); ?>">
						<span><?php esc_html_e( 'Enquire Now', 'raghushree' ); ?></span>
					</a>
				</div>
			</div>

			<!-- Column 3: Two Stacked Industrial Photographs -->
			<div class="about-triptych__stack">
				<div class="about-triptych__media reveal">
					<?php
					raghushree_render_image(
						'about_quality',
						array(
							'sizes'    => '(min-width: 1024px) 380px, (min-width: 640px) 48vw, 100vw',
							'priority' => false,
						)
					);
					?>
					<span class="about-triptych__caption"><?php esc_html_e( 'Specification & Sample Verification', 'raghushree' ); ?></span>
				</div>

				<div class="about-triptych__media reveal">
					<?php
					raghushree_render_image(
						'about_supply',
						array(
							'sizes'    => '(min-width: 1024px) 380px, (min-width: 640px) 48vw, 100vw',
							'priority' => false,
						)
					);
					?>
					<span class="about-triptych__caption"><?php esc_html_e( 'Domestic & Export Logistics', 'raghushree' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Section 3: Business Approach -->
<section class="section surface--pearl" id="business-approach">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Business Approach', 'raghushree' ),
				'title'       => __( 'Transparent Specification Matching Rather Than Generic Commodity Selling', 'raghushree' ),
				'description' => __( 'Every plastic manufacturing line has distinct melt flow, tooling, colour, and cost targets. Our commercial methodology starts with understanding your process before quoting a grade.', 'raghushree' ),
			)
		);

		$principles = array(
			array(
				'icon'  => 'check',
				'title' => __( 'Verified Information Only', 'raghushree' ),
				'desc'  => __( 'We confirm actual grade and form availability per enquiry rather than assuming every specification is universally applicable.', 'raghushree' ),
			),
			array(
				'icon'  => 'settings',
				'title' => __( 'Application-First Dialogue', 'raghushree' ),
				'desc'  => __( 'We align polymer categories and physical forms—granules or powder—around your moulding, extrusion, or film equipment.', 'raghushree' ),
			),
			array(
				'icon'  => 'truck',
				'title' => __( 'Long-Term Buyer Partnerships', 'raghushree' ),
				'desc'  => __( 'Structured support for repeat industrial buyers, contract moulders, procurement departments, and regional dealers.', 'raghushree' ),
			),
		);
		?>
		<div class="grid-3">
			<?php foreach ( $principles as $p ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'icon'        => $p['icon'],
						'title'       => $p['title'],
						'description' => $p['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
