<?php
/**
 * WooCommerce POS API setup
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Orders;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;
use WKWC_POS\Api\Includes\Orders\WKWCPOS_API_Create_Order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create offline order class.
 */
class WKWCPOS_API_Create_Offline_Order {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'create-offline-order';

	/**
	 * Namespace Name
	 *
	 * @var string $namespace Route namespace.
	 */
	public $namespace = 'pos/v1';

	/**
	 * Error class object.
	 *
	 * @var object $error Error class object.
	 */
	public $error;

	/**
	 * User outlet helper class object.
	 *
	 * @var object $helper User outlet helper class object.
	 */
	public $helper;

	/**
	 * Authentication class object.
	 *
	 * @var object $authentication Authentication class object.
	 */
	public $authentication;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->helper         = new WKWCPOS_API_Create_Order();
		$this->authentication = new WKWCPOS_API_Authentication();

	}

	/**
	 * API Callback.
	 *
	 * @param array $request Request array.
	 *
	 * @return array $order_array|$error.
	 */
	public function create_offline_pos_order( $request ) {

		try {

			$user_id = $request['logged_in_user_id'];

			$validate_auth_key = $this->authentication->wkwcpos_authenticate_request( $user_id );

			if ( 'ok' !== $validate_auth_key ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'wc_pos' ),
					'success'                => false,
				);
			}

			$order_array = array();

			if ( isset( $request['orders'] ) ) {

				$pos_orders = json_decode( $request['orders'] );

				if ( ! empty( $pos_orders ) && ! empty( $user_id ) ) {

					$pos_user = intval( $user_id );

					foreach ( $pos_orders as $order ) {
						if ( isset( $order->products ) && ! empty( $order->products ) && isset( $order->payment_mode ) && ! empty( $order->payment_mode ) && ! empty( $pos_user ) && isset( $order->email ) && ! empty( $order->email ) && isset( $order->order_id ) && ! empty( $order->order_id ) ) {
							$offline_id    = $order->order_id;
							$coupon        = $order->coupons;
							$cash_pay      = floatval( $order->cashPay ); // phpcs:ignore
							$card_pay      = floatval( $order->cardPay ); // phpcs:ignore
							$pos_cart      = $order->products;
							$payment_mode  = $order->payment_mode;
							$payment_title = $order->payment_title;
							$customer_id   = wp_strip_all_tags( $order->email );
							$tendered      = wp_strip_all_tags( $order->tendered );

							if ( 0 < $cash_pay && 0 < $card_pay ) {
								$payment_mode = 'Split';
							} elseif ( 0 < $cash_pay && 0 === $card_pay ) {
								$payment_mode = 'Cash';
							} elseif ( 0 < $card_pay && 0 === $cash_pay ) {
								$payment_mode = 'Card';
							}
							$order_data = array(
								'cart'                 => $pos_cart,
								'customer'             => $customer_id,
								'pos_user'             => $pos_user,
								'payment_method'       => $payment_mode,
								'payment_method_title' => $payment_title,
								'card_pay'             => $card_pay,
								'cash_pay'             => $cash_pay,
								'offline_id'           => $offline_id,
								'coupon'               => $coupon,
							);

							if ( ! empty( $tendered ) ) {
								$order_data['tendered'] = $tendered;
							}

							if ( isset( $order->discount ) && ! empty( $order->discount ) ) {
								$pos_discount = $order->discount;

								$order_data['discount'] = $pos_discount;
							}

							if ( isset( $order->order_note ) && ! empty( $order->order_note ) ) {
								$order_note = wp_strip_all_tags( $order->order_note );

								$order_data['order_note'] = $order_note;
							}

							if ( isset( $order->currency ) && ! empty( $order->currency ) ) {
								$currency_code = wp_strip_all_tags( $order->currency->code );

								$order_data['currency_code'] = $currency_code;
							}

							if ( ! empty( $order_data['payment_method'] ) && ( 'Card' === $order_data['payment_method'] || 'Split' === $order_data['payment_method'] ) ) {
								$order_data['payment_method'] = wp_strip_all_tags( $order->payment_mode );
							}

							$order_data = apply_filters( 'wkwcpos_change_order_data_for_process', $order_data, $order, $user_id );

							$response = $this->helper->create_order( $order_data, $user_id );

							$response['fake_id'] = $order->id;

							array_push( $order_array, $response );
						}
					}

					return $order_array;

				} else {

					return $this->error->set( 'User_id is not defined' );

				}
			} else {

				return $this->error->set( 'Order data is not Sufficient' );

			}
		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}

	}

}
