<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Orders;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Product stock check api class.
 */
class WKWCPOS_API_Product_Stock_Check {

	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'check-stock';

	/**
	 * Namespace Name.
	 *
	 * @var string the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Error class object.
	 *
	 * @var object $error Error class object.
	 */
	public $error;

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
		$this->authentication = new WKWCPOS_API_Authentication();
	}

	/**
	 * Validate product API Callback.
	 *
	 * @param array $request Request array.
	 */
	public function wk_wc_pos_validate_product_stock( $request ) {
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

			if ( isset( $request['cart_data'] ) & isset( $request['cart_data'] ) ) {
				$cart_list = json_decode( $request['cart_data'] );

				$not_valid_products = array( 'not_valid_products' => array() );

				if ( ! empty( $cart_list ) ) {
					foreach ( $cart_list as $key => $cart_data ) {

						if ( ! empty( $cart_data->options ) && is_object( $cart_data->options ) ) {

							$product_id = $cart_data->options->var_id;

						} else {

							$product_id = $cart_data->product_id;
						}

						$quantity = $cart_data->quantity;

						$manage_stock = get_post_meta( $product_id, '_manage_stock', true );

						if ( 'yes' === $manage_stock ) {
							$product_stock = (int) get_post_meta( $product_id, '_stock', true );
						} else {
							$product_stock = get_post_meta( $product_id, '_stock_status', true );
						}

						if ( 'outofstock' === $product_stock || ( is_numeric( $product_stock ) && $quantity > $product_stock ) ) {
							$not_valid_products['not_valid_products'][] = $product_id;
						}
					}
				}

				return apply_filters( 'wkwcpos_modify_not_valid_products_for_centralized_at_pos', $not_valid_products, $user_id, $cart_list );

			} else {
				return $this->error->set( 'No cart data found.' );
			}
		} catch ( \Exception $e ) {
			return $this->error->set( 'exception', $e );
		}
	}
}
