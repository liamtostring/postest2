<?php
/**
 * WooCommerce POS API setup
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Payment;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get payment modes api class.
 */
class WKWCPOS_API_Get_Payment_Modes {


	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'payment-modes';

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
	 * List of payment modes API Callback.
	 *
	 * @param array $request Request array.
	 *
	 * @return array|Error Outlet payment modes on success or Exception error.
	 */
	public function get_list_of_payment_modes( $request ) {
		global $wpdb;

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

			$outlet_id = $wpdb->get_var( $wpdb->prepare( "SELECT outlet_id FROM {$wpdb->prefix}woocommerce_pos_outlet_map WHERE user_id=%d", $user_id ) );

			$outlet_payment_modes = $wpdb->get_var( $wpdb->prepare( "SELECT outlet_payment from {$wpdb->prefix}woocommerce_pos_outlets WHERE id = %d", $outlet_id ) );

			if ( ! empty( $outlet_payment_modes ) ) {
				$outlet_payment_modes = implode( ', ', maybe_unserialize( $outlet_payment_modes ) );
				$outlet_payment_modes = $wpdb->get_results( "SELECT id, payment_slug, payment_name from {$wpdb->prefix}woocommerce_pos_payments WHERE id IN ( $outlet_payment_modes )", ARRAY_A );
				$outlet_payment_modes = maybe_unserialize( $outlet_payment_modes );
			} else {
				$outlet_payment_modes = array();
			}

			$cash = array(
				'id'           => '0',
				'payment_slug' => 'cash',
				'payment_name' => __( 'Cash Payment', 'wc_pos' ),
			);
			$card = array(
				'id'           => '1',
				'payment_slug' => 'card',
				'payment_name' => __( 'Card Payment', 'wc_pos' ),
			);

			array_push( $outlet_payment_modes, (object) $cash );
			array_push( $outlet_payment_modes, (object) $card );

			return apply_filters( 'wkwcpos_modify_payment_modes_list_at_pos', $outlet_payment_modes, $user_id );
		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}
	}
}
