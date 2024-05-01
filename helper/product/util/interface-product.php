<?php
/**
 * This file handles core pos Products interface.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\Product\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Product_Interface {

	/**
	 * Get all products count.
	 */
	public function pos_get_all_product_by_count();

	/**
	 * Get all pos products.
	 *
	 * @param string $search_query Search string for query.
	 * @param int    $off Offset for limit.
	 * @param int    $perpage Products perpage.
	 * @param string $filtered_outlet Filtered outlet.
	 */
	public function get_all_pos_products( $search_query = '', $off, $perpage, $filtered_outlet = '' );

}
