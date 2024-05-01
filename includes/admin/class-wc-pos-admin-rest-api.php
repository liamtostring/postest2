<?php
/**
 * This file handles all admin end actions.
 *
 * @package     WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Includes\Admin;

use WKWC_POS\Api\Includes\SetupWizard;
use WKWC_POS\Api\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Admin_Rest_Api' ) ) {

	/**
	 * POS admin rest api class.
	 */
	class WC_Pos_Admin_Rest_Api {

		/**
		 * Get reports api class data.
		 *
		 * @var $get_reports Get reports api class object.
		 */
		public $get_reports;

		/**
		 * Get reports api class data.
		 *
		 * @var $get_search_reports Get search reports api class object.
		 */
		public $get_search_reports;

		/**
		 * Setup wizard api.
		 *
		 * @var object $setup_wizard_api Setup wizard api class instance.
		 */
		public $setup_wizard_api;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$this->setup_wizard_api = new SetupWizard\WKWCPOS_Setup_Wizard_API();

			$this->get_reports        = new Includes\Reports\WKWCPOS_API_Reports();
			$this->get_search_reports = new Includes\Reports\WKWCPOS_API_Search_Reports();

			add_action( 'rest_api_init', array( $this, 'wkwcpos_admin_register_api_routes' ) );
		}

		/**
		 * Register admin api routes.
		 */
		public function wkwcpos_admin_register_api_routes() {

			register_rest_route(
				'adminpos/v1',
				$this->setup_wizard_api->get_base,
				array(
					'methods'             => 'GET',
					'callback'            => array( $this->setup_wizard_api, 'wk_wc_pos_get_setup_wizard_data' ),
					'permission_callback' => '__return_true',
				)
			);

			register_rest_route(
				'adminpos/v1',
				$this->setup_wizard_api->post_base,
				array(
					'methods'             => 'POST',
					'callback'            => array( $this->setup_wizard_api, 'wk_wc_pos_save_setup_wizard_data' ),
					'permission_callback' => '__return_true',
				)
			);

			// ************GET Reports API************ //
			register_rest_route(
				'adminpos/v1',
				$this->get_reports->base,
				array(
					'methods'             => 'POST',
					'callback'            => array( $this->get_reports, 'get_reports_order_details' ),
					'permission_callback' => '__return_true',
				)
			);

			// ************GET Search Reports API************ //
			register_rest_route(
				'adminpos/v1',
				$this->get_search_reports->base,
				array(
					'methods'             => 'POST',
					'callback'            => array( $this->get_search_reports, 'get_report_search_data' ),
					'permission_callback' => '__return_true',
				)
			);
		}
	}
}
