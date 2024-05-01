<?php
/**
 * POS manage invoice template class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Admin\Invoice;

use WKWC_POS\Helper\Invoice\WKWCPOS_Invoice_Helper;
use WKWC_POS\Helper\User\WC_Pos_User_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WKWCPOS_Manage_Invoice_Template' ) ) {

	/**
	 * Manage pos invoice template class.
	 */
	class WKWCPOS_Manage_Invoice_Template {

		/**
		 * Invoice helper class instance.
		 *
		 * @var object $invoice_helper Invoice helper class instance.
		 */
		public $invoice_helper;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$this->invoice_helper = new WKWCPOS_Invoice_Helper();

			add_action( 'wkwcpos_invoice_general', array( $this, 'wkwcpos_get_invoice_general_template' ) );

			add_action( 'wkwcpos_invoice_editor', array( $this, 'wkwcpos_get_invoice_editor_template' ) );

			$this->wkwcpos_get_manage_invoice_template();
		}

		/**
		 * Get manage invoice template function.
		 */
		public function wkwcpos_get_manage_invoice_template() {

			$tabs = array(
				'general' => esc_html__( 'General', 'wc_pos' ),
			);

			if ( ! empty( $_GET['id'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) { // phpcs:ignore
				$tabs['editor'] = esc_html__( 'Editor', 'wc_pos' );
			}

			$current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_title( $_GET['tab'] ); // phpcs:ignore

			$id = empty( $_GET['id'] ) ? '' : '&id=' . $_GET['id']; // phpcs:ignore

			echo '<div class="wrap">';

			echo '<nav class="nav-tab-wrapper">';

			foreach ( $tabs as $name => $label ) {

				echo '<a href="' . esc_url( admin_url( "admin.php?page={$_GET['page']}&tab={$name}&action={$_GET['action']}{$id}" ) ) . '" class="nav-tab ' . esc_attr( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';

			}

			echo '</nav>';

			do_action( 'wkwcpos_invoice_' . $current_tab, $this );

			echo '</div>';

		}

		/**
		 * Get invoice general template.
		 */
		public function wkwcpos_get_invoice_general_template() {

			$this->verify_nonce();

			if ( ! empty( $_GET['result'] ) ) { // phpcs:ignore

				$action_type = $_GET['result']; // phpcs:ignore

				if ( 'created' === $action_type ) {
					?>
					<div class='notice notice-success is-dismissible'>
						<p><?php echo wp_kses_post( esc_html_e( 'Invoice has been created successfully.', 'wc_pos' ) ); ?></p>
					</div>
					<?php
				} elseif ( 'updated' === $action_type ) {
					?>
					<div class='notice notice-success is-dismissible'>
						<p><?php echo wp_kses_post( esc_html_e( 'Invoice has been updated successfully.', 'wc_pos' ) ); ?></p>
					</div>
					<?php
				}
			}

			$name = '';

			if ( ! empty( $_GET['id'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) { // phpcs:ignore

				$invoice = $this->invoice_helper->wkwcpos_get_invoice_template( $_GET['id'] ); // phpcs:ignore

				if ( ! empty( $invoice ) ) {

					$name = $invoice['name'];

				}
			}

			?>

			<div class="wrap">

				<div id="wrapper">

					<div id="dashboard_right_now" class="formcontainer pos pos-settings">

						<form action="" method="post">

							<?php wp_nonce_field( 'pos_action', 'pos_nonce_field' ); ?>

							<div class="wc-pos-form-block">
								<div class="wc-pos-form-block-header">
									<span>
										<?php
										if ( 'add' === $_GET['action'] ) { // phpcs:ignore
											esc_html_e( 'Add Invoice Template', 'wc_pos' );
										} else {
											esc_html_e( 'Edit Invoice Template', 'wc_pos' );
										}
										?>
									</span>
								</div>
								<div class="wc-pos-form-block-body">
									<div class="options_group">
												<?php
												wkwcpos_text_input(
													array(
														'id'       => '_wkwcpos_invoice_name',
														'value'    => $name,
														'label'    => __( 'Name', 'wc_pos' ),
														'desc_tip' => true,
														'description' => __( 'Enter Invoice Name.', 'wc_pos' ),
													)
												);

												?>
									</div>

									<?php do_action( 'wkwcpos_manage_invoice_form_fields' ); ?>

								</div>
								<div class="wc-pos-form-block-footer">
									<button type="submit" name="wkwcpos_save_invoice" class="button button-primary"><?php esc_html_e( 'Save', 'wc_pos' ); ?></button>

								</div>
							</div>

						</form>

					</div>
				</div>

			</div>
			<?php

		}

		/**
		 * Get invoice editor template.
		 */
		public function wkwcpos_get_invoice_editor_template() {

			if ( ! empty( $_GET['invoice_saved'] ) ) { // phpcs:ignore
				?>
				<div class='notice notice-success is-dismissible'>
					<p><?php echo wp_kses_post( esc_html_e( 'Invoice has been saved successfully.', 'wc_pos' ) ); ?></p>
				</div>
				<?php
			}

			wp_enqueue_style( 'wkwcpos-invoice-style' );
			wp_enqueue_script( 'wkwcpos-invoice-script' );

			$dir = wp_upload_dir();

			$logo_invoice = get_option( '_pos_invoice_logo' );

			if ( ! empty( $logo_invoice ) ) {
				$logo_invoice = $dir['baseurl'] . $logo_invoice;
			} else {
				$logo_invoice = WK_WC_POS_API . '/assets/images/17241-200.png';
			}

			$invoice_data = array(
				'pos_user'       => '',
				'pos_user_phone' => '',
				'outlet'         => '',
				'logo_invoice'   => $logo_invoice,
				'pos_order'      => '',
			);

			if ( ! empty( $_REQUEST['wkwcpos_invoice_submit'] ) ) {  // phpcs:ignore
				// phpcs:ignore
				$pos_user  = ! empty( $_REQUEST['pos_user'] ) && is_numeric( $_REQUEST['pos_user'] ) ? intval( $_REQUEST['pos_user'] ) : '';

				// phpcs:ignore
				$pos_order = ! empty( $_REQUEST['pos_order'] ) && is_numeric( $_REQUEST['pos_order'] ) ? intval( $_REQUEST['pos_order'] ) : '';

				if ( ! empty( $pos_user ) && ! empty( $pos_order ) ) {

					$user = get_userdata( $pos_user );

					$user_roles = $user->roles;

					if ( ! empty( $user_roles ) && in_array( 'pos_user', $user_roles ) ) {

						$pos_order_outlet = get_post_meta( $pos_order, '_wk_wc_pos_outlet', true );

						$user_helper = new WC_Pos_User_Helper();
						$outlet_id   = $user_helper->_get_pos_user_outlet( $pos_user );

						if ( $outlet_id == $pos_order_outlet ) {

							global $wpdb;

							$table_name = $wpdb->prefix . 'woocommerce_pos_outlets';

							$outlet = $wpdb->get_row( $wpdb->prepare( "SELECT * from $table_name WHERE id=%d", $outlet_id ) );

							if ( ! empty( $outlet ) ) {
								$dir = wp_upload_dir();

								$logo_invoice = get_option( '_pos_invoice_logo' );

								if ( ! empty( $logo_invoice ) ) {
									$logo_invoice = $dir['baseurl'] . $logo_invoice;
								} else {
									$logo_invoice = '';
								}

								$table_name_order = $wpdb->prefix . 'woocommerce_order_items';

								$table_name_ordermeta = $wpdb->prefix . 'woocommerce_order_itemmeta';

								$p = 0;

								$coupon = array();

								$order = new \WC_Order( $pos_order );

								$currency_code = $order->get_currency();

								$currency_symbol = get_woocommerce_currency_symbol( $currency_code );

								$args = array(
									'currency' => $currency_code,
								);

								$items = $order->get_items();

								$order_detail_by_order_id['order_id'] = $order->get_id();
								$order_detail_by_order_id['id']       = $order->get_id();

								$order_detail_by_order_id['currency'] = $currency_symbol;

								$id = 1;

								foreach ( $items as $key => $value ) {
									$value_data = $value->get_data();

									$meta = array();

									$meta = apply_filters( 'get_order_item_meta_data', $meta, $value_data, $order, $value );

									$product_id = $value->get_product_id();

									if ( 0 == $product_id ) {
										$product_id = 'virtual' . $id;
										++$id;
									}
									$variable_id = $value->get_variation_id();

									$product_total_price = wc_price( $value_data['total'], $args );

									$qty = $value_data['quantity'];

									$product_unit_price = wc_price( $value_data['total'] / $qty, $args );

									$order_detail_by_order_id['products'][ $p ] = array(
										'product_id'   => $product_id,
										'product_name' => $value['name'],
										'qty'          => $qty,
										'variable_id'  => $variable_id,
										'product_unit_price' => $product_unit_price,
										'total_price'  => $value_data['total'],
										'product_total_price' => $product_total_price,
										'product_meta_data' => ! empty( $meta ) ? $meta : false,
									);

									$order_detail_by_order_id['products'][ $p ] = apply_filters( 'wkwcpos_update_order_product_detail', $order_detail_by_order_id['products'][ $p ], $product_id, $order, array() );

									++$p;
								}

								foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
									$order_detail_by_order_id['tax_lines'][] = array(
										'id'       => $tax->id,
										'rate_id'  => $tax->rate_id,
										'code'     => $tax_code,
										'title'    => $tax->label,
										'total'    => wc_price( wc_format_decimal( $tax->amount, 2 ), array( 'currency' => $order->get_currency() ) ),
										'compound' => (bool) $tax->is_compound,
									);
								}

								$billing_phone                       = $order->get_billing_phone();
								$billing_fname                       = $order->get_billing_first_name();
								$billing_lname                       = $order->get_billing_last_name();
								$billing_address                     = $order->get_billing_address_1();
								$billing_address2                    = $order->get_billing_address_2();
								$order_detail_by_order_id['billing'] = array(
									'phone'    => $billing_phone,
									'fname'    => $billing_fname,
									'lname'    => $billing_lname,
									'address1' => $billing_address,
									'address2' => $billing_address2,
								);
								if ( $order->get_billing_country() ) {
									$billing_city                                    = $order->get_billing_city();
									$billing_postcode                                = $order->get_billing_postcode();
									$billing_state                                   = $order->get_billing_state();
									$billing_country                                 = WC()->countries->countries[ $order->get_billing_country() ];
									$order_detail_by_order_id['billing']['city']     = $billing_city;
									$order_detail_by_order_id['billing']['postcode'] = $billing_postcode;
									$order_detail_by_order_id['billing']['state']    = $billing_state;
									$order_detail_by_order_id['billing']['country']  = $billing_country;
								}

								$shipping_fname                       = $order->get_shipping_first_name();
								$shipping_lname                       = $order->get_shipping_last_name();
								$shipping_address                     = $order->get_shipping_address_1();
								$shipping_address2                    = ! empty( $order->get_shipping_address_2() ) ? $order->get_shipping_address_2() : '';
								$order_detail_by_order_id['shipping'] = array(
									'fname'    => $shipping_fname,
									'lname'    => $shipping_lname,
									'address1' => $shipping_address,
									'address2' => $shipping_address2,
								);
								if ( $order->get_shipping_country() ) {
									$shipping_city                                    = $order->get_shipping_city();
									$shipping_postcode                                = $order->get_shipping_postcode();
									$shipping_state                                   = $order->get_shipping_state();
									$shipping_country                                 = WC()->countries->countries[ $order->get_shipping_country() ];
									$order_detail_by_order_id['shipping']['city']     = $shipping_city;
									$order_detail_by_order_id['shipping']['postcode'] = $shipping_postcode;
									$order_detail_by_order_id['shipping']['state']    = $shipping_state;
									$order_detail_by_order_id['shipping']['country']  = $shipping_country;
								}
								$args = array(
									'post_id' => $pos_order,
									'orderby' => 'comment_ID',
									'order'   => 'DESC',
									'approve' => 'approve',
									'type'    => 'order_note',
								);

								remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

								$notes = get_comments( $args );

								add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

								if ( ! empty( $notes ) ) {
									$notes = wp_list_pluck( $notes, 'comment_content' );
								} else {
									$notes = array();
								}

								$order_detail_by_order_id['order_notes'] = $notes;

								$tendered = get_post_meta( $pos_order, '_tendered_amnt', true );

								$cash_pay = get_post_meta( $pos_order, 'cash_pay', true );

								$card_pay = get_post_meta( $pos_order, 'card_pay', true );

								if ( ! empty( $tendered ) ) {
									$parse_total = $order->get_total();
								} else {
									$tendered = 0;
								}

								if ( $order->get_payment_method() == 'cash' && '' === $cash_pay ) {
									$cash_pay = $tendered;
									$card_pay = 0;
								}

								if ( $order->get_payment_method() == 'card' && '' === $card_pay ) {
									$card_pay = $tendered;
									$cash_pay = 0;
								}

								$balance = (float) $tendered - (float) $parse_total;

								$order_detail_by_order_id['tendered'] = wc_price( $tendered, $args );

								$order_detail_by_order_id['balance'] = wc_price( $balance, $args );

								$email                                     = $order->get_billing_email();
								$order_detail_by_order_id['email']         = $email;
								$order_date                                = $order->get_date_created();
								$order_date                                = $order_date->date_i18n( 'D M j, Y' );
								$order_detail_by_order_id['order_date']    = $order_date;
								$order_detail_by_order_id['payment_mode']  = $order->get_payment_method();
								$order_detail_by_order_id['payment_title'] = $order->get_payment_method_title();
								$order_detail_by_order_id['cashPay']       = $cash_pay;
								$order_detail_by_order_id['cardPay']       = $card_pay;
								$order_detail_by_order_id['cashPay_html']  = wc_price( $cash_pay, $args );
								$order_detail_by_order_id['cardPay_html']  = wc_price( $card_pay, $args );
								$coupons                                   = $order->get_items( 'coupon' );

								if ( $coupons ) :

									foreach ( $coupons as $item_id => $item ) :

										$coupon[ esc_html( $item->get_code() ) ] = wc_price( $item->get_discount() );

								endforeach;

								endif;

								$order_detail_by_order_id['order_html'] = wc_price( $order->get_total(), $args );

								$totals = $order->get_order_item_totals();

								if ( $totals ) {
									foreach ( $totals as $key => $total ) {
										$label = $key;

										$label_list = array( 'cart_subtotal', 'order_total', 'shipping' );

										if ( in_array( $label, $label_list, true ) ) {

											if ( 'order_total' === $label ) {
												$order_detail_by_order_id['order_html'] = $total['value'];
											} else {
												$order_detail_by_order_id[ strtolower( $label ) ] = $total['value'];
											}
										}
									}
								}

								$args = $wpdb->get_var( $wpdb->prepare( "SELECT order_item_id FROM $table_name_order WHERE order_id=%d  AND order_item_name LIKE 'Pos Discount'", $order->get_id() ) );

								if ( null !== $args ) {
									$args = $wpdb->get_var( "SELECT meta_value FROM $table_name_ordermeta WHERE order_item_id = $args AND meta_key LIKE '_line_total'" );

									if ( null !== $args && 0 != $args ) {
										$order_detail_by_order_id['discount'] = wc_price( $args );
									}
								} else {
									$order_detail_by_order_id['discount'] = wc_price( 0, $args );
								}

								$order_detail_by_order_id['order_type'] = 'online';

								$order_detail_by_order_id['coupons'] = $coupon;

								if ( ! isset( $order_detail_by_order_id['tax_lines'] ) ) {
									$order_detail_by_order_id['tax_lines'] = '';
								}

								$order_detail_by_order_id['order_total']  = $order->get_total();
								$order_detail_by_order_id['total_refund'] = $order->get_total_refunded();

								$invoice_data['pos_user']       = $user;
								$invoice_data['pos_user_phone'] = get_user_meta( $pos_user, 'billing_phone', true );
								$invoice_data['outlet']         = $outlet;
								$invoice_data['pos_order']      = $order_detail_by_order_id;

							} else {
								$message = esc_html__( 'No outlet assigned to this POS user.', 'wc_pos' );
								?>
								<div class='notice notice-error is-dismissible'>
									<p><?php echo wp_kses_post( $message ); ?></p>
								</div>
								<?php
							}
						} else {
							$message = esc_html__( 'this POS Order does not belongs to the select POS user.', 'wc_pos' );
							?>
							<div class='notice notice-error is-dismissible'>
								<p><?php echo wp_kses_post( $message ); ?></p>
							</div>
							<?php
						}
					} else {
						$message = esc_html__( 'This is not a valid POS user.', 'wc_pos' );
						?>
						<div class='notice notice-error is-dismissible'>
							<p><?php echo wp_kses_post( $message ); ?></p>
						</div>
						<?php
					}
				} else {
					$message = esc_html__( 'Data provided in the fields are either empty or invalid.', 'wc_pos' );
					?>
					<div class='notice notice-error is-dismissible'>
						<p><?php echo wp_kses_post( $message ); ?></p>
					</div>
					<?php
				}
			}

			$invoice_html = '';

			if ( ! empty( $_GET['id'] ) && isset( $_GET['action'] ) && 'edit' === $_GET['action'] ) { // phpcs:ignore

				$invoice = $this->invoice_helper->wkwcpos_get_invoice_template( $_GET['id'] ); // phpcs:ignore
				if ( ! empty( $invoice ) ) {
					$invoice_html = $invoice['invoice_html'];
				}
			}

			wp_localize_script(
				'wkwcpos-invoice-script',
				'wkwcposInvoiceObj',
				array(
					'api_admin_ajax' => admin_url( 'admin-ajax.php' ),
					'pos_api_nonce'  => wp_create_nonce( 'api-ajaxnonce' ),
					'invoice_data'   => $invoice_data,
					'invoice_html'   => $invoice_html,
				)
			);

			?>
			<div class="wrap pos-invoice-wrap">
				<form method="get">
					<input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ); ?>" />
					<input type="hidden" name="tab" value="<?php echo esc_attr( $_GET['tab'] ); ?>" />
					<input type="hidden" name="action" value="<?php echo esc_attr( $_GET['action'] ); ?>" />
					<input type="hidden" name="id" value="<?php echo esc_attr( $_GET['id'] ); ?>" />
					<input type="text" name="pos_user" class="pos_input_css" value="<?php echo esc_attr( ! empty( $_GET['pos_user'] ) ? $_GET['pos_user'] : '' ); // phpcs:ignore ?>" placeholder="<?php esc_attr_e( 'Enter POS User ID', 'wc_pos' ); ?>" />
					<input type="text" name="pos_order" class="pos_input_css" value="<?php echo esc_attr( ! empty( $_GET['pos_order'] ) ? $_GET['pos_order'] : '' ); // phpcs:ignore ?>" placeholder="<?php esc_attr_e( 'Enter User\'s Outlet\'s Order ID', 'wc_pos' ); ?>" />
					<input type="submit" class="button" name="wkwcpos_invoice_submit" value="<?php esc_attr_e( 'Submit', 'wc_pos' ); ?>" />
				</form>
				<div id="wkwcpos-invoice-editor-app"></div>
			</div>
			<?php

		}

		/**
		 * Verify nonce.
		 */
		public function verify_nonce() {

			if ( isset( $_POST['wkwcpos_save_invoice'] ) ) {

				if ( ! isset( $_POST['pos_nonce_field'] ) || ! wp_verify_nonce( wc_clean( $_POST['pos_nonce_field'] ), 'pos_action' ) ) {

					print esc_html__( 'Sorry, your nonce did not verify.', 'wc_pos' );
					exit;

				} else {

					$post_data = wc_clean( $_POST );

					$this->invoice_helper->wkwcpos_save_invoice_template( $post_data );

				}
			}

		}

	}

}
