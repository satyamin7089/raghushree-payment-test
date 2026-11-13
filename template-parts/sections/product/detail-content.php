<?php
/**
 * Shared Dynamic Product Detail Content.
 *
 * Powers all polymers (PP, HDPE, LDPE, LLDPE, PVC, PET, Recycled Polymers)
 * from a single CPT record:
 * 1. Product Hero Banner (Left: Heading, Description, Key Points, "Enquire Now" &
 *    "Buy Now" Contact CTAs | Right: Equal-height Fade-In / Fade-Out Image Slider)
 * 2. Sticky On-Page Section Navigation (Packaging & Processing removed)
 * 3. Product Overview
 * 4. Grades & Material Forms
 * 5. Applications & Industries
 * 6. Visual Material & End-Use Image Cards Showcase
 * 7. Technical Specifications & Consultation
 * 8. FAQs & Related Polymers
 * 9. Final RFQ / Enquiry CTA
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_queried_object_id();
$prod    = raghushree_get_product_data( $post_id );

if ( empty( $prod['id'] ) ) {
	return;
}

// Both Banner CTA buttons link to Contact Us with contextual pre-filled query args
$enquire_now_url = raghushree_get_enquiry_url(
	array(
		'material'     => $prod['title'],
		'enquiry_type' => 'Request a Quotation',
	)
);
$buy_now_url     = raghushree_get_enquiry_url(
	array(
		'material'     => $prod['title'],
		'enquiry_type' => 'Bulk Order Enquiry',
	)
);
$enquire_url     = raghushree_get_enquiry_url(
	array(
		'material'     => $prod['title'],
		'enquiry_type' => 'Material Availability',
	)
);
$sample_url      = raghushree_get_enquiry_url(
	array(
		'material'     => $prod['title'],
		'enquiry_type' => 'Sample Request',
	)
);
$tech_url        = raghushree_get_enquiry_url(
	array(
		'material'     => $prod['title'],
		'enquiry_type' => 'Technical Consultation',
	)
);

// Related products query (explicit IDs if set, otherwise sibling polymers)
$related_args = array(
	'post_type'      => 'products',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
	'post__not_in'   => array( $prod['id'] ),
	'no_found_rows'  => true,
);
if ( ! empty( $prod['related_ids'] ) ) {
	$related_args['post__in'] = $prod['related_ids'];
	$related_args['orderby']  = 'post__in';
} else {
	$related_args['orderby'] = array(
		'menu_order' => 'ASC',
		'title'      => 'ASC',
	);
}
$related_query = new WP_Query( $related_args );

// Build verified bullet points for Single Product Left Banner Column
$forms_summary = ! empty( $prod['forms_terms'] )
	? implode( ', ', wp_list_pluck( $prod['forms_terms'], 'name' ) )
	: __( 'Granules & applicable industrial forms', 'raghushree' );

$banner_points = array(
	sprintf(
		/* translators: %s: Physical forms list */
		__( 'Physical Forms: %s (verified per lot)', 'raghushree' ),
		$forms_summary
	),
	__( 'Grade selection aligned with injection, extrusion, blow or film lines', 'raghushree' ),
	__( 'Pre-order sample evaluation & commercial bulk supply coordination', 'raghushree' ),
	__( 'Domestic pan-India factory delivery & international export support', 'raghushree' ),
);

// Build Fade-In / Fade-Out Right-Column Banner Slider Slides
$banner_slides = array(
	array(
		'slot'          => $prod['image_slot'],
		'caption_title' => $prod['title'],
		'caption_sub'   => __( 'Verified industrial polymer raw material supply', 'raghushree' ),
	),
	array(
		'slot'          => 'app_injection',
		'caption_title' => __( 'Shop-Floor Conversion Compatibility', 'raghushree' ),
		'caption_sub'   => __( 'Grades matched to moulding & extrusion parameters', 'raghushree' ),
	),
	array(
		'slot'          => 'home_quality',
		'caption_title' => __( 'Grade & Form Verification', 'raghushree' ),
		'caption_sub'   => __( 'Confirm melt flow, form & batch suitability pre-dispatch', 'raghushree' ),
	),
	array(
		'slot'          => 'about_supply',
		'caption_title' => __( 'Bulk & Scheduled Dispatch', 'raghushree' ),
		'caption_sub'   => __( 'Coordinated from Kanpur for India & export buyers', 'raghushree' ),
	),
);

