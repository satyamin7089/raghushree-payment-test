<?php
/** Product page: show the product owner's content, not theme-authored claims. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
$post_id = get_queried_object_id();
$prod = raghushree_get_product_data( $post_id );
if ( empty( $prod['id'] ) ) { return; }
$enquire = raghushree_get_enquiry_url( array( 'material' => $prod['title'], 'enquiry_type' => 'Request a Quotation' ) );
$intros = raghushree_product_section_intros( $post_id );
foreach ( array( 'features', 'applications', 'specifications', 'faqs' ) as $key ) { $intros[$key] = isset( $intros[$key] ) && is_string( $intros[$key] ) ? trim( $intros[$key] ) : ''; }
$points = preg_split( '/\r\n|\r|\n/', trim( (string) get_post_meta( $post_id, '_raghushree_highlights', true ) ) );
$points = array_values( array_filter( array_map( 'trim', (array) $points ), 'strlen' ) );
$slides = array();
$featured_id = (int) get_post_thumbnail_id( $post_id );
if ( $featured_id && wp_attachment_is_image( $featured_id ) ) { $slides[] = array( 'slot' => $prod['image_slot'], 'caption_title' => $prod['title'] ); }
foreach ( $prod['gallery_ids'] as $image_id ) {
	if ( $image_id === $featured_id || ! wp_attachment_is_image( $image_id ) ) { continue; }
	$slides[] = array( 'attachment_id' => $image_id, 'caption_title' => $prod['title'] );
}
$badges = array();
if ( ! empty( $prod['categories'] ) ) { $badges[] = array( 'label' => $prod['primary_cat'], 'variant' => 'accent' ); }
if ( ! empty( $prod['polymer_code'] ) ) { $badges[] = array( 'label' => $prod['polymer_code'] ); }
get_template_part( 'template-parts/components/page-banner', null, array(
	'id' => 'product-hero', 'badges' => $badges, 'title' => $prod['title'],
	'description' => $prod['short_desc'], 'points' => $points,
	'purchase_post_id' => $post_id, 'purchase_enquire_url' => $enquire,
	'image_slot' => $prod['image_slot'], 'slider_slides' => $slides, 'post_id' => $post_id,
	'caption_title' => $prod['title'], 'caption_sub' => '', 'empty_image' => empty( $slides ),
) );
get_template_part( 'template-parts/components/product-checkout' );
$content = get_post_field( 'post_content', $post_id );
?>
<?php if ( '' !== trim( (string) $content ) ) : ?>
<section class="section surface--white single-product-section" id="overview"><div class="container product-editorial">
	<div class="product-editorial__heading"><span class="eyebrow"><?php esc_html_e( 'Product overview', 'raghushree' ); ?></span><h2><?php echo esc_html( $prod['title'] ); ?></h2></div>
	<div class="product-editorial__body entry-content"><?php echo wp_kses_post( apply_filters( 'the_content', $content ) ); ?></div>
</div></section>
<?php endif; ?>
<?php if ( ! empty( $prod['grades_notes'] ) || ! empty( $prod['grades_rows'] ) ) : ?>
<section class="section surface--pearl single-product-section" id="grades"><div class="container product-editorial">
	<div class="product-editorial__heading"><span class="eyebrow"><?php esc_html_e( 'Product details', 'raghushree' ); ?></span><h2><?php esc_html_e( 'Grades & forms', 'raghushree' ); ?></h2></div>
	<div class="product-editorial__body"><?php if ( ! empty( $prod['grades_notes'] ) ) : ?><p><?php echo esc_html( $prod['grades_notes'] ); ?></p><?php endif; ?>
	<?php if ( ! empty( $prod['grades_rows'] ) ) : ?><div class="table-wrap"><table class="data-table"><thead><tr><th><?php esc_html_e( 'Grade', 'raghushree' ); ?></th><th><?php esc_html_e( 'Form', 'raghushree' ); ?></th><th><?php esc_html_e( 'Typical use', 'raghushree' ); ?></th></tr></thead><tbody><?php foreach ( $prod['grades_rows'] as $row ) : ?><tr><?php for ( $i = 0; $i < 3; $i++ ) : ?><td><?php echo esc_html( $row[$i] ?? '' ); ?></td><?php endfor; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div>
</div></section><?php endif; ?>
<?php if ( ! empty( $prod['features'] ) || '' !== $intros['features'] ) : ?>
<section class="section product-features single-product-section" id="features" aria-labelledby="features-title">
  <div class="container product-editorial product-detail-layout">
    <div class="product-editorial__heading product-detail-heading" tabindex="0"><h2 id="features-title"><?php esc_html_e( 'Features', 'raghushree' ); ?></h2><?php if ( '' !== $intros['features'] ) : ?><p class="product-detail-heading__intro"><?php echo esc_html( $intros['features'] ); ?></p><?php endif; ?></div>
    <?php if ( ! empty( $prod['features'] ) && is_array( $prod['features'] ) ) : ?><div class="product-features__list">
      <?php foreach ( array_values( isset( $prod['features'] ) && is_array( $prod['features'] ) ? $prod['features'] : array() ) as $index => $row ) : ?>
      <article class="product-feature"><span class="product-feature__number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span><div><h3><?php echo esc_html( $row[0] ); ?></h3><?php if ( ! empty( $row[1] ) ) : ?><p><?php echo esc_html( $row[1] ); ?></p><?php endif; ?></div></article>
      <?php endforeach; ?>
    </div><?php endif; ?>
  </div>
</section><?php endif; ?>
<?php if ( ! empty( $prod['apps_rows'] ) || ! empty( $prod['industries'] ) || '' !== $intros['applications'] ) : ?>
<section class="section product-applications single-product-section" id="applications" aria-labelledby="applications-title">
  <div class="container product-editorial product-detail-layout">
    <div class="product-editorial__heading product-detail-heading" tabindex="0"><h2 id="applications-title"><?php esc_html_e( 'Applications', 'raghushree' ); ?></h2><?php if ( '' !== $intros['applications'] ) : ?><p class="product-detail-heading__intro"><?php echo esc_html( $intros['applications'] ); ?></p><?php endif; ?></div>
    <div class="product-applications__body">
    <?php if ( ! empty( $prod['apps_rows'] ) ) : ?><div class="product-applications__grid">
      <?php foreach ( array_values( $prod['apps_rows'] ) as $index => $row ) : ?>
      <article class="product-application"><span class="product-application__number" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span><h3><?php echo esc_html( $row[0] ); ?></h3><?php if ( ! empty( $row[1] ) ) : ?><p><?php echo esc_html( $row[1] ); ?></p><?php endif; ?></article>
      <?php endforeach; ?>
    </div><?php endif; ?>
    <?php if ( ! empty( $prod['industries'] ) ) : ?><div class="product-industries"><h3><?php esc_html_e( 'Industries', 'raghushree' ); ?></h3><ul><?php foreach ( $prod['industries'] as $row ) : ?><li><?php echo esc_html( $row[0] ); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    </div>
  </div>
</section><?php endif; ?>
<?php if ( ! empty( $prod['tech_specs'] ) || ! empty( $prod['datasheet'] ) || '' !== $intros['specifications'] ) : ?>
<section class="section surface--pearl single-product-section" id="technical-details"><div class="container product-editorial product-detail-layout">
	<div class="product-editorial__heading product-detail-heading" tabindex="0"><h2><?php esc_html_e( 'Technical specifications', 'raghushree' ); ?></h2><?php if ( '' !== $intros['specifications'] ) : ?><p class="product-detail-heading__intro"><?php echo esc_html( $intros['specifications'] ); ?></p><?php endif; ?></div><div class="product-editorial__body">
	<?php if ( ! empty( $prod['tech_specs'] ) ) : ?><div class="table-wrap"><table class="data-table"><thead><tr><th><?php esc_html_e( 'Property', 'raghushree' ); ?></th><th><?php esc_html_e( 'Value', 'raghushree' ); ?></th><th><?php esc_html_e( 'Notes', 'raghushree' ); ?></th></tr></thead><tbody><?php foreach ( $prod['tech_specs'] as $row ) : ?><tr><?php for ( $i = 0; $i < 3; $i++ ) : ?><td><?php echo esc_html( $row[$i] ?? '' ); ?></td><?php endfor; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
	<?php if ( ! empty( $prod['datasheet'] ) ) : ?><p><a class="btn btn--secondary" href="<?php echo esc_url( $prod['datasheet'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Download datasheet', 'raghushree' ); ?></a></p><?php endif; ?></div>
</div></section><?php endif; ?>
<?php if ( ! empty( $prod['processing'] ) || ! empty( $prod['packaging'] ) || ! empty( $prod['pack_size'] ) || ! empty( $prod['moq'] ) || ! empty( $prod['availability'] ) ) : ?>
<section class="section surface--white single-product-section" id="additional-details"><div class="container product-editorial"><div class="product-editorial__heading"><span class="eyebrow"><?php esc_html_e( 'Product details', 'raghushree' ); ?></span><h2><?php esc_html_e( 'Additional information', 'raghushree' ); ?></h2></div><div class="product-editorial__body">
<?php foreach ( array( 'packaging' => __( 'Packaging', 'raghushree' ), 'pack_size' => __( 'Pack size', 'raghushree' ), 'moq' => __( 'Minimum order quantity', 'raghushree' ), 'availability' => __( 'Availability', 'raghushree' ) ) as $key => $label ) : if ( empty( $prod[$key] ) ) { continue; } ?><div class="product-editorial__item"><h3><?php echo esc_html( $label ); ?></h3><p><?php echo esc_html( $prod[$key] ); ?></p></div><?php endforeach; ?>
<?php foreach ( $prod['processing'] as $row ) : ?><div class="product-editorial__item"><h3><?php echo esc_html( $row[0] ); ?></h3><p><?php echo esc_html( $row[1] ); ?></p></div><?php endforeach; ?>
</div></div></section><?php endif; ?>
<?php if ( ! empty( $prod['faqs'] ) || '' !== $intros['faqs'] ) : ?><section class="section surface--white single-product-section" id="faqs"><div class="container product-editorial product-detail-layout"><div class="product-editorial__heading product-detail-heading" tabindex="0"><h2><?php esc_html_e( 'Questions & answers', 'raghushree' ); ?></h2><?php if ( '' !== $intros['faqs'] ) : ?><p class="product-detail-heading__intro"><?php echo esc_html( $intros['faqs'] ); ?></p><?php endif; ?></div><div class="product-editorial__body"><?php if ( ! empty( $prod['faqs'] ) ) { get_template_part( 'template-parts/components/faq', null, array( 'faqs' => $prod['faqs'], 'id_prefix' => 'prod-faq-' . $prod['id'] ) ); } ?></div></div></section><?php endif; ?>
