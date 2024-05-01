<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Api;

use WKWC_POS\Api\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register POS API Routes Class.
 *
 * @class WKWCPOS_API_Register_Routes
 */
class WKWCPOS_API_Register_Routes {

	/**
	 * Get session id class object.
	 *
	 * @var object $session_id Get session id class object.
	 */
	public $session_id;

	/**
	 * Login user api class object.
	 *
	 * @var object $login Login user api class object.
	 */
	public $login;

	/**
	 * Get category api class object.
	 *
	 * @var object $category Get category api class object.
	 */
	public $category;

	/**
	 * Get products api class object.
	 *
	 * @var object $get_products Get products api class object.
	 */
	public $get_products;

	/**
	 * Pin product api class object.
	 *
	 * @var object $pin_product Pin product api class object.
	 */
	public $pin_product;

	/**
	 * Get products id api class object.
	 *
	 * @var object $get_products_id Get products id api class object.
	 */
	public $get_products_id;

	/**
	 * Get customer api class object.
	 *
	 * @var object $customer Get customer api class object.
	 */
	public $customer;

	/**
	 * Delete customer api class object.
	 *
	 * @var object $delete_customer Delete customer api class object.
	 */
	public $delete_customer;

	/**
	 * Create customer api class object.
	 *
	 * @var object $create_customer Create customer api class object.
	 */
	public $create_customer;

	/**
	 * Get currencies api class object.
	 *
	 * @var object $get_currencies Get currencies api class object.
	 */
	public $get_currencies;

	/**
	 * Get countries api class object.
	 *
	 * @var object $get_countries Get countries api class object.
	 */
	public $get_countries;

	/**
	 * Get states api class object.
	 *
	 * @var object $get_states Get states api class object.
	 */
	public $get_states;

	/**
	 * Get sale history api class object.
	 *
	 * @var object $get_sale_history Get sale history api class object.
	 */
	public $get_sale_history;

	/**
	 * Create drawer perday api class object.
	 *
	 * @var object $create_drawer_perday Create drawer perday api class object.
	 */
	public $create_drawer_perday;

	/**
	 * Get tax details api class object.
	 *
	 * @var object $get_tax_details Get tax details api class object.
	 */
	public $get_tax_details;

	/**
	 * Update manager api class object.
	 *
	 * @var object $update_manager Update manager api class object.
	 */
	public $update_manager;

	/**
	 * Custom send emails api class object.
	 *
	 * @var object $custom_emails Custom send emails api class object.
	 */
	public $custom_emails;

	/**
	 * Check coupon ( is_valid ) api class object.
	 *
	 * @var object $coupon_check Check coupon ( is_valid ) api class object.
	 */
	public $coupon_check;

	/**
	 * Check stock api class object.
	 *
	 * @var object $stock_check Check stock api class object.
	 */
	public $stock_check;

	/**
	 * Create order api class object.
	 *
	 * @var object $create_order Create order api class object.
	 */
	public $create_order;

	/**
	 * Create offline order api class object.
	 *
	 * @var object $create_offline_order Create offline order api class object.
	 */
	public $create_offline_order;

	/**
	 * Get orders api class object.
	 *
	 * @var object $get_orders Get orders api class object.
	 */
	public $get_orders;

	/**
	 * Get pos payment modes api class object.
	 *
	 * @var object $payment_modes Get pos payment modes api class object.
	 */
	public $payment_modes;

