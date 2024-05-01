<?php
/**
 * This file handles all front end actions.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Includes\Front;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Store' ) ) {

	/**
	 * Pos store class.
	 */
	class WC_Pos_Store {

		/**
		 * Pos global store method.
		 */
		public function wc_pos_global_store() {

			$defaultstate = array(

				'cashier'         => array(
					'first_name'  => '',
					'last_name'   => '',
					'email'       => '',
					'isLoggedIn'  => false,
					'isFetching'  => 0,
					'cashier_id'  => '',
					'profile_pic' => '',
					'logout_url'  => '',
				),
				'categories'      => array(
					'list'       => '',
					'isFetching' => 0,
				),
				'currency'        => array(
					'list'       => '',
					'isFetching' => 0,
					'default'    => '',
				),
				'countries'       => array(
					'list'       => '',
					'isFetching' => 0,
				),
				'tax'             => array(
					'list'       => '',
					'isFetching' => 0,
				),
				'invoice'         => '',
				'products'        => array(
					'list'       => '',
					'isFetching' => 0,
					's'          => '',
					'sproducts'  => '',
					'category'   => '',
					'cproducts'  => '',
				),

				'printers'        => array(
					'list'       => array(
						array( 'a4' => 'A4 Printer' ),
						array( 'a3' => 'A3 Printer' ),
						array( 'T88V' => 'Epson TM-T88V Thermal Printer' ),
						array( 'a5' => 'A5 Printer' ),
						array( 'a6' => 'A6 Printer' ),
					),
					'isFetching' => 1,
					'default'    => ! empty( get_option( '_pos_printer_type' ) ) ? get_option( '_pos_printer_type' ) : 'a4',
				),
				'customers'       => array(
					'list'       => '',
					'isFetching' => 0,
					'default'    => 0,
					's'          => '',
					'scustomer'  => '',
				),
				'orders'          => array(
					'list'       => '',
					'isFetching' => 0,
					's'          => '',
					'sorder'     => '',
				),
				'cart'            => array(
					'list'       => '',
					'isFetching' => 0,
					'total'      => '',
				),
				'discount'        => array(
					'list'       => '',
					'isFetching' => 0,
				),
				'coupon'          => array(
					'list'       => '',
					'isFetching' => 0,
				),
				'hold'            => array(
					'list'       => '',
					'isFetching' => 1,
				),
				'sale'            => array(
					'list'       => '',
					'isFetching' => 1,
				),
				'sideMenuVisible' => false,
				'current_cart'    => 0,

			);

			$defaultstate = apply_filters( 'wc_pos_global_store', $defaultstate );

			return $defaultstate;
		}


		/**
		 * Pos reducers.
		 */
		public function wc_pos_reducers() {

			$reducers = array(
				'sideMenuVisible',
				'current_cart',
				'currency',
				'customers',
				'categories',
				'tax',
				'invoice',
				'countries',
				'cashier',
				'products',
				'orders',
				'printers',
				'discount',
				'coupon',
				'cart',
				'hold',
				'sale',
			);

			$reducers = apply_filters( 'wc_pos_reducers', $reducers );

			return $reducers;
		}

	}

}

