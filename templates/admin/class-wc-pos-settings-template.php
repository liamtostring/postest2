<?php

/**
 * @author Webkul
 * @version 2.2.0
 * This file handles server settings template at admin end.
 */

namespace WKWC_POS\Templates\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Settings_Template' ) ) {

	/**
	 * Class for Admin setting template.
	 */
	class WC_Pos_Settings_Template {


		public function __construct() {

			$this->wk_wc_pos_settings_template();

		}

		/**
		 * Setting template.
		 */
		public function wk_wc_pos_settings_template() {

			if ( isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ) {

				$page = $_GET['page'];

				if ( $page == 'wc-pos-settings' ) {

					$wksa_tabs = array(

						'general-settings'  => '<span class="dashicons dashicons-admin-generic"></span>' . esc_html__( 'General', 'wc_pos' ),
						'customer-settings' => '<span class="dashicons dashicons-admin-users"></span>' . esc_html__( 'Customer', 'wc_pos' ),
						'payment-option'    => '<span class="dashicons dashicons-money-alt"></span>' . esc_html__( 'Payment', 'wc_pos' ),
						'printer-settings'  => '<span class="dashicons dashicons-printer"></span>' . esc_html__( 'Printer', 'wc_pos' ),
						'theme-settings'    => '<span class="dashicons dashicons-admin-appearance"></span>' . esc_html__( 'Appearance', 'wc_pos' ),
						'endpoint-settings' => '<span class="dashicons dashicons-admin-links"></span>' . esc_html__( 'Endpoints', 'wc_pos' ),

					);

					$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

					if ( ! $centralized_inventory_enabled ) {
						$wksa_tabs['assign-mass-masterstock'] = '<span class="dashicons dashicons-database-add"></span>' . esc_html__( 'POS Mass Assign', 'wc_pos' );
					}

					$wksa_tabs = apply_filters( 'wkwcpos_modify_settings_tabs', $wksa_tabs );

					$current_tab = empty( $_GET['tab'] ) ? 'general-settings' : sanitize_title( $_GET['tab'] );

					$pid = empty( $_GET['pid'] ) ? '' : '&pid=' . $_GET['pid'];

					echo '<div class="wrap">';

					echo '<nav class="nav-tab-wrapper">';

					foreach ( $wksa_tabs as $name => $label ) {

						echo '<a href="' . admin_url( 'admin.php?page=wc-pos-settings' . $pid . '&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';

					}

					echo '</nav>';

					do_action( 'pos_' . $current_tab, $this );

					echo '</div>';

				}
			}

		}

	}

}
