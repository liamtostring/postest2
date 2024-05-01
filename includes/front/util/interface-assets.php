<?php
/**
 * This file handles assets interface.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Front\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets interface.
 */
interface Assets_Interface {

	public function wk_wc_pos_Init();

	public function wk_wc_pos_EnqueueScripts_Admin();

	public function wk_wc_pos_EnqueueScripts_Front();

}

