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
 * Custom emails class.
 */
class WKWCPOS_API_Custom_Emails {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'custom_emails';

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
	 * Send custom emails.
	 *
	 * @param array $request Request array.
	 */
	public function wkwc_send_custom_emails( $request ) {
		try {
			$user_id   = $request['logged_in_user_id'];
			$pos_order = $request['order'];
			$order_id  = $pos_order['order_id'];
			$order     = wc_get_order( $order_id );
			$wc_emails = WC()->mailer()->get_emails();
			if ( empty( $wc_emails ) ) {
				return;
			}
			if ( $order->has_status( 'on-hold' ) ) {
				$email_id = 'customer_on_hold_order';
			} elseif ( $order->has_status( 'processing' ) ) {
				$email_id = 'customer_processing_order';
			} elseif ( $order->has_status( 'completed' ) ) {
				$email_id = 'customer_completed_order';
			} else {
				$email_id = 'nothing';
			}
			foreach ( $wc_emails as $wc_mail ) {
				if ( $wc_mail->id === $email_id ) {
					$wc_mail->trigger( $order->get_id() );
					$data = array( 'status' => 'success' );
				}
			}

			return $data;
		} catch ( \Exception $e ) {
			return $this->error->set( 'exception', $e );
		}
	}
}
