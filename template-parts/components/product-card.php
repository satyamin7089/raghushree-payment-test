<?php
/**
 * Reusable Dynamic Product Card Component.
 *
 * Powered by a single `products` CPT record via `raghushree_get_product_data()`.
 * Displays ONLY verified grades/forms when populated.
 *
 * Args:
 * - product_id (int) OR product (array from raghushree_get_product_data)
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = array();
if ( ! empty( $args['product'] ) && is_array( $args['product'] ) ) {
	$product = $args['product'];
} elseif ( ! empty( $args['product_id'] ) ) {
	$product = raghushree_get_product_data( (int) $args['product_id'] );
}

if ( empty( $product['id'] ) ) {
	return;
}

$cat_slugs   = wp_list_pluck( $product['categories'], 'slug' );
$grade_slugs = wp_list_pluck( $product['grades_terms'], 'slug' );
$form_slugs  = wp_list_pluck( $product['forms_terms'], 'slug' );

$search_blob = implode(
	' ',
	array(
		$product['title'],
		$product['polymer_code'],
		$product['primary_cat'],
		$product['short_desc'],
		implode( ' ', wp_list_pluck( $product['grades_terms'], 'name' ) ),
		implode( ' ', wp_list_pluck( $product['forms_terms'], 'name' ) ),
	)
);

$enquire_url = raghushree_get_enquiry_url(
	array(
		'material'     => $product['title'],
		'enquiry_type' => 'Request a Quotation',
	)
);
?>
<article
	class="product-card reveal"
	data-product-card
	data-search-text="<?php echo esc_attr( $search_blob ); ?>"
	data-categories="<?php echo esc_attr( implode( ',', $cat_slugs ) ); ?>"
	data-grades="<?php echo esc_attr( implode( ',', $grade_slugs ) ); ?>"
	data-forms="<?php echo esc_attr( implode( ',', $form_slugs ) ); ?>"
>
	<a class="product-card__media" href="<?php echo esc_url( $product['permalink'] ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( ! empty( $product['primary_cat'] ) ) : ?>
			<span class="product-card__badge"><?php echo esc_html( $product['primary_cat'] ); ?></span>
		<?php endif; ?>
		<?php
		raghushree_render_image(
			$product['image_slot'],
			array(
				'post_id'  => $product['id'],
				'size'     => 'raghushree-card',
				'sizes'    => '(min-width: 1200px) 380px, (min-width: 768px) 46vw, 100vw',
				'priority' => false,
			)
		);
		?>
	</a>

	<div class="product-card__body">
		<h3 class="product-card__title">
			<a href="<?php echo esc_url( $product['permalink'] ); ?>">
				<?php echo esc_html( $product['title'] ); ?>
			</a>
		</h3>

		<p class="product-card__desc"><?php echo esc_html( $product['short_desc'] ); ?></p>

		<?php if ( ! empty( $product['grades_terms'] ) || ! empty( $product['forms_terms'] ) ) : ?>
			<div class="product-card__tags" aria-label="<?php esc_attr_e( 'Verified Grades and Forms', 'raghushree' ); ?>">
				<?php foreach ( $product['grades_terms'] as $g_term ) : ?>
					<span class="tag tag--accent"><?php echo esc_html( $g_term->name ); ?></span>
				<?php endforeach; ?>
				<?php foreach ( $product['forms_terms'] as $f_term ) : ?>
					<span class="tag"><?php echo esc_html( $f_term->name ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="product-card__actions">
			<a class="btn btn--secondary btn--sm" href="<?php echo esc_url( $product['permalink'] ); ?>">
				<span><?php esc_html_e( 'Product Details', 'raghushree' ); ?></span>
			</a>
			<a class="btn btn--primary btn--sm" href="<?php echo esc_url( $enquire_url ); ?>">
				<span><?php esc_html_e( 'Enquire', 'raghushree' ); ?></span>
				<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	</div>
</article>
