<?php
/**
 * This file handles helper config class.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\Outlet;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Outlet_Helper' ) ) {

	/**
	 * Pos outlet helper class extends outlet_interface.
	 */
	class WC_Pos_Outlet_Helper implements Util\Outlet_Interface {

		/**
		 * Pos outlet table name.
		 *
		 * @var $table_name Outlet table name.
		 */
		public $table_name = '';

		/**
		 * Pos outlet map table name.
		 *
		 * @var $table_name2 Outlet map table name.
		 */
		public $table_name2 = '';

		/**
		 * Pos outlet product table name.
		 *
		 * @var $table_name3 Outlet product table name.
		 */
		public $table_name3 = '';

		/**
		 * Outlet id.
		 *
		 * @var int $outlet_id Outlet id.
		 */
		public $outlet_id = '';

		/**
		 * Database object.
		 *
		 * @var object $db Database object.
		 */
		public $db = '';

		/**
		 * Class constructor.
		 *
		 * @param int $outlet_id Outlet id.
		 */
		public function __construct( $outlet_id = '' ) {

			global $wpdb;

			$this->db          = $wpdb;
			$this->table_name  = $this->db->prefix . 'woocommerce_pos_outlets';
			$this->table_name2 = $this->db->prefix . 'woocommerce_pos_outlet_map';
			$this->table_name3 = $this->db->prefix . 'woocommerce_pos_outlet_product_map';

			if ( ! empty( $outlet_id ) ) {
				$this->outlet_id = $outlet_id;
			}

		}

		/**
		 * Update outlet status method.
		 *
		 * @param string $action Action type ( activate, deactivate, delete ).
		 * @param int    $outlet_id Outlet id.
		 *
		 * @return int|false The number of rows updated, or false on error.
		 */
		public function update_outlet_status( $action, $outlet_id ) {

			switch ( $action ) {
				case 'activate':
					$status = 0;
					break;

				case 'deactivate':
					$status = 1;
					break;

				case 'delete':
					$status = 0;
					break;

				default:
					$status = 0;
					break;
			}

			$response = $this->db->update(
				$this->table_name,
				array(
					'outlet_status' => $status,
				),
				array(
					'id' => intval( $outlet_id ),
				),
				array(
					'%d',
				),
				array(
					'%d',
				)
			);

			return $response;
		}

		/**
		 * Delete vendor outlet.
		 *
		 * @param int $outlet_id Outlet id.
		 *
		 * @return bool $response true on successful delete otherwise false.
		 */
		public function delete_vendor_outlet( $outlet_id ) {

			$response = $this->db->delete(
				$this->table_name,
				array(
					'id' => $outlet_id,
				),
				array(
					'%d',
				)
			);

			if ( $response ) {

				$this->db->delete(
					$this->table_name2,
					array(
						'outlet_id' => $outlet_id,
					),
					array(
						'%d',
					)
				);

				$this->db->delete(
					$this->table_name3,
					array(
						'outlet_id' => $outlet_id,
					),
					array(
						'%d',
					)
				);

				return true;

			} else {

				return false;
			}

		}

		/**
		 * Get all outlet count by search.
		 *
		 * @param string $text Search string.
		 *
		 * @return int $response Outlet count.
		 */
		public function pos_get_all_outlet_by_search_count( $text = '' ) {

			$text = apply_filters( 'wkwcpos_modify_search_term_for_getting_all_outlets_count', $text );

			$response = array();

			if ( ! empty( $text ) ) {

				$response = $this->db->get_var( $this->db->prepare( "Select count(*) from $this->table_name where outlet_name like %s", '%' . $text . '%' ) );

			} else {

				$response = $this->db->get_var( $this->db->prepare( "Select count(*) from $this->table_name where outlet_name like %s", '%' . $text . '%' ) );

			}

			return apply_filters( 'wkwcpos_modify_all_outlets_count', $response, $text );

		}

		/**
		 * Get all outlet list.
		 *
		 * @param string $text Search string.
		 * @param int    $off Offset for limit.
		 * @param int    $perpage Outlet list perpage.
		 *
		 * @return array $response Outlet list.
		 */
		public function pos_get_all_outlet_by_search( $text, $off, $perpage ) {

			$text = apply_filters( 'wkwcpos_modify_search_term_for_getting_all_outlets', $text );

			$response = array();

			if ( ! empty( $text ) ) {

				$response = $this->db->get_results( $this->db->prepare( "Select * from $this->table_name where outlet_name like %s LIMIT $perpage OFFSET $off", '%' . $text . '%' ) );

			} else {

				$response = $this->db->get_results( "Select * from $this->table_name LIMIT $perpage OFFSET $off" );

			}

			return apply_filters( 'wkwcpos_modify_all_outlets', $response, $text, $off, $perpage );

		}

		/**
		 * Get all pos outlets.
		 *
		 * @return array $response All outlet list.
		 */
		public function pos_get_all_outlets() {

			$response = array();

			$response = $this->db->get_results( "Select * from $this->table_name" );

			return apply_filters( 'wkwcpos_modify_all_outlets_for_filter_values', $response );

		}

		/**
		 * Get outlet data by outlet id.
		 *
		 * @return array $response Outlet data by outlet id.
		 */
		public function _get_pos_outlet() {

			$response = $this->db->get_row( "select * from $this->table_name where id=" . $this->outlet_id );

			return apply_filters( 'wkwcpos_modify_outlet_by_outlet_id', $response, $this->outlet_id );

		}

		/**
		 * Get outlet id by posuser id.
		 *
		 * @return int $response Outlet id.
		 */
		public function _get_pos_user_outlet() {

			$response = $this->db->get_var( "select outlet_id from $this->table_name where user_id=" . $this->outlet_id );

			return apply_filters( 'wkwcpos_modify_outlet_id_by_user_id', $response, $this->outlet_id );

		}

	}

}
