<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their pos.
 *
 * @package WooCommerce Point of Sale/Admin
 * @version 1.0.0
 */

namespace WKWC_POS\Includes\Admin\Menu;

use WKWC_POS\Templates\Admin;
use WKWC_POS\Helper\Outlet\Importer\WKWCPOS_Outlet_Importer_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Admin_Menu_Template' ) ) {

	/**
	 * Admin menu template class.
	 */
	class WC_Pos_Admin_Menu_Template {

		/**
		 * Add Menu in Backend.
		 */
		public function __construct() {

			$capability = apply_filters( 'wkwc_pos_modify_pos_menu_capabilities', 'manage_woocommerce' );

			$hook = add_menu_page( __( 'Point of Sale', 'wc_pos' ), __( 'Point of Sale', 'wc_pos' ), $capability, 'pos-system', array( $this, 'wk_wc_pos_list_users' ), 'dashicons-calculator', 55 );

			add_submenu_page( 'pos-system', __( 'Users', 'wc_pos' ), __( 'Users', 'wc_pos' ), $capability, 'pos-system', array( $this, 'wk_wc_pos_list_users' ) );

			$outlets = add_submenu_page( 'pos-system', __( 'Outlets', 'wc_pos' ), __( 'Outlets', 'wc_pos' ), apply_filters( 'change_user_access_for_outlet_menu', $capability ), 'pos-outlets', array( $this, 'wk_wc_pos_outlets' ) );

			$products = add_submenu_page( 'pos-system', __( 'Products', 'wc_pos' ), __( 'Products', 'wc_pos' ), $capability, 'pos-products', array( $this, 'wk_wc_pos_products' ) );

			$orders = add_submenu_page( 'pos-system', __( 'Orders ', 'wc_pos' ), __( 'Orders', 'wc_pos' ), $capability, 'pos-orders', array( $this, 'wk_wc_pos_orders' ) );

			add_submenu_page( 'pos-system', __( 'Reports', 'wc_pos' ), __( 'Reports', 'wc_pos' ), $capability, 'wc-pos-reports', array( $this, 'wk_wc_pos_report_tab_manager' ) );

			add_submenu_page( 'pos-system', __( 'Invoice Templates', 'wc_pos' ), __( 'Invoice Templates', 'wc_pos' ), $capability, 'wc-pos-invoice-templates', array( $this, 'wkwcpos_get_invoice_templates' ) );

			do_action( 'wkwcpos_manage_submenus' );

			add_submenu_page( 'pos-system', __( 'Settings', 'wc_pos' ), __( 'Settings', 'wc_pos' ), $capability, 'wc-pos-settings', array( $this, 'wk_wc_pos_settings' ) );

			add_submenu_page( 'pos-system', esc_html__( 'Extensions', 'wc_pos' ), esc_html__( 'Extensions', 'wc_pos' ), $capability, 'wc-pos-extensions', array( $this, 'wc_pos_extension_layout' ) );

			add_submenu_page( 'pos-system', esc_html__( 'Support & Services', 'wc_pos' ), esc_html__( 'Support & Services', 'wc_pos' ), $capability, 'wc-pos-support-and-services', array( $this, 'wc_pos_support_services_layout' ) );

			add_action( 'load-' . $hook, array( $this, 'wk_wc_pos_add_screen_option' ) );

			add_action( 'load-' . $outlets, array( $this, 'wk_wc_pos_add_screen_option' ) );

			add_action( 'load-' . $orders, array( $this, 'wk_wc_pos_add_screen_option' ) );

			add_action( 'load-' . $products, array( $this, 'wk_wc_pos_add_screen_option' ) );

			add_filter( 'set-screen-option', array( $this, 'wk_wc_pos_set_option', 10, 3 ) );

			$post = wc_clean( $_POST ); // phpcs:ignore

			if ( isset( $post['wp_screen_options'] ) && ! empty( $post['wp_screen_options'] ) ) {
				$post['wp_screen_options']['value'] = $post['wp_screen_options']['value'] > 100 ? 100 : $post['wp_screen_options']['value'];
				update_option( $post['wp_screen_options']['option'], $post['wp_screen_options']['value'], true );
			}
		}

		/**
		 * Set options.
		 *
		 * @param mixed  $status Mixed value.
		 * @param string $option Option name.
		 * @param int    $value Option value.
		 *
		 * @return int $value Option value.
		 */
		public function wk_wc_pos_set_option( $status, $option, $value ) {
			return $value;
		}

		/**
		 * Extension layout template function.
		 */
		public function wc_pos_extension_layout() {
			add_filter( 'admin_footer_text', array( $this, 'wc_pos_admin_footer_text' ) );

			?>
				<script src="https://wpdemo.webkul.com/wk-extensions/client/wk.ext.js" defer></script>
				<webkul-extensions></webkul-extensions>

			<?php
		}

		/**
		 *  Support and services layout template function.
		 */
		public function wc_pos_support_services_layout() {
			?>
				<script src="https://webkul.com/common/modules/wksas.bundle.js" defer></script>
				<wk-area></wk-area>
			<?php
		}

		/**
		 * Admin footer text.
		 *
		 * @param string $text text for admin footer.
		 *
		 * @return string Text with link for admin footer.
		 */
		public function wc_pos_admin_footer_text( $text ) {
			return sprintf( __( 'If you like <strong>Point of sale</strong> from <strong><a href="https://webkul.com/" target="_blank" class="wc-rating-link" data-rated="Thanks :)">Webkul</a></strong> please leave us a <a href="https://codecanyon.net/item/wordpress-woocommerce-pos-system-point-of-sale/21254976" target="_blank" class="wc-rating-link" data-rated="Thanks :)">★★★★★</a> rating. A huge thanks in advance!', 'wc_pos' ) );
		}

		/**
		 * List of pos users.
		 */
		public function wk_wc_pos_list_users() {

			if ( isset( $_GET['action'] ) && ( 'add' === $_GET['action'] || 'edit' === $_GET['action'] ) ) { // phpcs:ignore

				new Admin\User\WC_Pos_Add_User();
			} else {

				new Admin\User\WC_Pos_User_List();
			}

		}

		/**
		 * Add/Edit pos outlet function.
		 */
		public function wk_wc_pos_outlets() {

			if ( ( isset( $_GET['action'] ) && 'add' === $_GET['action'] ) || ( isset( $_GET['outlet_action'] ) && 'edit' === $_GET['outlet_action'] ) ) { // phpcs:ignore
				new Admin\Outlet\WC_Pos_Outlet_Tab_Manager();
			} elseif ( isset( $_GET['action'] ) && 'outlet-import' === $_GET['action'] ) { // phpcs:ignore
				/**
				 * The product importer.
				 *
				 * This has a custom screen - the Tools > Import item is a placeholder.
				 * If we're on that screen, redirect to the custom one.
				 */
				if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
					wp_safe_redirect( admin_url( 'admin.php?&page=pos-outlets&action=outlet-import' ) );
					exit;
				}

				include_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

				$importer = new WKWCPOS_Outlet_Importer_Controller();
				$importer->dispatch();
			} else {
				new Admin\Outlet\WC_Pos_Outlet_List();
			}

		}

		/**
		 * Dispaly pos products on admin.
		 */
		public function wk_wc_pos_products() {

			$obj = new Admin\WC_Pos_Products_List();

			?>
				<div class="wrap">

					<h2 class="handle ui-sortable-handle"><span><?php echo esc_html__( 'POS Products', 'wc_pos' ); ?> </span></h2>

					<form method="GET">

						<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); // phpcs:ignore ?>" />

						<?php
						wp_nonce_field( '_pos_nonce_action', '_pos_nonce', false );
						$obj->prepare_items();

						$obj->search_box( esc_html__( 'Search', 'wc_pos' ), 'search-product' );

						?>
					</form>
					<form method="GET">
						<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); // phpcs:ignore ?>" />
						<?php $obj->display(); ?>
					</form>
					<span class="wc-pos-barcode-print-demo" style="display: none;"><svg></svg></span>
				</div>
				<?php

		}

		/**
		 * Display pos orders on admin.
		 */
		public function wk_wc_pos_orders() {
			$obj = new Admin\WC_Pos_Outlet_Order_List();

			?>
				<div class = 'wrap' >

					<h2 class = 'hndle ui-sortable-handle' > <span>
					<?php
					esc_html_e( 'POS Orders', 'wc_pos' );
					?>
					</span></h2>

					<form method="GET">

						<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; // phpcs:ignore ?>" />

								<?php
								wp_nonce_field( '_pos_nonce_action', '_pos_nonce', false );
								$obj->prepare_items();
								$obj->search_box( __( 'Search', 'wc_pos' ), 'pos-order-search-id' );
								?>

					</form>

					<form method="GET">
						<input type="hidden" name="page" value="<?php echo $_REQUEST['page'];  // phpcs:ignore ?>" />
						<?php $obj->display(); ?>
					</form>

				</div>
			<?php
		}

		/**
		 * Pos sales report by date.
		 */
		public function wk_wc_pos_report_tab_manager() {

			new Admin\Reports\WC_Pos_Report_Tab_Manager_Template();

		}

		/**
		 * Get invoice template.
		 */
		public function wkwcpos_get_invoice_templates() {

			if ( isset( $_GET['action'] ) && ( 'add' === $_GET['action'] || 'edit' === $_GET['action'] ) ) { // phpcs:ignore
				new Admin\Invoice\WKWCPOS_Manage_Invoice_Template();
			} else {
				new Admin\Invoice\WKWCPOS_Invoice_Template_list();
			}

		}

		/**
		 * Pos settings template function.
		 */
		public function wk_wc_pos_settings() {

			new Admin\WC_Pos_Settings_Template();

		}

		/**
		 * Add screen option for table on admin.
		 */
		public function wk_wc_pos_add_screen_option() {
			$options = 'per_page';
			if ( isset( $_GET['page'] ) ) { // phpcs:ignore
				switch ( $_GET['page'] ) { // phpcs:ignore
					case 'pos-products':
						$args = array(
							'label'   => esc_html__( 'Products per page', 'wc_pos' ),
							'default' => get_option( 'pos_products_per_page', 20 ),
							'option'  => 'pos_products_per_page',
							'hidden'  => 'id',
						);
						add_screen_option( $options, $args );
						new Admin\WC_Pos_Products_List();
						break;
					case 'pos-orders':
						$args = array(
							'label'   => esc_html__( 'Orders per page', 'wc_pos' ),
							'default' => get_option( 'pos_orders_per_page', 20 ),
							'option'  => 'pos_orders_per_page',
							'hidden'  => 'id',
						);
						add_screen_option( $options, $args );
						new Admin\WC_Pos_Outlet_Order_List();
						break;
					case 'pos-outlets':
						if ( isset( $_GET['tab'] ) && 'manage-products' === $_GET['tab'] ) { // phpcs:ignore
							$args = array(
								'label'   => esc_html__( 'Outlet product Per Page', 'wc_pos' ),
								'default' => get_option( 'pos_outlet_manage_products_per_page', 20 ),
								'option'  => 'pos_outlet_manage_products_per_page',
								'hidden'  => 'id',
							);
							add_screen_option( $options, $args );
						}
						break;
					case 'pos-system':
						$args = array(
							'label'   => esc_html__( 'Users Per Page', 'wc_pos' ),
							'default' => get_option( 'users_per_page', 20 ),
							'option'  => 'users_per_page',
							'hidden'  => 'id',
						);
						add_screen_option( $options, $args );
						break;
					default:
						break;
				}
			}
		}

	}
}

