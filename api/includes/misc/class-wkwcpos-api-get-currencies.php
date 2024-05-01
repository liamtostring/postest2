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
 * Get currencies class.
 */
class WKWCPOS_API_Get_Currencies {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'currencies';

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
	 * Get currencies API Callback.
	 *
	 * @param array $request Request array.
	 */
	public function get_currencies( $request ) {

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

			$currencies            = array();
			$currency_code_options = get_woocommerce_currencies();

			foreach ( $currency_code_options as $code => $name ) {

				$currencies[ $code ]['code']   = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
				$currencies[ $code ]['symbol'] = get_woocommerce_currency_symbol( $code );

			}
			$current_currency                = get_option( 'woocommerce_currency' );
			$currencies['default']['code']   = $current_currency;
			$currencies['default']['symbol'] = get_woocommerce_currency_symbol( $current_currency );

			return apply_filters( 'wkwcpos_modify_currency_details_at_pos', $currencies, $user_id );

		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}
	}

}
