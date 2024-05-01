<?php
/**
 * POS Report API.
 *
 * @package  WooCommerce Point Of Sale API.
 * @since    4.3.0
 */

namespace WKWC_POS\Api\Includes\Reports;

use WKWC_POS\Api\Helper\WKWCPOS_API_User_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * POS Report API.
 *
 * @class WKWCPOS_API_Reports.
 */
class WKWCPOS_API_Reports {

	/**
	 * Base Name.
	 *
	 * @var string the route base.
	 */
	public $base = '/get-reports';

	/**
	 * Namespace Name.
	 *
	 * @var string the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Database object.
	 *
	 * @var object $db Database object.
	 */
	protected $db = '';

	/**
	 * User outlet helper class object.
	 *
	 * @var object $helper User outlet helper class object.
	 */
	public $helper;

	/**
	 * Wc order table.
	 *
	 * @var string $wc_order_table Wc order table.
	 */
	public $wc_order_table;

	/**
	 * Wc order table.
	 *
	 * @var string $wc_order_itemmeta_table Wc order itemmeta table.
	 */
	public $wc_order_itemmeta_table;


	/**
	 * Constructor.
	 */
	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->helper = new WKWCPOS_API_User_Outlet_Helper();

		$this->wc_order_table          = $this->db->prefix . 'woocommerce_order_items';
		$this->wc_order_itemmeta_table = $this->db->prefix . 'woocommerce_order_itemmeta';

	}

	/**
	 * Get reports order detials for report.
	 *
	 * @param array $request Request object.
	 *
	 * @return array $order_details Order details for report.
	 */
	public function get_reports_order_details( $request ) {

		try {

			$order_details = array();

			$start_date = isset( $request['start_date'] ) ? $request['start_date'] : '';
			$end_date   = isset( $request['end_date'] ) ? $request['end_date'] : '';
			$user_id    = isset( $request['user_id'] ) ? (int) $request['user_id'] : '';
			$outlet_id  = isset( $request['outlet_id'] ) ? (int) $request['outlet_id'] : '';
			$is_user    = isset( $request['is_user'] ) ? 'admin' : 'posuser';

			$current_user_outlet_id = $this->helper->_get_pos_user_outlet_with_status( $user_id );

			$is_get_orders = false;

			$args = array(
				'post_type'      => 'shop_order',
				'meta_key'       => '_wk_wc_pos_outlet',
				'post_status'    => array_keys( wc_get_order_statuses() ),
				'posts_per_page' => -1,
				'order'          => 'asc',
			);

			if ( ! empty( $start_date ) && ! empty( $end_date ) ) {

				$date       = $this->get_date( $start_date, $end_date );
				$start_date = $date[0];
				$end_date   = $date[1];

				$args['date_query'] = array(
					array(
						'after'     => $start_date,
						'before'    => $end_date,
						'inclusive' => true,
					),
				);

				if ( 'posuser' === $is_user && ! empty( $outlet_id ) && ! empty( $user_id ) && (int) $current_user_outlet_id === (int) $outlet_id ) {

					$args['meta_value'] = $outlet_id;
					$is_get_orders      = true;

				} elseif ( 'admin' === $is_user ) {
					$is_get_orders = true;
				}
			}

			if ( $is_get_orders ) {

				$args = apply_filters( 'wkwcpos_modify_report_order_args', $args, $request );

				$query = new \WP_Query( $args );

				$pos_orders = $query->get_posts();

				foreach ( $pos_orders as $key => $pos_order ) {

					$order = wc_get_order( $pos_order->ID );

					$order_id                      = $order->get_id();
					$order_status                  = $order->get_status();
					$order_currency_code           = $order->get_currency();
					$order_currency_symbol         = get_woocommerce_currency_symbol( $order_currency_code );
					$order_total                   = $order->get_total();
					$order_tax_total               = $order->get_total_tax();
					$order_subtotal                = $order->get_subtotal();
					$order_total_discount          = $order->get_total_discount();
					$order_total_discount_tax      = $order->get_discount_tax();
					$order_total_refunded          = $order->get_total_refunded();
					$order_total_tax_refunded      = $order->get_total_tax_refunded();
					$order_remaining_refund_amount = $order->get_remaining_refund_amount();
					$order_shipping_total          = $order->get_shipping_total();
					$order_shipping_tax            = $order->get_shipping_tax();
					$created_date                  = $order->get_date_created();
					$order_date                    = $created_date->format( 'Y-m-d' );
					$pos_order_discount            = 0;
					$outlet_id                     = get_post_meta( $order->get_id(), '_wk_wc_pos_outlet', true );

					$total_taxes = array();

					$applied_taxes = $order->get_items( 'tax' );

					foreach ( $applied_taxes as $tax_item_id => $tax_item ) {

						$tax_percent = \WC_Tax::get_rate_percent( $tax_item->get_rate_id() );

						$total_taxes[] = array(
							'id'              => $tax_item->get_rate_id(),
							'code'            => $tax_item->get_rate_code(),
							'label'           => $tax_item->get_label(),
							'tax_name'        => $tax_item->get_name(),
							'amount'          => $tax_item->get_tax_total(),
							'shipping_amount' => $tax_item->get_shipping_tax_total(),
							'rate'            => $tax_percent,
						);
					}

					$args = $this->db->get_var( $this->db->prepare( "SELECT order_item_id FROM $this->wc_order_table WHERE order_id=%d  AND order_item_name LIKE 'Pos Discount'", $order->get_id() ) );

					if ( null !== $args ) {

						if ( 'yes' !== get_option( 'woocommerce_prices_include_tax' ) ) {

							$args = $this->db->get_var( "SELECT SUM(meta_value) FROM $this->wc_order_itemmeta_table WHERE order_item_id = $args AND ( meta_key LIKE '_line_total' OR meta_key LIKE '_line_tax' )" );

						} else {
							$args = $this->db->get_var( "SELECT meta_value FROM $this->wc_order_itemmeta_table WHERE order_item_id = $args AND meta_key LIKE '_line_total'" );
						}

						if ( null !== $args && 0 !== $args ) {

							$pos_order_discount = abs( $args );
						}
					}

					$products = array();

					foreach ( $order->get_items() as $item_id => $item ) {
						$item_id           = $item_id;
						$item_product_id   = $item->get_product_id();
						$item_product      = $item->get_product();
						$item_variation_id = $item->get_variation_id();
						$item_product_name = $item->get_name();
						$item_quantity     = $item->get_quantity();
						$item_subtotal     = $item->get_subtotal();
						$item_total        = $item->get_total();
						$item_tax          = $item->get_subtotal_tax();
						$item_sku          = $item_product ? $item_product->get_sku() : '';

						$item_qty_refunded   = $order->get_qty_refunded_for_item( $item_id );
						$item_total_refunded = $order->get_total_refunded_for_item( $item_id );

						$products[] = array(
							'id'         => $item_product_id,
							'item_id'    => $item_id,
							'type'       => intval( $item_variation_id ) > 0 ? 'variable' : 'simeple',
							'title'      => $item_product_name,
							'qty'        => $item_quantity,
							'amount'     => floatval( $item_total ) / intval( $item_quantity ),
							'sub_total'  => $item_subtotal,
							'total'      => $item_total,
							'tax'        => $item_tax,
							'sku'        => $item_sku,
							'refund'     => $item_total_refunded,
							'refund_qty' => $item_qty_refunded,
						);
					}

					$order_coupons = $order->get_items( 'coupon' );

					$coupons = array();
					if ( $order_coupons && count( $order_coupons ) > 0 ) {
						foreach ( $order_coupons as $coupon_id => $coupon ) {

							$wc_coupon = new \WC_Coupon( $coupon->get_code() );

							$coupons[] = array(
								'id'         => $wc_coupon->get_id(),
								'applied_id' => $coupon->get_id(),
								'code'       => $coupon->get_code(),
								'amount'     => $coupon->get_discount(),
								'type'       => $wc_coupon->get_discount_type(),
							);
						}
					}

					$tendered = get_post_meta( $order->get_id(), '_tendered_amnt', true );

					$cash_pay = get_post_meta( $order->get_id(), 'cash_pay', true );

					$card_pay = get_post_meta( $order->get_id(), 'card_pay', true );

					if ( empty( $tendered ) ) {
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

					$balance = (float) $tendered - (float) $order_total;

					$other_payment_title = get_post_meta( $order->get_id(), 'other_payment_title', true );

					$payments = array();

					if ( floatval( $card_pay ) > 0 && floatval( $cash_pay ) > 0 ) {
						$payments[] = array(
							'title'  => $other_payment_title,
							'amount' => $card_pay,
						);

						$payments[] = array(
							'title'  => esc_html__( 'cash', 'wc_pos' ),
							'amount' => $cash_pay,
						);
					} elseif ( 'cash' === $order->get_payment_method() ) {
						$payments[] = array(
							'title'  => $other_payment_title,
							'amount' => $cash_pay,
						);
					} else {
						$payments[] = array(
							'title'  => $other_payment_title,
							'amount' => $card_pay,
						);
					}

					$single_order_detail = array(
						'order_id'                => $order_id,
						'id'                      => $order_id,
						'outlet_id'               => $outlet_id,
						'date'                    => $order_date,
						'order_status'            => $order_status,
						'total'                   => $order_total,
						'subtotal'                => $order_subtotal,
						'tax'                     => $order_tax_total,
						'taxes'                   => $total_taxes,
						'refund'                  => $order_total_refunded,
						'refund_tax'              => $order_total_tax_refunded,
						'remaining_refund_amount' => $order_remaining_refund_amount,
						'currency_code'           => $order_currency_code,
						'currency_symbol'         => $order_currency_symbol,
						'shipping_amount'         => $order_shipping_total,
						'shipping_tax'            => $order_shipping_tax,
						'coupon_amount'           => $order_total_discount,
						'coupon_tax'              => $order_total_discount_tax,
						'balance'                 => $balance,
						'discount'                => $pos_order_discount,
						'products'                => $products,
						'payments'                => $payments,
						'coupons'                 => $coupons,
					);

					$single_order_detail = apply_filters( 'wkwcpos_modify_report_single_order_data', $single_order_detail, $order, $request );

					$order_details[] = $single_order_detail;
				}
			}

			return apply_filters( 'wkwcpos_modify_report_order_data', $order_details, $request );

		} catch ( \Exception $e ) {
			return array();
		}
	}

	/**
	 * Get formatted date.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 *
	 * @return array [ $start_date, $end_date ]
	 */
	public function get_date( $start_date, $end_date ) {

		$date       = date_create( $start_date );
		$start_date = date_format( $date, 'Y-m-d' ) . ' 00:00:00';

		$date     = date_create( $end_date );
		$end_date = date_format( $date, 'Y-m-d' ) . ' 23:59:59';

		return array(
			$start_date,
			$end_date,
		);
	}
}