if ( ! empty( $prod['gallery_ids'] ) ) {
	foreach ( $prod['gallery_ids'] as $g_att_id ) {
		$banner_slides[] = array(
			'attachment_id' => (int) $g_att_id,
			'caption_title' => $prod['title'],
			'caption_sub'   => __( 'Product gallery reference view', 'raghushree' ),
		);
	}
}

// Curated Visual Image Cards for Single Product Page
$product_image_cards = array(
	array(
		'slot'   => $prod['image_slot'],
		'kicker' => __( '01 · Raw Material Feedstock', 'raghushree' ),
		'title'  => sprintf(
			/* translators: %s: Polymer title */
			__( '%s Feedstock Forms', 'raghushree' ),
			$prod['title']
		),
		'desc'   => __( 'Supplied in verified physical forms such as free-flowing granules or powder matched to your dosing and hopper equipment.', 'raghushree' ),
		'url'    => $enquire_now_url,
	),
	array(
		'slot'   => 'app_injection',
		'kicker' => __( '02 · Moulding & Forming', 'raghushree' ),
		'title'  => __( 'Precision Moulded Components', 'raghushree' ),
		'desc'   => __( 'Grades shortlisted around melt flow behaviour, cycle stability, stiffness, and impact performance on your tooling.', 'raghushree' ),
		'url'    => $tech_url,
	),
	array(
		'slot'   => 'app_extrusion',
		'kicker' => __( '03 · Continuous Conversion', 'raghushree' ),
		'title'  => __( 'Extrusion, Film & Industrial Lines', 'raghushree' ),
		'desc'   => __( 'Consistent melt viscosity options supporting downstream pipe, profile, sheet, tape, or packaging conversion.', 'raghushree' ),
		'url'    => $sample_url,
	),
	array(
		'slot'   => 'about_supply',
		'kicker' => __( '04 · Organized Supply', 'raghushree' ),
		'title'  => __( 'Trial Lots to Bulk Loads', 'raghushree' ),
		'desc'   => __( 'Structured procurement support from pre-bulk evaluation samples through scheduled full-load plant deliveries.', 'raghushree' ),
		'url'    => $buy_now_url,
	),
);
?>

<!-- 1. Product Hero Banner (Heading + Description + Points + Enquire Now / Buy Now + Fade Slider) -->
<?php
$hero_badges = array(
	array(
		'label'   => $prod['primary_cat'],
		'variant' => 'accent',
	),
);
if ( ! empty( $prod['polymer_code'] ) ) {
	$hero_badges[] = array(
		'label'   => $prod['polymer_code'],
		'variant' => 'default',
	);
}

get_template_part(
	'template-parts/components/page-banner',
	null,
	array(
		'id'            => 'product-hero',
		'badges'        => $hero_badges,
		'title'         => $prod['title'],
		'description'   => $prod['short_desc'],
		'points'        => $banner_points,
		'primary_cta'   => array(
			'label' => __( 'Enquire Now', 'raghushree' ),
			'url'   => $enquire_now_url,
		),
		'secondary_btn' => array(
			'label' => __( 'Buy Now', 'raghushree' ),
			'url'   => $buy_now_url,
		),
		'image_slot'    => $prod['image_slot'],
		'slider_slides' => $banner_slides,
		'post_id'       => $prod['id'],
		'caption_title' => $prod['title'],
		'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
	)
);
?>

