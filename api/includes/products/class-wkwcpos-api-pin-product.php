<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @since    5.0.0
 */

namespace WKWC_POS\Api\Includes\Products;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Pin product api class.
 */
class WKWCPOS_API_Pin_Product {

	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'pin-product';

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
	 * Constructor of the class.
	 */
	public function __construct() {
		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->authentication = new WKWCPOS_API_Authentication();

	}

	/**
	 * Pin product method.
	 *
	 * @param array $request Requested data.
	 */
	public function wkwcpos_pin_product( $request ) {

		try {

			$user_id = isset( $request['logged_in_user_id'] ) ? $request['logged_in_user_id'] : '';

			$validate_auth_key = $this->authentication->wkwcpos_authenticate_request( $user_id );

			$response = array(
				'success' => false,
				'message' => '',
			);

			if ( 'ok' !== $validate_auth_key ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'wc_pos' ),
					'message'                => __( 'Please provide valid Auth Key.', 'wc_pos' ),
					'success'                => false,
				);
			}

			$product_id = isset( $request['product_id'] ) ? $request['product_id'] : '';
			$pin        = isset( $request['pin'] ) && 'pin' === $request['pin'] ? 'pin' : 'unpin';

			if ( ! empty( $product_id ) ) {

				if ( 'pin' === $pin ) {
					update_post_meta( $user_id, 'wkwcpos_pin_product_' . $product_id, 'pin' );

					$response = array(
						'success' => true,
						'message' => esc_html__( 'Product pinned successfully', 'wc_pos' ),
						'pin'     => 'pin',
					);
				} else {
					update_post_meta( $user_id, 'wkwcpos_pin_product_' . $product_id, 'unpin' );

					$response = array(
						'success' => true,
						'message' => esc_html__( 'Product unpinned successfully', 'wc_pos' ),
						'pin'     => 'unpin',
					);
				}
			} else {
				$response = array(
					'success' => false,
					'message' => esc_html__( 'Product id can not be empty.', 'wc_pos' ),
				);
			}

			return apply_filters( 'wkwcpos_modify_pin_product_response', $response );

		} catch ( \Exception $e ) {
			$this->error->set( 'exception', $e );
		}

	}
}
