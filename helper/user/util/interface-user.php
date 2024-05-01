<?php
/**
 * This file handles core config interface.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\User\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Loads core config data.
 */
interface User_Interface {

	/**
	 * Get all users count.
	 *
	 * @param string $s Search string for query.
	 */
	public function pos_get_all_users_count( $s = '' );

	/**
	 * Get all pos users.
	 *
	 * @param string $s Search string for query.
	 * @param int    $perpage Pos users list perpage.
	 * @param int    $offset Offset for limit.
	 */
	public function pos_get_all_users( $s = '', $perpage, $offset );

	/**
	 * Get pos user outlet by pos user id.
	 *
	 * @param int $puser_id Pos user id.
	 */
	public function _get_pos_user_outlet( $puser_id );

	/**
	 * Get outlet id.
	 */
	public function _get_pos_user_outlet_with_status();

}
