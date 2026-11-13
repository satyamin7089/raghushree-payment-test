<?php
/**
 * Reusable Five-Slide Scroll-Pinned Visual Product Story Component.
 *
 * Features:
 * - Full-bleed high-resolution industrial background photography per slide
 * - Strong left-to-right black gradient ensuring AAA text legibility on the left
 * - Desktop vertical-scroll-pinned horizontal progression across all 5 slides (GSAP ScrollTrigger)
 * - Releases cleanly after Slide 05 downward and before Slide 01 upward (never traps visitor)
 * - Controlled directional snapping, keyboard (ArrowLeft/ArrowRight), Prev/Next & 01–05 buttons
 * - Native horizontal touch-swipe on mobile, prefers-reduced-motion support, and no-JS fallback
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section_id = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'visual-product-story';
$products   = raghushree_get_catalog_items( 7 );

// Map canonical polymer permalinks dynamically when seeded
$poly_links = array();
foreach ( $products as $p_item ) {
	$p_obj = get_post( $p_item['id'] );
	if ( $p_obj instanceof WP_Post ) {
		$poly_links[ $p_obj->post_name ] = $p_item['permalink'];
	}
}

$url_pp       = isset( $poly_links['polypropylene-pp'] ) ? $poly_links['polypropylene-pp'] : raghushree_get_products_url();
$url_hdpe     = isset( $poly_links['high-density-polyethylene-hdpe'] ) ? $poly_links['high-density-polyethylene-hdpe'] : raghushree_get_products_url();
$url_pvc      = isset( $poly_links['polyvinyl-chloride-pvc'] ) ? $poly_links['polyvinyl-chloride-pvc'] : raghushree_get_products_url();
$url_recycled = isset( $poly_links['recycled-reprocessed-polymers'] ) ? $poly_links['recycled-reprocessed-polymers'] : raghushree_get_products_url();

$slides = array(
	array(
		'num'       => '01',
		'kicker'    => __( 'Visual Product Story · Polypropylene (PP)', 'raghushree' ),
		'title'     => __( 'Polypropylene Engineered for Rigid Moulding & High-Tenacity Raffia', 'raghushree' ),
		'desc'      => __( 'Balanced stiffness, chemical resistance, and flex-fatigue performance across injection-moulded crates, closures, automotive parts, and stretched woven packaging tapes.', 'raghushree' ),
		'points'    => array(
			__( 'Homopolymer & copolymer grade alignment per machine', 'raghushree' ),
			__( 'Supplied in free-flowing granules & verified forms', 'raghushree' ),
			__( 'Pre-bulk sample evaluation & scheduled plant supply', 'raghushree' ),
		),
		'slot'      => 'polymer_pp',
		'cta_label' => __( 'Explore Polypropylene', 'raghushree' ),
		'cta_url'   => $url_pp,
		'rfq_url'   => raghushree_get_enquiry_url(
			array(
				'material'     => 'Polypropylene (PP)',
				'enquiry_type' => 'Request a Quotation',
			)
		),
	),
	array(
		'num'       => '02',
		'kicker'    => __( 'Visual Product Story · Polyethylene Family (HDPE · LDPE · LLDPE)', 'raghushree' ),
		'title'     => __( 'High-Strength Containers, Pressure Conduits & Flexible Packaging Films', 'raghushree' ),
		'desc'      => __( 'From blow-moulded chemical drums and extruded HDPE pipes to high-clarity LDPE liners and puncture-resistant LLDPE stretch films—matched to your melt flow window.', 'raghushree' ),
		'points'    => array(
			__( 'HDPE blow moulding, pipe extrusion & crate grades', 'raghushree' ),
			__( 'LDPE & LLDPE film extrusion and rotomoulding options', 'raghushree' ),
			__( 'Consistent feed form verified prior to commercial dispatch', 'raghushree' ),
		),
		'slot'      => 'polymer_hdpe',
		'cta_label' => __( 'Explore Polyethylene Range', 'raghushree' ),
		'cta_url'   => $url_hdpe,
		'rfq_url'   => raghushree_get_enquiry_url(
			array(
				'material'     => 'High-Density Polyethylene (HDPE)',
				'enquiry_type' => 'Request a Quotation',
			)
		),
	),
	array(
		'num'       => '03',
		'kicker'    => __( 'Visual Product Story · PVC & PET Raw Materials', 'raghushree' ),
		'title'     => __( 'Infrastructure Conduits, Cable Insulation & High-Clarity Containers', 'raghushree' ),
		'desc'      => __( 'Dependable PVC resin/compound sourcing for rigid piping, profiles, and wire sheathing alongside crystalline PET grades for preforms, thermoformed sheet, and strapping.', 'raghushree' ),
		'points'    => array(
			__( 'Rigid & flexible PVC conversion support across granules & powder', 'raghushree' ),
			__( 'PET bottle, sheet & strapping raw material options', 'raghushree' ),
			__( 'Technical consultation for line temperature & drying setups', 'raghushree' ),
		),
		'slot'      => 'polymer_pvc',
		'cta_label' => __( 'Explore PVC & PET', 'raghushree' ),
		'cta_url'   => $url_pvc,
		'rfq_url'   => raghushree_get_enquiry_url(
			array(
				'material'     => 'Polyvinyl Chloride (PVC)',
				'enquiry_type' => 'Request a Quotation',
			)
		),
	),
	array(
		'num'       => '04',
		'kicker'    => __( 'Visual Product Story · Recycled & Reprocessed Polymers', 'raghushree' ),
		'title'     => __( 'Practical Cost-Efficiency & Circular Thermoplastic Feedstock', 'raghushree' ),
		'desc'      => __( 'Sorted reprocessed and recycled polymer granules helping manufacturers optimize raw material economics across non-food industrial mouldings, utility goods, and conduits.', 'raghushree' ),
		'points'    => array(
			__( 'Categorized by base polymer family, colour sort & filtration', 'raghushree' ),
			__( 'Honest batch-by-batch parameter confirmation', 'raghushree' ),
			__( 'Trial sample testing encouraged before full-load commitment', 'raghushree' ),
		),
		'slot'      => 'polymer_recycled',
		'cta_label' => __( 'Explore Recycled Polymers', 'raghushree' ),
		'cta_url'   => $url_recycled,
		'rfq_url'   => raghushree_get_enquiry_url(
			array(
				'material'     => 'Recycled & Reprocessed Polymers',
				'enquiry_type' => 'Sample Request',
			)
		),
	),
	array(
		'num'       => '05',
		'kicker'    => __( 'Visual Product Story · Kanpur Hub to Domestic & Export Plants', 'raghushree' ),
		'title'     => __( 'Organized B2B Raw Material Supply Backed by Commercial Clarity', 'raghushree' ),
		'desc'      => __( 'Coordinated from General Ganj, Kanpur, Raghushree Plast Products Pvt. Ltd. supports single-load requirements, trial validations, and recurring factory dispatches across India and export markets.', 'raghushree' ),
		'points'    => array(
			__( 'Transparent grade, form & packaging confirmation at quotation', 'raghushree' ),
			__( 'Responsive coordination for OEMs, moulders & distributors', 'raghushree' ),
			__( 'Direct RFQ & WhatsApp commercial support desk', 'raghushree' ),
		),
		'slot'      => 'about_supply',
		'cta_label' => __( 'View Complete Catalog', 'raghushree' ),
		'cta_url'   => raghushree_get_products_url(),
		'rfq_url'   => raghushree_get_page_url( 'contact' ),
	),
);
?>
<section
	class="vps-section surface--ink"
	id="<?php echo esc_attr( $section_id ); ?>"
	data-visual-story
	aria-roledescription="carousel"
	aria-label="<?php esc_attr_e( 'Five-Slide Visual Polymer Story', 'raghushree' ); ?>"
>
	<div class="vps-viewport" data-vps-viewport tabindex="0">
		<div class="vps-track" data-vps-track>
			<?php foreach ( $slides as $s_idx => $slide ) : ?>
				<article
					class="vps-slide<?php echo 0 === $s_idx ? ' is-active' : ''; ?>"
					data-vps-slide="<?php echo esc_attr( (string) $s_idx ); ?>"
					role="group"
					aria-roledescription="slide"
					aria-label="<?php echo esc_attr( sprintf( '%d of %d: %s', $s_idx + 1, count( $slides ), $slide['title'] ) ); ?>"
				>
					<!-- Full-View Background Image -->
					<div class="vps-slide__bg" aria-hidden="true">
						<?php
						raghushree_render_image(
							$slide['slot'],
							array(
								'size'     => 'raghushree-hero',
								'sizes'    => '100vw',
								'priority' => 0 === $s_idx,
								'class'    => 'vps-slide__img',
							)
						);
						?>
						<!-- Strong Left-to-Right Black Gradient for Crisp Text Legibility -->
						<div class="vps-slide__scrim"></div>
					</div>

					<!-- Left-Aligned Editorial Story Content -->
					<div class="container vps-slide__container">
						<div class="vps-slide__content">
							<div class="vps-slide__meta">
								<span class="vps-slide__badge"><?php echo esc_html( $slide['num'] . ' / 05' ); ?></span>
								<span class="eyebrow eyebrow--on-dark"><?php echo esc_html( $slide['kicker'] ); ?></span>
							</div>

							<h2 class="vps-slide__title"><?php echo esc_html( $slide['title'] ); ?></h2>
							<p class="vps-slide__desc"><?php echo esc_html( $slide['desc'] ); ?></p>

							<ul class="vps-slide__points">
								<?php foreach ( $slide['points'] as $pt ) : ?>
									<li>
										<span class="vps-slide__point-icon" aria-hidden="true"><?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
										<span><?php echo esc_html( $pt ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>

							<div class="vps-slide__actions">
								<a class="btn btn--primary" href="<?php echo esc_url( $slide['cta_url'] ); ?>">
									<span><?php echo esc_html( $slide['cta_label'] ); ?></span>
									<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
								<a class="btn btn--on-dark" href="<?php echo esc_url( $slide['rfq_url'] ); ?>">
									<span><?php esc_html_e( 'Enquire Now', 'raghushree' ); ?></span>
								</a>
							</div>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<!-- Accessible Bottom Story Controls (Buttons + 01–05 Stepper + Progress Bar) -->
		<div class="vps-controls">
			<div class="container vps-controls__inner">
				<div class="vps-steps" role="tablist" aria-label="<?php esc_attr_e( 'Select visual story slide', 'raghushree' ); ?>">
					<?php foreach ( $slides as $s_idx => $slide ) : ?>
						<button
							type="button"
							class="vps-step<?php echo 0 === $s_idx ? ' is-active' : ''; ?>"
							role="tab"
							aria-selected="<?php echo 0 === $s_idx ? 'true' : 'false'; ?>"
							data-vps-goto="<?php echo esc_attr( (string) $s_idx ); ?>"
							aria-label="<?php echo esc_attr( sprintf( '%s %s: %s', __( 'Go to slide', 'raghushree' ), $slide['num'], $slide['title'] ) ); ?>"
						>
							<span class="vps-step__num"><?php echo esc_html( $slide['num'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="vps-progress" aria-hidden="true">
					<div class="vps-progress__bar" data-vps-progress style="width: 20%;"></div>
				</div>

				<div class="vps-arrows">
					<button type="button" class="vps-arrow" data-vps-prev aria-label="<?php esc_attr_e( 'Previous story slide', 'raghushree' ); ?>">
						<?php echo raghushree_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
					<button type="button" class="vps-arrow" data-vps-next aria-label="<?php esc_attr_e( 'Next story slide', 'raghushree' ); ?>">
						<?php echo raghushree_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</button>
				</div>
			</div>
		</div>
	</div>
</section>
