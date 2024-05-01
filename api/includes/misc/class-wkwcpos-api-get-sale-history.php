<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @since    3.2.0
 */

namespace WKWC_POS\Api\Includes\Misc;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;
use WKWC_POS\Api\Helper\WKWCPOS_API_User_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get sale history api class.
 */
class WKWCPOS_API_Get_Sale_History {

	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'get-sale-history';

	/**
	 * Namespace Name.
	 *
	 * @var string the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Database object.
	 *
	 * @var object $db Database object.
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

		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->helper         = new WKWCPOS_API_User_Outlet_Helper();
		$this->authentication = new WKWCPOS_API_Authentication();
	}

	/**
	 * API Callback.
	 *
	 * @param array $request Request array.
	 *
	 * @return array|object $result|$exception.
	 */
	public function get_sale_history( $request ) {
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

			if ( ! isset( $request['logged_in_user_id'] ) || empty( $request['logged_in_user_id'] ) ) {
				$this->error->set( 'user_id_required' );
			}

			$result = false;

			$pos_user = intval( $request['logged_in_user_id'] );

			$outlet_id = $this->helper->_get_pos_user_outlet_with_status( $pos_user );

			if ( ! empty( $pos_user ) && ! empty( $outlet_id ) ) {
				$result = $this->get_drawer_history_per_day( $outlet_id, $pos_user );
			}

			return $result;
		} catch ( \Exception $e ) {
			return $this->error->set( 'exception', $e );
		}
	}

	/**
	 * Get per day drqwer history.
	 *
	 * @param int $outlet_id Outlet id.
	 * @param int $pos_user Posuser id.
	 *
	 * @return array $drawer_data.
	 */
	public function get_drawer_history_per_day( $outlet_id, $pos_user ) {
		$data = array();

		$table_name = $this->db->prefix . 'woocommerce_pos_drawer_transaction';

		$res = $this->db->get_results( $this->db->prepare( "SELECT * from $table_name where outlet_id=%d ORDER BY t_id DESC", $outlet_id ) );

		if ( $res ) {
			foreach ( $res as $key => $value ) {
				$data[ $key ]['id']              = $value->t_id;
				$data[ $key ]['card_sale']       = $value->card_amount;
				$data[ $key ]['cash_sale']       = $value->cash_amount;
				$data[ $key ]['opening_balance'] = $value->opening_amount;
				$data[ $key ]['closing_balance'] = $value->closing_amount;
				$data[ $key ]['date']            = $value->date;
				$data[ $key ]['drawer_note']     = $value->remark ? $value->remark : 'N/A	';
			}
		}

		return apply_filters( 'wkwcpos_modify_drawer_history_at_pos', $data, $pos_user, $outlet_id );
	}
}
