<?php
/**
 * WooCommerce POS API setup.
 *
 * @package WooCommerce Point of Sale
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get session id.
 */
class WKWCPOS_API_Get_Session_ID {

	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'get-session-id';

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
	 * Parent Constructor.
	 */
	public function __construct() {
		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->authentication = new WKWCPOS_API_Authentication();
	}

	/**
	 * Get session id API Callback.
	 *
	 * @param array $request Request array.
	 */
	public function get_session_id( $request ) {
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

			return array(
				'status'  => 200,
				'success' => true,
			);

		} catch ( \Exception $e ) {
			$this->error->set( 'exception', $e );
		}
	}
}
