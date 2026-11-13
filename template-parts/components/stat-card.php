<?php
/**
 * Reusable Verified Business Strength / Fact Card Component.
 *
 * Args:
 * - label (string)
 * - value (string)
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$label = isset( $args['label'] ) ? (string) $args['label'] : '';
$value = isset( $args['value'] ) ? (string) $args['value'] : '';

if ( '' === $value ) {
	return;
}
?>
<div class="about-fact">
	<?php if ( '' !== $label ) : ?>
		<span class="about-fact__label"><?php echo esc_html( $label ); ?></span>
	<?php endif; ?>
	<div class="about-fact__value"><?php echo esc_html( $value ); ?></div>
</div>
