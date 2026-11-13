<?php
/**
 * Reusable Button Group Component.
 *
 * Args:
 * - buttons: array of [ 'label' => string, 'url' => string, 'variant' => 'primary'|'secondary'|'dark'|'on-dark', 'icon' => string, 'external' => bool ]
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$buttons = ( isset( $args['buttons'] ) && is_array( $args['buttons'] ) ) ? $args['buttons'] : array();
if ( empty( $buttons ) ) {
	return;
}
?>
<div class="btn-group">
	<?php foreach ( $buttons as $btn ) : ?>
		<?php
		if ( empty( $btn['label'] ) || empty( $btn['url'] ) ) {
			continue;
		}
		$variant  = ! empty( $btn['variant'] ) ? $btn['variant'] : 'primary';
		$icon     = ! empty( $btn['icon'] ) ? $btn['icon'] : '';
		$external = ! empty( $btn['external'] );
		?>
		<a
			class="btn btn--<?php echo esc_attr( $variant ); ?>"
			href="<?php echo esc_url( $btn['url'] ); ?>"
			<?php echo $external ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
		>
			<?php if ( 'whatsapp' === $icon ) : ?>
				<?php echo raghushree_icon( 'whatsapp' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
			<span><?php echo esc_html( $btn['label'] ); ?></span>
			<?php if ( '' !== $icon && 'whatsapp' !== $icon ) : ?>
				<?php echo raghushree_icon( $icon, 'arrow-right' === $icon ? 'icon-arrow' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
