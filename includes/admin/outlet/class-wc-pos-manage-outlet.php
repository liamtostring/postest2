<?php
/**
 * Manage pos outlet class.
 *
 * @package WooCommerce Point of Sale
 * @version 1.0.0
 */

namespace WKWC_POS\Includes\Admin\Outlet;

use WKWC_POS\Inc\WC_Pos_Errors;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Manage_Outlet' ) ) {
	/**
	 * WC Pos Manage Outlet class.
	 */
	class WC_Pos_Manage_Outlet extends WC_Pos_Errors {

		/**
		 * Outlet data.
		 *
		 * @var string|array $outlet_data Outlet data.
		 */
		protected $outlet_data = '';

		/**
		 * Pos outlet table name.
		 *
		 * @var string $table_name Pos outlet table name.
		 */
		protected $table_name = '';

		/**
		 * Pos outlet map table name.
		 *
		 * @var string $table_name2 Pos outlet map table name.
		 */
		protected $table_name2 = '';

		/**
		 * Current logged in user id.
		 *
		 * @var int $current_user Current logged in user id.
		 */
		protected $current_user = '';

		/**
		 * Database object.
		 *
		 * @var object  $wpdb Database object.
		 */
		protected $wpdb = '';

		/**
		 * Constructor of the class.
		 *
		 * @param string|array $data Outlet data.
		 */
		public function __construct( $data = '' ) {
			global $wpdb;

			$this->wpdb         = $wpdb;
			$this->current_user = get_current_user_id();
			$this->table_name   = $this->wpdb->prefix . 'woocommerce_pos_outlets';
			$this->table_name2  = $this->wpdb->prefix . 'woocommerce_pos_outlet_map';
			$this->outlet_data  = $data;
			$this->wk_wc_manage_pos_outlet_data();
		}

		/**
		 * Manage pos outlet data.
		 */
		public function wk_wc_manage_pos_outlet_data() {
			if ( ! empty( $this->outlet_data ) && check_admin_referer( 'pos_outlet_action', 'pos_outlet_nonce_field' ) ) {
				$pos_outlet = isset( $this->outlet_data['_pos_outlet_id'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_id'] ) ) : '';

				$pos_outlet_name = isset( $this->outlet_data['_pos_outlet_name'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_name'] ) ) : '';

				$pos_outlet_addr = isset( $this->outlet_data['_pos_outlet_addr'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_addr'] ) ) : '';

				$pos_outlet_status = isset( $this->outlet_data['_pos_outlet_status'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_status'] ) ) : '';

				$pos_outlet_city = isset( $this->outlet_data['_pos_outlet_city'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_city'] ) ) : '';

				$pos_outlet_state = isset( $this->outlet_data['_pos_outlet_state'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_state'] ) ) : '';

				$pos_outlet_postcode = isset( $this->outlet_data['_pos_outlet_postcode'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_postcode'] ) ) : '';

				$pos_outlet_country = isset( $this->outlet_data['_pos_outlet_country'] ) ? wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_country'] ) ) : '';

				$pos_outlet_payment = isset( $this->outlet_data['_pos_outlet_payment'] ) ? maybe_serialize( wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_payment'] ) ) ) : '';

				$pos_outlet_invoice = isset( $this->outlet_data['_pos_outlet_invoice'] ) ? maybe_serialize( wc_clean( wp_unslash( $this->outlet_data['_pos_outlet_invoice'] ) ) ) : '';

				if ( empty( $pos_outlet_name ) ) {
					$message = __( 'Outlet name is empty', 'wc_pos' );
					parent::set_error_code( 1 );
					parent::wk_wc_pos_print_notification( $message );
				}

				if ( empty( $pos_outlet_addr ) ) {
					$message = __( 'Outlet address is missing', 'wc_pos' );
					parent::set_error_code( 1 );
					parent::wk_wc_pos_print_notification( $message );
				}

				if ( $pos_outlet_status < 0 && $pos_outlet_status > 1 ) {
					$message = __( 'Outlet status is mandatory', 'wc_pos' );
					parent::set_error_code( 1 );
					parent::wk_wc_pos_print_notification( $message );
				}

				if ( parent::get_error_code() == 0 ) {
					if ( empty( $pos_outlet ) && isset( $this->outlet_data['save-outlet'] ) ) {
						$insert = $this->wpdb->insert(
							$this->table_name,
							apply_filters(
								'wkwcpos_modify_outlet_args',
								array(
									'outlet_name'     => $pos_outlet_name,
									'outlet_status'   => $pos_outlet_status,
									'outlet_address'  => $pos_outlet_addr,
									'outlet_city'     => $pos_outlet_city,
									'outlet_state'    => $pos_outlet_state,
									'outlet_postcode' => $pos_outlet_postcode,
									'outlet_country'  => $pos_outlet_country,
									'outlet_payment'  => $pos_outlet_payment,
									'outlet_invoice'  => $pos_outlet_invoice,
								),
								$this->outlet_data
							),
							apply_filters(
								'wkwcpos_modify_outlet_placeholder_args',
								array(
									'%s',
									'%d',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
								)
							)
						);

						if ( $insert ) {
							$outlet_id = $this->wpdb->insert_id;

							$this->wpdb->insert(
								$this->table_name2,
								array(
									'outlet_id' => $outlet_id,
									'user_id'   => 0,
								),
								array(
									'%d',
									'%d',
								)
							);
						}
						do_action( 'pos_manage_save_outlets_custom_fields', $this->outlet_data, $outlet_id );
						$message = __( 'Outlet created successfully', 'wc_pos' );
						parent::wk_wc_pos_print_notification( $message );
					} elseif ( isset( $this->outlet_data['update-outlet'] ) && ! empty( $pos_outlet ) ) {
						$this->wpdb->update(
							$this->table_name,
							apply_filters(
								'wkwcpos_modify_outlet_args',
								array(
									'outlet_name'     => $pos_outlet_name,
									'outlet_status'   => $pos_outlet_status,
									'outlet_address'  => $pos_outlet_addr,
									'outlet_city'     => $pos_outlet_city,
									'outlet_state'    => $pos_outlet_state,
									'outlet_postcode' => $pos_outlet_postcode,
									'outlet_country'  => $pos_outlet_country,
									'outlet_payment'  => $pos_outlet_payment,
									'outlet_invoice'  => $pos_outlet_invoice,
								),
								$this->outlet_data
							),
							array(
								'id' => intval( $this->outlet_data['_pos_outlet_id'] ),
							),
							apply_filters(
								'wkwcpos_modify_outlet_placeholder_args',
								array(
									'%s',
									'%d',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
								)
							),
							array(
								'%d',
							)
						);

						do_action( 'pos_manage_save_outlets_custom_fields', $this->outlet_data, $this->outlet_data['_pos_outlet_id'] );
						$message = __( 'Outlet updated successfully', 'wc_pos' );
						parent::wk_wc_pos_print_notification( $message );
					}
				} else {
					$message = __( 'Please fill up all the required fields ', 'wc_pos' );
					parent::wk_wc_pos_print_notification( $message );
				}
			}
		}
	}
}
