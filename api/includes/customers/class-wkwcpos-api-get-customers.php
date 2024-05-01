<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Customers;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;
use WKWC_POS\Api\Helper\WKWCPOS_API_User_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get customers.
 */
class WKWCPOS_API_Get_Customers {

	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'get-customers';

	/**
	 * Namespace Name.
	 *
	 * @var string the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Database object.
	 *
	 * @var object $db Database.
	 */
	protected $db = '';

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
		global $wpdb;
		$this->db             = $wpdb;
		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->authentication = new WKWCPOS_API_Authentication();
		$this->helper         = new WKWCPOS_API_User_Outlet_Helper();
	}

	/**
	 * Get Customer API Callback.
	 *
	 * @param array $request Request array.
	 */
	public function get_customers( $request ) {
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

			if ( ! isset( $request['logged_in_user_id'] ) || empty( $request['logged_in_user_id'] ) ) {
				return $this->error->set( 'user_id_required' );
			}

			$manager_id = $request['logged_in_user_id'];

			$outlet_id = $this->helper->_get_pos_user_outlet_with_status( $manager_id );

			if ( ! empty( $outlet_id ) ) {
				$page = $request['page'];

				$number = 500;

				$customers = get_users(
					array(
						'role'    => 'customer',
						'orderby' => 'registered',
						'number'  => $number,
						'offset'  => $page * $number,
					)
				);

				$customers_arr = array();

				$i = 0;

				if ( ! empty( $customers ) ) {
					foreach ( $customers as $user ) {
						$customer = new \WC_Customer( $user->id );

						$coupon_details = array();

						$coupons = $this->db->get_results( $this->db->prepare( "SELECT ID, post_title FROM {$this->db->prefix}posts as posts JOIN {$this->db->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type=%s AND posts.post_status=%s AND postmeta.meta_key=%s AND postmeta.meta_value LIKE %s", 'shop_coupon', 'publish', 'customer_email', "%{$customer->get_email()}%" ) );

						if ( ! empty( $coupons ) ) {
							foreach ( $coupons as $key => $coupon ) {

								$c = new \WC_Coupon( $coupon->post_title );

								$coupon_details[] = array(
									'code'               => $c->get_code(),
									'price'              => wc_format_decimal( $c->get_amount(), 2 ),
									'type'               => $c->get_discount_type(),
									'product_ids'        => $c->get_data()['product_ids'],
									'product_categories' => $c->get_data()['product_categories'],
									'email_restrictions' => $c->get_data()['email_restrictions'],
								);
							}
						}

						$customers_arr[ $i ] = array(
							'id'         => $customer->get_id(),
							'email'      => $customer->get_email(),
							'first_name' => $customer->get_first_name(),
							'last_name'  => $customer->get_last_name(),
							'username'   => $customer->get_username(),
							'avatar_url' => $customer->get_avatar_url(),

							'coupons'    => $coupon_details,

							'billing'    => array(
								'first_name' => $customer->get_billing_first_name(),
								'last_name'  => $customer->get_billing_last_name(),
								'company'    => $customer->get_billing_company(),
								'address_1'  => $customer->get_billing_address_1(),
								'address_2'  => $customer->get_billing_address_2(),
								'city'       => $customer->get_billing_city(),
								'state'      => $customer->get_billing_state(),
								'postcode'   => $customer->get_billing_postcode(),
								'country'    => $customer->get_billing_country(),
								'email'      => $customer->get_billing_email(),
								'phone'      => $customer->get_billing_phone(),
							),
							'shipping'   => array(
								'first_name' => $customer->get_shipping_first_name(),
								'last_name'  => $customer->get_shipping_last_name(),
								'company'    => $customer->get_shipping_company(),
								'address_1'  => $customer->get_shipping_address_1(),
								'address_2'  => $customer->get_shipping_address_2(),
								'city'       => $customer->get_shipping_city(),
								'state'      => $customer->get_shipping_state(),
								'postcode'   => $customer->get_shipping_postcode(),
								'country'    => $customer->get_shipping_country(),
							),
						);

						$is_default = get_user_meta( $user->id, 'deault_customer_pos', true );

						$customers_arr[ $i ]['is_true']          = $is_default;
						$customers_arr[ $i ]['default_customer'] = $is_default;
						$customers_arr[ $i ]                     = apply_filters( 'manage_custom_customer_details_support', $customers_arr[ $i ], $customer, $i );

						++$i;
					}

					return apply_filters( 'wkwcpos_modify_all_customer_details_at_pos', $customers_arr, $manager_id, $outlet_id, $page );
				} else {
					return $this->error->set( 'customers_not_found' );
				}
			} else {
				return $this->error->set( 'customers_not_found' );
			}
		} catch ( \Exception $e ) {
			return $this->error->set( 'exception', $e );
		}
	}
}