<!-- 2. Static Sticky On-Page Section Navigation (Packaging & Processing Removed) -->
<section class="product-subnav" id="product-section-nav" aria-label="<?php esc_attr_e( 'On-page product sections', 'raghushree' ); ?>">
	<div class="container">
		<nav aria-label="<?php esc_attr_e( 'Product Detail Sections', 'raghushree' ); ?>">
			<ul class="product-subnav__list">
				<li><a class="product-subnav__link is-active" href="#overview"><?php esc_html_e( 'Overview', 'raghushree' ); ?></a></li>
				<li><a class="product-subnav__link" href="#grades"><?php esc_html_e( 'Grades', 'raghushree' ); ?></a></li>
				<li><a class="product-subnav__link" href="#applications"><?php esc_html_e( 'Applications', 'raghushree' ); ?></a></li>
				<li><a class="product-subnav__link" href="#material-showcase"><?php esc_html_e( 'Visual Showcase', 'raghushree' ); ?></a></li>
				<li><a class="product-subnav__link" href="#technical-details"><?php esc_html_e( 'Technical Details', 'raghushree' ); ?></a></li>
				<li><a class="product-subnav__link" href="#faqs"><?php esc_html_e( 'FAQs', 'raghushree' ); ?></a></li>
				<li><a class="product-subnav__link" href="#enquiry"><?php esc_html_e( 'Enquiry', 'raghushree' ); ?></a></li>
			</ul>
		</nav>
	</div>
</section>

