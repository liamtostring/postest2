<?php
/**
 * This file handles all front end actions.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Front;

use WKWC_POS\Includes\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Front_Hook_Handler' ) ) {

	/**
	 * Front hook handler class.
	 */
	class WC_Pos_Front_Hook_Handler {

		/**
		 * Function handler class object.
		 *
		 * @var object $function_handler Function handler class object.
		 */
		public $function_handler = '';

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$this->function_handler = new Front\WC_Pos_Front_Function_Handler();

			add_action( 'woocommerce_init', array( $this->function_handler, 'wkwcpos_init_pos_api' ) );

			add_filter( 'login_form_middle', array( $this->function_handler, 'wkwcpos_add_lost_password_link' ) );

			add_action( 'woocommerce_checkout_create_order', array( $this->function_handler, 'wkwcpos_stop_mails_for_customers' ), 1, 1 );

		}

	}

}

