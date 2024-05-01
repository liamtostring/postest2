<?php
/**
 * WooCommerce Coupons Check Class
 *
 * Handles requests to the /coupons endpoint
 *
 * @since       2.1
 * @version     2.1
 * @package     WKWC_POS/Includes Woo_Coupon
 */

namespace WKWC_POS\Includes;

use WP_Error;
use WC_Coupon;
use WC_API_Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * WooCommerce Coupons Check Class.
 */
class Woo_Coupon {

	/**
	 * Get the coupon for the given code.
	 *
	 * @param string $code the coupon code.
	 * @param string $fields fields to include in response.
	 * @return int|WP_Error
	 */
	public function get_coupon_by_code( $code, $fields = null ) {

		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1;", $code ) );

		if ( is_null( $id ) ) {
			return new WP_Error( 'woocommerce_api_invalid_coupon_code', __( 'Invalid coupon code', 'wc_pos' ), array( 'status' => 404 ) );
		}

		return $this->get_coupon( $id, $fields );
	}

	/**
	 * Get the coupon for the given ID
	 *
	 * @since 2.1
	 *
	 * @param int    $id the coupon ID.
	 * @param string $fields fields to include in response.
	 *
	 * @throws \WC_API_Exception Throw exception.
	 *
	 * @return array|WP_Error
	 */
	public function get_coupon( $id, $fields = null ) {

		$coupon = new \WC_Coupon( $id );

		if ( 0 === $coupon->get_id() ) {
			throw new \WC_API_Exception( 'woocommerce_api_invalid_coupon_id', __( 'Invalid coupon ID', 'wc_pos' ), 404 );
		}

		$coupon_data = array(
			'amount'             => wc_format_decimal( $coupon->get_amount(), 2 ),
			'code'               => $coupon->get_code(),
			'type'               => $coupon->get_discount_type(),
			'date_expires'       => $coupon->get_date_expires(),
			'product_ids'        => $coupon->get_product_ids(),
			'product_categories' => $coupon->get_product_categories(),
		);

		return array( 'coupon' => $coupon_data );
	}

}
