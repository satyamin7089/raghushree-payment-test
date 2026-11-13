<?php
/**
 * Reusable Four-Card 180° Interactive Flip Section:
 * Eyebrow: "Supporting Core Plastic Processing & Conversion Methods"
 * Title:   "Useful detail, on the other side."
 *
 * Rules:
 * - 4 cards with image + concise front info, and verified technical details + enquiry link on back
 * - Flips 180 degrees ONLY on click, tap, or keyboard activation (Enter/Space)
 * - Clear "Return to Front" control on back (plus Escape support)
 * - Strictly NO automatic hover/timer flipping
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section_id = ! empty( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'manufacturing-applications';
$surface    = ! empty( $args['surface'] ) ? sanitize_html_class( $args['surface'] ) : 'surface--white';

$cards = array(
	array(
		'num'      => '01',
		'slot'     => 'app_injection',
		'title'    => __( 'Injection Moulding', 'raghushree' ),
		'front'    => __( 'Rigid industrial components, caps and closures, crates, automotive parts, electrical housings, and utility articles.', 'raghushree' ),
		'polymers' => array( 'PP', 'HDPE', 'LDPE', 'PET', 'Recycled' ),
		'details'  => array(
			__( 'Medium-to-high melt flow grades matched to cavity wall thickness', 'raghushree' ),
			__( 'Homopolymer stiffness or impact copolymer toughness options', 'raghushree' ),
			__( 'Free-flowing granules supporting consistent screw recovery', 'raghushree' ),
		),
		'rfq_url'  => raghushree_get_enquiry_url(
			array(
				'enquiry_type' => 'Technical Consultation',
				'application'  => 'Injection Moulding',
			)
		),
	),
	array(
		'num'      => '02',
		'slot'     => 'app_blow',
		'title'    => __( 'Blow Moulding', 'raghushree' ),
		'front'    => __( 'Hollow bottles, jerrycans, chemical containers, liquid packaging, and industrial drums requiring parison stability.', 'raghushree' ),
		'polymers' => array( 'HDPE', 'PET', 'PP', 'LDPE' ),
		'details'  => array(
			__( 'High melt strength parison control for hollow containers & drums', 'raghushree' ),
			__( 'Strong Environmental Stress Crack Resistance (ESCR) for chemicals', 'raghushree' ),
			__( 'Bottle-grade PET chips & blow-grade polyolefin granules', 'raghushree' ),
		),
		'rfq_url'  => raghushree_get_enquiry_url(
			array(
				'enquiry_type' => 'Technical Consultation',
				'application'  => 'Blow Moulding',
			)
		),
	),
	array(
		'num'      => '03',
		'slot'     => 'app_extrusion',
		'title'    => __( 'Extrusion & Pipes', 'raghushree' ),
		'front'    => __( 'Water and agricultural piping, electrical conduits, rigid profiles, sheets, raffia tapes, strapping, and cable sheathing.', 'raghushree' ),
		'polymers' => array( 'PVC', 'HDPE', 'PP', 'Recycled' ),
		'details'  => array(
			__( 'Controlled low-MFI pipe pellets & PVC resin/compound options', 'raghushree' ),
			__( 'Granules and powder forms aligned with single or twin-screw lines', 'raghushree' ),
			__( 'Dimensional stability for continuous conduit, profile & tape runs', 'raghushree' ),
		),
		'rfq_url'  => raghushree_get_enquiry_url(
			array(
				'enquiry_type' => 'Technical Consultation',
				'application'  => 'Extrusion',
			)
		),
	),
	array(
		'num'      => '04',
		'slot'     => 'app_film',
		'title'    => __( 'Film & Packaging', 'raghushree' ),
		'front'    => __( 'Blown and cast packaging films, stretch wraps, heavy-duty liners, woven sacks, and thermoformed rigid packaging.', 'raghushree' ),
		'polymers' => array( 'LLDPE', 'LDPE', 'HDPE', 'PP', 'PET' ),
		'details'  => array(
			__( 'Balanced bubble stability, optical gloss & puncture resistance', 'raghushree' ),
			__( 'LDPE/LLDPE blend compatibility for thin-gauge drawdown', 'raghushree' ),
			__( 'Confirm slip, antiblock & micron requirements per enquiry', 'raghushree' ),
		),
		'rfq_url'  => raghushree_get_enquiry_url(
			array(
				'enquiry_type' => 'Technical Consultation',
				'application'  => 'Film & Packaging',
			)
		),
	),
);
?>
<section class="section <?php echo esc_attr( $surface ); ?>" id="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Supporting Core Plastic Processing & Conversion Methods', 'raghushree' ),
				'title'       => __( 'Useful detail, on the other side.', 'raghushree' ),
				'description' => __( 'Select any conversion card below to flip 180 degrees and inspect compatible polymer families, processing checkpoints, and direct enquiry links.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<div class="flip-grid" data-flip-grid>
			<?php foreach ( $cards as $card ) : ?>
				<article class="flip-card reveal" data-flip-card>
					<div class="flip-card__inner">
						<!-- FRONT FACE -->
						<div class="flip-card__face flip-card__front" data-flip-front aria-hidden="false">
							<div class="flip-card__media" data-flip-trigger-surface>
								<span class="flip-card__num"><?php echo esc_html( $card['num'] ); ?></span>
								<?php
								raghushree_render_image(
									$card['slot'],
									array(
										'sizes'    => '(min-width: 1200px) 300px, (min-width: 768px) 46vw, 100vw',
										'priority' => false,
									)
								);
								?>
							</div>
							<div class="flip-card__body" data-flip-trigger-surface>
								<h3 class="flip-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
								<p class="flip-card__summary"><?php echo esc_html( $card['front'] ); ?></p>
							</div>
							<div class="flip-card__footer">
								<button
									type="button"
									class="flip-card__btn"
									data-flip-open
									aria-expanded="false"
									aria-label="<?php echo esc_attr( sprintf( '%s — %s', $card['title'], __( 'Flip card to view technical details', 'raghushree' ) ) ); ?>"
								>
									<span><?php esc_html_e( 'View Technical Detail', 'raghushree' ); ?></span>
									<?php echo raghushree_icon( 'refresh' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
							</div>
						</div>

						<!-- BACK FACE (Rotated 180deg) -->
						<div class="flip-card__face flip-card__back surface--ink" data-flip-back aria-hidden="true">
							<div class="flip-card__back-top">
								<span class="flip-card__back-kicker"><?php echo esc_html( $card['num'] . ' · ' . __( 'Conversion Detail', 'raghushree' ) ); ?></span>
								<h3 class="flip-card__back-title"><?php echo esc_html( $card['title'] ); ?></h3>

								<div class="flip-card__tags" aria-label="<?php esc_attr_e( 'Compatible polymers', 'raghushree' ); ?>">
									<?php foreach ( $card['polymers'] as $poly_tag ) : ?>
										<span class="flip-card__tag"><?php echo esc_html( $poly_tag ); ?></span>
									<?php endforeach; ?>
								</div>

								<ul class="flip-card__list">
									<?php foreach ( $card['details'] as $detail_line ) : ?>
										<li>
											<span class="flip-card__list-icon" aria-hidden="true"><?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
											<span><?php echo esc_html( $detail_line ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>

							<div class="flip-card__back-actions">
								<a class="btn btn--primary btn--sm btn--full" href="<?php echo esc_url( $card['rfq_url'] ); ?>" data-flip-back-focusable tabindex="-1">
									<span><?php esc_html_e( 'Enquire for This Process', 'raghushree' ); ?></span>
									<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
								<button
									type="button"
									class="flip-card__return"
									data-flip-close
									data-flip-back-focusable
									tabindex="-1"
									aria-label="<?php echo esc_attr( sprintf( '%s — %s', __( 'Return to front of card', 'raghushree' ), $card['title'] ) ); ?>"
								>
									<?php echo raghushree_icon( 'arrow-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<span><?php esc_html_e( 'Return to Front', 'raghushree' ); ?></span>
								</button>
							</div>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
