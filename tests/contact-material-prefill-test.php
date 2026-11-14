<?php
define( 'ABSPATH', __DIR__ );
require __DIR__ . '/../inc/helpers.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } }
$options = array( 'Product A', 'Custom' );
check( raghushree_contact_material_options( $options, 'Product B' ) === array( 'Product A', 'Custom', 'Product B' ), 'preserves a product outside the short list' );
check( raghushree_contact_material_options( $options, 'Product A' ) === $options, 'no duplicate options' );
check( raghushree_contact_material_options( $options, str_repeat( 'x', 801 ) ) === $options, 'prefill is bounded' );
echo "Contact material prefill smoke tests passed.\n";