	/**
	 * Get invoice template api class object.
	 *
	 * @var object $get_invoice_template Get invoice template api class object.
	 */
	public $get_invoice_template;

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
	 *  Constructor.
	 *
	 * @var object
	 */
	public function __construct() {

		$this->session_id           = new Includes\WKWCPOS_API_Get_Session_ID();
		$this->login                = new Includes\Login\WKWCPOS_API_Login_User();
		$this->category             = new Includes\Products\WKWCPOS_API_Product_Categories();
		$this->get_products         = new Includes\Products\WKWCPOS_API_Get_All_Products();
		$this->pin_product          = new Includes\Products\WKWCPOS_API_Pin_Product();
		$this->get_products_id      = new Includes\Products\WKWCPOS_API_Get_All_Products_Id();
		$this->customer             = new Includes\Customers\WKWCPOS_API_Get_Customers();
		$this->delete_customer      = new Includes\Customers\WKWCPOS_API_Delete_Customer();
		$this->create_customer      = new Includes\Customers\WKWCPOS_API_Create_Customer();
		$this->get_currencies       = new Includes\Misc\WKWCPOS_API_Get_Currencies();
		$this->get_countries        = new Includes\Misc\WKWCPOS_API_Get_Countries();
		$this->get_states           = new Includes\Misc\WKWCPOS_API_Get_States();
		$this->get_sale_history     = new Includes\Misc\WKWCPOS_API_Get_Sale_History();
		$this->create_drawer_perday = new Includes\Misc\WKWCPOS_API_Create_Drawer_Perday();
		$this->get_tax_details      = new Includes\Misc\WKWCPOS_API_Get_Tax_Details();
		$this->update_manager       = new Includes\Misc\WKWCPOS_API_Update_Manager();
		$this->custom_emails        = new Includes\Misc\WKWCPOS_API_Custom_Emails();
		$this->coupon_check         = new Includes\Orders\WKWCPOS_API_Coupon_Check();
		$this->stock_check          = new Includes\Orders\WKWCPOS_API_Product_Stock_Check();
		$this->create_order         = new Includes\Orders\WKWCPOS_API_Create_Order();
		$this->create_offline_order = new Includes\Orders\WKWCPOS_API_Create_Offline_Order();
		$this->get_orders           = new Includes\Orders\WKWCPOS_API_Get_Orders();
		$this->payment_modes        = new Includes\Payment\WKWCPOS_API_Get_Payment_Modes();
		$this->get_invoice_template = new Includes\Misc\WKWCPOS_API_Get_Invoice_Template();
		$this->get_reports          = new Includes\Reports\WKWCPOS_API_Reports();
		$this->get_search_reports   = new Includes\Reports\WKWCPOS_API_Search_Reports();
		add_action( 'rest_api_init', array( $this, 'wkwcpos_register_api_routes' ) );

	}

