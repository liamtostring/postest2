<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\SetupWizard;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Setup Wizard.
 */
class WKWCPOS_Setup_Wizard_API {

	/**
	 * Base Name.
	 *
	 * @var string $get_base Route base
	 */
	public $get_base = 'get-setup-wizard-data';

	/**
	 * Post base name.
	 *
	 * @var string $post_base Post route base.
	 */
	public $post_base = 'save-setup-wizard-data';

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
	 * Parent Constructor.
	 */
	public function __construct() {
		$this->error = new WKWCPOS_API_Error_Handler();
		// hook for pos report tab.
	}

	/**
	 * Get setup wizard data API Callback.
	 */
	public function wk_wc_pos_get_setup_wizard_data() {

		try {

				$args = array(
					'meta_key'   => 'deault_customer_pos',
					'meta_value' => '1',
				);

				$domain = str_replace(
					array( 'https://', 'http://', 'Https://', 'Http://', '/', 'www', 'www.' ),
					array( '', '', '', '', '', '', '' ),
					site_url()
				);

				$random_password = wp_generate_password( 12, true, false );

				$api_username   = empty( get_option( 'wkwcpos_api_username' ) ) ? 'admin' : get_option( 'wkwcpos_api_username' );
				$api_password   = empty( get_option( 'wkwcpos_api_password' ) ) ? $random_password : get_option( 'wkwcpos_api_password' );
				$inventory_type = empty( get_option( '_pos_inventory_type' ) ) ? 'centralized_stock' : get_option( '_pos_inventory_type' );
				$pwa_name       = empty( get_option( '_pos_pwa_name' ) ) ? 'Point of Sale' : get_option( '_pos_pwa_name' );
				$pwa_shortname  = empty( get_option( '_pos_pwa_shortname' ) ) ? 'POS' : get_option( '_pos_pwa_shortname' );
				$pwa_themecolor = get_option( '_pos_pwa_themecolor' );
				$pwa_bgcolor    = get_option( '_pos_pwa_bgcolor' );
				$user_id        = '';
				$username       = 'deafultcustomer';
				$phone          = '9999999999';
				$email          = 'deafultcustomer@' . apply_filters( 'wc_pos_modify_default_customer_domain', $domain );
				$password       = wp_generate_password( 12, true, false );

				$pos_customer = get_users( $args );
			if ( ! empty( $pos_customer ) ) {
				foreach ( $pos_customer as $customer ) {
					$user_id  = $customer->ID;
					$phone    = get_user_meta( $user_id, 'billing_phone', true );
					$username = $customer->user_login;
					$email    = $customer->user_email;
					$password = $customer->user_pass;
				}
			}

				$pos_wizard_settings_data = array(

					'id'             => esc_attr( $user_id ),
					'api_username'   => esc_attr( $api_username ),
					'api_password'   => esc_attr( $api_password ),
					'inventory_type' => esc_attr( $inventory_type ),
					'pwa_name'       => esc_attr( $pwa_name ),
					'pwa_shortname'  => esc_attr( $pwa_shortname ),
					'pwa_themecolor' => esc_attr( $pwa_themecolor ),
					'pwa_bgcolor'    => esc_attr( $pwa_bgcolor ),
					'username'       => esc_attr( $username ),
					'phone'          => esc_attr( $phone ),
					'email'          => esc_attr( $email ),
					'password'       => esc_attr( $password ),

				);

				return apply_filters( 'wkwcpos_modify_pos_get_setup_wizard_data', $pos_wizard_settings_data );
		} catch ( \Exception $e ) {
			$this->error->set( 'exception', $e );
		}
	}

