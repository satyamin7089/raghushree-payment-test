<?php
/**
 * Reusable Feature / Capability Card Component.
 *
 * Args:
 * - icon (string)
 * - meta (string)
 * - title (string)
 * - description (string)
 * - link_label (string)
 * - link_url (string)
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icon        = isset( $args['icon'] ) ? (string) $args['icon'] : '';
$meta        = isset( $args['meta'] ) ? (string) $args['meta'] : '';
$title       = isset( $args['title'] ) ? (string) $args['title'] : '';
$description = isset( $args['description'] ) ? (string) $args['description'] : '';
$link_label  = isset( $args['link_label'] ) ? (string) $args['link_label'] : '';
$link_url    = isset( $args['link_url'] ) ? (string) $args['link_url'] : '';

if ( '' === $title ) {
	return;
}
?>
<article class="feature-card reveal">
	<?php if ( '' !== $icon ) : ?>
		<div class="feature-card__icon">
			<?php echo raghushree_icon( $icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $meta ) : ?>
		<div class="feature-card__meta"><?php echo esc_html( $meta ); ?></div>
	<?php endif; ?>

	<h3 class="feature-card__title"><?php echo esc_html( $title ); ?></h3>

	<?php if ( '' !== $description ) : ?>
		<p class="feature-card__desc"><?php echo esc_html( $description ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $link_label && '' !== $link_url ) : ?>
		<div class="feature-card__footer">
			<a class="link-arrow" href="<?php echo esc_url( $link_url ); ?>">
				<span><?php echo esc_html( $link_label ); ?></span>
				<?php echo raghushree_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</div>
	<?php endif; ?>
</article>
