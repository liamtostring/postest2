<?php
/**
 * This file handles all file includes.
 *
 * @version 4.1.0
 *
 * @package WooCommerce Point of Sale
 */

use WKWC_POS\Includes\Front;
use WKWC_POS\Includes\Admin;
use WKWC_POS\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WK_WC_POS_PLUGIN_FILE . 'includes/wc-pos-defaults.php';

require_once WK_WC_POS_PLUGIN_FILE . 'inc/autoload.php';

$filter_callback = new Includes\Filters\WC_Pos_Filter_Callbacks();

$filter = new Includes\Filters\WC_Pos_Filter_Hooks( $filter_callback );

$script_loader = new Front\WC_Pos_Script_Loader();

$script_loader->wk_wc_pos_Init();

if ( ! is_admin() ) {

	new Front\WC_Pos_Front_Hook_Handler();

} else {

	new Admin\WC_Pos_Hook_Handler();

}
new Admin\WC_Pos_Admin_Rest_Api();
new Front\WC_Pos_Front_Ajax_Hooks();
