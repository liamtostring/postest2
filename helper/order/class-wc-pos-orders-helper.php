<?php
/**
 * This file handles helper config class.
 *
 * @version 4.1.0
 *
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Orders_Helper' ) ) {
	/**
	 * Class for including pos Orders data.
	 */
	class WC_Pos_Orders_Helper implements Util\Order_Interface {

		/**
		 * Postmeta table name.
		 *
		 * @var string $table_name Postmeta table name.
		 */
		public $table_name = '';

		/**
		 * Post table name.
		 *
		 * @var string $table_name_post Post table name.
		 */
		public $table_name_post = '';

		/**
		 * Order status list.
		 *
		 * @var array|string $order_status_list Order status list.
		 */
		public $order_status_list = '';

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
		 */
		public function __construct() {
			global $wpdb;

			$this->db = $wpdb;

			$this->table_name = $wpdb->prefix . 'postmeta';

			$this->table_name_outlet = $this->db->prefix . 'woocommerce_pos_outlets';

			$this->table_name_post = $wpdb->prefix . 'posts';

			$order_status = array_keys( wc_get_order_statuses() );

			$this->order_status_list = implode(
				',',
				array_map(
					function ( $val ) {
						return sprintf( "'%s'", $val );
					},
					$order_status
				)
			);
		}

		/**
		 * Get all pos outlet name and its id.
		 *
		 * @return array $response Outlet name and id.
		 */
		public function pos_get_all_outlets_name_and_id() {
			$response = array();
			$response = $this->db->get_results( "Select id, outlet_name from $this->table_name_outlet", ARRAY_A );

			return apply_filters( 'wkwcpos_modify_all_outlets_names_for_filter_values', $response );
		}

		/**
		 * Get outlet name by outlet id.
		 *
		 * @param int $outlet_id Outlet id.
		 *
		 * @return string $response Outlet name.
		 */
		public function pos_get_outlet_name_by_id( $outlet_id ) {
			$response = '';
			$response = $this->db->get_var( $this->db->prepare( "Select outlet_name from $this->table_name_outlet WHERE id = %d", $outlet_id ) );

			return apply_filters( 'wkwcpos_modify_outlet_name_for_filter_values', $response );
		}

		/**
		 * Get orders count.
		 *
		 * @param string $search_query Search query.
		 * @param int    $outlet_id Outlet id.
		 *
		 * @return int $result Orders count.
		 */
		public function pos_get_all_order_by_search_count( $search_query = '', $outlet_id = '' ) {
			$search_query = apply_filters( 'wkwcpos_modify_search_query_for_getting_all_orders_count', $search_query );

			$outlet_id = apply_filters( 'wkwcpos_modify_outlet_id_for_getting_all_orders_count', $outlet_id );

			switch ( true ) {
				case ( ! empty( $search_query ) && ! empty( $outlet_id ) ):
					$query = $this->db->prepare(
						"SELECT count(*) FROM $this->table_name_post as posts , $this->table_name as postmeta  where posts.ID = postmeta.post_id AND
				 AND posts.post_status IN (" . $this->order_status_list . ") AND posts.post_type = 'shop_order' AND  postmeta.meta_key = '_wk_wc_pos_outlet' AND postmeta.meta_value = %d AND posts.ID=%d",
						$search_query,
						$outlet_id
					);
					break;
				case ( empty( $search_query ) && ! empty( $outlet_id ) ):
					$query = $this->db->prepare(
						"SELECT count(*) FROM $this->table_name_post as posts , $this->table_name as postmeta  where posts.ID = postmeta.post_id AND posts.post_status IN (" . $this->order_status_list . ") AND posts.post_type = 'shop_order' AND  postmeta.meta_key = '_wk_wc_pos_outlet' AND postmeta.meta_value = %s",
						$outlet_id
					);
					break;
				case ( ! empty( $search_query ) && empty( $outlet_id ) ):
					$query = $this->db->prepare( "SELECT count(*) FROM $this->table_name_post as posts JOIN $this->table_name as postmeta on posts.ID = postmeta.post_id where posts.post_status IN (" . $this->order_status_list . ") AND posts.post_type = 'shop_order' AND  postmeta.meta_key = '_wk_wc_pos_outlet' AND posts.ID=%d", $search_query );
					break;
				default:
					$query = "SELECT count(*) FROM $this->table_name_post as posts JOIN $this->table_name as postmeta on posts.ID = postmeta.post_id where posts.post_status IN (" . $this->order_status_list . ") AND posts.post_type = 'shop_order' AND  postmeta.meta_key = '_wk_wc_pos_outlet'";
					break;
			}

			$result = $this->db->get_var( $query );

			return apply_filters( 'wkwcpos_modify_pos_orders_count', $result, $search_query, $outlet_id );
		}

		/**
		 * Get all orders.
		 *
		 * @param int    $perpage Perpage order.
		 * @param int    $offset Offset.
		 * @param string $search Search order string.
		 * @param int    $outlet_id Outlet id.
		 *
		 * @return array $pos_orders Pos orders.
		 */
		public function pos_get_all_orders( $perpage, $offset, $search = '', $outlet_id = '' ) {
			$order_search = '';
			$args         = array(
				'post_type'      => 'shop_order',
				'meta_key'       => '_wk_wc_pos_outlet',
				'posts_per_page' => $perpage,
				'offset'         => $offset,
				'post_status'    => array_keys( wc_get_order_statuses() ),
			);

			switch ( true ) {
				case ( ! empty( $search ) && ! empty( $outlet_id ) ):
					$args['include']    = $search;
					$args['meta_value'] = $outlet_id;
					break;
				case ( ! empty( $search ) && empty( $outlet_id ) ):
					$args['include'] = $search;
					break;
				case ( empty( $search ) && ! empty( $outlet_id ) ):
					$args['meta_value'] = $outlet_id;
					break;
				default:
					break;
			}

			$pos_orders = get_posts( $args );

			return apply_filters( 'wkwcpos_modify_pos_orders', $pos_orders, $perpage, $offset, $search, $outlet_id );
		}

		/**
		 * Get prefixed order number.
		 *
		 * @param int    $order_number Order number.
		 * @param string $hash String( 'hash' | '' ) to return prefixed with hash.
		 *
		 * @return string $prefixed_order_number Prefixed order number.
		 */
		public function get_prefixed_order_number( $order_number, $hash = '' ) {

			$prefixed_order_number = $order_number;

			if ( ! empty( $order_number ) ) {

				$is_pos_order = get_post_meta( $order_number, '_wk_wc_pos_outlet' );

				if ( ! empty( $is_pos_order ) ) {

					$prefix = get_option( '_pos_order_id_prefix', '' );

					if ( ! empty( $prefix ) ) {
						$prefixed_order_number = $prefix . '' . $prefixed_order_number;
					}

					if ( 'hash' === $hash ) {
						$prefixed_order_number = '#' . $prefixed_order_number;
					}
				}
			}

			return apply_filters( 'wkwcpos_modify_prefixed_order_number', $prefixed_order_number, $order_number, $hash );

		}
	}
}
