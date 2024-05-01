<?php
/**
 * POS low in stock report admin class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version 2.1.0
 */

namespace WKWC_POS\Templates\Admin\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * POS low in stock report admin class.
 */
class WC_Pos_Report_Low_In_Stock extends WC_Report_Stock {

	/**
	 * No items found text.
	 */
	public function no_items() {
		_e( 'No low in stock products found.', 'wc_pos' );
	}

	/**
	 * Get products matching stock criteria.
	 *
	 * @param int $current_page Current page.
	 * @param int $per_page Item per page.
	 * @param int $outlet_id Outlet id.
	 */
	public function get_items( $current_page, $per_page, $outlet_id ) {
		global $wpdb;

		$this->max_items = 0;
		$this->items     = array();

		$outlet_id = ! empty( $outlet_id ) && intval( $outlet_id ) > 0 ? intval( $outlet_id ) : 0;

		$stock = absint( max( get_option( '_pos_low_stock_warn' ), 0 ) );

		$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

		$offset = ( $current_page - 1 ) * $per_page;

		$product_map = $wpdb->prefix . 'woocommerce_pos_outlet_product_map';

		$query = "SELECT posts.ID AS id, posts.post_parent AS parent,  meta1.meta_value AS manage_stock, meta2.meta_value AS stock_status, meta3.meta_value AS stock FROM $wpdb->posts AS posts,
				$wpdb->postmeta as meta1,
				$wpdb->postmeta as meta2,
				$wpdb->postmeta as meta3,
				$product_map as mapped
				WHERE posts.ID = meta1.post_id AND
				posts.ID = meta2.post_id AND
				posts.ID = meta3.post_id AND
				posts.post_type IN ('product','product_variation') AND
				posts.post_status = 'publish' AND
				meta1.meta_key = '_manage_stock'";

		if ( $centralized_inventory_enabled ) {

			$query .= " AND ( meta1.meta_value = 'yes' AND meta3.meta_key = '_stock' AND meta3.meta_value > 0 AND meta3.meta_value <= $stock)";

		} else {
			$query .= ' AND posts.ID = mapped.product_id';
			$query .= " AND mapped.outlet_id = $outlet_id";
			$query .= " AND mapped.pos_stock > 0 AND mapped.pos_stock <= $stock";
		}

		$query .= " GROUP BY posts.ID ORDER BY CAST(meta1.meta_value AS SIGNED) DESC LIMIT $offset, $per_page";

		$this->items = $wpdb->get_results( $query );

		$this->max_items = count( $this->items );

	}
}
