<?php
/**
 * POS error class.
 *
 * @version 4.1.0
 * @package WKWC_POS\Inc
 */

namespace WKWC_POS\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Errors' ) ) {
	/**
	 * POS error class.
	 */
	class WC_Pos_Errors {

		/**
		 * Error code.
		 *
		 * @var int $error_code Error code ( 0 | 1 ).
		 */
		public $error_code = 0;

		/**
		 * Constructor of the error class.
		 *
		 * @param int $error_code Error code ( 0 | 1 ).
		 */
		public function __construct( $error_code = 0 ) {

			$this->error_code = $error_code;

		}

		/**
		 * Set error code function.
		 *
		 * @param int $code Error code ( 0 | 1 ).
		 */
		public function set_error_code( $code ) {

			if ( ! empty( $code ) ) {

				$this->error_code = $code;

			}
		}

		/**
		 * Getter function ( Get error code ).
		 *
		 * @return int $error_code Error code ( 0 | 1 ).
		 */
		public function get_error_code() {
			return $this->error_code;
		}

		/**
		 * Print error | success notification.
		 *
		 * @param string $message Notification message.
		 */
		public function wk_wc_pos_print_notification( $message ) {

			if ( is_admin() ) {

				if ( 0 === $this->error_code ) {

					echo '<div class="notice notice-success">';
					echo '<p>' . esc_html( $message ) . '</p>';
					echo '</div>';

				} elseif ( 1 === $this->error_code ) {

					echo '<div class="notice notice-error">';
					echo '<p>' . esc_html( $message ) . '</p>';
					echo '</div>';
				}
			} else {

				if ( 0 === $this->error_code ) {

					wc_print_notice( $message, 'success' );

				} elseif ( 1 === $this->error_code ) {

					wc_print_notice( $message, 'error' );

				}
			}

		}

	}

}
