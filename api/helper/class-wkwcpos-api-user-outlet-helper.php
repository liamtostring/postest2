<?php
/**
 * WooCommerce POS API setup.
 *
 * @version    1.0.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Api\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * POS User Outlet Handler.
 *
 * @class WKWCPOS_API_User_Outlet_Helper
 */
class WKWCPOS_API_User_Outlet_Helper {

	/**
	 * Database object.
	 *
	 * @var object $db Database object.
	 */
	public $db = '';

	/**
	 * Mab table name.
	 *
	 * @var string $map_table Map table name.
	 */
	public $map_table = '';

	/**
	 * Outlet table name.
	 *
	 * @var string $outlet_table Outlet table name.
	 */
	public $outlet_table = '';

	/**
	 * Product table name.
	 *
	 * @var string $product_table Product table name.
	 */
	public $product_table = '';

	/**
	 * Posts table name.
	 *
	 * @var string $posts_table Posts table name.
	 */
	public $posts_table = '';

	/**
	 * Is centralized inventory enabled.
	 *
	 * @var bool $centralized_inventory_enabled Is centralized inventory enabled.
	 */
	public $centralized_inventory_enabled = false;

	/**
	 * Constructor of the class.
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;

		$this->map_table     = $this->db->prefix . 'woocommerce_pos_outlet_map';
		$this->outlet_table  = $this->db->prefix . 'woocommerce_pos_outlets';
		$this->product_table = $this->db->prefix . 'woocommerce_pos_outlet_product_map';
		$this->posts_table   = $this->db->prefix . 'posts';

		$this->centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

	}

	/**
	 * Get pos user outlet with status.
	 *
	 * @param int $puser_id Pos user id.
	 *
	 * @return array Enable outlet ids.
	 */
	public function _get_pos_user_outlet_with_status( $puser_id ) {
		$outlet_id = $this->db->get_var( $this->db->prepare( "select map.outlet_id from $this->map_table as map JOIN $this->outlet_table  as outlet ON outlet.id=map.outlet_id where map.user_id=%d AND outlet.outlet_status=0", $puser_id ) );

		return apply_filters( 'wkwcpos_modify_enabled_outlet_id_for_pos_user', $outlet_id, $puser_id );

	}

	/**
	 * Get pos product stock.
	 *
	 * @param int $product_id Product id.
	 * @param int $outlet_id Outlet id.
	 *
	 * @return int Pos product stock.
	 */
	public function get_pos_product_stock( $product_id, $outlet_id = '' ) {

		$result = $this->db->get_var( $this->db->prepare( "SELECT pos_stock FROM $this->product_table WHERE product_id =%d and outlet_id=%d", $product_id, $outlet_id ) );

		return apply_filters( 'wkwcpos_modify_product_pos_stock_by_product_and_outlet_id', $result, $product_id, $outlet_id );

	}

	/**
	 * Get product category.
	 *
	 * @param int $pro_id Product id.
	 *
	 * @return array $product_cat_id Product category id.
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

		if ( 0 !== (int) $parent_id ) {
			$arr_ids[] = $parent_id;

			$this->check_parent( $arr_ids, $parent_id );
		}

		return $arr_ids;
	}

	/**
	 * Update outlet product stock.
	 *
	 * @param int $product_id Product id.
	 * @param int $outlet_id Outlet id.
	 * @param int $product_qty Product quantity.
	 */
	public function update_outlet_product_stock( $product_id, $outlet_id, $product_qty ) {

		$pos_stock = $this->get_pos_product_stock( $product_id, $outlet_id );

		if ( $pos_stock ) {
			$new_pos_stock = abs( $pos_stock ) - abs( $product_qty );
		} else {
			$new_pos_stock = '';
		}
		if ( apply_filters( 'wkwcpos_reduce_pos_stock_from_outlet', true, $product_id, $outlet_id, $product_qty ) ) {

			$this->db->update(
				$this->product_table,
				array(
					'pos_stock' => $new_pos_stock,
				),
				array(
					'product_id' => $product_id,
					'outlet_id'  => $outlet_id,
				)
			);
		}

	}

	/**
	 * Get outlet details.
	 *
	 * @param int $outlet_id Outlet id.
	 *
	 * @return array|object $response Outlet details.
	 */
	public function _get_pos_outlet( $outlet_id ) {

		$response = $this->db->get_row( "select * from $this->outlet_table where id=" . $outlet_id );

		return apply_filters( 'wkwcpos_modify_outlet_by_outlet_id', $response, $outlet_id );
	}

	/**
	 * Get outlet products.
	 *
	 * @param int $outlet_id Outlet id.
	 *
	 * @return array|object $res Outlet product id.
	 */
	public function get_pos_user_outlet_products( $outlet_id ) {

		if ( ! $this->centralized_inventory_enabled ) {
			$res = $this->db->get_results( $this->db->prepare( "SELECT outlet_product_map.product_id from $this->product_table as outlet_product_map JOIN $this->posts_table as posts ON outlet_product_map.product_id=posts.ID WHERE outlet_product_map.pos_status='enabled' AND outlet_product_map.pos_stock > 0 AND outlet_product_map.outlet_id=%d AND outlet_product_map.product_id=posts.ID AND posts.post_status=%s", $outlet_id, 'publish' ) );

		} else {
			$res = $this->db->get_results( $this->db->prepare( "SELECT outlet_product_map.product_id from $this->product_table as outlet_product_map JOIN $this->posts_table as posts ON outlet_product_map.product_id=posts.ID WHERE outlet_product_map.pos_status='enabled' AND outlet_product_map.outlet_id=%d AND outlet_product_map.product_id=posts.ID AND posts.post_status=%s", $outlet_id, 'publish' ) );

		}

		$res = apply_filters( 'wkwcpos_modify_outlet_product_ids_by_outlet_id', $res, $outlet_id );

		if ( ! empty( $res ) ) {
			return $res;
		} else {
			return '';
		}
	}
}
