<?php
/**
 * POS report tab manager template class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Admin\Reports;

use WKWC_POS\Helper\Outlet\WC_Pos_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Pos_Report_Tab_Manager_Template' ) ) {

	/**
	 * POS report tab manager template class.
	 */
	class WC_Pos_Report_Tab_Manager_Template {

		/**
		 * Outlet helper class.
		 *
		 * @var object $outlet_helper Oulet helper class object.
		 */
		public $outlet_helper;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$this->outlet_helper = new WC_Pos_Outlet_Helper();

			$this->wk_wc_pos_get_report_tab_manager_template();

		}

		/**
		 * Report tab manager template function.
		 */
		public function wk_wc_pos_get_report_tab_manager_template() {

			if ( isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ) { //phpcs:ignore

				$page = $_GET['page']; //phpcs:ignore

				if ( 'wc-pos-reports' === $page ) :

					$wksa_tabs = array(
						'order'    => esc_html__( 'Order', 'wc_pos' ),
						'customer' => esc_html__( 'Customer', 'wc_pos' ),
						'stock'    => esc_html__( 'Stock', 'wc_pos' ),
					);

					$wksa_tabs = apply_filters( 'wkwcpos_modify_report_tabs', $wksa_tabs );

					$current_tab = empty( $_GET['tab'] ) ? 'order' : sanitize_title( $_GET['tab'] ); //phpcs:ignore

					echo '<div class="wrap outlet">';
					echo '<h1>';

					$outlet_id = isset( $_GET['outlet_id'] ) && ! empty((int)$_GET['outlet_id'] ) ? (int)( $_GET['outlet_id'] ) : 0; //phpcs:ignore

					echo esc_html__( 'POS Reports', 'wc_pos' );
					echo '</h1>';
					echo '<nav class="nav-tab-wrapper">';

					foreach ( $wksa_tabs as $name => $label ) {

						$query_args = array(
							'page' => 'wc-pos-reports',
							'tab'  => $name,
						);

						if ( 'stock' === $name ) {

							if ( empty( $outlet_id ) && 0 >= (int) $outlet_id ) {

								$outlets = $this->outlet_helper->pos_get_all_outlets();
								foreach ( $outlets as $key => $outlet ) {
									$outlet_id = (int) $outlet->id;
								}
							}

							$query_args['outlet_id'] = $outlet_id;
						}

						$url = add_query_arg( $query_args, admin_url( 'admin.php' ) );

						$active_class = $current_tab === $name ? 'nav-tab-active' : '';

						echo wp_sprintf( '<a href="%s" class="nav-tab %s">%s</a>', esc_url( $url ), esc_html( $active_class ), esc_html( $label ) );
					}

					echo '</nav>';

					do_action( 'wk_pos_report_' . $current_tab );

					echo '</div>';

				endif;
			}

		}

	}

}
