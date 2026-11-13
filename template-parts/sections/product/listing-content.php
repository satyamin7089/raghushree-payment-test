<?php
/**
 * Shared Dynamic Products Listing Content (Used by `archive-products.php` & `template-products.php`).
 *
 * Supports both server-side URL query filtering (no-JS safe) and instant
 * client-side progressive filtering without duplicating markup.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_q     = isset( $_GET['product_q'] ) ? sanitize_text_field( wp_unslash( $_GET['product_q'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_cat   = isset( $_GET['polymer_cat'] ) ? sanitize_title( wp_unslash( $_GET['polymer_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_grade = isset( $_GET['material_grade'] ) ? sanitize_title( wp_unslash( $_GET['material_grade'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$active_form  = isset( $_GET['material_form'] ) ? sanitize_title( wp_unslash( $_GET['material_form'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( is_tax( 'polymer_category' ) && '' === $active_cat ) {
	$q_term = get_queried_object();
	if ( $q_term instanceof WP_Term ) {
		$active_cat = $q_term->slug;
	}
}

$cat_terms = get_terms(
	array(
		'taxonomy'   => 'polymer_category',
		'hide_empty' => true,
	)
);
if ( is_wp_error( $cat_terms ) ) {
	$cat_terms = array();
}

// Grade & Form filters appear ONLY when verified terms are assigned to published products
$grade_terms = get_terms(
	array(
		'taxonomy'   => 'material_grade',
		'hide_empty' => true,
	)
);
if ( is_wp_error( $grade_terms ) ) {
	$grade_terms = array();
}

$form_terms = get_terms(
	array(
		'taxonomy'   => 'material_form',
		'hide_empty' => true,
	)
);
if ( is_wp_error( $form_terms ) ) {
	$form_terms = array();
}

$all_products  = raghushree_get_catalog_items( 50 );
$has_subfilter = ( ! empty( $grade_terms ) || ! empty( $form_terms ) );

// Pre-compute initial server-side visibility so non-JS requests also filter accurately
$visible_initial = 0;
$card_visibility = array();
foreach ( $all_products as $prod ) {
	$c_slugs = wp_list_pluck( $prod['categories'], 'slug' );
	$g_slugs = wp_list_pluck( $prod['grades_terms'], 'slug' );
	$f_slugs = wp_list_pluck( $prod['forms_terms'], 'slug' );
	$haystack = strtolower(
		implode(
			' ',
			array(
				$prod['title'],
				$prod['polymer_code'],
				$prod['primary_cat'],
				$prod['short_desc'],
			)
		)
	);

	$match = true;
	if ( '' !== $active_q && false === strpos( $haystack, strtolower( $active_q ) ) ) {
		$match = false;
	}
	if ( '' !== $active_cat && ! in_array( $active_cat, $c_slugs, true ) ) {
		$match = false;
	}
	if ( '' !== $active_grade && ! in_array( $active_grade, $g_slugs, true ) ) {
		$match = false;
	}
	if ( '' !== $active_form && ! in_array( $active_form, $f_slugs, true ) ) {
		$match = false;
	}

	$card_visibility[ $prod['id'] ] = $match;
	if ( $match ) {
		$visible_initial++;
	}
}

$products_faqs = array(
	array(
		'question' => __( 'How do I verify whether a specific polymer grade or form is currently available?', 'raghushree' ),
		'answer'   => __( 'Click "Enquire" on any polymer card or open its Product Details page to submit your target melt flow, colour, form (granules or powder), and quantity. Our team confirms verified current availability.', 'raghushree' ),
	),
	array(
		'question' => __( 'Do you supply both primary commodity polymers and recycled/reprocessed grades?', 'raghushree' ),
		'answer'   => __( 'Yes. Our catalog supports PP, HDPE, LDPE, LLDPE, PVC, and PET alongside dedicated recycled and reprocessed polymer grades where verified batches are available.', 'raghushree' ),
	),
	array(
		'question' => __( 'Can we request technical guidance if we are unsure which polymer grade fits our mould or extruder?', 'raghushree' ),
		'answer'   => __( 'Yes. Select "Technical Consultation" on our enquiry form and describe your end-product and processing equipment so our team can assist with grade selection.', 'raghushree' ),
	),
);
?>
<!-- 1. Product Listing Hero -->
<?php
get_template_part(
	'template-parts/components/page-banner',
	null,
	array(
		'id'            => 'products-hero',
		'eyebrow'       => __( 'Polymer Raw Material Catalog', 'raghushree' ),
		'title'         => __( 'Plastic Raw Materials & Polymer Categories', 'raghushree' ),
		'description'   => __( 'Browse thermoplastic polymer categories supplied by Raghushree for injection moulding, blow moulding, film packaging, pipe extrusion, and industrial manufacturing.', 'raghushree' ),
		'primary_cta'   => array(
			'label' => __( 'Request a Quotation', 'raghushree' ),
			'url'   => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Request a Quotation' ) ),
		),
		'secondary_cta' => array(
			'kicker' => __( 'Bulk & Trial Evaluation', 'raghushree' ),
			'label'  => __( 'Request a Sample', 'raghushree' ),
			'url'    => raghushree_get_enquiry_url( array( 'enquiry_type' => 'Sample Request' ) ),
			'icon'   => 'phone',
		),
		'image_slot'    => 'products_hero',
		'caption_title' => __( 'Industrial Polymer Raw Material Supply', 'raghushree' ),
		'caption_sub'   => __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' ),
	)
);
?>

<!-- 2. Introduction & Polymer Category Quick Navigation -->
<section class="section section--compact surface--white" id="polymer-category-navigation">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Explore by Polymer Family', 'raghushree' ),
				'title'       => __( 'Select a Polymer Category or Filter by Specification', 'raghushree' ),
				'description' => __( 'Display shows verified polymer information. Because grade and physical form availability varies by polymer and production schedule, contact our team to confirm specific lot specifications.', 'raghushree' ),
				'align'       => 'split',
			)
		);
		?>

		<?php if ( ! empty( $cat_terms ) ) : ?>
			<nav aria-label="<?php esc_attr_e( 'Polymer Categories', 'raghushree' ); ?>">
				<div class="category-pills">
					<a
						class="category-pill<?php echo '' === $active_cat ? ' is-active' : ''; ?>"
						href="<?php echo esc_url( raghushree_get_products_url() ); ?>"
						data-category-pill=""
						aria-pressed="<?php echo '' === $active_cat ? 'true' : 'false'; ?>"
					>
						<?php esc_html_e( 'All Polymers', 'raghushree' ); ?>
					</a>
					<?php foreach ( $cat_terms as $c_term ) : ?>
						<a
							class="category-pill<?php echo $active_cat === $c_term->slug ? ' is-active' : ''; ?>"
							href="<?php echo esc_url( raghushree_get_products_url( array( 'polymer_cat' => $c_term->slug ) ) ); ?>"
							data-category-pill="<?php echo esc_attr( $c_term->slug ); ?>"
							aria-pressed="<?php echo $active_cat === $c_term->slug ? 'true' : 'false'; ?>"
						>
							<?php echo esc_html( $c_term->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</nav>
		<?php endif; ?>
	</div>
</section>

<!-- 3. Search, Filters & Dynamic Product Grid -->
<section class="section surface--pearl" id="products-catalog-grid">
	<div class="container">
		<div class="products-toolbar">
			<form
				class="products-toolbar__form<?php echo ! $has_subfilter ? ' products-toolbar__form--compact' : ''; ?>"
				method="get"
				action="<?php echo esc_url( raghushree_get_products_url() ); ?>"
				data-products-filter-form
			>
				<div class="form-field">
					<label class="form-label" for="filter_product_q"><?php esc_html_e( 'Search Polymers', 'raghushree' ); ?></label>
					<input
						class="form-input"
						type="search"
						id="filter_product_q"
						name="product_q"
						value="<?php echo esc_attr( $active_q ); ?>"
						placeholder="<?php esc_attr_e( 'Search PP, HDPE, pipes, film, granules...', 'raghushree' ); ?>"
					/>
				</div>

				<div class="form-field">
					<label class="form-label" for="filter_polymer_cat"><?php esc_html_e( 'Polymer Category', 'raghushree' ); ?></label>
					<select class="form-select" id="filter_polymer_cat" name="polymer_cat">
						<option value=""><?php esc_html_e( 'All Categories', 'raghushree' ); ?></option>
						<?php foreach ( $cat_terms as $c_term ) : ?>
							<option value="<?php echo esc_attr( $c_term->slug ); ?>" <?php selected( $active_cat, $c_term->slug ); ?>>
								<?php echo esc_html( $c_term->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<?php if ( ! empty( $grade_terms ) ) : ?>
					<div class="form-field">
						<label class="form-label" for="filter_material_grade"><?php esc_html_e( 'Verified Grade', 'raghushree' ); ?></label>
						<select class="form-select" id="filter_material_grade" name="material_grade">
							<option value=""><?php esc_html_e( 'All Verified Grades', 'raghushree' ); ?></option>
							<?php foreach ( $grade_terms as $g_term ) : ?>
								<option value="<?php echo esc_attr( $g_term->slug ); ?>" <?php selected( $active_grade, $g_term->slug ); ?>>
									<?php echo esc_html( $g_term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $form_terms ) ) : ?>
					<div class="form-field">
						<label class="form-label" for="filter_material_form"><?php esc_html_e( 'Verified Form', 'raghushree' ); ?></label>
						<select class="form-select" id="filter_material_form" name="material_form">
							<option value=""><?php esc_html_e( 'All Verified Forms', 'raghushree' ); ?></option>
							<?php foreach ( $form_terms as $f_term ) : ?>
								<option value="<?php echo esc_attr( $f_term->slug ); ?>" <?php selected( $active_form, $f_term->slug ); ?>>
									<?php echo esc_html( $f_term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<div class="products-toolbar__actions">
					<button type="submit" class="btn btn--primary btn--sm">
						<span><?php esc_html_e( 'Apply', 'raghushree' ); ?></span>
					</button>
					<a class="btn btn--secondary btn--sm" href="<?php echo esc_url( raghushree_get_products_url() ); ?>" data-products-reset>
						<span><?php esc_html_e( 'Reset', 'raghushree' ); ?></span>
					</a>
				</div>
			</form>
		</div>

		<div class="products-status">
			<div data-products-count aria-live="polite">
				<?php
				printf(
					/* translators: %d: Number of visible polymer records */
					esc_html( _n( '%d polymer record shown', '%d polymer records shown', $visible_initial, 'raghushree' ) ),
					absint( $visible_initial )
				);
				?>
			</div>
			<div>
				<a class="link-arrow" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Material Availability' ) ) ); ?>">
					<span><?php esc_html_e( 'Need a specific MFI or custom grade? Enquire directly', 'raghushree' ); ?></span>
					<?php echo raghushree_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</div>
		</div>

		<?php if ( ! empty( $all_products ) ) : ?>
			<div class="grid-3">
				<?php foreach ( $all_products as $prod ) : ?>
					<?php
					$is_vis = ! empty( $card_visibility[ $prod['id'] ] );
					if ( ! $is_vis ) {
						// Render hidden for progressive JS instant filter while keeping non-JS filtered cleanly
						ob_start();
						get_template_part( 'template-parts/components/product-card', null, array( 'product' => $prod ) );
						$card_html = (string) ob_get_clean();
						echo preg_replace( '/^<article\b/', '<article hidden', trim( $card_html ), 1 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						get_template_part( 'template-parts/components/product-card', null, array( 'product' => $prod ) );
					}
					?>
				<?php endforeach; ?>
			</div>

			<div class="products-empty" data-products-empty <?php echo $visible_initial > 0 ? 'hidden' : ''; ?>>
				<h3><?php esc_html_e( 'No Matching Polymer Records Found', 'raghushree' ); ?></h3>
				<p style="max-width: 540px; margin: var(--space-3) auto var(--space-5);">
					<?php esc_html_e( 'Try clearing one of the active filters or contact our sales team directly—many specific polymer grades and forms are confirmed per custom enquiry.', 'raghushree' ); ?>
				</p>
				<div class="btn-group" style="justify-content: center;">
					<a class="btn btn--secondary" href="<?php echo esc_url( raghushree_get_products_url() ); ?>" data-products-reset>
						<span><?php esc_html_e( 'Show All Polymers', 'raghushree' ); ?></span>
					</a>
					<a class="btn btn--primary" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Material Availability' ) ) ); ?>">
						<span><?php esc_html_e( 'Enquire About Custom Grade', 'raghushree' ); ?></span>
					</a>
				</div>
			</div>
		<?php else : ?>
			<div class="products-empty">
				<h3><?php esc_html_e( 'Polymer Catalog Updating', 'raghushree' ); ?></h3>
				<p><?php esc_html_e( 'Contact our commercial team directly for current PP, HDPE, LDPE, LLDPE, PVC, PET, and recycled polymer availability.', 'raghushree' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>

<!-- 4. Mid-Page Specification Enquiry CTA -->
<section class="section section--compact surface--tint" id="products-enquiry-strip">
	<div class="container">
		<div class="forms-notice" style="margin-top: 0;">
			<div>
				<strong><?php esc_html_e( 'Looking for Bulk Supply, Granules vs Powder, or Sample Evaluation?', 'raghushree' ); ?></strong>
				<span><?php esc_html_e( 'Share your target polymer specification and estimated monthly or batch volume with our Kanpur commercial desk.', 'raghushree' ); ?></span>
			</div>
			<a class="btn btn--primary btn--sm" href="<?php echo esc_url( raghushree_get_enquiry_url( array( 'enquiry_type' => 'Bulk Order Enquiry' ) ) ); ?>">
				<span><?php esc_html_e( 'Submit Bulk Enquiry', 'raghushree' ); ?></span>
				<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</section>

<!-- 5. Catalog FAQs -->
<section class="section surface--white" id="products-faqs">
	<div class="container--narrow">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow' => __( 'Catalog FAQs', 'raghushree' ),
				'title'   => __( 'Questions About Polymer Grades, Forms & Availability', 'raghushree' ),
				'align'   => 'center',
			)
		);

		get_template_part(
			'template-parts/components/faq',
			null,
			array(
				'faqs'      => $products_faqs,
				'id_prefix' => 'products-faq',
			)
		);
		?>
	</div>
</section>

<!-- 6. Final CTA -->
<section class="section surface--pearl" id="products-final-cta">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/cta-banner',
			null,
			array(
				'eyebrow'     => __( 'Custom RFQ & Technical Consultation', 'raghushree' ),
				'title'       => __( 'Request Pricing & Availability for Your Selected Polymer', 'raghushree' ),
				'description' => __( 'Our sales team supports domestic Indian manufacturers, contract moulders, dealers, and international export enquiries.', 'raghushree' ),
			)
		);
		?>
	</div>
</section>
