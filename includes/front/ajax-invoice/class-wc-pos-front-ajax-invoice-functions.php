<?php
/**
 * This file handles all front end ajax callbacks.
 *
 * @package WooCommerce Point of Sale
 *
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Front\Ajax_Invoice;

use WKWC_POS\Helper\Invoice\WKWCPOS_Invoice_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Front_Ajax_Invoice_Functions' ) ) {
	/**
	 * Pos front ajax invoice function class.
	 */
	class WC_Pos_Front_Ajax_Invoice_Functions {

		/**
		 * WordPress database object.
		 *
		 * @var object $db WordPress database object.
		 */
		protected $db;

		/**
		 * Invoice helper class instance.
		 *
		 * @var object $invoice_helper Invoice helper class instance.
		 */
		public $invoice_helper;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			global $wpdb;

			$this->db             = $wpdb;
			$this->invoice_helper = new WKWCPOS_Invoice_Helper();
		}

		/**
		 * Save invoice function.
		 */
		public function wkwcpos_save_invoice() {

			if ( check_ajax_referer( 'api-ajaxnonce', 'nonce', false ) ) {

				$post_data = $_POST; // phpcs:ignore

				$data = array(
					'invoice_html' => ! empty( $post_data['invoice_html'] ) ? stripslashes_deep( $post_data['invoice_html'] ) : '',
					'id'           => ! empty( $post_data['id'] ) ? $post_data['id'] : '',
				);

				$success = $this->invoice_helper->wkwcpos_save_invoice_html( $data );

				wp_send_json(
					array(
						'success' => $success,
					)
				);
				exit();
			}
		}
	}
}
