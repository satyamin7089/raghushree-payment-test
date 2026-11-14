<?php
/** Editable product sections with non-destructive legacy-field fallback. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function raghushree_product_section_definitions() {
	return array(
		'features' => array( 'title' => __( 'Features', 'raghushree' ), 'fields' => array( __( 'Feature', 'raghushree' ), __( 'Details (optional)', 'raghushree' ) ), 'required' => 1 ),
		'applications' => array( 'title' => __( 'Applications', 'raghushree' ), 'fields' => array( __( 'Application', 'raghushree' ), __( 'Description (optional)', 'raghushree' ) ), 'required' => 1 ),
		'specifications' => array( 'title' => __( 'Technical details', 'raghushree' ), 'fields' => array( __( 'Property', 'raghushree' ), __( 'Value', 'raghushree' ), __( 'Notes (optional)', 'raghushree' ) ), 'required' => 2 ),
		'faqs' => array( 'title' => __( 'Frequently asked questions', 'raghushree' ), 'fields' => array( __( 'Question', 'raghushree' ), __( 'Answer', 'raghushree' ) ), 'required' => 2 ),
	);
}
function raghushree_product_sections( $post_id ) {
	$saved = get_post_meta( $post_id, '_raghushree_product_sections', true );
	if ( is_array( $saved ) ) { return array_merge( array_fill_keys( array_keys( raghushree_product_section_definitions() ), array() ), $saved ); }
	return array(
		'features' => array(),
		'applications' => raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_applications_rows', true ), 2 ),
		'specifications' => raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_tech_specs', true ), 2 ),
		'faqs' => raghushree_parse_pipe_rows( (string) get_post_meta( $post_id, '_raghushree_product_faqs', true ), 2 ),
	);
}
/** Optional plain-text introductions, independent of the row format. */
function raghushree_product_section_intros( $post_id ) {
	$saved = get_post_meta( $post_id, '_raghushree_section_intros', true );
	return array_merge( array_fill_keys( array_keys( raghushree_product_section_definitions() ), '' ), is_array( $saved ) ? $saved : array() );
}
/** Render editable, numbered rows; blank rows are ignored on save. */
function raghushree_render_product_sections( $post_id ) {
	$sections = raghushree_product_sections( $post_id );
	$intros = raghushree_product_section_intros( $post_id );
	$draft = get_transient( 'raghushree_sections_draft_' . get_current_user_id() . '_' . $post_id );
	if ( is_array( $draft ) ) { $sections = $draft; if ( isset( $draft['_intros'] ) && is_array( $draft['_intros'] ) ) { $intros = $draft['_intros']; } }
	?><div class="rg-section-editor"><h3><?php esc_html_e( 'Product page sections', 'raghushree' ); ?></h3><p class="description"><?php esc_html_e( 'Optional: add the content you want shown. Leave unused rows blank. Use Add or Remove to change the number of rows.', 'raghushree' ); ?></p>
	<input type="hidden" name="rg_sections_present" value="1"><input type="hidden" name="rg_section_intros_present" value="1">
	<?php foreach ( raghushree_product_section_definitions() as $key => $definition ) : $rows = isset( $sections[$key] ) && is_array( $sections[$key] ) ? $sections[$key] : array(); if ( ! $rows ) { $rows = array( array() ); } ?>
	<fieldset class="rg-section-fieldset" data-section="<?php echo esc_attr( $key ); ?>"><legend><strong><?php echo esc_html( $definition['title'] ); ?></strong></legend><label class="rg-section-intro"><?php esc_html_e( 'Introduction beneath heading (optional)', 'raghushree' ); ?><textarea class="widefat" name="rg_section_intros[<?php echo esc_attr( $key ); ?>]" rows="3" maxlength="1200"><?php echo esc_textarea( $intros[$key] ?? '' ); ?></textarea></label><div data-section-rows>
	<?php foreach ( array_values( $rows ) as $index => $row ) : ?><div class="rg-section-row" data-section-row>
	<?php foreach ( $definition['fields'] as $column => $label ) : ?><label><?php echo esc_html( $label ); ?><input class="widefat" type="text" maxlength="2000" name="rg_sections[<?php echo esc_attr( $key ); ?>][<?php echo esc_attr( $index ); ?>][<?php echo esc_attr( $column ); ?>]" value="<?php echo esc_attr( $row[$column] ?? '' ); ?>"></label><?php endforeach; ?>
	<button class="button" type="button" data-section-remove><?php esc_html_e( 'Remove', 'raghushree' ); ?></button></div><?php endforeach; ?>
	</div><button class="button" type="button" data-section-add data-next-index="<?php echo esc_attr( count( $rows ) ); ?>"><?php esc_html_e( 'Add another', 'raghushree' ); ?></button></fieldset><?php endforeach; ?>
	<input type="hidden" name="rg_sections_complete" value="1"></div><?php
}
/** Validate all section rows before storing any, preventing truncated forms from deleting data. */
function raghushree_save_product_sections( $post_id ) {
	if ( ! isset( $_POST['rg_sections_present'] ) ) { return; }
	if ( ! isset( $_POST['rg_sections_complete'] ) || ( isset( $_POST['rg_sections'] ) && ! is_array( $_POST['rg_sections'] ) ) ) { raghushree_product_sections_invalid( $post_id ); return; }
	$submitted = isset( $_POST['rg_sections'] ) ? wp_unslash( $_POST['rg_sections'] ) : array(); $result = array();
	$has_intros = isset( $_POST['rg_section_intros_present'] );
	$submitted_intros = $has_intros && isset( $_POST['rg_section_intros'] ) && is_array( $_POST['rg_section_intros'] ) ? wp_unslash( $_POST['rg_section_intros'] ) : null;
	$intros = array();
	if ( $has_intros ) {
		if ( ! is_array( $submitted_intros ) ) { raghushree_product_sections_invalid( $post_id, $submitted, $submitted_intros ); return; }
		foreach ( raghushree_product_section_definitions() as $key => $definition ) {
			$value = $submitted_intros[$key] ?? null;
			if ( ! is_string( $value ) || strlen( $value ) > 4800 ) { raghushree_product_sections_invalid( $post_id, $submitted, $submitted_intros ); return; }
			$intros[$key] = trim( sanitize_textarea_field( $value ) );
		}
	}
	foreach ( raghushree_product_section_definitions() as $key => $definition ) {
		$rows = $submitted[$key] ?? array();
		if ( ! is_array( $rows ) || count( $rows ) > 50 ) { raghushree_product_sections_invalid( $post_id, $submitted, $submitted_intros ); return; }
		$result[$key] = array();
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) { raghushree_product_sections_invalid( $post_id, $submitted, $submitted_intros ); return; }
			$clean = array(); $filled = false;
			foreach ( $definition['fields'] as $i => $label ) {
				$value = $row[$i] ?? '';
				if ( ! is_scalar( $value ) || strlen( (string) $value ) > 2000 ) { raghushree_product_sections_invalid( $post_id, $submitted, $submitted_intros ); return; }
				$value = trim( sanitize_text_field( (string) $value ) ); $clean[] = $value; if ( '' !== $value ) { $filled = true; }
			}
			if ( ! $filled ) { continue; }
			for ( $i = 0; $i < $definition['required']; $i++ ) { if ( '' === $clean[$i] ) { raghushree_product_sections_invalid( $post_id, $submitted, $submitted_intros ); return; } }
			$result[$key][] = $clean;
		}
	}
	update_post_meta( $post_id, '_raghushree_product_sections', $result );
	if ( $has_intros ) { update_post_meta( $post_id, '_raghushree_section_intros', $intros ); }
	delete_transient( 'raghushree_sections_draft_' . get_current_user_id() . '_' . $post_id );
}
function raghushree_product_sections_invalid( $post_id, $submitted = null, $submitted_intros = null ) {
	// Retain a safe, bounded draft for the same administrator to correct. Published content is unchanged.
	if ( is_array( $submitted ) ) {
		$draft = array();
		foreach ( raghushree_product_section_definitions() as $key => $definition ) {
			$draft[$key] = array(); $rows = isset( $submitted[$key] ) && is_array( $submitted[$key] ) ? array_slice( $submitted[$key], 0, 50 ) : array();
			foreach ( $rows as $row ) { if ( ! is_array( $row ) ) { continue; } $clean = array(); foreach ( $definition['fields'] as $i => $label ) { $value = $row[$i] ?? ''; $clean[] = is_scalar( $value ) ? sanitize_text_field( substr( (string) $value, 0, 2000 ) ) : ''; } $draft[$key][] = $clean; }
		}
		if ( is_array( $submitted_intros ) ) {
			$draft['_intros'] = array();
			foreach ( raghushree_product_section_definitions() as $key => $definition ) {
				$value = $submitted_intros[$key] ?? '';
				$draft['_intros'][$key] = is_scalar( $value ) ? sanitize_textarea_field( substr( (string) $value, 0, 4800 ) ) : '';
			}
		}
		set_transient( 'raghushree_sections_draft_' . get_current_user_id() . '_' . $post_id, $draft, 600 );
	}
	set_transient( 'raghushree_sections_invalid_' . get_current_user_id(), 1, 60 );
}
function raghushree_product_sections_notice() {
	$key = 'raghushree_sections_invalid_' . get_current_user_id();
	if ( get_transient( $key ) ) { delete_transient( $key ); ?><div class="notice notice-error"><p><?php esc_html_e( 'Product page sections were not saved. Check that introductions are within the length limit, each technical row has a property and value, and each FAQ has a question and answer. Previous published section content was retained; when possible, your attempted section edits are available to correct for 10 minutes. The rest of the product may have saved.', 'raghushree' ); ?></p></div><?php }
}
add_action( 'admin_notices', 'raghushree_product_sections_notice' );
