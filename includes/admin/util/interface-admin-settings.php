<?php
/**
 * This file handles admin settings interface.
 *
 * @package     WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Admin\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings interface.
 */
interface Admin_Settings_Interface {


	/**
	 * Add Menu in Backend
	 */
	public function wk_wc_pos_add_dashboard_menu();

	/**
	 * Register ElasticSearch Settings
	 */
	public function wk_wc_pos_register_settings();

}

