<?php
/**
 * This file handles all front end ajax actions.
 *
 * @version 4.1.0 *
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Includes\Front;

use WKWC_POS\Includes\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Front_Ajax_Hooks' ) ) {

	/**
	 * Front Ajax Hooks.
	 */
	class WC_Pos_Front_Ajax_Hooks {

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$front_ajax_product_functions = new Front\Ajax_Products\WC_Pos_Front_Ajax_Product_Functions();

			$front_ajax_outlet_functions = new Front\Ajax_Outlets\WC_Pos_Front_Ajax_Outlet_Functions();

			$front_ajax_invoice_functions = new Front\Ajax_Invoice\WC_Pos_Front_Ajax_Invoice_Functions();

			add_action( 'wp_ajax_nopriv_get_all_products', array( $front_ajax_product_functions, 'get_all_products' ) );
			add_action( 'wp_ajax_get_all_products', array( $front_ajax_product_functions, 'get_all_products' ) );

			add_action( 'wp_ajax_nopriv_update_pos_outlet_stock', array( $front_ajax_outlet_functions, 'update_pos_outlet_stock' ) );
			add_action( 'wp_ajax_update_pos_outlet_stock', array( $front_ajax_outlet_functions, 'update_pos_outlet_stock' ) );
			add_action( 'wp_ajax_sync_all_variable_product', array( $front_ajax_outlet_functions, 'wkwcpos_sync_all_variable_product' ) );

			add_action( 'wp_ajax_nopriv_wkwcpos_do_ajax_outlet_import', array( $front_ajax_outlet_functions, 'wkcwpos_do_ajax_outlet_import' ) );

			add_action( 'wp_ajax_wkwcpos_do_ajax_outlet_import', array( $front_ajax_outlet_functions, 'wkcwpos_do_ajax_outlet_import' ) );

			add_action( 'wp_ajax_wkwcpos_save_invoice', array( $front_ajax_invoice_functions, 'wkwcpos_save_invoice' ) );

			$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

			if ( ! $centralized_inventory_enabled ) {

				add_action( 'wp_ajax_nopriv_assign_pos_master_stock', array( $front_ajax_outlet_functions, 'assign_pos_master_stock' ) );
				add_action( 'wp_ajax_assign_pos_master_stock', array( $front_ajax_outlet_functions, 'assign_pos_master_stock' ) );
			}

		}

	}

}
