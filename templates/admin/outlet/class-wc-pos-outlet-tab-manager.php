<?php
/**
 * POS outlet tab manager class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Admin\Outlet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Outlet_Tab_Manager' ) ) {

	/**
	 * POS outlet tab manager class.
	 */
	class WC_Pos_Outlet_Tab_Manager {

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$this->wk_wc_pos_get_outlet_tab_manager_template();

		}

		/**
		 * Get outlet tab manager.
		 */
		public function wk_wc_pos_get_outlet_tab_manager_template() {

			$tabber = '';

			if ( isset( $_GET['outlet_id'] ) && ! empty( $_GET['outlet_id'] ) && isset( $_GET['outlet_action'] ) && 'edit' === $_GET['outlet_action'] ) { // phpcs:ignore

				$wksa_tabs = array(

					'general'         => __( 'General', 'wc_pos' ),
					'manage-products' => __( 'Manage Products', 'wc_pos' ),

				);

				$wksa_tabs = apply_filters( 'wkwcpos_modify_edit_outlet_tabs', $wksa_tabs );

				$tabber = '&outlet_action=edit&outlet_id=' . $_GET['outlet_id']; // phpcs:ignore

			} else {

				$wksa_tabs = array(

					'general' => __( 'General', 'wc_pos' ),

				);

			}

			$wksa_tabs = apply_filters( 'wkwcpos_modify_outlet_tabs', $wksa_tabs );

			$current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] ); // phpcs:ignore

			echo '<div class="wrap outlet">';

			echo '<nav class="nav-tab-wrapper">';

			foreach ( $wksa_tabs as $name => $label ) {

				echo '<a href="' . admin_url( 'admin.php?page=pos-outlets&tab=' . $name . $tabber ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>'; // phpcs:ignore

			}

			echo '</nav>';

			do_action( 'wk_add_edit_pos_' . $current_tab );

			echo '</div>';

		}

	}

}
