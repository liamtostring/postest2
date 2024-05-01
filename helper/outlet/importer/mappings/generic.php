<?php
/**
 * Generic mappings
 *
 * @package WooCommerce\Admin\Importers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add generic mappings.
 *
 * @since 3.1.0
 * @param array $mappings Importer columns mappings.
 * @return array
 */
function wkwcpos_importer_generic_mappings( $mappings ) {
	$generic_mappings = array(
		__( 'Title', 'wc_pos' )         => 'name',
		__( 'Product Title', 'wc_pos' ) => 'name',
		__( 'Price', 'wc_pos' )         => 'regular_price',
		__( 'Parent SKU', 'wc_pos' )    => 'parent_id',
		__( 'Quantity', 'wc_pos' )      => 'stock_quantity',
		__( 'Menu order', 'wc_pos' )    => 'menu_order',
	);

	return array_merge( $mappings, $generic_mappings );
}
add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'wkwcpos_importer_generic_mappings' );
