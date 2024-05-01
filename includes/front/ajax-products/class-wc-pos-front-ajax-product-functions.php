<?php
/**
 * This file handles all front end ajax callbacks.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Front\Ajax_Products;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Front_Ajax_Product_Functions' ) ) {
	/**
	 * Pos front ajax product functions class.
	 */
	class WC_Pos_Front_Ajax_Product_Functions {

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
		protected $current_user;

		/**
		 * Centralized inventory enabled/disabled.
		 *
		 * @var bool $centralized_inventory_enabled Centralized inventory enabled/disabled.
		 */
		protected $centralized_inventory_enabled = false;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			global $wpdb;

			$this->db = $wpdb;

			$this->current_user = get_current_user_id();

			$this->centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );
		}

		/**
		 * Get all products.
		 */
		public function get_all_products() {
			if ( check_ajax_referer( 'api-ajaxnonce', 'nonce', false ) ) {

				$paged = intval( $_POST['paged'] ); // phpcs:ignore

				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => 100,
					'paged'          => $paged,
				);

				$product_array = array();

				$products = get_posts( $args );

				$i = 0;

				foreach ( $products as $pkey => $pvalue ) {
					$variation_arr = array();

					$_product = wc_get_product( $pvalue->ID );

					$product_type = $_product->get_type();

					if ( 'variable' === $product_type ) {
						$product_variations = $_product->get_available_variations();

						$p_id = $_product->get_id();

						$product_array[ $i ]['id'] = $p_id;

						foreach ( $product_variations as $vkey => $vvalue ) {
							array_push(
								$variation_arr,
								array(
									'id'  => $vvalue['variation_id'],
									'qty' => $vvalue['max_qty'],
								)
							);
						}

						if ( ! empty( $variation_arr ) ) {
							$product_array[ $i ]['variations'] = $variation_arr;
						} else {
							$product_array[ $i ]['variations'] = false;
						}
						++$i;
					} elseif ( 'simple' === $product_type ) {
						$p_id = $_product->get_id();

						$product_array[ $i ] = array(
							'id'         => $p_id,
							'variations' => false,
							'qty'        => $_product->get_stock_quantity(),
						);
						++$i;
					}
				}

				wp_send_json( $product_array );

				wp_die();
			}
		}

	}
}
