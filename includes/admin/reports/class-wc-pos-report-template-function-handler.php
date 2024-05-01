<?php
/**
 * POS report template function handler class.
 *
 * @package WooCommerce Point of Sale
 * @version 1.0.0
 */


namespace WKWC_POS\Includes\Admin\Reports;

use WKWC_POS\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Report_Template_Function_Handler' ) ) {

	/**
	 * POS report template function handler class.
	 */
	class WC_Pos_Report_Template_Function_Handler {

		/**
		 * Pos order report template handler function.
		 */
		public function wk_wc_pos_report_order() {
			echo '<div class="wkwcpos-report" style="margin-top:20px" id="wkwcpos-report"></div>';

		}

		/**
		 * Pos customer report template handler function.
		 */
		public function wk_wc_pos_report_customer() {

			$report_customer_list = new Admin\Reports\WC_Pos_Report_Customer_List();

			$report_customer_list->output_report();

		}

		/**
		 * Pos stock report template handler function.
		 */
		public function wk_wc_pos_report_stock() {

			new Admin\Reports\WC_Pos_Report_Stock_Setting();

		}

		/**
		 * Pos low stock report template handler function.
		 */
		public function wk_wc_pos_report_stock_low() {

			$report_low_in_stock = new Admin\Reports\WC_Pos_Report_Low_In_Stock();

			$report_low_in_stock->output_report();

		}

		/**
		 * Pos out of stock report template handler function.
		 */
		public function wk_wc_pos_report_stock_out() {

			$report_out_of_stock = new Admin\Reports\WC_Pos_Report_Out_Of_Stock();

			$report_out_of_stock->output_report();

		}

		/**
		 * Pos most stock report template handler function.
		 */
		public function wk_wc_pos_report_stock_most() {

			$report_most_stocked = new Admin\Reports\WC_Pos_Report_Most_Stocked();

			$report_most_stocked->output_report();

		}
	}

}

