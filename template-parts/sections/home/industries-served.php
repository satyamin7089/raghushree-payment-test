<?php
/**
 * Homepage Section 8: Industries and Customers Served.
 *
 * @package Raghushree
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sectors = array(
	array(
		'title' => __( 'Plastic Product Manufacturers', 'raghushree' ),
		'desc'  => __( 'OEM and branded manufacturers producing rigid, flexible, household, and industrial plastic goods.', 'raghushree' ),
	),
	array(
		'title' => __( 'Contract Moulding Companies', 'raghushree' ),
		'desc'  => __( 'Custom injection, blow, and rotational moulding units running multi-polymer customer tooling.', 'raghushree' ),
	),
	array(
		'title' => __( 'Packaging Manufacturers', 'raghushree' ),
		'desc'  => __( 'Blown/cast film extruders, stretch wrap makers, woven sack units, and bottle/container converters.', 'raghushree' ),
	),
	array(
		'title' => __( 'Pipes, Electrical & Industrial Units', 'raghushree' ),
		'desc'  => __( 'Manufacturers of water/agri pipes, cable conduits, electrical housings, and technical components.', 'raghushree' ),
	),
);

$buyer_roles = array(
	__( 'Procurement & Purchasing Teams', 'raghushree' ),
	__( 'Production Managers', 'raghushree' ),
	__( 'Technical & R&D Teams', 'raghushree' ),
	__( 'Quality Control Teams', 'raghushree' ),
	__( 'Plastic Distributors & Dealers', 'raghushree' ),
	__( 'Business Owners & Directors', 'raghushree' ),
);
?>
<section class="section surface--tint" id="industries-served">
	<div class="container">
		<?php
		get_template_part(
			'template-parts/components/section-heading',
			null,
			array(
				'eyebrow'     => __( 'Industries & Customers Served', 'raghushree' ),
				'title'       => __( 'Supplying Diverse Manufacturing Sectors & Procurement Teams', 'raghushree' ),
				'description' => __( 'We work directly with industrial manufacturers, converters, and distribution partners across domestic Indian industrial clusters and international markets.', 'raghushree' ),
			)
		);
		?>

		<div class="grid-4">
			<?php foreach ( $sectors as $sec ) : ?>
				<?php
				get_template_part(
					'template-parts/components/feature-card',
					null,
					array(
						'title'       => $sec['title'],
						'description' => $sec['desc'],
					)
				);
				?>
			<?php endforeach; ?>
		</div>

		<div class="buyer-roles">
			<h3><?php esc_html_e( 'Decision-Makers & Teams We Support', 'raghushree' ); ?></h3>
			<p><?php esc_html_e( 'Whether you are evaluating raw material grades for a new mould, managing plant-wide resin purchasing, or sourcing recurring distributor lots, our team structures clear commercial and technical responses.', 'raghushree' ); ?></p>
			<div class="buyer-roles__list">
				<?php foreach ( $buyer_roles as $role ) : ?>
					<span class="tag tag--accent"><?php echo esc_html( $role ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