	/**
	 * Register api routes.
	 */
	public function wkwcpos_register_api_routes() {

		// ************GET-SESSION-ID API************ //
		register_rest_route(
			$this->session_id->namespace,
			$this->session_id->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->session_id, 'get_session_id' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-SESSION-ID API************ //

		// ************Login API************ //
		register_rest_route(
			$this->login->namespace,
			$this->login->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->login, 'user_login' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************Login API************ //

		// ************GET-CATEGORIES API************ //
		register_rest_route(
			$this->category->namespace,
			$this->category->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->category, 'get_all_categories' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-CATEGORIES API************ //

		// ************GET-CUSTOMERS API************ //
		register_rest_route(
			$this->customer->namespace,
			$this->customer->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->customer, 'get_customers' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-CUSTOMERS API************ //

		// ************DELETE-CUSTOMER API************ //
		register_rest_route(
			$this->delete_customer->namespace,
			$this->delete_customer->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->delete_customer, 'delete_customer' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************DELETE-CUSTOMER API************ //

		// ************CREATE-CUSTOMER API************ //
		register_rest_route(
			$this->create_customer->namespace,
			$this->create_customer->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->create_customer, 'create_customer' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************CREATE-CUSTOMER API************ //

		// ************COUPON-CHECK API************ //
		register_rest_route(
			$this->coupon_check->namespace,
			$this->coupon_check->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->coupon_check, 'check_coupon_online' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************COUPON-CHECK API************ //

		// ************GET-CURRENCIES API************ //
		register_rest_route(
			$this->get_currencies->namespace,
			$this->get_currencies->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_currencies, 'get_currencies' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-CURRENCIES API************ //

		// ************GET-COUNTRIES API************ //
		register_rest_route(
			$this->get_countries->namespace,
			$this->get_countries->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_countries, 'get_list_of_countries' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-COUNTRIES API************ //

		// ************GET-STATES API************ //
		register_rest_route(
			$this->get_states->namespace,
			$this->get_states->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_states, 'get_list_of_states' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-STATES API************ //

		// ************GET-Payment Modes API************ //
		register_rest_route(
			$this->payment_modes->namespace,
			$this->payment_modes->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->payment_modes, 'get_list_of_payment_modes' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-Payment Modes API************ //

		// ************SALARY-HISTORY API************ //
		register_rest_route(
			$this->get_sale_history->namespace,
			$this->get_sale_history->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_sale_history, 'get_sale_history' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************SALARY-HISTORY API************ //

		// ************GET-TAX-DETAILS API************ //
		register_rest_route(
			$this->get_tax_details->namespace,
			$this->get_tax_details->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_tax_details, 'get_tax_details' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-TAX-DETAILS API************ //

		// ************GET-INVOICE-TEMPLATE API************ //
		register_rest_route(
			$this->get_invoice_template->namespace,
			$this->get_invoice_template->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_invoice_template, 'get_invoice_template' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-INVOICE-TEMPLATE API************ //

		// ************DRAWER-PERDAY API************ //
		register_rest_route(
			$this->create_drawer_perday->namespace,
			$this->create_drawer_perday->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->create_drawer_perday, 'create_drawer_perday' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************DRAWER-PERDAY API************ //

		// ************UPDATE-POS-MANAGER API************ //
		register_rest_route(
			$this->update_manager->namespace,
			$this->update_manager->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->update_manager, 'update_pos_manager' ),
				'permission_callback' => '__return_true',
			)
		);

		// ************SEND CUSTOM EMAILS API************ //
		register_rest_route(
			$this->custom_emails->namespace,
			$this->custom_emails->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->custom_emails, 'wkwc_send_custom_emails' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************UPDATE-POS-MANAGER API************ //

		// ************GET-POS-PRODUCTS-ID API************ //
		register_rest_route(
			$this->get_products_id->namespace,
			$this->get_products_id->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_products_id, 'get_all_pos_products_id' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-POS-PRODUCTS-ID API************ //

		// ************GET-PRODUCTS API************ //
		register_rest_route(
			$this->get_products->namespace,
			$this->get_products->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_products, 'get_popular_products' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-PRODUCTS API************ //

		// ************PIN-PRODUCT API************ //
		register_rest_route(
			$this->pin_product->namespace,
			$this->pin_product->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->pin_product, 'wkwcpos_pin_product' ),
				'permission_callback' => '__return_true',
			)
		);

		// ************PIN-PRODUCT API************ //

		// ************CHECK-PRODUCT-STOCK API************ //
		register_rest_route(
			$this->stock_check->namespace,
			$this->stock_check->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->stock_check, 'wk_wc_pos_validate_product_stock' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************CHECK-PRODUCT-STOCK API************ //

		// ************CREATE-ORDERS API************ //
		register_rest_route(
			$this->create_order->namespace,
			$this->create_order->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->create_order, 'create_pos_order' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************CREATE-ORDERS API************ //

		// ************CREATE-OFFLINE-ORDERS API************ //
		register_rest_route(
			$this->create_offline_order->namespace,
			$this->create_offline_order->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->create_offline_order, 'create_offline_pos_order' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************CREATE-OFFLINE-ORDERS API************ //

		// ************GET-ORDERS API************ //
		register_rest_route(
			$this->get_orders->namespace,
			$this->get_orders->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_orders, 'get_pos_order' ),
				'permission_callback' => '__return_true',
			)
		);
		// ************GET-ORDERS API************ //

		// ************GET Reports API************ //
		register_rest_route(
			$this->get_reports->namespace,
			$this->get_reports->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_reports, 'get_reports_order_details' ),
				'permission_callback' => '__return_true',
			)
		);

		// ************GET Search Reports API************ //
		register_rest_route(
			$this->get_search_reports->namespace,
			$this->get_search_reports->base,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->get_search_reports, 'get_report_search_data' ),
				'permission_callback' => '__return_true',
			)
		);

		do_action( 'wkwcpos_register_pos_rest_routes' );

	}
}
