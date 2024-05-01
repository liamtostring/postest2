<?php
/**
 * This file handles helper outlet product data.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\Outlet\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Outlet_Product_Helper' ) ) {
	/**
	 * Class for including core outlet product data.
	 */
	class Outlet_Product_Helper {

		/**
		 * Posts table name.
		 *
		 * @var string $table_name_post Posts table name.
		 */
		public $table_name_post = '';

		/**
		 * Pos outlet product map table name.
		 *
		 * @var string $table_name_map Outlet product map table name.
		 */
		public $table_name_map = '';

		/**
		 * Outlet id.
		 *
		 * @var int $outlet_id Outlet id.
		 */
		public $outlet_id = '';

		/**
		 * Database object.
		 *
		 * @var object $db Database object.
		 */
		public $db = '';

		/**
		 * Pos outlet table name.
		 *
		 * @var string $table_name_outlet Pos outlet table name.
		 */
		public $table_name_outlet = '';

		/**
		 * Class constructor.
		 *
		 * @param int $outlet_id Outlet id.
		 */
		public function __construct( $outlet_id = '' ) {
			global $wpdb;

			$this->db                = $wpdb;
			$this->table_name_post   = $this->db->prefix . 'posts';
			$this->table_name_map    = $this->db->prefix . 'woocommerce_pos_outlet_product_map';
			$this->table_name_outlet = $this->db->prefix . 'woocommerce_pos_outlets';

			if ( ! empty( $outlet_id ) ) {
				$this->outlet_id = $outlet_id;
			}
		}

		/**
		 * Get outlet product count.
		 *
		 * @param string $search_query Search string for query.
		 *
		 * @return int $response Outlet product count.
		 */
		public function get_count_outlet_product( $search_query = '' ) {
			$response = 0;

			$response = $this->db->get_var( "SELECT count(ID) FROM $this->table_name_post WHERE post_type='product' AND post_status='publish' AND post_title LIKE '%$search_query%'" );

			return apply_filters( 'wk_pos_wpml_get_count_outlet_product', $response, $search_query );
		}

		/**
		 * Get all product lists by vendor.
		 *
		 * @param int $off Offset Offset for limit.
		 * @param int $perpage Product lists perpage.
		 *
		 * @return array $result Product lists.
		 */
		public function get_all_products_by_vendor( $off, $perpage ) {
			$result = array();

			$result = $this->db->get_results( "SELECT ID FROM $this->table_name_post WHERE post_type='product' AND post_status='publish' LIMIT $perpage OFFSET $off", ARRAY_A );

			$args = array(
				'off'     => $off,
				'perpage' => $perpage,
			);

			return apply_filters( 'wk_pos_wpml_get_all_products_by_vendor', $result, $args );
		}

		/**
		 * Get product lists by search query.
		 *
		 * @param string $search_query Search string for query.
		 * @param int    $off OFfset for limit.
		 * @param int    $perpage  Product lists perpage.
		 *
		 * @return array $result Product lists.
		 */
		public function get_all_products_by_search( $search_query, $off, $perpage ) {
			$result = array();

			$result = $this->db->get_results( "SELECT ID FROM $this->table_name_post WHERE post_type='product' AND post_status='publish' AND post_title like '%$search_query%' LIMIT $perpage OFFSET $off", ARRAY_A );

			$args = array(
				'search_query' => $search_query,
				'off'          => $off,
				'perpage'      => $perpage,
			);

			return apply_filters( 'wk_pos_wpml_get_all_products_by_search', $result, $args );
		}

		/**
		 * Get product status by product id.
		 *
		 * @param int $pro_id Product id.
		 * @param int $outlet_id Outlet id.
		 *
		 * @return string $pos_status Product status.
		 */
		public function get_pos_product_status( $pro_id, $outlet_id ) {
			$pos_status = $this->db->get_var( $this->db->prepare( "SELECT pos_status from $this->table_name_map where product_id=%d AND outlet_id=%d", $pro_id, $outlet_id ) );

			$pos_status = apply_filters( 'wkwcpos_modify_outlet_product_status_by_outlet_and_product_id', $pos_status, $outlet_id, $pro_id );

			return $pos_status;
		}

		/**
		 * Save pos product status by product and outlet id.
		 *
		 * @param int $pro_id Product id.
		 * @param int $outlet_id Outlet id.
		 *
		 * @return bool true on success otherwise false.
		 */
		public function save_pos_product_status( $pro_id, $outlet_id ) {

			$product = wc_get_product( $pro_id );

			if ( $product->get_type() == 'variable' ) {
				$this->db->insert(
					$this->table_name_map,
					array(
						'outlet_id'  => $outlet_id,
						'product_id' => $pro_id,
						'pos_status' => 'enabled',
						'pos_stock'  => 1000000,
					)
				);
			} else {

				$this->db->insert(
					$this->table_name_map,
					array(
						'outlet_id'  => $outlet_id,
						'product_id' => $pro_id,
						'pos_status' => 'enabled',
						'pos_stock'  => 0,
					)
				);
			}

			if ( ! empty( $this->db->insert_id ) ) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Update pos product status.
		 *
		 * @param int    $pro_id Product id.
		 * @param int    $outlet_id Outlet id.
		 * @param string $st Outlet status.
		 *
		 * @return bool true on success otherwise false.
		 */
		public function update_pos_product_status( $pro_id, $outlet_id, $st ) {
			$pos_outlet_status = $st;

			$product = wc_get_product( $pro_id );

			if ( $product->get_type() == 'variable' ) {
				$res = $this->db->update(
					$this->table_name_map,
					array(
						'pos_status' => $pos_outlet_status,
						'pos_stock'  => 1000000,
					),
					array(
						'product_id' => $pro_id,
						'outlet_id'  => $outlet_id,
					)
				);
			} else {

				if ( 'disabled' === $pos_outlet_status ) {
					$res = $this->db->update(
						$this->table_name_map,
						array(
							'pos_status' => $pos_outlet_status,
						),
						array(
							'product_id' => $pro_id,
							'outlet_id'  => $outlet_id,
						)
					);

					if ( ! empty( $res ) ) {
						return true;
					} else {
						return false;
					}
				} elseif ( 'enabled' === $pos_outlet_status ) {
					$res = $this->db->update(
						$this->table_name_map,
						array(
							'pos_status' => $pos_outlet_status,
						),
						array(
							'product_id' => $pro_id,
							'outlet_id'  => $outlet_id,
						)
					);

					if ( ! empty( $res ) ) {
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		}

		/**
		 * Get all outlet id.
		 *
		 * @return array $outlet_arr All outlet id.
		 */
		public function get_all_outlet_id() {
			$outlet_arr = array();

			$res_out = $this->db->get_results( "select id,outlet_name from $this->table_name_outlet" );

			if ( ! empty( $res_out ) ) {
				foreach ( $res_out as $key => $value ) {
					$outlet_arr[ $value->id ] = $value->id;
				}
			}

			$outlet_arr = apply_filters( 'wkwcpos_modify_outlet_id_data', $outlet_arr );

			return $outlet_arr;
		}

		/**
		 * Get pos product master stock by product id.
		 *
		 * @param int $pro_id Product id.
		 *
		 * @return int $rp Product master stock.
		 */
		public function get_pos_product_master_stock( $pro_id ) {
			$rp = get_post_meta( $pro_id, '_pos_master_stock', true );

			return apply_filters( 'wkwcpos_modify_product_master_stock_by_product_id', $rp, $pro_id );
		}

		/**
		 * Get product total stock.
		 *
		 * @param int $product_id Product id.
		 * @return array $result Product total stock.
		 */
		public function get_product_total_stock( $product_id ) {
			$result = $this->db->get_results( $this->db->prepare( "SELECT pos_stock FROM $this->table_name_map WHERE product_id =%d", $product_id ) );

			return apply_filters( 'wkwcpos_modify_product_total_pos_stock_by_product_id', $result, $product_id );
		}

		/**
		 * Check parent category.
		 *
		 * @param array $arr_ids Category ids.
		 * @param int   $cat_id Category id.
		 *
		 * @return array $arr_ids Category ids.
		 */
		public function check_parent( $arr_ids, $cat_id ) {
			$cat = get_term( $cat_id );

			$parent_id = $cat->parent;

			if ( 0 !== $parent_id ) {
				$arr_ids[] = $parent_id;

				$this->check_parent( $arr_ids, $parent_id );
			}

			return $arr_ids;
		}

		/**
		 * Get product category by product id.
		 *
		 * @param int $pro_id Product id.
		 *
		 * @return array $product_cat_id Product category ids.
		 */
		public function get_pos_product_cat( $pro_id ) {
			$product_cat_id = array();

			$terms = get_the_terms( $pro_id, 'product_cat' );

			if ( $terms ) {
				foreach ( $terms as $term ) {
					$product_cat_id[] = $term->term_id;

					if ( 0 !== $term->parent ) {
						$product_cat_id[] = $term->parent;

						$product_cat_id = $this->check_parent( $product_cat_id, $term->parent );
					}
				}
			}

			return apply_filters( 'wkwcpos_modify_product_category_ids_by_product_id', $product_cat_id, $pro_id );
		}

		/**
		 * Get pos product stock.
		 *
		 * @param int $product_id Product id.
		 * @param int $outlet_id Outlet id.
		 *
		 * @return int $result Pos stock.
		 */
		public function get_pos_product_stock( $product_id, $outlet_id = '' ) {
			if ( empty( $outlet_id ) ) {
				$outlet_id = $_GET['outlet_id']; //phpcs:ignore
			}

			$result = $this->db->get_var( $this->db->prepare( "SELECT pos_stock FROM $this->table_name_map WHERE product_id =%d and outlet_id=%d", $product_id, $outlet_id ) );

			return apply_filters( 'wkwcpos_modify_product_pos_stock_by_product_and_outlet_id', $result, $product_id, $outlet_id );
		}

		/**
		 * Get pos variable product master stock.
		 *
		 * @param int $pro_id Product id.
		 *
		 * @return int $rp product master stock.
		 */
		public function get_pos_variable_product_master_stock( $pro_id ) {
			$rp = get_post_meta( $pro_id, '_pos_variation_master_stock', true );

			return apply_filters( 'wkwcpos_modify_variation_master_stock_by_variation_id', $rp, $pro_id );
		}

		/**
		 * Get pos outlet name.
		 *
		 * @param int $outlet_id Outlet id.
		 *
		 * @return string $outlet_name Outlet name.
		 */
		public function get_pos_outlet_name( $outlet_id ) {
			$outlet_name = $this->db->get_var( $this->db->prepare( "SELECT outlet_name FROM $this->table_name_outlet WHERE id =%d", $outlet_id ) );

			return apply_filters( 'wkwcpos_modify_outlet_name_by_outlet_id', $outlet_name, $outlet_id );
		}

		/**
		 * Get total product stock.
		 *
		 * @param int $pro_id Product_id.
		 * @param int $outlet_id Outlet id.
		 *
		 * @return array $result Pos product stock.
		 */
		public function get_total_stock( $pro_id, $outlet_id = '' ) {
			$result = $this->db->get_results( $this->db->prepare( "SELECT SUM(pos_stock) AS stock FROM $this->table_name_map WHERE product_id =%d and outlet_id=%d", $pro_id, $outlet_id ), ARRAY_A );

			return apply_filters( 'wkwcpos_modify_product_total_pos_stock_by_product_and_outlet_id', $result, $pro_id, $outlet_id );
		}
	}
}
