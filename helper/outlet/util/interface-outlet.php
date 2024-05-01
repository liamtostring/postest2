<?php
/**
 * This file handles core config interface.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\Outlet\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads core config data interface.
 */
interface Outlet_Interface {

	/**
	 * Update outlet status.
	 *
	 * @param string $action Action type ( activate, deactivate, delete ).
	 * @param int    $outlet_id Outlet id.
	 */
	public function update_outlet_status( $action, $outlet_id );

	/**
	 * Delete vendor outlet
	 *
	 * @param int $outlet_id Outlet id.
	 */
	public function delete_vendor_outlet( $outlet_id );

	/**
	 * Get outlet count.
	 *
	 * @param string $text Search string for query.
	 */
	public function pos_get_all_outlet_by_search_count( $text = '' );

	/**
	 * Get all outlet lists.
	 *
	 * @param string $text Search string for query.
	 * @param int    $off Offset for limit query.
	 * @param int    $perpage Outlet list perpage.
	 */
	public function pos_get_all_outlet_by_search( $text, $off, $perpage );

	/**
	 * Get all outlet lists.
	 */
	public function pos_get_all_outlets();

	/**
	 * Get pos outlet data by outlet id.
	 */
	public function _get_pos_outlet();

	/**
	 * Get pos outlet id by posuser id.
	 */
	public function _get_pos_user_outlet();

}
