<?php
/**
 * This file handles all front end actions.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Index_Db' ) ) {

	/**
	 * Pos indexed db class.
	 */
	class WC_Pos_Index_Db {
		/**
		 * Pos indexed db tables method.
		 *
		 * @return array $index_db Indexed db(POS) tables.
		 */
		public function wc_pos_index_db_tables() {

			$index_db = array(
				'pos_temp'         => 'id',

				'pos_currency'     => '++id, shortcode, symbol, code, is_default',

				'pos_customers'    => 'id,first_name,last_name,email,username,billing,shipping,avatar_url,is_true',

				'pos_orders'       => 'id,order_id,balance,cart_subtotal,coupons,total_refund ,order_date,email,billing,discount,currency,order_total,order_html,order_notes,products,payment_mode,tax_lines,tendered,order_type,cashPay,cardPay,cashPay_html, cardPay_html, offline_id',

				'pos_cart'         => 'id,cart_id,cart',

				'pos_sale'         => 'id,opening_balance,closing_balance,drawer_note,card_sale,cash_sale,date,
        user_card, user_cash',

				'pos_discount'     => 'id,cart_id, discount',

				'pos_coupon'       => 'id,cart_id,coupon',

				'pos_holds'        => 'id,note,date,time,cart_id',

				'pos_products'     => 'product_id,sku,categories,price,special,stock,tax,tax_label,title,variations,price_html,type',

				'pos_remove_id'    => 'id',

				'pos_tax'          => 'id,compound,label,rate,shipping',

				'pos_invoice'      => 'id, invoice_html',

				'pos_categories'   => '++id, cat_id, name',

				'pos_current_cart' => 'id, cart_id',
			);

			$index_db = apply_filters( 'wc_pos_modify_index_db_tables', $index_db );

			return $index_db;
		}

	}

}

