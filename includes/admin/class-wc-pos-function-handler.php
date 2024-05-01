<?php
/**
 * This file handles all admin end action callbacks.
 *
 * @package     WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Admin;

use WKWC_POS\Includes;
use WKWC_POS\Templates\Admin;
use WKWC_POS\Helper\Order\WC_Pos_Orders_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Function_Handler' ) ) {

	/**
	 * Pos function handler class extend Admin setting interface
	 */
	class WC_Pos_Function_Handler implements Util\Admin_Settings_Interface {

		/**
		 * WordPress database object.
		 *
		 * @var object $db Database object.
		 */
		protected $db = '';

		/**
		 * Order helper class object.
		 *
		 * @var WC_Pos_Orders_Helper $order_helper Order helper class object.
		 */
		protected $order_helper = null;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			global $wpdb;

			$this->db = $wpdb;

			$this->order_helper = ! empty( $this->order_helper ) ? $this->order_helper : new WC_Pos_Orders_Helper();
		}

		/**
		 * Add settings for demo admin.
		 *
		 * @param array $settings_hooks Setting hooks for demo admin.
		 *
		 * @return array $settings_hooks Setting hooks for demo admin.
		 */
		public function wk_wc_pos_add_settings_for_demo_admin( $settings_hooks ) {

			array_push(
				$settings_hooks,
				'option_page_capability_pos-general-settings-group',
				'option_page_capability_pos-settings-group',
				'option_page_capability_pos-printer-settings-group',
				'option_page_capability_pos-appearance-settings-group',
				'option_page_capability_pos-endpoint-settings-group'
			);

			return $settings_hooks;
		}

		/**
		 * Add pos default customer.
		 *
		 * @param array  $actions User actions like edit, delete , set default customer etc.
		 * @param object $user_obj User data.
		 *
		 * @return array $actions User actions like edit, delete , set default customer etc.
		 */
		public function wkwcpos_add_default_customer( $actions, $user_obj ) {

			$user_id = isset( $_GET['wkpos_set_default_customer'] ) ? $_GET['wkpos_set_default_customer'] : ''; // phpcs:ignore

			if ( ! empty( $user_id ) ) {

				$this->set_default_customer( $user_id );

			}

			$link = admin_url( 'users.php?wkpos_set_default_customer=' . $user_obj->ID );

			if ( in_array( 'customer', $user_obj->roles ) ) {

				$actions['wkpos_default_customer'] = '<a href="' . $link . '">' . esc_html__( 'Set Pos Default Customer', 'wc_pos' ) . '</a>';
			}

			return $actions;
		}

		/**
		 * Set default customer function.
		 *
		 * @param int $user_id User id.
		 */
		public function set_default_customer( $user_id ) {

			$args = array(
				'meta_key'   => 'deault_customer_pos',
				'meta_value' => '1',
			);

			$pos_default_customer = get_users( $args );

			if ( ! empty( $pos_default_customer ) ) {

				foreach ( $pos_default_customer as $default_customer ) {
					$default_customer_id = $default_customer->ID;
					delete_user_meta( $default_customer_id, 'deault_customer_pos', '1' );
					update_user_meta( $user_id, 'deault_customer_pos', '1' );
				}
			} else {
				update_user_meta( $user_id, 'deault_customer_pos', '1' );
			}

			wp_safe_redirect( admin_url( 'admin.php?page=wc-pos-settings&tab=customer-settings' ) );
			exit;
		}

		/**
		 * Start setup wizard.
		 */
		public function wkwcpos_start_setup_wizard() {
			// Setup/welcome.
			if ( ! empty( $_GET['page'] ) && $_GET['page'] == 'wkwcpos-setup' ) { // phpcs:ignore
				new Includes\Admin\WKWCPOS_Setup_Wizard();
			}
		}

		/**
		 * Add dashboard menu.
		 */
		public function wk_wc_pos_add_dashboard_menu() {
			new Menu\WC_Pos_Admin_Menu_Template();
		}

		/**
		 * POS general settings.
		 */
		public function wk_wc_pos_general_settings() {
			new Admin\Settings\WC_Pos_General_Settings();
		}

		/**
		 * POS admin screen ids.
		 *
		 * @param array $screen_id Screen id.
		 *
		 * @return array $screen_id Screen id.
		 */
		public function add_pos_screen_ids( $screen_id ) {
			$arr = 'point-of-sale_page_wc-pos-settings';

			array_push( $screen_id, $arr, 'toplevel_page_pos-system', 'toplevel_page_wkwcpos-setup', 'point-of-sale_page_pos-outlets', 'point-of-sale_page_wc-add-pos-user', 'point-of-sale_page_wc-pos-invoice-templates' );

			return $screen_id;
		}

		/**
		 * POS customer settings.
		 */
		public function wk_wc_pos_customer_settings() {
			new Admin\Settings\WC_Pos_Customer_Settings();
		}

		/**
		 * POS printer settings.
		 */
		public function wk_wc_pos_printer_settings() {
			new Admin\Settings\WC_Pos_Printer_Settings();
		}

		/**
		 * POS appearance settings.
		 */
		public function wk_wc_pos_appearance_settings() {
			new Admin\Settings\WC_Pos_Appearance();
		}

		/**
		 * POS endpoint settings.
		 */
		public function wk_wc_pos_endpoint_settings() {
			new Admin\Settings\WC_Pos_Endpoint_Settings();
		}

		/**
		 * POS payment settings.
		 */
		public function wk_wc_pos_payment_settings() {
			if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'add' || $_GET['action'] == 'edit' ) ) { // phpcs:ignore
				new Admin\Settings\WC_Pos_Manage_Payment();
			} else {
				new Admin\Settings\WC_Pos_Payment_List();
			}
		}

		/**
		 * POS outlet mass assign master stock.
		 */
		public function wk_wc_pos_outlet_mass_assign_master_stock() {
			new Admin\Settings\WC_Pos_Mass_Assign_Product_Master_Stock_Settings();
		}

		/**
		 * POS outlet general settings.
		 */
		public function wk_wc_pos_outlet_general_settings() {
			new Admin\Outlet\WC_Pos_Add_Outlet();
		}

		/**
		 * Save pos user.
		 *
		 * @param array $post Posted data.
		 */
		public function wk_wc_save_pos_user( $post ) {
			new Includes\Admin\User\WC_Pos_Manage_User( $post );
		}

		/**
		 * Save pos outlet.
		 *
		 * @param array $post Posted data.
		 */
		public function wk_wc_save_pos_outlet( $post ) {
			new Includes\Admin\Outlet\WC_Pos_Manage_Outlet( $post );
		}

		/**
		 * Update POS master stock while changing the status from admin end.
		 *
		 * @param int   $post_id  Post/Order id.
		 * @param array $post Post/Order array.
		 * @param bool  $update Is update.
		 */
		public function wk_wc_pos_update_stock( $post_id, $post, $update ) {

			$decrease_status = array( 'wc-cancelled', 'wc-pending' );
			$increase_status = array( 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-refunded' );

			$update_stock_status = array(
				'decrease_status' => $decrease_status,
				'increase_status' => $increase_status,
			);

			$update_stock_status = apply_filters( 'wkwcpos_modify_update_stock_status_list', $update_stock_status, $post_id, $post, $update );

			$decrease_status = $update_stock_status['decrease_status'];
			$increase_status = $update_stock_status['increase_status'];

			$post_data = wc_clean( $_POST ); // phpcs:ignore

			$post_type = isset( $post_data['post_type'] ) ? $post_data['post_type'] : '';

			if ( 'shop_order' === $post_type ) {

				$current_post_status  = isset( $post_data['post_status'] ) ? $post_data['post_status'] : '';
				$selected_post_status = isset( $post_data['order_status'] ) ? $post_data['order_status'] : '';

				if ( ! empty( $current_post_status ) && ! empty( $selected_post_status ) ) {

					if ( in_array( $current_post_status, $decrease_status, true ) ) {

						if ( in_array( $selected_post_status, $increase_status, true ) ) {
							$this->wkwcpos_order_stock_restore( $post_id, 'decrease' );
						}
					} elseif ( in_array( $current_post_status, $increase_status, true ) ) {

						if ( in_array( $selected_post_status, $decrease_status, true ) ) {
							$this->wkwcpos_order_stock_restore( $post_id, 'increase' );
						}
					}
				}
			}
		}

		/**
		 * Prevent woocommerce stock update for pos master stock.
		 *
		 * @param bool   $status Status to prevent woocommerce to update stock.
		 * @param object $order Wc order object.
		 *
		 * @return bool $status Status to prevent woocommerce to update stock.
		 */
		public function wk_wc_pos_prevent_woo_update_stock( $status, $order ) {

			$main_outlet = get_post_meta( $order->get_id(), '_wk_wc_pos_outlet', true );

			if ( ! empty( $main_outlet ) ) {
				$status = false;
			}

			return $status;
		}

		/**
		 * Restore/Update master stock.
		 *
		 * @param int    $order_id Order id.
		 * @param string $increment_type Increment type ( increase/decrease ).
		 */
		public function wkwcpos_order_stock_restore( $order_id, $increment_type = '' ) {

			$order = wc_get_order( $order_id );

			$main_outlet = get_post_meta( $order->get_id(), '_wk_wc_pos_outlet', true );

			if ( ! empty( $main_outlet ) && ! empty( $order ) ) {

				$table_name = $this->db->prefix . 'woocommerce_pos_outlet_product_map';

				foreach ( $order->get_items() as $item ) {

					$product_id = $item->get_product_id();

					$product_quantity = $item->get_quantity();

					$product = wc_get_product( $product_id );

					if ( $product->get_type() == 'simple' ) {

						$pos_stock = $this->db->get_var( "SELECT pos_stock FROM $table_name WHERE product_id = $product_id AND outlet_id = $main_outlet" );

						$master_stock = get_post_meta( $product_id, '_pos_master_stock', true );

						if ( '' !== $pos_stock ) {

							if ( 'decrease' === $increment_type ) {

								$pos_stock    = intval( $pos_stock ) - intval( $product_quantity );
								$master_stock = $master_stock - $product_quantity;

							} else {

								$pos_stock    = intval( $pos_stock ) + intval( $product_quantity );
								$master_stock = $master_stock + $product_quantity;
							}

							update_post_meta( $product_id, '_pos_master_stock', $master_stock );

							$pos_stock = $this->db->get_results( "UPDATE $table_name SET pos_stock = $pos_stock WHERE product_id = $product_id AND outlet_id = $main_outlet" );
						}
					} elseif ( $product->get_type() == 'variable' ) {
						$product_variation_id = $item->get_variation_id();

						$pos_stock = $this->db->get_var( "SELECT pos_stock FROM $table_name WHERE product_id = $product_variation_id AND outlet_id = $main_outlet" );

						$master_stock = get_post_meta( $product_variation_id, '_pos_variation_master_stock', true );

						if ( '' !== $pos_stock ) {

							if ( 'decrease' === $increment_type ) {

								$pos_stock    = intval( $pos_stock ) - intval( $product_quantity );
								$master_stock = $master_stock - $product_quantity;

							} else {

								$pos_stock    = intval( $pos_stock ) + intval( $product_quantity );
								$master_stock = $master_stock + $product_quantity;
							}

							update_post_meta( $product_variation_id, '_pos_variation_master_stock', $master_stock );

							$pos_stock = $this->db->get_results( "UPDATE $table_name SET pos_stock = $pos_stock WHERE product_id = $product_variation_id AND outlet_id = $main_outlet" );
						}
					}
				}
			}
		}

		/**
		 * Manage pos outlet products settings.
		 */
		public function wk_wc_pos_outlet_mgproducts_settings() {
			if ( isset( $_GET['page'] ) && 'pos-outlets' === $_GET['page'] && isset( $_GET['tab'] ) && 'manage-products' === $_GET['tab'] && isset( $_GET['outlet_id'] ) && ! empty( $_GET['outlet_id'] ) ) { // phpcs:ignore

				$table_name = $this->db->prefix . 'woocommerce_pos_outlets';
				$outlet_id  = esc_attr( $_GET['outlet_id'] ); // phpcs:ignore

				$res_pos = $this->db->get_results( $this->db->prepare( "SELECT * from $table_name WHERE id=%d", $outlet_id ) );

				if ( $res_pos ) {
					new Admin\Outlet\WC_Pos_Outlet_Product_List();
				} else {
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php esc_html_e( 'No Such Outlet found.', 'wc_pos' ); ?></p>
					</div>
					<?php
				}
			}
		}

		/**
		 * POS register settings.
		 */
		public function wk_wc_pos_register_settings() {
			// API Settings.
			register_setting( 'pos-general-settings-group', 'wkwcpos_api_username' );
			register_setting( 'pos-general-settings-group', 'wkwcpos_api_password' );

			// General Settings.
			register_setting( 'pos-general-settings-group', '_pos_heading_login' );
			register_setting( 'pos-general-settings-group', '_pos_footer_brand_link' );
			register_setting( 'pos-general-settings-group', '_pos_footer_brand_name' );
			register_setting( 'pos-general-settings-group', '_pos_popular_product_count' );
			register_setting( 'pos-general-settings-group', '_pos_low_stock_warn' );
			register_setting( 'pos-general-settings-group', '_define_difference_after_absolute' );
			register_setting( 'pos-general-settings-group', '_pos_logo_to_pos_screen' );
			register_setting( 'pos-general-settings-group', '_pos_product_image_view' );
			register_setting( 'pos-general-settings-group', '_pos_barcode_print_page_preview' );
			register_setting( 'pos-general-settings-group', '_pos_auto_sync_offline_orders' );
			register_setting( 'pos-general-settings-group', '_pos_load_woo_orders_on_outlet' );
			register_setting( 'pos-general-settings-group', '_pos_order_id_prefix' );
			register_setting( 'pos-general-settings-group', '_pos_enable_keyboard_shortcuts' );
			register_setting( 'pos-general-settings-group', '_pos_invoice_logo' );
			register_setting( 'pos-general-settings-group', '_pos_inventory_type' );
			register_setting( 'pos-general-settings-group', '_pos_product_default_status' );
			register_setting( 'pos-general-settings-group', '_pos_enable_zero_price_products' );
			register_setting( 'pos-general-settings-group', '_pos_unit_price_feature' );
			register_setting( 'pos-general-settings-group', '_pos_opening_amount_drawer' );
			register_setting( 'pos-general-settings-group', '_pos_invoice_option' );
			register_setting( 'pos-general-settings-group', '_pos_mails_at_pos_end' );

			// PWA Settings.
			register_setting( 'pos-general-settings-group', '_pos_pwa_name' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_shortname' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_themecolor' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_bgcolor' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_icon48' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_icon96' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_icon144' );
			register_setting( 'pos-general-settings-group', '_pos_pwa_icon196' );

			// Barcode Settings.
			register_setting( 'pos-general-settings-group', '_pos_barcode_width' );
			register_setting( 'pos-general-settings-group', '_pos_barcode_height' );
			register_setting( 'pos-general-settings-group', '_pos_barcode_bg_color' );
			register_setting( 'pos-general-settings-group', '_pos_barcode_display_title' );
			register_setting( 'pos-general-settings-group', '_pos_barcode_text_position' );
			register_setting( 'pos-general-settings-group', '_pos_barcode_text_size' );

			// Notification Settings.
			register_setting( 'pos-settings-group', 'pos_text' );
			register_setting( 'pos-settings-group', 'pos_text_color' );

			// Printer Settings.
			register_setting( 'pos-printer-settings-group', '_pos_printer_type' );

			// Appearance settings.
			register_setting( 'pos-appearance-settings-group', '_pos_theme_mode' );

			register_setting( 'pos-endpoint-settings-group', '_pos_endpoint_name', array( $this, '_pos_endpoint_name_validator' ) );

			do_action( 'wkwcpos_save_endpoint_settings', 'pos-endpoint-settings-group' );
			do_action( 'wkwcpos_save_printer_settings', 'pos-printer-settings-group' );
			do_action( 'wkwcpos_save_general_settings', 'pos-general-settings-group' );
			do_action( 'wkwcpos_save_barcode_settings', 'pos-barcode-settings-group' );
			do_action( 'wkwcpos_save_endpoint_settings', 'pos-endpoint-settings-group' );
			do_action( 'wkwcpos_save_appearance_settings', 'pos-appearance-settings-group' );
		}

		/**
		 * End point validator name.
		 *
		 * @param string $data End point.
		 *
		 * @return string|Error $data End point.
		 */
		public function _pos_endpoint_name_validator( $data ) {
			if ( empty( $data ) ) {
				add_settings_error(
					'requiredTextFieldEmpty',
					'empty',
					esc_html__( 'Fields cannot be empty', 'wc_pos' ),
					'error'
				);
			} else {
				return sanitize_text_field( $data );
			}

		}

		/**
		 *  Custom field to bulk product edit.
		 */
		public function wk_wc_pos_custom_field_product_bulk_edit() {
			?>
			<div class="inline-edit-group">
				<label class="alignleft">
					<span class="title"><?php _e( 'Master Stock', 'wc_pos' ); ?></span>
					<span class="input-text-wrap">
						<select class="change_t_dostawy change_to" name="change_t_dostawy">
							<?php
							$options = array(
								''  => __( '— No change —', 'wc_pos' ),
								'1' => __( 'Change to:', 'wc_pos' ),
							);
							foreach ( $options as $key => $value ) {
								echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
							}
							?>
						</select>
					</span>
				</label>
				<label class="change-input">
					<input type="number" name="_pos_master_stock" class="text t_dostawy" min='0' placeholder="<?php _e( 'Enter master stock', 'wc_pos' ); ?>" value="" />
				</label>
			</div>
			<?php
		}

		/**
		 * Save custom field data while bulk product editing.
		 *
		 * @param object $product Product object.
		 */
		public function wk_wc_pos_save_custom_field_product_bulk_edit( $product ) {
			if ( ! empty( $product ) && $product->is_type( 'simple' ) ) {
				$product_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $product->id;

				if ( isset( $_REQUEST['_pos_master_stock'] ) ) { // phpcs:ignore
					update_post_meta( $product_id, '_pos_master_stock', intval( $_REQUEST['_pos_master_stock'] ) ); // phpcs:ignores
				}
			}
		}

		/**
		 * Variation settings field.
		 *
		 * @param int    $loop Loop value.
		 * @param object $variation_data Variation data.
		 * @param object $variation Variation data.
		 */
		public function wk_wc_pos_variation_settings_fields( $loop, $variation_data, $variation ) {
			// Number Field.
			woocommerce_wp_text_input(
				array(
					'id'                => '_pos_variation_master_stock[' . $variation->ID . ']',
					'label'             => __( 'POS Variation Master Stock', 'wc_pos' ),
					'desc_tip'          => 'true',
					'description'       => __( 'POS Enter the master stock for variation.', 'wc_pos' ),
					'value'             => get_post_meta( $variation->ID, '_pos_variation_master_stock', true ),
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				)
			);
		}

		/**
		 * Save variation settings field.
		 *
		 * @param int $post_id Post id.
		 */
		public function wk_wc_pos_save_variation_settings_fields( $post_id ) {

			$table_name = $this->db->prefix . 'woocommerce_pos_outlet_product_map';

			// manage master stock when product main stock is updated.
			if ( isset( $_POST['variable_post_id'] ) ) { // phpcs:ignore

				$post_data = wc_clean( $_POST ); // phpcs:ignore
				foreach ( $post_data['variable_post_id'] as $key => $value ) {
					$total_stock = $this->db->get_results( $this->db->prepare( "SELECT pos_stock FROM $table_name WHERE product_id = '%d'", $value ) );

					$total_stock = wp_list_pluck( $total_stock, 'pos_stock' );

					$total_stock = array_sum( $total_stock );

					if ( isset( $post_data['_pos_variation_master_stock'][ $value ] ) && $post_data['_pos_variation_master_stock'][ $value ] ) {
						$master_stock = $post_data['_pos_variation_master_stock'][ $value ];

						$remaining_master_stock = $master_stock - $total_stock;

						if ( $remaining_master_stock >= 0 ) {
							update_post_meta( $value, '_pos_variation_master_stock', $master_stock );
						} else {
							update_post_meta( $value, '_pos_variation_master_stock', ( $total_stock ) );
						}
					}
				}
			}
		}

		/**
		 * POS master stock field for simple products.
		 */
		public function wk_wc_pos_simple_woo_custom_fields() {
			global $post_id;

			$product = wc_get_product( $post_id );

			if ( ! empty( $product ) && $product->is_type( 'simple' ) ) {
				// Number Field.
				woocommerce_wp_text_input(
					array(
						'id'                => '_pos_master_stock',
						'label'             => __( 'POS Master Stock', 'wc_pos' ),
						'desc_tip'          => 'true',
						'description'       => __( 'POS Enter the master stock for product.', 'wc_pos' ),
						'value'             => get_post_meta( $post_id, '_pos_master_stock', true ),
						'custom_attributes' => array(
							'step' => 'any',
							'min'  => '0',
						),
					)
				);
			}
		}

		/**
		 * Update pos master stock quantity from order.
		 *
		 * @param int $order_id Order id.
		 */
		public function wk_wc_pos_send_order_to_mypage( $order_id ) {

			$order = wc_get_order( $order_id );

			$pos_order = get_post_meta( $order_id, '_wk_wc_pos_outlet', true );

			if ( ! $pos_order ) {
				$items = $order->get_items();

				if ( ! empty( $items ) ) {
					foreach ( $items as $key => $value ) {
						if ( $value->get_variation_id() ) {
							$item_id = $value->get_variation_id();

							$item_master_stock = get_post_meta( $item_id, '_pos_variation_master_stock', true );

							$sold_quantity = $value->get_quantity();

							$updated_master_stock = ( $item_master_stock >= $sold_quantity ) ? ( $item_master_stock - $sold_quantity ) : $item_master_stock;

							update_post_meta( $item_id, '_pos_variation_master_stock', $updated_master_stock );
						} else {
							$item_id = $value->get_product_id();

							$item_master_stock = get_post_meta( $item_id, '_pos_master_stock', true );

							$sold_quantity = $value->get_quantity();

							$updated_master_stock = ( $item_master_stock >= $sold_quantity ) ? ( $item_master_stock - $sold_quantity ) : $item_master_stock;

							update_post_meta( $item_id, '_pos_master_stock', $updated_master_stock );
						}
					}
				}
			}
		}

		/**
		 * Manage product master stock.
		 *
		 * @param int $post_id Post id.
		 */
		public function wk_wc_pos_manage_product_master_stock( $post_id ) {

			$table_name = $this->db->prefix . 'woocommerce_pos_outlet_product_map';

			$post_data = wc_clean( $_POST ); // phpcs:ignore

			if ( isset( $post_data['product-type'] ) && 'simple' == $post_data['product-type'] ) {

				$total_stock = $this->db->get_results( $this->db->prepare( "SELECT pos_stock FROM $table_name WHERE product_id = '%d'", $post_id ) );

				$total_stock = wp_list_pluck( $total_stock, 'pos_stock' );

				$total_stock = array_sum( $total_stock );

				if ( isset( $post_data['_pos_master_stock'] ) && $post_data['_pos_master_stock'] ) {
					$master_stock = $post_data['_pos_master_stock'];

					$remaining_master_stock = $master_stock - $total_stock;

					if ( $remaining_master_stock >= 0 ) {
						update_post_meta( $post_id, '_pos_master_stock', $master_stock );
					} else {
						update_post_meta( $post_id, '_pos_master_stock', ( $total_stock ) );
					}
				}
			}

		}

		/**
		 * Enable product in outlet.
		 *
		 * @param int $post_id Post id.
		 */
		public function wkwcpos_enable_product_in_outlet( $post_id ) {

			if ( get_option( '_pos_product_default_status', 'enabled' ) == 'enabled' ) {

				$outlets = $this->db->get_results( "SELECT id FROM {$this->db->prefix}woocommerce_pos_outlets", ARRAY_A );

				if ( ! empty( $outlets ) ) {

					$product_obj = wc_get_product( $post_id );

					if ( ! empty( $product_obj ) && is_object( $product_obj ) ) {

						foreach ( $outlets as $key => $outlet ) {

							$entry_exists = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->db->prefix}woocommerce_pos_outlet_product_map WHERE outlet_id=%d AND product_id=%d", $outlet['id'], $post_id ) );

							if ( empty( $entry_exists ) ) {

								if ( $product_obj->get_type() == 'variable' ) {

									$this->db->insert(
										$this->db->prefix . 'woocommerce_pos_outlet_product_map',
										array(
											'outlet_id'  => $outlet['id'],
											'product_id' => $post_id,
											'pos_status' => 'enabled',
											'pos_stock'  => 1000000,
										),
										array( '%d', '%d', '%s', '%d' )
									);
								} else {

									$this->db->insert(
										$this->db->prefix . 'woocommerce_pos_outlet_product_map',
										array(
											'outlet_id'  => $outlet['id'],
											'product_id' => $post_id,
											'pos_status' => 'enabled',
											'pos_stock'  => 0,
										),
										array( '%d', '%d', '%s', '%d' )
									);
								}
							}
						}
					}
				}
			}

		}

		/**
		 * Add pos login in admin bar menu.
		 *
		 * @param object $wp_admin_bar WordPress admin bar object.
		 */
		public function wk_wc_pos_admin_bar_menus( $wp_admin_bar ) {
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			if ( ! is_admin() || ! is_user_logged_in() ) {
				return;
			}

			// Show only when the user is a member of this site, or they're a super admin.
			if ( ! is_user_member_of_blog() && ! is_super_admin() ) {
				return;
			}

			// Don't display when shop page is the same of the page on front.
			if ( get_option( 'page_on_front' ) == wc_get_page_id( $pos_endpoint ) ) {
				return;
			}

			$data = apply_filters(
				'wkwcpos_manage_admin_bar_menu',
				array(
					'parent' => 'site-name',
					'id'     => 'view-pos',
					'title'  => esc_html__( 'Visit POS', 'wc_pos' ),
					'href'   => wp_logout_url( site_url( $pos_endpoint ) ),
				)
			);

			// Add an option to visit the store.
			$wp_admin_bar->add_node(
				$data
			);
		}

		/**
		 * Add pos setup wizard in help tab in screens.
		 */
		public function wk_wc_pos_add_help_tab() {
			if ( ! function_exists( 'wc_get_screen_ids' ) ) {
				return;
			}

			$screen = get_current_screen();

			if ( ! $screen || ! in_array( $screen->id, wc_get_screen_ids(), true ) ) {
				return;
			}

			// Remove the old help tab if it exists.
			$help_tabs = $screen->get_help_tabs();

			// Add the new help tab.
			$help_tab = array(
				'title' => esc_html__( 'Point of sale Setup wizard', 'wc_pos' ),
				'id'    => 'woocommerce_pos_onboard_tab',
			);

			$help_tab['content'] = '<h2>' . esc_html__( 'Point Of Sale Onboarding', 'wc_pos' ) . '</h2>';

			$help_tab['content'] .= '<h3>' . esc_html__( 'POS Setup Wizard', 'wc_pos' ) . '</h3>';
			$help_tab['content'] .= '<p>' . esc_html__( 'If you need to access the setup wizard again, please click on the button below.', 'wc_pos' ) . '</p>' .
				'<p><a href="' . wc_admin_url( '&page=wkwcpos-setup' ) . '" class="button button-primary">' . esc_html__( 'Setup wizard', 'wc_pos' ) . '</a></p>';

			$screen->add_help_tab( $help_tab );
		}

		/**
		 * Add prefix in order id in woocommerce order list.
		 *
		 * @param int      $order_number Order id.
		 * @param WC_Order $parent WooCommerce order class object.
		 *
		 * @return string $order_number Formatted order number with prefix.
		 */
		public function wkwcpos_add_prefix_in_order( $order_number, $parent ) {
			return $this->order_helper->get_prefixed_order_number( $order_number, '' );
		}
	}
}
