<?php
/**
 * Accessible FAQ Accordion Component + Automatic Visible FAQ Schema Sync.
 *
 * Args:
 * - faqs: array of [ 'question' => string, 'answer' => string ]
 * - id_prefix: unique string prefix for ARIA panel IDs
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$faqs      = ( isset( $args['faqs'] ) && is_array( $args['faqs'] ) ) ? $args['faqs'] : array();
$id_prefix = ! empty( $args['id_prefix'] ) ? sanitize_html_class( $args['id_prefix'] ) : 'faq';

if ( empty( $faqs ) ) {
	return;
}

// Register these exact visibly rendered FAQs for FAQPage JSON-LD structured data
raghushree_register_visible_faqs( $faqs );
?>
<div class="faq-list">
	<?php foreach ( $faqs as $idx => $faq ) : ?>
		<?php
		if ( empty( $faq['question'] ) || empty( $faq['answer'] ) ) {
			continue;
		}
		$is_first = ( 0 === $idx );
		$panel_id = $id_prefix . '-panel-' . ( $idx + 1 );
		$btn_id   = $id_prefix . '-btn-' . ( $idx + 1 );
		?>
		<div class="faq-item<?php echo $is_first ? ' is-open' : ''; ?>">
			<h3 class="faq-item__heading">
				<button
					type="button"
					class="faq-trigger"
					id="<?php echo esc_attr( $btn_id ); ?>"
					aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
					aria-controls="<?php echo esc_attr( $panel_id ); ?>"
				>
					<span><?php echo esc_html( $faq['question'] ); ?></span>
					<span class="faq-trigger__icon" aria-hidden="true">
						<?php echo raghushree_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>
				</button>
			</h3>
			<div
				class="faq-panel"
				id="<?php echo esc_attr( $panel_id ); ?>"
				role="region"
				aria-labelledby="<?php echo esc_attr( $btn_id ); ?>"
				<?php echo $is_first ? '' : 'hidden'; ?>
			>
				<p><?php echo esc_html( $faq['answer'] ); ?></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>
