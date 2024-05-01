<?php
/**
 * WooCommerce POS API setup
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Customers;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Create customer api class.
 */
class WKWCPOS_API_Create_Customer {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'create-customer';

	/**
	 * Namespace Name
	 *
	 * @var string $namespace the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Database object.
	 *
	 * @var object $db Database object.
	 */
	protected $db = '';

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

		global $wpdb;
		$this->db = $wpdb;

		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->authentication = new WKWCPOS_API_Authentication();
	}

	/**
	 * Class Helper Functions.
	 *
	 * @param string $name User name.
	 *
	 * @return string Unique username.
	 */
	private function generate_unique_username( $name ) {

		$user_exists = 1;

		do {
			$rand_suffix = sprintf( '%0d', wp_rand( 1, 999999 ) );
			$user_exists = username_exists( $name . $rand_suffix );

		} while ( $user_exists > 0 );

		return $name . $rand_suffix;
	}


	/**
	 * Create Customer API Callback.
	 *
	 * @param array $request Create customer data.
	 */
	public function create_customer( $request ) {

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

			if ( ! isset( $request['pos'] ) || empty( $request['pos'] ) ) {

				return $this->error->set( __( 'Data not found', 'wc_pos' ) );

			}

			$c_id     = '';
			$phone    = '';
			$name     = '';
			$fname    = '';
			$lname    = '';
			$email    = '';
			$bemail   = '';
			$address1 = '';
			$address2 = '';
			$country  = '';
			$state    = '';
			$city     = '';
			$pincode  = '';
			$bphone   = '';

			$data = apply_filters( 'wkwcpos_modify_customer_update_request', $request['pos'], $request );

			if ( ! empty( $data ) ) {

				foreach ( $data as $key => $val ) {

					switch ( $val['name'] ) {
						case 'pos_customer_id':
							$c_id = ! empty( $val['value'] ) ? intval( $val['value'] ) : '';
							break;
						case 'pos_customer_phone':
							$phone = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_name':
							$name = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_email':
							$email = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_fname':
							$fname = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_lname':
							$lname = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_bemail':
							$bemail = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_address1':
							$address1 = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_address2':
							$address2 = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_country':
							$country = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_state':
							$state = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_city':
							$city = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_pincode':
							$pincode = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
						case 'pos_customer_bphone':
							$bphone = ! empty( $val['value'] ) ? wp_strip_all_tags( $val['value'] ) : '';
							break;
					}
				}
			}

			if ( ! empty( $c_id ) ) {

				if ( ! empty( $email ) && ! empty( $name ) ) {
					$name_f = '';
					$name_l = '';
					if ( substr_count( $name, ' ' ) > 1 ) {

						$name_f = ( substr( $name, 0, strrpos( $name, ' ' ) ) );

						$name_l = substr( strrchr( $name, ' ' ), 1 );
					} else {

						$name = explode( ' ', $name );

						$name_f = isset( $name[0] ) ? $name[0] : $name_f;

						$name_l = isset( $name[1] ) ? $name[1] : $name_l;

					}

					// Set the nickname.
					wp_update_user(
						array(
							'ID'         => $c_id,
							'user_email' => $email,
							'first_name' => $name_f,
							'last_name'  => $name_l,
						)
					);

					$customer = new \WC_Customer( $c_id );

					$customer->set_first_name( $name_f );

					$customer->set_last_name( $name_l );

					$customer->set_billing_first_name( $fname );
					$customer->set_billing_last_name( $lname );
					$customer->set_billing_address_1( $address1 );
					$customer->set_billing_address_2( $address2 );
					$customer->set_billing_city( $city );
					$customer->set_billing_state( $state );
					$customer->set_billing_postcode( $pincode );
					$customer->set_billing_country( $country );
					$customer->set_billing_email( $bemail );
					$customer->set_billing_phone( $phone );

					$customer->set_shipping_first_name( $fname );
					$customer->set_shipping_last_name( $lname );
					$customer->set_shipping_address_1( $address1 );
					$customer->set_shipping_address_2( $address2 );
					$customer->set_shipping_city( $city );
					$customer->set_shipping_state( $state );
					$customer->set_shipping_postcode( $pincode );
					$customer->set_shipping_country( $country );

					$customer->save();

					do_action( 'wkwc_add_meta_customer_data', $data, $c_id );

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

					$customers_arr = array(
						'id'         => $customer->get_id(),
						'email'      => get_userdata( $c_id )->user_email,
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

					$customers_arr = apply_filters( 'wkwcpos_modify_customer_details_by_customer_id_at_pos', $customers_arr, $customer->get_id() );

					$msg = array(
						'success' => 1,
						'msg'     => __( 'Customer updated successfully.!', 'wc_pos' ),
						'data'    => $customers_arr,
					);
					return $msg;

				} else {

					$msg = array(
						'error' => 1,
						'msg'   => __( 'Fields are required', 'wc_pos' ),
					);

					return $msg;
				}
			} else {

				if ( ! empty( $email ) && ! empty( $name ) ) {

					if ( isset( $data['pos_customer_phone'] ) && ! empty( $phone ) ) {

						$billno = $this->db->get_results( "SELECT meta_value FROM {$this->db->prefix}usermeta WHERE meta_key = 'billing_phone'", ARRAY_A );

						if ( ! empty( $billno ) ) {

							$arr_bno = wp_list_pluck( $billno, 'meta_value' );

						}

						if ( ! empty( $arr_bno ) && ! empty( $phone ) && in_array( $phone, $arr_bno ) ) {

							$msg = array(
								'error' => 1,
								'msg'   => __( 'Phone number already exists', 'wc_pos' ),
							);

							return $msg;

						}
					}

					$elm = explode( '@', $email );

					$elm = $elm[0];

					if ( false == email_exists( $email ) ) {

						// Generate the password and create the user.
						if ( username_exists( $elm ) ) {

							$elm = $this->get_random_unique_username( $elm );

						}

						$pwd = wp_generate_password();

						$name_f = '';
						$name_l = '';

						$user_id = wc_create_new_customer( $email, $elm, $pwd );

						wp_mail( $email, 'Welcome! you are now new customer On our POS SYSTEM', 'Your Password: ' . $pwd );

						if ( substr_count( $name, ' ' ) > 1 ) {

							$name_f = ( substr( $name, 0, strrpos( $name, ' ' ) ) );

							$name_l = substr( strrchr( $name, ' ' ), 1 );
						} else {

							$name = explode( ' ', $name );

							$name_f = $name[0];

							if ( ! empty( $name[1] ) ) {

								$name_l = $name[1];

							}
						}

						// Set the nickname.
						wp_update_user(
							array(
								'ID'         => $user_id,
								'user_email' => $email,
								'nickname'   => $email,
								'first_name' => $name_f,
								'last_name'  => $name_l,
							)
						);

						$customer = new \WC_Customer( $user_id );

						$customer->set_first_name( $name_f );
						$customer->set_last_name( $name_l );

						$customer->set_billing_first_name( $fname );
						$customer->set_billing_last_name( $lname );
						$customer->set_billing_address_1( $address1 );
						$customer->set_billing_address_2( $address2 );
						$customer->set_billing_city( $city );
						$customer->set_billing_state( $state );
						$customer->set_billing_postcode( $pincode );
						$customer->set_billing_country( $country );
						$customer->set_billing_email( $bemail );
						$customer->set_billing_phone( $phone );

						$customer->set_shipping_first_name( $fname );
						$customer->set_shipping_last_name( $lname );
						$customer->set_shipping_address_1( $address1 );
						$customer->set_shipping_address_2( $address2 );
						$customer->set_shipping_city( $city );
						$customer->set_shipping_state( $state );
						$customer->set_shipping_postcode( $pincode );
						$customer->set_shipping_country( $country );

						$customer->save();

						do_action( 'wkwc_add_meta_customer_data', $data, $user_id );

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

						$customers_arr = array(
							'id'         => $customer->get_id(),
							'email'      => get_userdata( $user_id )->user_email,
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

						$customers_arr = apply_filters( 'wkwcpos_modify_customer_details_by_customer_id_at_pos', $customers_arr, $customer->get_id() );

						$msg = array(
							'success' => 1,
							'msg'     => __( 'Customer created successfully.!', 'wc_pos' ),
							'data'    => $customers_arr,
						);
						return $msg;

					} else {

						$msg = array(
							'error' => 1,
							'msg'   => __( 'Email already exists', 'wc_pos' ),
						);
						return $msg;

					}
				} else {

					$msg = array(
						'error' => 1,
						'msg'   => __( 'Fields are required', 'wc_pos' ),
					);
					return $msg;

				}
			}  //Return Newly Added Customer ID.

		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}
	}

	/**
	 * Get random unique username.
	 *
	 * @param string $prefix Prefix.
	 *
	 * @return string Random unique username.
	 */
	public function get_random_unique_username( $prefix = '' ) {

		$user_exists = 1;

		do {
			$rnd_str     = sprintf( '%0d', wp_rand( 1, 999999 ) );
			$user_exists = username_exists( $prefix . $rnd_str );
		} while ( $user_exists > 0 );

		return $prefix . $rnd_str;

	}

}
