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
 * Delete customer class.
 */
class WKWCPOS_API_Delete_Customer {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'delete-customer';

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
	 * Namespace Name
	 *
	 * @var string $namespace the route namespace
	 */
	public $namespace = 'pos/v1';

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
	 * @param array $request Customer details.
	 *
	 * @return string|object $response Error or Success string|object.
	 */
	public function delete_customer( $request ) {

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

			if ( ! isset( $request['customer'] ) || empty( $request['customer'] ) ) {

				return $this->error->set( 'customer_id_not_set' );

			}

			$customer_id = wp_strip_all_tags( $request['customer'] );
			$customer    = get_user_by( 'ID', $customer_id );

			if ( ! $customer ) {

				return $this->error->set( 'customer_not_found' );
			}

			$role = $customer->roles;    // Get roles.

			// Check Role to be 'customer' while deleting customers.
			if ( ! empty( $role ) && in_array( 'customer', $role, true ) ) {

				require_once ABSPATH . 'wp-admin/includes/user.php';
				$response = wp_delete_user( intval( $customer->ID ) );
				return ( $response ) ? array( $customer_id ) : $this->error->set( 'customer_not_deleted' );

			} else {

				return $this->error->set( 'customer_not_found' );
			}
		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}
	}

}
