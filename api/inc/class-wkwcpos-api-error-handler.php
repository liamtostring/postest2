<?php
/**
 * WooCommerce POS API setup
 *
 * @package  WooCommerce Point Of Sale API
 * @version    1.0.0
 */

namespace WKWC_POS\Api\Inc;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * POS Errors API Class.
 *
 * @class POS_Api_Error_Setter
 */
class WKWCPOS_API_Error_Handler {

	/**
	 * Set API Errors.
	 *
	 * @param string $error Type of error.
	 * @param object $e Error Object.
	 *
	 * @return json
	 */
	public function set( $error, $e = '' ) {

		switch ( $error ) {

			case 'user_id_required':
				return new WP_Error( 'pos_user_id_required', __( 'POS manager ID is Required', 'wc_pos' ), array( 'status' => 200 ) );

			case 'user_not_exist':
				return new WP_Error( 'pos_user_not_exist', __( 'User do not exists', 'wc_pos' ), array( 'status' => 200 ) );

			case 'user_not_manager':
				return new WP_Error( 'pos_user_not_manager', __( 'No manager exist by that email.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'nonce_error':
				return new WP_Error( 'pos_nonce_error', __( 'Nonce did not verified.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customer_not_found':
				return new WP_Error( 'pos_customer_not_found', __( 'Customer not Found by requested ID.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customer_not_deleted':
				return new WP_Error( 'pos_customer_not_delete', __( 'Not able to Delete customer. Please try again.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customer_id_not_set':
				return new WP_Error( 'pos_customer_id_not_set', __( 'customer_id is required.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customer_not_created':
				return new WP_Error( 'pos_customer_not_create', __( 'Not able to Create customer. Please try again.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customer_name_or_email_empty':
				return new WP_Error( 'pos_customer_name_or_email_empty', __( 'Customer Name & Email is Required', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customer_email_exists':
				return new WP_Error( 'pos_customer_email_exists', __( 'Email already exists', 'wc_pos' ), array( 'status' => 200 ) );

			case 'customers_not_found':
				return new WP_Error( 'pos_customers_not_found', __( 'No customers found.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'invalid_coupon':
				return new WP_Error( 'pos_invalid_coupon', __( 'Invalid Coupon', 'wc_pos' ), array( 'status' => 200 ) );

			case 'country_is_empty':
				return new WP_Error( 'pos_country_is_empty', __( 'Parameter country_code not set. Pass a valid country code.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'all_field_required':
				return new WP_Error( 'pos_all_field_required', __( 'All Fields are required.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'outlet_update_error':
				return new WP_Error( 'pos_outlet_update_error', __( 'Missing params product_id, outlet_id, stock.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'pos_stock_unavailable':
				return new WP_Error( 'pos_pos_stock_unavailable', __( 'POS Stock is unavailable', 'wc_pos' ), array( 'status' => 200 ) );

			case 'form_data_empty':
				return new WP_Error( 'pos_form_data_empty', __( 'Please provide data', 'wc_pos' ), array( 'status' => 200 ) );

			case 'old_password_not_matched':
				return new WP_Error( 'pos_old_password_not_matched', __( 'You have entered a wrong password.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'password_blank_error':
				return new WP_Error( 'pos_form_data_empty', __( 'Please enter & confirm new password.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'user_data_required':
				return new WP_Error( 'pos_user_data_required', __( 'Data to update is required.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'outlet_user_map_not_found':
				return new WP_Error( 'pos_outlet_user_map_not_found', __( 'No mapped Outlet Found.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'insufficient_order_data':
				return new WP_Error( 'pos_insufficient_order_data', __( 'Insufficient Data For Creating Order.', 'wc_pos' ), array( 'status' => 200 ) );

			case 'order_empty':
				return new WP_Error( 'pos_order_empty', __( 'Order is Empty', 'wc_pos' ), array( 'status' => 200 ) );

			case 'exception':
				return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => 200 ) );

		}

	}

}
