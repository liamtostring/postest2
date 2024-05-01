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
use WKWC_POS\Includes\Woo_Coupon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Coupon check api class.
 */
class WKWCPOS_API_Coupon_Check {
	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'check-coupon';

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
	 * API Callback.
	 *
	 * @param array $request Request array.
	 *
	 * @return array $coupon|$error.
	 */
	public function check_coupon_online( $request ) {
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

			if ( isset( $request['coupon_code'] ) ) {
				$res = array();

				$ccode = wp_strip_all_tags( $request['coupon_code'] );

				$customer_email = $request['customer'][0]['id'];

				$cart = $request['cart'];

				if ( ! empty( $ccode ) ) {

					$woo_coupon = new Woo_Coupon();

					$coup_code = $woo_coupon->get_coupon_by_code( $ccode );

					$wc_coupon_data_store_cpt = new \WC_Coupon_Data_Store_CPT();

					$coup_obj = new \WC_Coupon( $ccode );
					$validate = $this->validate_coupon( $coup_obj, $cart );

					if ( isset( $validate['status'] ) && false === $validate['status'] ) {

						return $validate;

					}

					$used_by_customer = $wc_coupon_data_store_cpt->get_usage_by_user_id( $coup_obj, $customer_email );

					$total_usage_by_customer = $coup_obj->get_usage_limit_per_user();

					if ( empty( $total_usage_by_customer ) || ( $total_usage_by_customer - $used_by_customer ) > 0 ) {

						if ( is_array( $coup_code ) && ! empty( $coup_code['coupon'] ) ) {

							if ( empty( $coup_code['coupon']['date_expires'] ) || strtotime( current_time( 'Y-m-d' ) ) <= strtotime( $coup_code['coupon']['date_expires']->date( 'Y-m-d' ) ) ) {

								$res['coupon'] = array(
									'code'               => $coup_code['coupon']['code'],
									'price'              => $coup_code['coupon']['amount'],
									'type'               => $coup_code['coupon']['type'],
									'product_ids'        => $coup_code['coupon']['product_ids'],
									'product_categories' => $coup_code['coupon']['product_categories'],
								);

								$res['coupon'] = apply_filters( 'wkwcpos_modify_applied_coupon_at_pos', $res['coupon'], $user_id, $ccode );

								$res['success'] = true;

							} else {
								$res['error'] = esc_html__( 'Sorry, Coupon Expired. Try another one!', 'wc_pos' );
							}
						} elseif ( is_object( $coup_code ) && isset( $coup_code->errors ) ) {
							$res['error'] = $coup_code->errors['woocommerce_api_invalid_coupon_code'][0];
						} else {
							$res['error'] = esc_html__( 'Invalid Coupon Code', 'wc_pos' );
						}
					} else {
						$res['error'] = esc_html__( 'Sorry, Coupon already used at limit by this customer.', 'wc_pos' );
					}
				} else {
					$res['msg'] = esc_html__( 'Invalid Coupon', 'wc_pos' );
				}

				return $res;
			}
		} catch ( \Exception $e ) {
			return $this->error->set( 'exception', $e );
		}
	}

	/**
	 * Validate coupon function.
	 *
	 * @param array|object $coupon Coupon.
	 * @param array|object $carts Cart data.
	 *
	 * @return array $response Response ( status, error ).
	 */
	public function validate_coupon( $coupon, $carts ) {
		$res           = array();
		$res['status'] = true;
		$res['error']  = '';
		if ( $coupon->get_exclude_sale_items() && ! empty( $carts ) ) {
			foreach ( $carts as $cart ) {
				foreach ( $cart['cart'] as $product ) {
					if ( $product['price'] !== $product['special'] && 1 === count( $cart['cart'] ) ) {
						$res['status'] = false;
						$res['error']  = esc_html__( 'Sorry, Coupon is not valid for your cart.', 'wc_pos' );

					}
				}
			}
		}
		$match  = 0;
		$match1 = 0;
		if ( $coupon->get_product_categories() && ! empty( $carts ) ) {
			$coupon_catagory = $coupon->get_product_categories();
			foreach ( $carts as $cart ) {
				foreach ( $cart['cart'] as $product ) {

					$catagory = $this->wc_pos_get_categories_by_product_id( $product['id'], 'ids' );
					foreach ( $coupon_catagory as $c_catagory ) {
						foreach ( $catagory as $p_catagory ) {
							if ( (int) $p_catagory === (int) $c_catagory ) {
								$match = 1;
							}
						}
					}
				}
			}
			if ( 1 !== (int) $match ) {
				$res['status'] = false;
				$res['error']  = esc_html__( 'Sorry, Coupon is not valid for your cart.', 'wc_pos' );
			}
		}
		if ( $coupon->get_excluded_product_categories() && ! empty( $carts ) ) {
			$coupon_catagory = $coupon->get_excluded_product_categories();
			foreach ( $carts as $cart ) {
				foreach ( $cart['cart'] as $product ) {

					$catagory = $this->wc_pos_get_categories_by_product_id( $product['id'], 'ids' );
					foreach ( $coupon_catagory as $c_catagory ) {
						foreach ( $catagory as $p_catagory ) {
							if ( $p_catagory === $c_catagory ) {
								$match1 = 1;
							}
						}
					}
				}
			}

			if ( 1 === $match1 ) {
				$res['status'] = false;
				$res['error']  = esc_html__( 'Sorry, Coupon is not valid for your cart.', 'wc_pos' );
			}
		}
		return apply_filters( 'wkwcpos_validate_coupon_restrictions', $res, $coupon, $carts );
	}

	/**
	 * Get product categories by product id.
	 *
	 * @param int    $product_id Product id.
	 * @param string $fields Field name.
	 *
	 * @return array|object Category.
	 */
	public function wc_pos_get_categories_by_product_id( $product_id, $fields ) {
		return wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => $fields ) );
	}

}
