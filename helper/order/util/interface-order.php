<?php
/**
 * This file handles core pos orders interface.
 *
 * @version 4.1.0
 *
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\Order\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface Order_Interface {

	/**
	 * Get all orders count by search.
	 *
	 * @param string $search_query Search string.
	 * @param string $pay_method Payment method.
	 */
	public function pos_get_all_order_by_search_count( $search_query = '', $pay_method = '' );

	/**
	 * Get all pos orders.
	 *
	 * @param int $perpage Orders perpage.
	 * @param int $offset Offset.
	 */
	public function pos_get_all_orders( $perpage, $offset );

}