	/**
	 * Save setup wizard data.
	 *
	 * @param array $request Request array.
	 *
	 * @return  array|Error Success array on success or Validation error or Exception error.
	 */
	public function wk_wc_pos_save_setup_wizard_data( $request ) {

			update_option( 'wkwc_pos_setup_wizard_completed', 1, true );

		try {

			$api_username   = isset( $request['api_username'] ) ? wc_clean( wp_unslash( $request['api_username'] ) ) : '';
			$api_password   = isset( $request['api_password'] ) ? wc_clean( wp_unslash( $request['api_password'] ) ) : '';
			$inventory_type = isset( $request['inventory_type'] ) ? wc_clean( wp_unslash( $request['inventory_type'] ) ) : '';
			$pwa_name       = isset( $request['pos_name'] ) ? wc_clean( wp_unslash( $request['pos_name'] ) ) : '';
			$pwa_shortname  = isset( $request['short_name'] ) ? wc_clean( wp_unslash( $request['short_name'] ) ) : '';
			$pwa_themecolor = isset( $request['theme_color'] ) ? wc_clean( wp_unslash( $request['theme_color'] ) ) : '';
			$pwa_bgcolor    = isset( $request['background_color'] ) ? wc_clean( wp_unslash( $request['background_color'] ) ) : '';
			$username       = isset( $request['username'] ) ? wc_clean( wp_unslash( $request['username'] ) ) : '';
			$user_password  = isset( $request['user_password'] ) ? wc_clean( wp_unslash( $request['user_password'] ) ) : '';
			$user_telephone = isset( $request['user_telephone'] ) ? wc_clean( wp_unslash( $request['user_telephone'] ) ) : '';
			$user_email     = isset( $request['user_email'] ) ? wc_clean( wp_unslash( $request['user_email'] ) ) : '';
			$user_id        = isset( $request['id'] ) ? wc_clean( wp_unslash( $request['id'] ) ) : '';

			$error_code               = 0;
			$username_error_msg       = '';
			$user_email_error_msg     = '';
			$user_password_error_msg  = '';
			$user_telephone_error_msg = '';

			if ( empty( $username ) ) {
				$username_error_msg = esc_html__( 'username is mandatory', 'wc_pos' );
				$error_code         = 1;
			}

			if ( empty( $user_email ) ) {
				$user_email_error_msg = esc_html__( 'Customer email is mandatory', 'wc_pos' );
				$error_code           = 1;
			} elseif ( ! filter_var( $user_email, FILTER_VALIDATE_EMAIL ) ) {
				$user_email_error_msg = esc_html__( 'Customer email is not valid', 'wc_pos' );
				$error_code           = 1;
			}

			if ( empty( $user_password ) ) {
				$user_password_error_msg = esc_html__( 'Customer password is mandatory.', 'wc_pos' );
				$error_code              = 1;
			}

			if ( empty( $user_telephone ) ) {
				$user_telephone_error_msg = esc_html__( 'Customer Phone number is mandatory.', 'wc_pos' );
				$error_code               = 1;
			} elseif ( strlen( $user_telephone ) < 4 || strlen( $user_telephone ) > 12 ) {
				$user_telephone_error_msg = esc_html__( 'Please enter a valid phone number', 'wc_pos' );
				$error_code               = 1;
			}

			if ( 0 === $error_code ) {
				if ( empty( $user_id ) ) {
					if ( false === email_exists( $user_email ) ) {

						// Create the user.
						$elm = explode( '@', $user_email );
						$elm = $elm[0];

						$user_id = wc_create_new_customer( $user_email, $elm, $user_password );

						// Set the nickname.
						wp_update_user(
							array(
								'ID'         => $user_id,
								'nickname'   => $user_email,
								'first_name' => esc_html__( 'default', 'wc_pos' ),
								'last_name'  => esc_html__( 'customer', 'wc_pos' ),
							)
						);

						update_option( 'wkwcpos_api_username', $api_username );
						update_option( 'wkwcpos_api_password', $api_password );
						update_option( '_pos_inventory_type', $inventory_type );
						update_option( '_pos_pwa_name', $pwa_name );
						update_option( '_pos_pwa_shortname', $pwa_shortname );
						update_option( '_pos_pwa_themecolor', $pwa_themecolor );
						update_option( '_pos_pwa_bgcolor', $pwa_bgcolor );

						update_user_meta( $user_id, 'billing_username', $username );
						update_user_meta( $user_id, 'billing_first_name', esc_html__( 'default', 'wc_pos' ) );
						update_user_meta( $user_id, 'billing_last_name', esc_html__( 'customer', 'wc_pos' ) );
						update_user_meta( $user_id, 'billing_email', $user_email );
						update_user_meta( $user_id, 'billing_phone', $user_telephone );
						update_user_meta( $user_id, 'deault_customer_pos', true );

						return array(
							'code'    => 'ok',
							'message' => esc_html__( 'POS setup wizard data inserted', 'wc_pos' ),
							'data'    => array( 'status' => 200 ),
						);

					} else {
						return new \WP_Error( 'Error', esc_html__( 'User already exists from this email address', 'wc_pos' ), array( 'status' => 409 ) );
					}
				} else {

					// Set the nickname.
					wp_update_user(
						array(
							'ID'         => $user_id,
							'user_email' => $user_email,
							'user_login' => $username,
							'user_pass'  => $user_password,
						)
					);

					update_option( 'wkwcpos_api_username', $api_username );
					update_option( 'wkwcpos_api_password', $api_password );
					update_option( '_pos_inventory_type', $inventory_type );
					update_option( '_pos_pwa_name', $pwa_name );
					update_option( '_pos_pwa_shortname', $pwa_shortname );
					update_option( '_pos_pwa_themecolor', $pwa_themecolor );
					update_option( '_pos_pwa_bgcolor', $pwa_bgcolor );

					update_user_meta( $user_id, 'billing_username', $username );
					update_user_meta( $user_id, 'billing_email', $user_email );
					update_user_meta( $user_id, 'billing_phone', $user_telephone );
					update_user_meta( $user_id, 'deault_customer_pos', true );

					return array(
						'code'    => 'ok',
						'message' => esc_html__( 'POS setup wizard data updated', 'wc_pos' ),
						'data'    => array( 'status' => 200 ),
					);
				}
			} else {
				$errors = array(
					'username_error_msg'       => $username_error_msg,
					'user_email_error_msg'     => $user_email_error_msg,
					'user_password_error_msg'  => $user_password_error_msg,
					'user_telephone_error_msg' => $user_telephone_error_msg,
				);
				return new \WP_Error( 'Error', $errors, array( 'status' => 400 ) );
			}
		} catch ( \Exception $e ) {
			$this->error->set( 'exception', $e );
		}
	}
}
