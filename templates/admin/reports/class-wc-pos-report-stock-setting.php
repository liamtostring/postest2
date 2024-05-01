<?php
/**
 * POS stock setting report admin class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version 2.1.0
 */

namespace WKWC_POS\Templates\Admin\Reports;

use WKWC_POS\Helper\Outlet\WC_Pos_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Pos_Report_Stock_Setting' ) ) {

	/**
	 * POS stock setting report admin class.
	 */
	class WC_Pos_Report_Stock_Setting {

		protected $wpdb;
		protected $outlet_table;
		protected $outlet_mapped_table;
		protected $outlet_helper;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			global $wpdb;

			$this->wpdb = $wpdb;

			$this->outlet_map_table = $this->wpdb->prefix . 'woocommerce_pos_outlet_map';

			$this->outlet_table = $this->wpdb->prefix . 'woocommerce_pos_outlets';

			$this->outlet_helper = new WC_Pos_Outlet_Helper();

			$this->wk_wc_pos_get_report_stock_setting_template();

		}

		public function wk_wc_pos_get_report_stock_setting_template() {

			settings_errors();

			echo '<ul class="subsubsub" style="float:none">';

			$sections = array(
				'1' => 'Low in Stock',
				'2' => 'Out of Stock',
				'3' => 'Most Stocked',
			);

			$current_section = empty( $_GET['section'] ) ? '1' : intval( $_GET['section'] );

			$outlet_id = empty( $_GET['outlet_id'] ) ? '' : ( $_GET['outlet_id'] );

			$array_keys = array_keys( $sections );

			$outlets = $this->outlet_helper->pos_get_all_outlets();

			$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

			$default_outlet_id = 0;
			foreach ( $outlets as $key => $outlet ) {

				if ( 0 === $key ) {
					$default_outlet_id = $outlet->id;
				}
			}

			if ( isset( $_GET['outlet_id'] ) && ! empty( $_GET['outlet_id'] ) ) {

				$selected_outlet_id = $_GET['outlet_id'];

				foreach ( $sections as $id => $label ) {
					echo '<li><a href="' . admin_url( 'admin.php?page=wc-pos-reports&outlet_id=' . $selected_outlet_id . '&tab=stock&section=' . $id ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
				}
			} else {
				foreach ( $sections as $id => $label ) {
					echo '<li><a href="' . admin_url( 'admin.php?page=wc-pos-reports&outlet_id=' . $default_outlet_id . '&tab=stock&section=' . $id ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
				}
			}

			echo '</ul>';
			if ( ! $centralized_inventory_enabled ) {

				echo '<br />';
				echo "<form action='' method = 'GET' >";
				echo "<input type='hidden' value='wc-pos-reports' name='page'>";
				echo "<input type='hidden' value='stock' name='tab'>";
				echo "<input type='hidden' value='" . esc_attr( $current_section ) . "' name='section'>";

				echo '<div><strong>' . esc_html__( 'Select Outlet', 'wc_pos' ) . '</strong></div>';
				echo '<select name="outlet_id">';

				foreach ( $outlets as $key => $outlet ) {

					$current_outlet_id = $outlet->id;
					$outlet_name       = $outlet->outlet_name;

					echo '<option value="' . $current_outlet_id . '" ' . selected( $outlet_id, $current_outlet_id, true ) . '>' . $outlet_name . '</option>';

				}
				echo '</select>';
				echo '<span> </span>';
				echo '<input type="submit" class ="button" value = "' . __( 'Apply', 'wc_pos' ) . '"> ';
				echo '</form>';
			}
			echo '<br />';

			do_action( 'wk_pos_report_stock_section_pos_' . $current_section );

		}

	}

}
