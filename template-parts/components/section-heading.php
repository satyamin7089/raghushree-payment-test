<?php
/**
 * Reusable Section Heading Component.
 *
 * Args:
 * - eyebrow (string)
 * - title (string)
 * - description (string)
 * - align ('default' | 'center' | 'split')
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$eyebrow     = isset( $args['eyebrow'] ) ? (string) $args['eyebrow'] : '';
$title       = isset( $args['title'] ) ? (string) $args['title'] : '';
$description = isset( $args['description'] ) ? (string) $args['description'] : '';
$align       = isset( $args['align'] ) ? (string) $args['align'] : 'default';

if ( '' === $title && '' === $eyebrow ) {
	return;
}

$mod_class = 'section-heading';
if ( 'center' === $align ) {
	$mod_class .= ' section-heading--center';
} elseif ( 'split' === $align ) {
	$mod_class .= ' section-heading--split';
}
?>
<div class="<?php echo esc_attr( $mod_class ); ?>">
	<?php if ( 'split' === $align ) : ?>
		<div>
			<?php if ( '' !== $eyebrow ) : ?>
				<div class="eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
			<?php endif; ?>
			<?php if ( '' !== $title ) : ?>
				<h2 class="section-heading__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $description ) : ?>
			<div>
				<p class="section-heading__desc"><?php echo esc_html( $description ); ?></p>
			</div>
		<?php endif; ?>
	<?php else : ?>
		<?php if ( '' !== $eyebrow ) : ?>
			<div class="eyebrow"><?php echo esc_html( $eyebrow ); ?></div>
		<?php endif; ?>
		<?php if ( '' !== $title ) : ?>
			<h2 class="section-heading__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( '' !== $description ) : ?>
			<p class="section-heading__desc"><?php echo esc_html( $description ); ?></p>
		<?php endif; ?>
	<?php endif; ?>
</div>
