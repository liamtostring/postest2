<?php
/**
 * WooCommerce POS API setup
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Misc;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get states api class.
 */
class WKWCPOS_API_Get_States {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'get-states';

	/**
	 * Namespace Name
	 *
	 * @var string $namespace the route namespace
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
	 * @return array|object $response|$exception.
	 */
	public function get_list_of_states( $request ) {

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

			if ( ! isset( $request['country_code'] ) || empty( $request['country_code'] ) ) {
				return $this->error->set( 'country_is_empty' );
			}

			$country = wp_unslash( $request['country_code'] );

			$countries_obj          = new \WC_Countries();
			$default_country_states = $countries_obj->get_states( $country );

			$response = array(
				'states' => $default_country_states,
			);

			return apply_filters( 'wkwcpos_modify_states_list_at_pos', $response, $user_id );

		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}
	}

}
