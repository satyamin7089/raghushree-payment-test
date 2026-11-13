<?php
/**
 * Homepage Section 3: About Raghushree (Split Two-Column Layout).
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$company = raghushree_get_company_info();
?>
<section class="section surface--white" id="about-raghushree">
	<div class="container">
		<div class="split-layout">
			<div class="split-layout__content">
				<div class="eyebrow"><?php esc_html_e( 'About Raghushree', 'raghushree' ); ?></div>
				<h2><?php esc_html_e( 'A Focused Raw Material Partner for Plastic Product Manufacturers', 'raghushree' ); ?></h2>
				<p>
					<?php
					printf(
						/* translators: %s: Legal company name */
						esc_html__( '%s operates as a manufacturer, exporter, and supplier of thermoplastic raw materials from Kanpur, Uttar Pradesh, India—working directly with industrial manufacturers, contract moulding units, packaging converters, and polymer distributors.', 'raghushree' ),
						esc_html( $company['legal_name'] )
					);
					?>
				</p>
				<p>
					<?php esc_html_e( 'Our commercial approach centres on clear material communication, practical grade matching for your machinery, transparent availability confirmation, and responsive coordination from initial sample evaluation through bulk order supply.', 'raghushree' ); ?>
				</p>

				<div class="about-facts" style="margin-bottom: var(--space-6);">
					<?php
					get_template_part(
						'template-parts/components/stat-card',
						null,
						array(
							'label' => __( 'Business Role', 'raghushree' ),
							'value' => __( 'Manufacturer, Exporter & Supplier', 'raghushree' ),
						)
					);
					get_template_part(
						'template-parts/components/stat-card',
						null,
						array(
							'label' => __( 'Headquarters', 'raghushree' ),
							'value' => __( 'Kanpur, U.P., India', 'raghushree' ),
						)
					);
					get_template_part(
						'template-parts/components/stat-card',
						null,
						array(
							'label' => __( 'Market Focus', 'raghushree' ),
							'value' => __( 'Industrial & B2B Buyers', 'raghushree' ),
						)
					);
					?>
				</div>

				<div>
					<a class="btn btn--secondary" href="<?php echo esc_url( raghushree_get_page_url( 'about' ) ); ?>">
						<span><?php esc_html_e( 'Learn More About Raghushree', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</div>

			<div class="split-layout__media">
				<?php
				raghushree_render_image(
					'home_about',
					array(
						'sizes'    => '(min-width: 1200px) 580px, (min-width: 900px) 46vw, 100vw',
						'priority' => false,
					)
				);
				?>
			</div>
		</div>
	</div>
</section>