<!-- 3. Product Overview -->
<section class="section surface--white single-product-section" id="overview">
	<div class="container">
		<div class="overview-layout">
			<div class="overview-layout__content">
				<div class="eyebrow"><?php esc_html_e( 'Product Overview', 'raghushree' ); ?></div>
				<h2>
					<?php
					printf(
						/* translators: %s: Polymer title */
						esc_html__( '%s Raw Material Supply Overview', 'raghushree' ),
						esc_html( $prod['title'] )
					);
					?>
				</h2>
				<div class="entry-content">
					<?php
					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					echo wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $prod['id'] ) ) );
					?>
				</div>
			</div>

			<aside class="overview-summary-card" aria-label="<?php esc_attr_e( 'Material Summary', 'raghushree' ); ?>">
				<h3><?php esc_html_e( 'Material Reference', 'raghushree' ); ?></h3>
				<div class="summary-kv">
					<div class="summary-kv__row">
						<span class="summary-kv__label"><?php esc_html_e( 'Polymer Category', 'raghushree' ); ?></span>
						<span class="summary-kv__val"><?php echo esc_html( $prod['primary_cat'] ); ?></span>
					</div>
					<?php if ( ! empty( $prod['grades_terms'] ) ) : ?>
						<div class="summary-kv__row">
							<span class="summary-kv__label"><?php esc_html_e( 'Verified Grades', 'raghushree' ); ?></span>
							<span class="summary-kv__val"><?php echo esc_html( implode( ', ', wp_list_pluck( $prod['grades_terms'], 'name' ) ) ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $prod['forms_terms'] ) ) : ?>
						<div class="summary-kv__row">
							<span class="summary-kv__label"><?php esc_html_e( 'Verified Physical Forms', 'raghushree' ); ?></span>
							<span class="summary-kv__val"><?php echo esc_html( implode( ', ', wp_list_pluck( $prod['forms_terms'], 'name' ) ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="summary-kv__row">
						<span class="summary-kv__label"><?php esc_html_e( 'Sample & Trial Support', 'raghushree' ); ?></span>
						<span class="summary-kv__val"><?php esc_html_e( 'Available upon verified buyer enquiry', 'raghushree' ); ?></span>
					</div>
				</div>
				<div style="margin-top: var(--space-5);">
					<a class="btn btn--primary btn--full" href="<?php echo esc_url( $sample_url ); ?>">
						<span><?php esc_html_e( 'Request Evaluation Sample', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</aside>
		</div>
	</div>
</section>

<!-- 4. Grades and Specifications -->
<section class="section surface--pearl single-product-section" id="grades">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Grades & Material Forms', 'raghushree' ),
				'title'       => __( 'Grade Selection & Physical Form Availability', 'raghushree' ),
				'description' => ! empty( $prod['grades_notes'] )
					? $prod['grades_notes']
					: __( 'Available polymer grades and physical forms (such as granules or powder) are confirmed per enquiry against your target processing method.', 'raghushree' ),
			)
		);
		?>

		<?php if ( ! empty( $prod['grades_rows'] ) ) : ?>
			<div class="table-wrap sp-grades-table">
				<table class="data-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Verified Grade / Classification', 'raghushree' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Material Form', 'raghushree' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Typical Processing Suitability', 'raghushree' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $prod['grades_rows'] as $g_row ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $g_row[0] ); ?></strong></td>
								<td><?php echo esc_html( isset( $g_row[1] ) ? $g_row[1] : '—' ); ?></td>
								<td><?php echo esc_html( isset( $g_row[2] ) ? $g_row[2] : '—' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<div class="sp-grade-grid">
			<article class="sp-grade-card">
				<div class="sp-grade-card__head">
					<span class="sp-icon-badge" aria-hidden="true">
						<?php echo raghushree_icon( 'layers' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="sp-kicker"><?php esc_html_e( '01 / Grade Classification', 'raghushree' ); ?></span>
				</div>
				<h3 class="sp-grade-card__title"><?php esc_html_e( 'Process-Aligned Polymer Grades', 'raghushree' ); ?></h3>
				<p class="sp-grade-card__desc"><?php esc_html_e( 'Grade options—including primary-compatible, reprocessed, or recycled classifications where applicable—are shortlisted to fit your moulding or extrusion line.', 'raghushree' ); ?></p>
				<div class="sp-grade-card__tags">
					<?php if ( ! empty( $prod['grades_terms'] ) ) : ?>
						<?php foreach ( $prod['grades_terms'] as $g_term ) : ?>
							<span class="tag tag--accent"><?php echo esc_html( $g_term->name ); ?></span>
						<?php endforeach; ?>
					<?php else : ?>
						<span class="tag tag--accent"><?php esc_html_e( 'Confirmed Per Process', 'raghushree' ); ?></span>
					<?php endif; ?>
				</div>
			</article>

			<article class="sp-grade-card">
				<div class="sp-grade-card__head">
					<span class="sp-icon-badge" aria-hidden="true">
						<?php echo raghushree_icon( 'box' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="sp-kicker"><?php esc_html_e( '02 / Physical Material Forms', 'raghushree' ); ?></span>
				</div>
				<h3 class="sp-grade-card__title"><?php esc_html_e( 'Granules & Applicable Forms', 'raghushree' ); ?></h3>
				<p class="sp-grade-card__desc"><?php esc_html_e( 'Supplied in verified physical forms such as plastic granules or plastic powder depending on polymer family, equipment feed requirements, and current supply.', 'raghushree' ); ?></p>
				<div class="sp-grade-card__tags">
					<?php if ( ! empty( $prod['forms_terms'] ) ) : ?>
						<?php foreach ( $prod['forms_terms'] as $f_term ) : ?>
							<span class="tag"><?php echo esc_html( $f_term->name ); ?></span>
						<?php endforeach; ?>
					<?php else : ?>
						<span class="tag"><?php esc_html_e( 'Granules / Powder Verified Per Lot', 'raghushree' ); ?></span>
					<?php endif; ?>
				</div>
			</article>

			<article class="sp-grade-card">
				<div class="sp-grade-card__head">
					<span class="sp-icon-badge" aria-hidden="true">
						<?php echo raghushree_icon( 'settings' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<span class="sp-kicker"><?php esc_html_e( '03 / Lot & Colour Matching', 'raghushree' ); ?></span>
				</div>
				<h3 class="sp-grade-card__title"><?php esc_html_e( 'Melt Flow & Appearance Fit', 'raghushree' ); ?></h3>
				<p class="sp-grade-card__desc"><?php esc_html_e( 'Selection is verified against your target melt flow behaviour, natural or colour preference, part geometry, and end-product durability requirements.', 'raghushree' ); ?></p>
				<div class="sp-grade-card__tags">
					<span class="tag"><?php esc_html_e( 'Trial Sample Coordination', 'raghushree' ); ?></span>
				</div>
			</article>
		</div>

		<div class="sp-verify-bar surface--ink">
			<div class="sp-verify-bar__body">
				<span class="sp-icon-badge sp-icon-badge--dark" aria-hidden="true">
					<?php echo raghushree_icon( 'shield' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
				<div>
					<span class="sp-kicker sp-kicker--light"><?php esc_html_e( 'Live Batch & Grade Verification', 'raghushree' ); ?></span>
					<h3 class="sp-verify-bar__title"><?php esc_html_e( 'Confirm Current Batch & Form Availability', 'raghushree' ); ?></h3>
					<p class="sp-verify-bar__desc"><?php esc_html_e( 'Specific melt flow grades, reprocessed/recycled classifications, and granule or powder forms are verified directly against current supply.', 'raghushree' ); ?></p>
				</div>
			</div>
			<div class="sp-verify-bar__actions">
				<a class="btn btn--primary" href="<?php echo esc_url( $enquire_url ); ?>">
					<span><?php esc_html_e( 'Verify Grade Availability', 'raghushree' ); ?></span>
					<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
				<a class="btn btn--on-dark" href="<?php echo esc_url( $sample_url ); ?>">
					<span><?php esc_html_e( 'Request Sample', 'raghushree' ); ?></span>
				</a>
			</div>
		</div>
	</div>
</section>

<!-- 5. Applications and Industries -->
<section class="section surface--white single-product-section" id="applications">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow' => __( 'Applications & Industries', 'raghushree' ),
				'title'   => sprintf(
					/* translators: %s: Polymer title */
					__( 'Typical Manufacturing Applications for %s', 'raghushree' ),
					$prod['title']
				),
			)
		);
		?>

		<?php if ( ! empty( $prod['apps_rows'] ) ) : ?>
			<div class="grid-2">
				<?php foreach ( $prod['apps_rows'] as $app_row ) : ?>
					<?php
					get_template_part(
						'template-parts/components/feature-card',
						null,
						array(
							'icon'        => 'layers',
							'title'       => $app_row[0],
							'description' => isset( $app_row[1] ) ? $app_row[1] : '',
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $prod['industries'] ) ) : ?>
			<div class="sp-industries-panel">
				<div class="sp-industries-panel__head">
					<span class="sp-icon-badge" aria-hidden="true">
						<?php echo raghushree_icon( 'globe' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
					<div>
						<span class="sp-kicker"><?php esc_html_e( 'Industrial Buyer Segments', 'raghushree' ); ?></span>
						<h3 class="sp-industries-panel__title"><?php esc_html_e( 'Target Industries & Buyer Segments', 'raghushree' ); ?></h3>
					</div>
				</div>
				<div class="sp-industries-panel__list">
					<?php foreach ( $prod['industries'] as $ind_row ) : ?>
						<span class="tag tag--white"><?php echo esc_html( $ind_row[0] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- 6. Visual Material & Industrial Showcase Image Cards -->
<section class="section surface--tint single-product-section" id="material-showcase">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Visual Material Showcase', 'raghushree' ),
				'title'       => sprintf(
					/* translators: %s: Polymer title */
					__( 'Explore %s Across Feedstock, Conversion & Supply', 'raghushree' ),
					$prod['title']
				),
				'description' => __( 'Representative industrial views illustrating raw material handling, machine conversion, and organized B2B supply coordination.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<div class="showcase-img-grid">
			<?php foreach ( $product_image_cards as $icard ) : ?>
				<article class="showcase-img-card reveal">
					<div class="showcase-img-card__media">
						<?php
						raghushree_render_image(
							$icard['slot'],
							array(
								'size'     => 'raghushree-card',
								'sizes'    => '(min-width: 1200px) 300px, (min-width: 768px) 46vw, 100vw',
								'priority' => false,
							)
						);
						?>
					</div>
					<div class="showcase-img-card__body">
						<span class="sp-kicker"><?php echo esc_html( $icard['kicker'] ); ?></span>
						<h3 class="showcase-img-card__title"><?php echo esc_html( $icard['title'] ); ?></h3>
						<p class="showcase-img-card__desc"><?php echo esc_html( $icard['desc'] ); ?></p>
						<a class="showcase-img-card__link" href="<?php echo esc_url( $icard['url'] ); ?>">
							<span><?php esc_html_e( 'Enquire Now', 'raghushree' ); ?></span>
							<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- 7. Technical Specifications and Datasheet -->
<section class="section surface--white single-product-section" id="technical-details">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Technical Details', 'raghushree' ),
				'title'       => __( 'Technical Specifications & Grade Documentation', 'raghushree' ),
				'description' => __( 'Technical parameters vary by specific grade and processing class. Share your target operational criteria so our team can verify matching batch specifications.', 'raghushree' ),
			)
		);
		?>

		<?php if ( ! empty( $prod['tech_specs'] ) ) : ?>
			<div class="table-wrap sp-tech-table">
				<table class="data-table">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Property / Parameter', 'raghushree' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Verified Value / Range', 'raghushree' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Reference / Notes', 'raghushree' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $prod['tech_specs'] as $spec_row ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $spec_row[0] ); ?></strong></td>
								<td><?php echo esc_html( isset( $spec_row[1] ) ? $spec_row[1] : '—' ); ?></td>
								<td><?php echo esc_html( isset( $spec_row[2] ) ? $spec_row[2] : '—' ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<div class="sp-tech-layout">
			<div class="sp-tech-grid">
				<article class="sp-tech-item">
					<span class="sp-step-badge"><?php esc_html_e( '01', 'raghushree' ); ?></span>
					<span class="sp-kicker"><?php esc_html_e( 'Rheology & Flow', 'raghushree' ); ?></span>
					<h3 class="sp-tech-item__title"><?php esc_html_e( 'Melt Flow & Process Window', 'raghushree' ); ?></h3>
					<p class="sp-tech-item__desc"><?php esc_html_e( 'Specify your target melt flow range or conversion process so resin viscosity aligns with your tooling and cycle stability.', 'raghushree' ); ?></p>
				</article>

				<article class="sp-tech-item">
					<span class="sp-step-badge"><?php esc_html_e( '02', 'raghushree' ); ?></span>
					<span class="sp-kicker"><?php esc_html_e( 'Mechanical Profile', 'raghushree' ); ?></span>
					<h3 class="sp-tech-item__title"><?php esc_html_e( 'Stiffness, Impact & Load Fit', 'raghushree' ); ?></h3>
					<p class="sp-tech-item__desc"><?php esc_html_e( 'Share end-part mechanical expectations such as rigidity, drop-impact strength, elongation, or stacking durability.', 'raghushree' ); ?></p>
				</article>

				<article class="sp-tech-item">
					<span class="sp-step-badge"><?php esc_html_e( '03', 'raghushree' ); ?></span>
					<span class="sp-kicker"><?php esc_html_e( 'Form & Appearance', 'raghushree' ); ?></span>
					<h3 class="sp-tech-item__title"><?php esc_html_e( 'Granule / Powder & Colour Spec', 'raghushree' ); ?></h3>
					<p class="sp-tech-item__desc"><?php esc_html_e( 'Indicate preferred physical form, natural/white/black/colour sorting, and surface finish requirements for your line.', 'raghushree' ); ?></p>
				</article>

				<article class="sp-tech-item">
					<span class="sp-step-badge"><?php esc_html_e( '04', 'raghushree' ); ?></span>
					<span class="sp-kicker"><?php esc_html_e( 'Quality Evaluation', 'raghushree' ); ?></span>
					<h3 class="sp-tech-item__title"><?php esc_html_e( 'Batch Documentation & Trials', 'raghushree' ); ?></h3>
					<p class="sp-tech-item__desc"><?php esc_html_e( 'Request available grade documentation or coordinate evaluation samples for laboratory testing and machine sign-off.', 'raghushree' ); ?></p>
				</article>
			</div>

			<aside class="sp-tech-aside surface--ink" aria-label="<?php esc_attr_e( 'Technical Consultation', 'raghushree' ); ?>">
				<div class="sp-tech-aside__top">
					<div class="sp-tech-aside__head">
						<span class="sp-icon-badge sp-icon-badge--dark" aria-hidden="true">
							<?php echo raghushree_icon( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<span class="sp-kicker sp-kicker--light"><?php esc_html_e( 'Technical Desk', 'raghushree' ); ?></span>
					</div>
					<h3 class="sp-tech-aside__title"><?php esc_html_e( 'Grade-Specific Technical Consultation', 'raghushree' ); ?></h3>
					<p class="sp-tech-aside__desc">
						<?php
						if ( ! empty( $prod['datasheet'] ) ) {
							esc_html_e( 'Download the attached technical datasheet or contact our team to confirm batch-specific parameters for your line.', 'raghushree' );
						} else {
							esc_html_e( 'Because technical properties depend on the exact grade selected, share your target MFI, density, or end-product parameters so our team can share verified batch details.', 'raghushree' );
						}
						?>
					</p>
					<ul class="sp-check-list">
						<li>
							<span class="sp-check-list__icon" aria-hidden="true"><?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php esc_html_e( 'Application-aligned grade shortlisting', 'raghushree' ); ?></span>
						</li>
						<li>
							<span class="sp-check-list__icon" aria-hidden="true"><?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php esc_html_e( 'Batch parameter confirmation prior to dispatch', 'raghushree' ); ?></span>
						</li>
						<li>
							<span class="sp-check-list__icon" aria-hidden="true"><?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span><?php esc_html_e( 'Sample coordination for QC & shop-floor trials', 'raghushree' ); ?></span>
						</li>
					</ul>
				</div>

				<div class="sp-tech-aside__actions">
					<?php if ( ! empty( $prod['datasheet'] ) ) : ?>
						<a class="btn btn--on-dark btn--full" href="<?php echo esc_url( $prod['datasheet'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo raghushree_icon( 'file-text' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<span><?php esc_html_e( 'Download Datasheet', 'raghushree' ); ?></span>
						</a>
					<?php endif; ?>
					<a class="btn btn--primary btn--full" href="<?php echo esc_url( $tech_url ); ?>">
						<span><?php esc_html_e( 'Discuss Technical Specs', 'raghushree' ); ?></span>
						<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>
			</aside>
		</div>
	</div>
</section>

<!-- 8. FAQs and Related Products -->
<section class="section surface--pearl single-product-section" id="faqs">
	<div class="container">
		<?php if ( ! empty( $prod['faqs'] ) ) : ?>
			<div class="sp-faq-block">
				<?php
				get_template_part(
					'template-parts/components/section-heading',
					null,
					array(
						'eyebrow' => __( 'Buyer Questions', 'raghushree' ),
						'title'   => sprintf(
							/* translators: %s: Polymer title */
							__( 'Frequently Asked Questions — %s', 'raghushree' ),
							$prod['title']
						),
					)
				);

				get_template_part(
					'template-parts/components/faq',
					null,
					array(
						'faqs'      => $prod['faqs'],
						'id_prefix' => 'prod-faq-' . $prod['id'],
					)
				);
				?>
			</div>
		<?php endif; ?>

		<?php if ( $related_query->have_posts() ) : ?>
			<div class="related-products-block">
				<?php
				get_template_part(
					'template-parts/components/section-heading',
					null,
					array(
						'eyebrow' => __( 'Explore Related Polymers', 'raghushree' ),
						'title'   => __( 'Other Plastic Raw Material Categories', 'raghushree' ),
					)
				);
				?>
				<div class="grid-3">
					<?php foreach ( $related_query->posts as $rel_post ) : ?>
						<?php
						get_template_part(
							'template-parts/components/product-card',
							null,
							array( 'product_id' => $rel_post->ID )
						);
						?>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- 9. Final RFQ / Enquiry CTA -->
<section class="section surface--white single-product-section" id="enquiry">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/cta-banner',
			null,
			array(
				'eyebrow'     => __( 'Request Pricing or Samples', 'raghushree' ),
				'title'       => sprintf(
					/* translators: %s: Polymer title */
					__( 'Enquire About %s Supply Today', 'raghushree' ),
					$prod['title']
				),
				'description' => __( 'Submit your target grade, physical form, application, and estimated quantity to receive a verified quotation from Raghushree Plast Products Pvt. Ltd.', 'raghushree' ),
				'material'    => $prod['title'],
			)
		);
		?>
	</div>
</section>
