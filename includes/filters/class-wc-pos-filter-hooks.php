<?php
/**
 * This file handles all admin end actions.
 *
 * @version    1.0.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Includes\Filters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Filter_Hooks' ) ) {

	/**
	 * Pos hooks( filter/action  ) class.
	 */
	class WC_Pos_Filter_Hooks {

		/**
		 * Callback function class object.
		 *
		 * @var object $obj Callback function class object.
		 */
		public $obj;

		/**
		 * Constructor of the class.
		 *
		 * @param object $obj Callback functions class object.
		 */
		public function __construct( $obj ) {

			$this->obj = $obj;

			// Filters.
			add_filter( 'pos_user_status', array( $this->obj, 'wk_wc_get_pos_user_status' ), 10, 1 );

			add_filter( 'pos_outlet_status', array( $this->obj, 'wk_wc_get_pos_outlet_status' ), 10, 1 );

			add_filter( 'pos_outlet_list', array( $this->obj, 'wk_wc_get_pos_outlet_list' ), 10, 1 );
			add_filter( 'get_countries_list', array( $this->obj, 'wk_wc_get_pos_countries_list' ), 10, 1 );
			add_action( 'pos_save_default_customer', array( $this->obj, 'wk_wc_save_pos_default_customer' ), 10, 1 );
			add_action( 'wkwcpos_save_pos_payment', array( $this->obj, 'wk_wc_save_pos_payment' ), 10, 1 );

			add_action( 'init', array( $this->obj, 'wk_wc_poslogin_init_internal' ) );
			add_filter( 'query_vars', array( $this->obj, 'wk_wc_poslogin_query_vars' ) );

			add_filter( 'rewrite_rules_array', array( $this->obj, 'wkwcpos_insertcustom_rules' ) );

			add_action( 'wp_login_failed', array( $this->obj, 'wk_wc_pos_login_failed' ) );

			add_filter( 'wp_loaded', array( $this->obj, 'wk_wc_pos_managers_only' ), 10, 3 );

			add_action( 'parse_request', array( $this->obj, 'wk_wc_poslogin_parse_request' ) );

			add_action( 'wp', array( $this->obj, 'wkwcpos_include_custom_query_vars' ) );

		}

	}

}
