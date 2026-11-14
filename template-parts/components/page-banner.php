<?php
/**
 * Reusable Full-Width Inner-Page Industrial Hero Banner Component.
 *
 * Supports:
 * - Standard inner-page split banner (About, Contact, Products, Archives, 404)
 * - Single-Product enhanced banner: left heading + description + verified bullet
 *   points + two Contact CTA buttons ("Enquire Now" & "Buy Now"), and right
 *   equal-height smooth cross-fade (fade-in / fade-out) multi-image slider.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section_id    = ! empty( $args['id'] ) ? $args['id'] : '';
$eyebrow       = ! empty( $args['eyebrow'] ) ? $args['eyebrow'] : '';
$badges        = ( ! empty( $args['badges'] ) && is_array( $args['badges'] ) ) ? $args['badges'] : array();
$title         = ! empty( $args['title'] ) ? $args['title'] : '';
$description   = ! empty( $args['description'] ) ? $args['description'] : '';
$points        = ( ! empty( $args['points'] ) && is_array( $args['points'] ) ) ? $args['points'] : array();
$primary_cta   = ( ! empty( $args['primary_cta'] ) && is_array( $args['primary_cta'] ) ) ? $args['primary_cta'] : array();
$secondary_btn = ( ! empty( $args['secondary_btn'] ) && is_array( $args['secondary_btn'] ) ) ? $args['secondary_btn'] : array();
$secondary_cta = ( ! empty( $args['secondary_cta'] ) && is_array( $args['secondary_cta'] ) ) ? $args['secondary_cta'] : array();
$image_slot    = ! empty( $args['image_slot'] ) ? $args['image_slot'] : 'about_hero';
$slider_slides = ( ! empty( $args['slider_slides'] ) && is_array( $args['slider_slides'] ) ) ? $args['slider_slides'] : array();
$post_id       = ! empty( $args['post_id'] ) ? (int) $args['post_id'] : 0;
$caption_title = ! empty( $args['caption_title'] ) ? $args['caption_title'] : __( 'Industrial Polymer Raw Material Supply', 'raghushree' );
$caption_sub   = ! empty( $args['caption_sub'] ) ? $args['caption_sub'] : __( 'Bulk orders, grade consultation & sample enquiries', 'raghushree' );
$has_slider    = count( $slider_slides ) > 1;
?>
<section class="section section--hero page-banner-section"<?php echo '' !== $section_id ? ' id="' . esc_attr( $section_id ) . '"' : ''; ?>>
	<div class="page-banner-card">
		<div class="page-banner-card__dots" aria-hidden="true"></div>
		<div class="page-banner-card__accent-bar" aria-hidden="true"></div>

		<div class="container page-banner-card__grid">
			<!-- Left Column: Page-Specific Verified Content (Equal Height to Right Image) -->
			<div class="page-banner-card__left">
				<div class="page-banner-card__top">
					<?php if ( ! empty( $badges ) ) : ?>
						<div class="page-banner-card__badges">
							<?php foreach ( $badges as $badge ) : ?>
								<?php
								if ( empty( $badge['label'] ) ) {
									continue;
								}
								$b_cls = ( isset( $badge['variant'] ) && 'accent' === $badge['variant'] ) ? 'tag tag--accent' : 'tag';
								?>
								<span class="<?php echo esc_attr( $b_cls ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( '' !== $eyebrow ) : ?>
						<div class="eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
					<?php endif; ?>

					<?php if ( '' !== $title ) : ?>
						<h1 class="page-banner-card__title"><?php echo wp_kses_post( $title ); ?></h1>
					<?php endif; ?>

					<?php if ( '' !== $description ) : ?>
						<p class="page-banner-card__desc"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $points ) ) : ?>
						<ul class="page-banner-card__points">
							<?php foreach ( $points as $point_text ) : ?>
								<?php if ( '' !== trim( (string) $point_text ) ) : ?>
									<li>
										<span class="page-banner-card__point-icon" aria-hidden="true">
											<?php echo raghushree_icon( 'check' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</span>
										<span><?php echo esc_html( $point_text ); ?></span>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $args['purchase_post_id'] ) ) { get_template_part( 'template-parts/components/product-purchase', null, array( 'product_id' => $args['purchase_post_id'], 'enquire_url' => $args['purchase_enquire_url'] ) ); } ?>

				<?php if ( ! empty( $primary_cta ) || ! empty( $secondary_btn ) || ! empty( $secondary_cta ) ) : ?>
					<div class="page-banner-card__cta-row">
						<?php if ( ! empty( $primary_cta['label'] ) && ! empty( $primary_cta['url'] ) ) : ?>
							<a class="btn btn--primary" href="<?php echo esc_url( $primary_cta['url'] ); ?>">
								<span><?php echo esc_html( $primary_cta['label'] ); ?></span>
								<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>

						<?php if ( ! empty( $secondary_btn['label'] ) && ! empty( $secondary_btn['url'] ) ) : ?>
							<a class="btn btn--dark" href="<?php echo esc_url( $secondary_btn['url'] ); ?>">
								<span><?php echo esc_html( $secondary_btn['label'] ); ?></span>
								<?php echo raghushree_icon( 'arrow-right', 'icon-arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php elseif ( ! empty( $secondary_cta['label'] ) && ! empty( $secondary_cta['url'] ) ) : ?>
							<?php $sec_icon = ! empty( $secondary_cta['icon'] ) ? $secondary_cta['icon'] : 'phone'; ?>
							<a class="page-banner-card__callout" href="<?php echo esc_url( $secondary_cta['url'] ); ?>">
								<span class="page-banner-card__callout-icon" aria-hidden="true">
									<?php echo raghushree_icon( $sec_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
								<span class="page-banner-card__callout-info">
									<?php if ( ! empty( $secondary_cta['kicker'] ) ) : ?>
										<span class="page-banner-card__callout-kicker"><?php echo esc_html( $secondary_cta['kicker'] ); ?></span>
									<?php endif; ?>
									<strong class="page-banner-card__callout-label"><?php echo esc_html( $secondary_cta['label'] ); ?></strong>
								</span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right Column: Equal-Height Image Frame (Single Image or Smooth Fade-In / Fade-Out Slider) -->
			<div class="page-banner-card__right">
				<div class="page-banner-card__frame<?php echo $has_slider ? ' banner-fade-slider' : ''; ?>"<?php echo $has_slider ? ' data-fade-slider' : ''; ?>>
					<?php if ( $has_slider ) : ?>
						<div class="banner-fade-slider__slides">
							<?php foreach ( $slider_slides as $sl_idx => $sl ) : ?>
								<div
									class="banner-fade-slider__slide<?php echo 0 === $sl_idx ? ' is-active' : ''; ?>"
									data-fade-slide="<?php echo esc_attr( (string) $sl_idx ); ?>"
									data-caption-title="<?php echo esc_attr( ! empty( $sl['caption_title'] ) ? $sl['caption_title'] : $caption_title ); ?>"
									data-caption-sub="<?php echo esc_attr( ! empty( $sl['caption_sub'] ) ? $sl['caption_sub'] : $caption_sub ); ?>"
									aria-hidden="<?php echo 0 === $sl_idx ? 'false' : 'true'; ?>"
								>
									<?php
									if ( ! empty( $sl['attachment_id'] ) ) {
										raghushree_render_image(
											$image_slot,
											array(
												'attachment_id' => (int) $sl['attachment_id'],
												'size'          => 'raghushree-hero',
												'sizes'         => '(min-width: 900px) 520px, 100vw',
												'priority'      => 0 === $sl_idx,
											)
										);
									} else {
										raghushree_render_image(
											! empty( $sl['slot'] ) ? $sl['slot'] : $image_slot,
											array(
												'post_id'  => 0 === $sl_idx ? $post_id : 0,
												'size'     => 'raghushree-hero',
												'sizes'    => '(min-width: 900px) 520px, 100vw',
												'priority' => 0 === $sl_idx,
											)
										);
									}
									?>
								</div>
							<?php endforeach; ?>
						</div>

						<div class="page-banner-card__caption page-banner-card__caption--slider">
							<div class="page-banner-card__caption-text">
								<span class="page-banner-card__caption-title" data-fade-title><?php echo esc_html( ! empty( $slider_slides[0]['caption_title'] ) ? $slider_slides[0]['caption_title'] : $caption_title ); ?></span>
								<span class="page-banner-card__caption-sub" data-fade-sub><?php echo esc_html( ! empty( $slider_slides[0]['caption_sub'] ) ? $slider_slides[0]['caption_sub'] : $caption_sub ); ?></span>
							</div>
							<div class="banner-fade-slider__nav" role="group" aria-label="<?php esc_attr_e( 'Product banner image controls', 'raghushree' ); ?>">
								<button type="button" class="banner-fade-slider__btn" data-fade-prev aria-label="<?php esc_attr_e( 'Previous image', 'raghushree' ); ?>">
									<?php echo raghushree_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
								<div class="banner-fade-slider__dots">
									<?php foreach ( $slider_slides as $sl_idx => $sl ) : ?>
										<button
											type="button"
											class="banner-fade-slider__dot<?php echo 0 === $sl_idx ? ' is-active' : ''; ?>"
											data-fade-dot="<?php echo esc_attr( (string) $sl_idx ); ?>"
											aria-label="<?php echo esc_attr( sprintf( '%s %d', __( 'View image', 'raghushree' ), $sl_idx + 1 ) ); ?>"
											aria-pressed="<?php echo 0 === $sl_idx ? 'true' : 'false'; ?>"
										></button>
									<?php endforeach; ?>
								</div>
								<button type="button" class="banner-fade-slider__btn" data-fade-next aria-label="<?php esc_attr_e( 'Next image', 'raghushree' ); ?>">
									<?php echo raghushree_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</button>
							</div>
						</div>
					<?php else : ?>
						<?php
						raghushree_render_image(
							$image_slot,
							array(
								'post_id'  => $post_id,
								'size'     => 'raghushree-hero',
								'sizes'    => '(min-width: 900px) 520px, 100vw',
								'priority' => true,
							)
						);
						?>
						<div class="page-banner-card__caption">
							<span class="page-banner-card__caption-title"><?php echo esc_html( $caption_title ); ?></span>
							<span class="page-banner-card__caption-sub"><?php echo esc_html( $caption_sub ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
