<?php
/**
 * WooCommerce POS API setup
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Misc;

use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;
use WKWC_POS\Api\Helper\WKWCPOS_API_User_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get invoice template class.
 */
class WKWCPOS_API_Get_Invoice_Template {

	/**
	 * Base Name
	 *
	 * @var string $base the route base
	 */
	public $base = 'get-invoice-template';

	/**
	 * Namespace Name
	 *
	 * @var string $namespace the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Table name.
	 *
	 * @var string $table_name Table name.
	 */
	public $table_name = '';

	/**
	 * Database object.
	 *
	 * @var object $db.
	 */
	public $db = '';

	/**
	 * Error class object.
	 *
	 * @var object $error Error class object.
	 */
	public $error;

	/**
	 * User outlet helper class object.
	 *
	 * @var object $helper User outlet helper class object.
	 */
	public $helper;

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

		global $wpdb;

		$this->db = $wpdb;

		$this->helper         = new WKWCPOS_API_User_Outlet_Helper();
		$this->authentication = new WKWCPOS_API_Authentication();
		$this->table_name     = $this->db->prefix . 'woocommerce_pos_invoice_templates';

	}

	/**
	 * Get invoice template.
	 *
	 * @param array $request Request array.
	 *
	 * @return string invoice html.
	 */
	public function get_invoice_template( $request ) {

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

		$pos_user = $request['logged_in_user_id'];

		$outlet_id = $this->helper->_get_pos_user_outlet_with_status( $pos_user );

		$outlet_invoice = '
                <style>
                    .wkwcpos-invoice-wrapper {
                        padding: 10px;
                        background-color: #fff;
                        border-radius: 2px;
                        grid-area: second;
                    }
                    .wkwcpos-invoice-wrapper * {
                        padding: 0;
                        margin: 0;
                    }
                    .wkwcpos-invoice-wrapper .invoice-header, .wkwcpos-invoice-wrapper .invoice-footer .footer-details {
                        text-align: center;
                    }
                    .wkwcpos-invoice-wrapper .invoice-header img {
                        width: 50px;
                        margin: 10px 0;
                    }
                    .wkwcpos-invoice-wrapper .invoice-details {
                        width: 100%;
                        display: inline-block;
                    }
                    .wkwcpos-invoice-wrapper .order-details, .wkwcpos-invoice-wrapper .outlet-details {
                        width: 50%;
                    }
                    .wkwcpos-invoice-wrapper .invoice-details .order-details {
                        float: left;
                    }
                    .wkwcpos-invoice-wrapper .invoice-details .outlet-details {
                        float: right;
                        text-align: right;
                    }
                    .wkwcpos-invoice-wrapper .product-details {
                        margin: 15px 0;
                    }
                    .wkwcpos-invoice-wrapper .product-details table {
                        border-collapse: collapse;
                        width: 100%;
                    }
                    .wkwcpos-invoice-wrapper .product-details table th, .wkwcpos-invoice-wrapper .product-details table td {
                        padding: 3px 0;
                        vertical-align:middle;
                    }
                    .wkwcpos-invoice-wrapper .product-details table th, .wkwcpos-invoice-wrapper .product-details table td p {
                        padding: 3px 0;
                        vertical-align:middle;
                    }
                    .dashed-wrap {
                       border-top: 3px dashed #ddd;
                    border-bottom: 3px dashed #ddd;
                    }
                    .dashed-top {
                        border-top: 3px dashed #ddd;
                    }
                    .dashed-bottom {
                        border-bottom: 3px dashed #ddd;
                    }

                    .wkwcpos-invoice-wrapper hr {
                        width: 35%;
                        margin: 10px auto 7px;
                        border-style: dashed;
                        border-width: 3px 0;
                        border-top-color: #ddd;
                        border-bottom-color: #fafafa;
                    }
                </style>

                <div class="wkwcpos-invoice-wrapper">

                    <div class="invoice-header wkwcpos-invoice-editable">
                        <p class="wkwcpos-invoice-editable">Tax Invoice/Bill of Supply</p>
                        <img src="${logo_invoice}" class="wkwcpos-invoice-editable" />
                        <h3 class="wkwcpos-invoice-editable">${outlet_name}</h3>
                    </div>

                    <div class="invoice-details">
                        <div class="order-details">
                            <p class="wkwcpos-invoice-editable">Order - ${order_id}</p>
                            <p class="wkwcpos-invoice-editable">Date : ${order_date}</p>
                            <p class="wkwcpos-invoice-editable">Customer : ${customer_fname} ${customer_lname}</p>
                        </div>
                        <div class="outlet-details">
                            <p class="wkwcpos-invoice-editable">${outlet_address}</p>
                            <p class="wkwcpos-invoice-editable">${outlet_city} ${outlet_state}</p>
                            <p class="wkwcpos-invoice-editable">Tel No: ${customer_phone}</p>
                        </div>
                    </div>

                    <div class="product-details">
                        <table>
                            <tbody class="products-thead">
                                <tr>
                                    <th class="wkwcpos-invoice-editable dashed-wrap" style="width:40%;text-align:left;">Product Name</th>
                                    <th class="wkwcpos-invoice-editable dashed-wrap" style="width:20%;text-align:center;">Unit Price</th>
                                    <th class="wkwcpos-invoice-editable dashed-wrap" style="width:20%;text-align:center;">Quantity</th>
                                    <th class="wkwcpos-invoice-editable dashed-wrap" style="width:20%;text-align:right;">Total Price</th>
                                </tr>
                            </tbody>
                            <tbody>
                                 <tr>
                                    <td colspan="4" class="wkwcpos-invoice-editable order_products_data dashed-bottom">${order_products_data}</td>
                                    </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">SubTotal</td>
                                    <td class="wkwcpos-invoice-editable">${sub_total}</td>
                                </tr>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">${tax_title}</td>
                                    <td class="wkwcpos-invoice-editable">${order_tax}</td>
                                </tr>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">Discount</td>
                                    <td class="wkwcpos-invoice-editable">${order_discount}</td>
                                </tr>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">${coupon_name}</td>
                                    <td class="wkwcpos-invoice-editable">${coupon_amount}</td>
                                </tr>
                            </tbody>
                            <tbody>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">Total</td>
                                    <td class="wkwcpos-invoice-editable">${order_total}</td>
                                </tr>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">Cash Payment</td>
                                    <td class="wkwcpos-invoice-editable">${cashpay_amount}</td>
                                </tr>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">${other_payment_text}</td>
                                    <td class="wkwcpos-invoice-editable">${otherpay_amount}</td>
                                </tr>
                                <tr>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable"></td>
                                    <td class="wkwcpos-invoice-editable">Change</td>
                                    <td class="wkwcpos-invoice-editable">${order_change}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="invoice-footer dashed-top">
                        <p class="wkwcpos-invoice-editable">Note: ${cashier_note}</p>
                        <p class="wkwcpos-invoice-editable">Cashier: ${cashier_name}</p>
                        <div class="footer-details">
                            <p class="wkwcpos-invoice-editable">${outlet_name}</p>
                            <p class="wkwcpos-invoice-editable">Tel No: ${pos_user_phone}</p>
                            <p class="wkwcpos-invoice-editable">Email: ${pos_user_email}</p>
                            <hr class="wkwcpos-invoice-editable" />
                            <p class="wkwcpos-invoice-editable">Have a nice day</p>
                        </div>
                    </div>
                </div>';

		if ( ! empty( $outlet_id ) ) {

			$invoice_id = $this->db->get_var( $this->db->prepare( "SELECT outlet_invoice from {$this->db->prefix}woocommerce_pos_outlets WHERE id = %d", $outlet_id ) );

			if ( ! empty( $invoice_id ) ) {
				$invoice = $this->db->get_var( $this->db->prepare( "SELECT invoice_html from $this->table_name WHERE id=%d", $invoice_id ) );

				if ( ! empty( $invoice ) ) {
					$outlet_invoice = $invoice;
				}
			}
		}

		return apply_filters( 'wkwcpos_modify_invoice_template_at_pos', $outlet_invoice, $pos_user );

	}

}
