<?php
/**
 * This file handles all admin end actions.
 *
 * @package     WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Admin;

use WKWC_POS\Includes\Admin;
use WKWC_POS\Api\Includes\SetupWizard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Hook_Handler' ) ) {
	/**
	 * Pos hook handler class.
	 */
	class WC_Pos_Hook_Handler {

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			$function_handler        = new Admin\WC_Pos_Function_Handler();
			$setup_wizard            = new SetupWizard\WKWCPOS_Setup_Wizard_API();
			$report_function_handler = new Admin\Reports\WC_Pos_Report_Template_Function_Handler();

			add_filter( 'wk_allow_settings_update_to_demo_admin', array( $function_handler, 'wk_wc_pos_add_settings_for_demo_admin' ) );

			add_action( 'init', array( $function_handler, 'wkwcpos_start_setup_wizard' ) );

			// admin menu.
			add_action( 'admin_menu', array( $function_handler, 'wk_wc_pos_add_dashboard_menu' ), 99 );

			add_action( 'admin_init', array( $function_handler, 'wk_wc_pos_register_settings' ) );

			add_action( 'woocommerce_manage_pos_user', array( $function_handler, 'wk_wc_save_pos_user' ), 10, 1 );

			add_filter( 'woocommerce_screen_ids', array( $function_handler, 'add_pos_screen_ids' ), 10, 1 );

			add_action( 'woocommerce_manage_pos_outlet', array( $function_handler, 'wk_wc_save_pos_outlet' ), 10, 1 );

			// setup wizard hooks.
			add_action( 'wp_ajax_wk_pos_get_setup_wizard_data', array( $setup_wizard, 'wk_wc_pos_get_setup_wizard_data' ) );
			add_action( 'wp_ajax_wk_pos_save_setup_wizard_data', array( $setup_wizard, 'wk_wc_pos_save_setup_wizard_data' ) );

			// hooks for Add pos Outlet tab.
			add_action( 'wk_add_edit_pos_general', array( $function_handler, 'wk_wc_pos_outlet_general_settings' ), 1, 1 );
			add_action( 'wk_add_edit_pos_manage-products', array( $function_handler, 'wk_wc_pos_outlet_mgproducts_settings' ), 10, 1 );

			// hooks for basic settings of pos system.
			add_action( 'pos_general-settings', array( $function_handler, 'wk_wc_pos_general_settings' ), 10, 1 );
			add_action( 'pos_customer-settings', array( $function_handler, 'wk_wc_pos_customer_settings' ), 10, 1 );
			add_action( 'pos_payment-option', array( $function_handler, 'wk_wc_pos_payment_settings' ), 10, 1 );
			add_action( 'pos_printer-settings', array( $function_handler, 'wk_wc_pos_printer_settings' ), 10, 1 );
			add_action( 'pos_theme-settings', array( $function_handler, 'wk_wc_pos_appearance_settings' ), 10, 1 );
			add_action( 'pos_endpoint-settings', array( $function_handler, 'wk_wc_pos_endpoint_settings' ), 10, 1 );

			// hook for pos report tab.
			add_action( 'wk_pos_report_order', array( $report_function_handler, 'wk_wc_pos_report_order' ), 1, 1 );
			add_action( 'wk_pos_report_customer', array( $report_function_handler, 'wk_wc_pos_report_customer' ), 10, 1 );
			add_action( 'wk_pos_report_stock', array( $report_function_handler, 'wk_wc_pos_report_stock' ), 10, 1 );

			// hooks for pos report stock.
			add_action( 'wk_pos_report_stock_section_pos_1', array( $report_function_handler, 'wk_wc_pos_report_stock_low' ), 1, 1 );
			add_action( 'wk_pos_report_stock_section_pos_2', array( $report_function_handler, 'wk_wc_pos_report_stock_out' ), 10, 1 );
			add_action( 'wk_pos_report_stock_section_pos_3', array( $report_function_handler, 'wk_wc_pos_report_stock_most' ), 10, 1 );

			// hooks for pos report tax.

			$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

			if ( ! $centralized_inventory_enabled ) {

				add_action( 'save_post', array( $function_handler, 'wk_wc_pos_update_stock' ), 10, 3 );

				add_filter( 'woocommerce_can_restore_order_stock', array( $function_handler, 'wk_wc_pos_prevent_woo_update_stock' ), 10, 2 );

				add_action( 'pos_assign-mass-masterstock', array( $function_handler, 'wk_wc_pos_outlet_mass_assign_master_stock' ), 10, 1 );

				// Add a custom field to product bulk edit special page.

				add_action( 'woocommerce_product_bulk_edit_start', array( $function_handler, 'wk_wc_pos_custom_field_product_bulk_edit' ), 10, 0 );

				// Save the custom fields data when submitted for product bulk edit.

				add_action( 'woocommerce_product_bulk_edit_save', array( $function_handler, 'wk_wc_pos_save_custom_field_product_bulk_edit' ), 10, 1 );

				add_action( 'woocommerce_product_after_variable_attributes', array( $function_handler, 'wk_wc_pos_variation_settings_fields' ), 10, 3 );

				// Save variation settings.
				add_action( 'woocommerce_save_product_variation', array( $function_handler, 'wk_wc_pos_save_variation_settings_fields' ), 10, 2 );

				add_action( 'woocommerce_product_options_inventory_product_data', array( $function_handler, 'wk_wc_pos_simple_woo_custom_fields' ) );

				add_action( 'save_post', array( $function_handler, 'wk_wc_pos_manage_product_master_stock' ), 10 );
			}

			add_action( 'save_post', array( $function_handler, 'wkwcpos_enable_product_in_outlet' ), 1 );

			add_action( 'admin_bar_menu', array( $function_handler, 'wk_wc_pos_admin_bar_menus' ), 10, 1 );

			add_action( 'current_screen', array( $function_handler, 'wk_wc_pos_add_help_tab' ), 60 );

			add_filter( 'user_row_actions', array( $function_handler, 'wkwcpos_add_default_customer' ), 10, 2 );

			add_filter( 'woocommerce_order_number', array( $function_handler, 'wkwcpos_add_prefix_in_order' ), 10, 2 );
		}
	}
}
