<?php
/**
 * This file handles all admin end actions.
 *
 * @package WooCommerce Point of Sale
 * @version    1.0.0
 */

namespace WKWC_POS\Includes\Filters;

use WKWC_POS\Templates\Front\WC_Pos_Login;
use WKWC_POS\Inc\WC_Pos_Errors;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Filter_Callbacks' ) ) {

	/**
	 * Filter callback class.
	 */
	class WC_Pos_Filter_Callbacks extends WC_Pos_Errors {

		/**
		 * Database object.
		 *
		 * @var object $db Database object.
		 */
		protected $db;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			global $wpdb;
			$this->db = $wpdb;
		}

		/**
		 * Get pos countries list.
		 */
		public function wk_wc_get_pos_countries_list() {

			$countries_obj = new \WC_Countries();
			$countries     = $countries_obj->__get( 'countries' );

			return $countries;
		}

		/**
		 * Get pos outlet list.
		 *
		 * @param int $outlet_id Outlet id.
		 *
		 * @return array Outlet list.
		 */
		public function wk_wc_get_pos_outlet_list( $outlet_id ) {
			$outlet_arr = array();

			$table_name  = $this->db->prefix . 'woocommerce_pos_outlets';
			$table_name2 = $this->db->prefix . 'woocommerce_pos_outlet_map';

			if ( ! empty( $outlet_id ) ) {
				$res_out = $this->db->get_row( "SELECT id,outlet_name from $table_name where outlet_status=0 and id=" . $outlet_id );

				if ( ! empty( $res_out ) ) {
					$outlet_arr[ $res_out->id ] = $res_out->outlet_name;
				}
			} else {
				$res_out = $this->db->get_results( "select id,outlet_name from $table_name  where outlet_status=0" );

				if ( isset( $_GET['pos_user'] ) && ! empty( $_GET['pos_user'] ) ) { // phpcs:ignore

					$pos_user = $_GET['pos_user']; // phpcs:ignore

					$mapped_outlet = $this->db->get_results( "select outlet_id from $table_name2 WHERE user_id =$pos_user OR user_id = '0' ", ARRAY_A );
				} else {
					$mapped_outlet = $this->db->get_results( "select outlet_id from $table_name2  WHERE user_id = '0'", ARRAY_A );
				}

				if ( ! empty( $mapped_outlet ) ) {
					foreach ( $mapped_outlet as $key => $value ) {
						$mapped_outlet[ $key ] = $value['outlet_id'];
					}
				}

				if ( ! empty( $res_out ) ) {
					$outlet_arr[-1] = 'Select';

					foreach ( $res_out as $res ) {
						$outlet_arr[ $res->id ] = $res->outlet_name;
					}
				}
			}

			return $outlet_arr;
		}

		/**
		 * Save pos payment methods.
		 *
		 * @param array $post Post data.
		 */
		public function wk_wc_save_pos_payment( $post ) {

			$error_obj               = new WC_Pos_Errors();
			$pos_payment_name        = isset( $post['_pos_payment_name'] ) && ! empty( $post['_pos_payment_name'] ) ? sanitize_text_field( $post['_pos_payment_name'] ) : '';
			$pos_payment_slug        = isset( $post['_pos_payment_slug'] ) && ! empty( $post['_pos_payment_slug'] ) ? sanitize_text_field( $post['_pos_payment_slug'] ) : '';
			$pos_payment_description = isset( $post['_pos_payment_description'] ) && ! empty( $post['_pos_payment_description'] ) ? sanitize_text_field( $post['_pos_payment_description'] ) : '';
			$pos_status              = isset( $post['_pos_status'] ) && ! empty( $post['_pos_status'] ) ? sanitize_text_field( $post['_pos_status'] ) : 0;
			$payment_id              = isset( $post['_pos_payment_id'] ) && ! empty( $post['_pos_payment_id'] ) ? sanitize_text_field( $post['_pos_payment_id'] ) : 0;

			if ( empty( $pos_payment_name ) ) {
				$message = __( 'Pos payment name is empty ', 'wc_pos' );
				$error_obj->set_error_code( 1 );
				$error_obj->wk_wc_pos_print_notification( $message );
			} elseif ( empty( $pos_payment_slug ) ) {
				$message = __( 'Pos payment slug is empty ', 'wc_pos' );
				$error_obj->set_error_code( 1 );
				$error_obj->wk_wc_pos_print_notification( $message );
			} elseif ( empty( $pos_payment_description ) ) {
				$message = __( 'Pos payment description is empty ', 'wc_pos' );
				$error_obj->set_error_code( 1 );
				$error_obj->wk_wc_pos_print_notification( $message );
			} else {

				$payment_data = array(
					'data'      => array(
						'payment_name'        => $pos_payment_name,
						'payment_description' => $pos_payment_description,
						'payment_status'      => $pos_status,
						'payment_slug'        => $pos_payment_slug,
					),
					'data_type' => array( '%s', '%s', '%d', '%s' ),
				);

				$payment_data = apply_filters( 'wkwcpos_modify_payment_data_and_type', $payment_data, $post );

				if ( $payment_id ) {

					$payment_result = $this->db->get_var( $this->db->prepare( "SELECT payment_slug FROM {$this->db->prefix}woocommerce_pos_payments WHERE payment_slug = %s AND id != %d", $pos_payment_slug, $payment_id ) );

					$payment_result = apply_filters( 'wkwcpos_modify_update_get_payment_result', $payment_result, $post, $payment_id );

					if ( empty( $payment_result ) ) {

						$this->db->update(
							"{$this->db->prefix}woocommerce_pos_payments",
							$payment_data['data'],
							array( 'id' => $payment_id ),
							$payment_data['data_type'],
							array( '%d' )
						);

						$message = $pos_payment_name . __( ' Payment Gateway Updated Successfully ', 'wc_pos' );
						$error_obj->set_error_code( 0 );
						$error_obj->wk_wc_pos_print_notification( $message );

					} else {
						$error_obj->set_error_code( 1 );
						$message = $pos_payment_name . __( ' Pos payment already exists from this slug.', 'wc_pos' );
						$error_obj->wk_wc_pos_print_notification( $message );
					}
				} else {

					$payment_result = $this->db->get_var( $this->db->prepare( "SELECT payment_slug FROM {$this->db->prefix}woocommerce_pos_payments WHERE payment_slug = %s", $pos_payment_slug ) );

					$payment_result = apply_filters( 'wkwcpos_modify_insert_get_payment_result', $payment_result, $post );

					if ( empty( $payment_result ) ) {

						$this->db->insert(
							"{$this->db->prefix}woocommerce_pos_payments",
							$payment_data['data'],
							$payment_data['data_type']
						);

						$message = $pos_payment_name . __( ' Payment Gateway Created Successfully', 'wc_pos' );
						$error_obj->set_error_code( 0 );
						$error_obj->wk_wc_pos_print_notification( $message );

					} else {

						$error_obj->set_error_code( 1 );
						$message = $pos_payment_name . __( ' Pos payment already exists from this slug.', 'wc_pos' );
						$error_obj->wk_wc_pos_print_notification( $message );
					}
				}
			}

		}

		/**
		 * Save pos default customer data.
		 */
		public function wk_wc_save_pos_default_customer() {
			$wkpos_data = apply_filters( 'wkpos_save_default_customer_settings', $_POST ); // phpcs:ignore

			$post_data = $wkpos_data;
			$pwd       = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_password'] ) );
			$fname     = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_fname'] ) );
			$lname     = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_lname'] ) );
			$email     = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_email'] ) );
			$phone     = preg_replace( '/[^0-9]/', '', sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_telephone'] ) ) );
			$company   = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_company'] ) );
			$addr1     = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_address1'] ) );
			$addr2     = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_address2'] ) );
			$city      = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_city'] ) );
			$postcode  = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_defcustomer_postcode'] ) );
			$country   = sanitize_text_field( wp_strip_all_tags( $post_data['_pos_store_country'] ) );

			if ( empty( $email ) ) {
				$message = __( 'Customer email is mandatory ', 'wc_pos' );

				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$message = __( 'Customer email is not valid ', 'wc_pos' );
				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( empty( $fname ) ) {
				$message = __( 'Customer first name is mandatory.', 'wc_pos' );
				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( empty( $lname ) ) {
				$message = __( 'Customer last Name is mandatory.', 'wc_pos' );
				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( empty( $pwd ) ) {
				$message = __( 'Customer password is mandatory.', 'wc_pos' );
				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( empty( $phone ) ) {
				$message = __( 'Customer Phone number is mandatory.', 'wc_pos' );
				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( ! empty( $phone ) && ( strlen( $phone ) < 9 || strlen( $phone ) > 12 ) ) {
				$message = __( 'Please enter a valid phone number(Only number allowed).', 'wc_pos' );
				parent::set_error_code( 1 );
				parent::wk_wc_pos_print_notification( $message );
			}

			if ( parent::get_error_code() == 0 ) {
				if ( isset( $_POST['save_default_customer'] ) ) { // phpcs:ignore
					if ( ! empty( $email ) && ! empty( $pwd ) && ! empty( $lname ) && ! empty( $fname ) ) {
						if ( false == email_exists( $email ) ) {
							// Generate the password and create the user.

							$elm = explode( '@', $email );
							$elm = $elm[0];

							$user_id = wc_create_new_customer( $email, $elm, $pwd );

							// Set the nickname.
							wp_update_user(
								array(
									'ID'         => $user_id,
									'nickname'   => $email,
									'first_name' => $fname,
									'last_name'  => $lname,
								)
							);
							update_user_meta( $user_id, 'billing_first_name', $fname );
							update_user_meta( $user_id, 'billing_last_name', $lname );
							update_user_meta( $user_id, 'billing_company', $company );
							update_user_meta( $user_id, 'billing_address_1', $addr1 );
							update_user_meta( $user_id, 'billing_address_2', $addr2 );
							update_user_meta( $user_id, 'billing_city', $city );
							update_user_meta( $user_id, 'billing_postcode', $postcode );
							update_user_meta( $user_id, 'billing_country', $country );
							update_user_meta( $user_id, 'billing_email', $email );
							update_user_meta( $user_id, 'billing_phone', $phone );
							update_user_meta( $user_id, 'deault_customer_pos', true );

							wp_mail( $email, 'Welcome! you are now new customer On our POS SYSTEM', 'Your Password: ' . $pwd, __( 'Account created successfully', 'wc_pos' ) ); // phpcs:ignore
							?>

<div class="notice notice-success is-dismissible">

	<p><?php esc_html_e( 'Default Customer for POS Created Successfully!', 'wc_pos' ); ?></p>


</div>

							<?php
						}
					} else {
						?>

<div class="notice notice-error is-dismissible">

	<p><?php esc_html_e( 'Ther must some required fields', 'wc_pos' ); ?></p>

</div>

						<?php
					}
				} else {
					$user_id = $_POST['default_customer_id']; // phpcs:ignore

					// Set the nickname.
					wp_update_user(
						array(
							'ID'         => $user_id,
							'first_name' => $fname,
							'last_name'  => $lname,
							'user_email' => $email,
							'user_pass'  => $pwd,
						)
					);

					update_user_meta( $user_id, 'billing_first_name', $fname );
					update_user_meta( $user_id, 'billing_last_name', $lname );
					update_user_meta( $user_id, 'billing_company', $company );
					update_user_meta( $user_id, 'billing_address_1', $addr1 );
					update_user_meta( $user_id, 'billing_address_2', $addr2 );
					update_user_meta( $user_id, 'billing_city', $city );
					update_user_meta( $user_id, 'billing_postcode', $postcode );
					update_user_meta( $user_id, 'billing_country', $country );
					update_user_meta( $user_id, 'billing_email', $email );
					update_user_meta( $user_id, 'billing_phone', $phone );
					update_user_meta( $user_id, 'deault_customer_pos', true );
					?>

<div class="notice notice-success is-dismissible">

	<p><?php esc_html_e( 'Default Customer for POS Updated Successfully!', 'wc_pos' ); ?></p>

</div>

					<?php
				}
			}
			do_action( 'wkpos_delete_meta_after_validation', $user_id );
		}

		/**
		 * WordPress function for redirecting users on login based on user role.
		 */
		public function wk_wc_pos_managers_only() {

			$url          = $_SERVER['REQUEST_URI']; // phpcs:ignore
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			if ( ! is_admin() && preg_match( '/\b\/' . $pos_endpoint . '\b/', $url ) && is_user_logged_in() ) {

				$user_id   = get_current_user_id();
				$user      = get_userdata( $user_id );
				$user_role = $user->roles;

				if ( empty( $user_role ) || ! in_array( 'pos_user', $user_role ) || intval( $user->data->user_status ) === 1 ) {
					$location = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
					if ( ! apply_filters( 'wkwcpos_allow_pos_access_for_custom_role', false, $user ) ) {
						wp_safe_redirect( $location );
						exit( 0 );
					}
				}
			}
		}

		/**
		 * Check pos login failed.
		 *
		 * @param array $user User data.
		 */
		public function wk_wc_pos_login_failed( $user ) {
			// check what page the login attempt is coming from.
			$referrer     = $_SERVER['HTTP_REFERER']; // phpcs:ignore
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';
			// check that were not on the default login page.
			if ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) && strstr( $referrer, $pos_endpoint ) && null != $user ) {
				// make sure we don’t already have a failed login attempt.
				if ( ! strstr( $referrer, '?login=failed' ) ) {
					// Redirect to the login page and append a querystring of login failed.
					wp_safe_redirect( $referrer . '?login=failed' );
					exit();
				} else {
					wp_safe_redirect( $referrer );
					exit();
				}

				exit;
			}
		}

		/**
		 * Get pos user status.
		 *
		 * @param string $pos_user Pos user.
		 *
		 * @return array $status_arr Pos user status array.
		 */
		public function wk_wc_get_pos_user_status( $pos_user ) {
			$status_arr = array( esc_html__( 'Active', 'wc_pos' ), esc_html__( 'Deactive', 'wc_pos' ) );

			if ( ! empty( $pos_user ) ) {
				if ( in_array( $pos_user, $status_arr ) ) {
					return $status_arr;
				} else {
					return array( $status_arr[ $pos_user ] );
				}
			} else {
				return $status_arr;
			}
		}

		/**
		 * Get pos outlet status.
		 *
		 * @param string $pos_user Pos user.
		 *
		 * @return array $status_arr Outlet status array.
		 */
		public function wk_wc_get_pos_outlet_status( $pos_user ) {
			$status_arr = array( esc_html__( 'Active', 'wc_pos' ), esc_html__( 'Deactive', 'wc_pos' ) );

			if ( ! empty( $pos_user ) ) {
				if ( in_array( $pos_user, $status_arr ) ) {
					return $status_arr;
				} else {
					return array( $status_arr[ $pos_user ] );
				}
			} else {
				return $status_arr;
			}
		}

		/**
		 * Pos login init internal function.
		 */
		public function wk_wc_poslogin_init_internal() {
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';
			add_rewrite_rule( $pos_endpoint . '$', 'index.php?' . $pos_endpoint . '=1', 'top' );
		}

		/**
		 * Pos login query vars function.
		 *
		 * @param array $query_vars Query vars.
		 *
		 * @return array $query_vars Query vars.
		 */
		public function wk_wc_poslogin_query_vars( $query_vars ) {
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			$query_vars[] = $pos_endpoint;
			$query_vars[] = 'pagename';
			$query_vars[] = 'main_page';
			$query_vars[] = 'view';
			$query_vars[] = 'pid';
			$query_vars[] = 'cid';

			return $query_vars;
		}

		/**
		 * Pos login parse request function.
		 *
		 * @param array|object $wp WordPress core object.
		 */
		public function wk_wc_poslogin_parse_request( &$wp ) {
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			if ( array_key_exists( $pos_endpoint, $wp->query_vars ) || ( ! empty( $wp->query_vars['pagename'] ) && $pos_endpoint == $wp->query_vars['pagename'] ) ) {

				new WC_Pos_Login();

				exit();
			}
		}

		/**
		 * Insert custom rules
		 *
		 * @param array $rules Custom rules.
		 *
		 * @return array $rules Custom rules
		 */
		public function wkwcpos_insertcustom_rules( $rules ) {

			$newrules = array();

			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			$pos_page_slug = $pos_endpoint;

			$newrules = array(
				$pos_page_slug . '/(.+)/(.+)/(.+)?' => 'index.php?pagename=' . $pos_page_slug . '&main_page=$matches[1]&view=$matches[2]&pid=$matches[3]',
				$pos_page_slug . '/(.+)/([0-9]+)?'  => 'index.php?pagename=' . $pos_page_slug . '&main_page=$matches[1]&cid=$matches[2]',
				$pos_page_slug . '/(.+)/?'          => 'index.php?pagename=' . $pos_page_slug . '&main_page=$matches[1]',
			);

			return $newrules + $rules;

		}

		/**
		 * Include custom query vars.
		 */
		public function wkwcpos_include_custom_query_vars() {
			global $wp_query;
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			$pagename  = isset( $wp_query->query_vars['pagename'] ) ? $wp_query->query_vars['pagename'] : '';
			$main_page = isset( $wp_query->query_vars['main_page'] ) ? $wp_query->query_vars['main_page'] : '';
			$view      = isset( $wp_query->query_vars['view'] ) ? $wp_query->query_vars['view'] : '';
			$pid       = isset( $wp_query->query_vars['pid'] ) ? $wp_query->query_vars['pid'] : '';
			$cid       = isset( $wp_query->query_vars['cid'] ) ? $wp_query->query_vars['cid'] : '';

			$main_pages = array(
				'orders',
				'cashier',
				'settings',
				'customers',
				'pay',
				'reports',
				'category',
			);

			$main_pages = apply_filters( 'wkwcpos_modify_main_pages_for_pos', $main_pages );

			$view_pages = array(
				'tab',
			);

			$pids = array(
				'history',
				'hold',
				'offline',
				'drawer',
				'sale',
				'today',
				'account',
				'other',
			);

			if ( ( ! empty( $pagename ) && $pos_endpoint === $pagename ) ) {

				if ( ! empty( $main_page ) && in_array( $main_page, $main_pages ) ) {

					new WC_Pos_Login();

					if ( ! empty( $cid ) ) {

						new WC_Pos_Login();

					}

					if ( ! empty( $view ) && ! empty( $pid ) && in_array( $view, $view_pages ) && in_array( $pid, $pids ) ) {

						new WC_Pos_Login();

					}
				} elseif ( empty( $main_page ) ) {

					new WC_Pos_Login();

				}

				if ( is_user_logged_in() ) {

					new WC_Pos_Login();
				}
				die;

			}

		}
	}
}
