<?php
/**
 * This file handles all front end ajax callbacks.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Front\Ajax_Outlets;

use WKWC_POS\Helper\Outlet\Product\Outlet_Product_Helper;
use WKWC_POS\Helper\Outlet\Importer\WKWCPOS_Outlet_Importer_Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Front_Ajax_Outlet_Functions' ) ) {

	/**
	 * Fron ajax manage outlet function class.
	 */
	class WC_Pos_Front_Ajax_Outlet_Functions {

		/**
		 * WordPress database object.
		 *
		 * @var object $db WordPress database object.
		 */
		protected $db;

		/**
		 * Current logged in user id.
		 *
		 * @var int $current_user Current logged in user id.
		 */
		protected $current_user = '';

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			global $wpdb;

			$this->db           = $wpdb;
			$this->current_user = get_current_user_id();
		}

		/**
		 * Update pos outlet stock.
		 */
		public function update_pos_outlet_stock() {

			$post_data = wc_clean( $_POST ); // phpcs:ignore

			$product_id = intval( $post_data['product_id'] );
			$outlet_id  = intval( $post_data['outlet_id'] );
			$stock      = intval( $post_data['stock'] );

			$new_arr        = array();
			$total_stock    = 0;
			$table_name_map = $this->db->prefix . 'woocommerce_pos_outlet_product_map';

			if ( ! empty( $product_id ) && ! empty( $outlet_id ) ) {

				$_product = wc_get_product( $product_id );

				if ( $_product->get_type() == 'variation' ) {
					$master_stock = get_post_meta( $product_id, '_pos_variation_master_stock', true );
				} else {
					$master_stock = get_post_meta( $product_id, '_pos_master_stock', true );
				}

				$outlet_product_helper = new Outlet_Product_Helper();

				$total_stock = $outlet_product_helper->get_product_total_stock( $product_id );

				$res_out = $outlet_product_helper->get_pos_product_stock( $product_id, $outlet_id );

				if ( ! empty( $total_stock ) ) {
					$total_stock = wp_list_pluck( $total_stock, 'pos_stock' );
					$total_stock = array_sum( $total_stock );
				} else {
					$total_stock = 0;
				}

				if ( '' != $res_out ) {
					$assigned = $res_out;

					$total_stock = $total_stock - $assigned;
				}

				$remaining = intval( $master_stock ) - ( $total_stock + $stock );

				$remaining = apply_filters( 'wkwcpos_get_warehouse_stock', $remaining, $product_id, $outlet_id );

				if ( intval( $remaining ) < 0 ) {
					$new_arr['err'] = __( 'POS Stock is not available', 'wc_pos' );
				} else {
					if ( '' != $res_out ) {
						if ( intval( $stock ) ) {
							$res = $this->db->update(
								$table_name_map,
								array(
									'pos_stock' => $stock,
								),
								array(
									'product_id' => $product_id,
									'outlet_id'  => $outlet_id,
								)
							);

							do_action( 'wkwcpos_update_warehouse_stock', $product_id, $outlet_id, $remaining - $stock );

							if ( $res ) {
								$new_arr['msg'] = __( 'POS Stock for product updated', 'wc_pos' );
							}
						} elseif ( '' == $stock ) {
							$res = $this->db->update(
								$table_name_map,
								array(
									'pos_stock'  => $stock,
									'pos_status' => 'disabled',
								),
								array(
									'product_id' => $product_id,
									'outlet_id'  => $outlet_id,
								)
							);

							do_action( 'wkwcpos_update_warehouse_stock', $product_id, $outlet_id, $remaining - $stock );

							if ( $res ) {
								$new_arr['msg'] = __( 'POS Stock for product updated', 'wc_pos' );
							}
						}
					} else {
						$res = $this->db->insert(
							$table_name_map,
							array(
								'pos_stock'  => $stock,
								'product_id' => $product_id,
								'outlet_id'  => $outlet_id,
								'pos_status' => 'enabled',
							)
						);

						do_action( 'wkwcpos_update_warehouse_stock', $product_id, $outlet_id, $remaining - $stock );

						if ( $res ) {
							$new_arr['msg'] = __( 'POS Stock for product updated', 'wc_pos' );
						}
					}
				}
			}

			wp_send_json( $new_arr );

			wp_die();
		}

		/**
		 * Sync all variable products.
		 */
		public function wkwcpos_sync_all_variable_product() {
			if ( check_ajax_referer( 'api-ajaxnonce', 'nonce', false ) ) {
				global $wpdb;
				$outlet_id   = $_POST['outlet_id']; // phpcs:ignore
				$product_ids = $wpdb->get_results( $wpdb->prepare( "SELECT product_id from {$wpdb->prefix}woocommerce_pos_outlet_product_map where outlet_id = %d ", $outlet_id ) );

				foreach ( $product_ids as $p_data ) {
					$product_id = $p_data->product_id;
					$type       = get_post_type( $product_id );
					if ( 'product' === $type || 'product_variation' === $type ) {
						$product = wc_get_product( $product_id );

						if ( 'variable' === $product->get_type() ) {
							$wpdb->update(
								$wpdb->prefix . 'woocommerce_pos_outlet_product_map',
								array(
									'pos_stock' => 1000000,
								),
								array(
									'outlet_id'  => (int) $outlet_id,
									'pos_status' => 'enabled',
									'product_id' => (int) $product_id,
								),
								array(
									'%d',
								),
								array(
									'%d',
									'%s',
									'%d',
								)
							);
						}
					}
				}
				$return = array(
					'status'  => 'success',
					'message' => esc_html__( 'Product Sync successfully', 'wc_pos' ),
				);
				wp_send_json( $return );
				wp_die();
			}
			$return = array(
				'status'  => 'error',
				'message' => esc_html__( 'Nonce not verified', 'wc_pos' ),
			);
			wp_send_json( $return );
			wp_die();
		}

		/**
		 * Assign pos master stock.
		 */
		public function assign_pos_master_stock() {
			if ( check_ajax_referer( 'api-ajaxnonce', 'nonce', false ) ) {

				$table_name = $this->db->prefix . 'woocommerce_pos_outlet_product_map';

				$post_data = wc_clean( $_POST );

				$count = 0;

				$products = $post_data['products'];

				$percent = $post_data['percent'];

				$products = json_decode( stripslashes( $products ) );

				$outlet_product_helper = new Outlet_Product_Helper();

				$enabled = 'enabled';

				$outlets_list = $outlet_product_helper->get_all_outlet_id();

				foreach ( $products as $pkey => $pvalue ) {
					$product_id = $pvalue->id;

					if ( false === $pvalue->variations ) {
						$quantity = $percent;

						$master_stock = 0;

						foreach ( $outlets_list as $id ) {
							$res = $this->db->get_results( $this->db->prepare( "SELECT pos_stock FROM $table_name WHERE outlet_id = %d and product_id = %d ", $id, $product_id ) );

							if ( isset( $res[0]->pos_stock ) && 0 === (int) $res[0]->pos_stock ) {
								$check_res = true;
							} else {
								$check_res = isset( $res[0]->pos_stock ) ? true : false;
							}

							if ( ! $check_res ) {

								$res2 = $this->db->query( $this->db->prepare( "INSERT INTO $table_name ( pos_status, pos_stock, outlet_id, product_id) VALUES ( %s, %d, %d, %d)", $enabled, $quantity, $id, $product_id ) );

								if ( $res2 ) {
									$master_stock = $master_stock + $quantity;
								}
							} else {

								$res3 = $this->db->query( $this->db->prepare( "UPDATE $table_name SET pos_stock = %d  WHERE  outlet_id = %d AND product_id = %d", (int) $res[0]->pos_stock + $quantity, $id, $product_id ) );

								if ( $res3 ) {
									$master_stock = $master_stock + $quantity + (int) $res[0]->pos_stock;
								}
							}
						}

						update_post_meta( $product_id, '_pos_master_stock', $master_stock );

						++$count;
					} else {
						$product_variations = $pvalue->variations;

						foreach ( $outlets_list as $id ) {
							$res = $this->db->get_results( $this->db->prepare( "SELECT pos_stock FROM $table_name WHERE outlet_id = %d and product_id = %d ", $id, $product_id ) );
							if ( isset( $res[0]->pos_stock ) && 0 === (int) $res[0]->pos_stock ) {
								$check_res = true;
							} else {
								$check_res = isset( $res[0]->pos_stock ) ? true : false;
							}
							if ( ! $check_res ) {
								$res2 = $this->db->query( $this->db->prepare( "INSERT into $table_name ( pos_status, pos_stock, outlet_id, product_id) VALUES ( %s, %d, %d, %d)", $enabled, 0, $id, $product_id ) );
							}
						}

						foreach ( $product_variations as $vkey => $vvalue ) {
							$quantity = intval( $vvalue->qty );

							$master_stock = intval( $quantity );

							$quantity = $percent;

							$var_product_id = $vvalue->id;

							foreach ( $outlets_list as $id ) {
								$res = $this->db->get_results( $this->db->prepare( "SELECT pos_stock FROM $table_name WHERE outlet_id = %d and product_id = %d ", $id, $var_product_id ) );
								if ( isset( $res[0]->pos_stock ) && 0 === (int) $res[0]->pos_stock ) {
									$check_res = true;
								} else {
									$check_res = isset( $res[0]->pos_stock ) ? true : false;
								}
								if ( ! $check_res ) {
									$res2 = $this->db->query( $this->db->prepare( "INSERT into $table_name ( pos_status, pos_stock, outlet_id, product_id) VALUES ( %s, %d, %d, %d)", $enabled, $quantity, $id, $var_product_id ) );

									if ( $res2 ) {
										$master_stock = $master_stock + $quantity;
									}
								} else {
									$res3 = $this->db->query( $this->db->prepare( "UPDATE $table_name SET pos_stock = %d  WHERE  outlet_id = %d AND product_id = %d", $res[0]->pos_stock + $quantity, $id, $var_product_id ) );

									if ( $res3 ) {
										$master_stock = $master_stock + $quantity + intval( $res[0]->pos_stock );
									}
								}
							}

							update_post_meta( $var_product_id, '_pos_variation_master_stock', $master_stock );
						}

						++$count;
					}
				}

				wp_send_json( $count );

				wp_die();
			}
		}

		/**
		 * Return true if WooCommerce imports are allowed for current user, false otherwise.
		 *
		 * @return bool Whether current user can perform imports.
		 */
		protected function import_allowed() {
			return current_user_can( 'edit_products' ) && current_user_can( 'import' );
		}

		/**
		 * Ajax callback for importing one batch of products from a CSV.
		 */
		public function wkcwpos_do_ajax_outlet_import() {

			check_ajax_referer( 'wkwcpos-outlet-import', 'security' );

			if ( ! $this->import_allowed() || ! isset( $_POST['file'] ) ) { // PHPCS: input var ok.
				wp_send_json_error( array( 'message' => __( 'Insufficient privileges to import products.', 'wc_pos' ) ) );
			}

			$file   = wc_clean( wp_unslash( $_POST['file'] ) ); // PHPCS: input var ok.
			$params = array(
				'delimiter'       => ! empty( $_POST['delimiter'] ) ? wc_clean( wp_unslash( $_POST['delimiter'] ) ) : ',', // PHPCS: input var ok.
				'start_pos'       => isset( $_POST['position'] ) ? absint( $_POST['position'] ) : 0, // PHPCS: input var ok.
				'mapping'         => isset( $_POST['mapping'] ) ? (array) wc_clean( wp_unslash( $_POST['mapping'] ) ) : array(), // PHPCS: input var ok.
				'update_existing' => isset( $_POST['update_existing'] ) ? (bool) $_POST['update_existing'] : false, // PHPCS: input var ok.
				'lines'           => apply_filters( 'wkwcpos_outlet_import_batch_size', 30 ),
				'parse'           => true,
			);

			// Log failures.
			if ( 0 !== $params['start_pos'] ) {
				$error_log = array_filter( (array) get_user_option( 'wkwcpos_outlet_import_error_log' ) );
			} else {
				$error_log = array();
			}

			$importer         = WKWCPOS_Outlet_Importer_Controller::get_importer( $file, $params );
			$results          = $importer->import();
			$percent_complete = $importer->get_percent_complete();
			$error_log        = array_merge( $error_log, $results['failed'], $results['skipped'] );

			update_user_option( get_current_user_id(), 'wkwcpos_outlet_import_error_log', $error_log );

			if ( 100 === $percent_complete ) {

				// Send success.
				wp_send_json_success(
					array(
						'position'   => 'done',
						'percentage' => 100,
						'url'        => add_query_arg( array( '_wpnonce' => wp_create_nonce( 'wkwcpos-csv-importer' ) ), admin_url( 'admin.php?page=pos-outlets&action=outlet-import&step=done' ) ),
						'imported'   => count( $results['imported'] ),
						'failed'     => count( $results['failed'] ),
						'updated'    => count( $results['updated'] ),
						'skipped'    => count( $results['skipped'] ),
					)
				);
			} else {
				wp_send_json_success(
					array(
						'position'   => $importer->get_file_position(),
						'percentage' => $percent_complete,
						'imported'   => count( $results['imported'] ),
						'failed'     => count( $results['failed'] ),
						'updated'    => count( $results['updated'] ),
						'skipped'    => count( $results['skipped'] ),
					)
				);
			}
		}
	}
}
