<?php
/**
 * This file handles helper config class.
 *
 * @version 4.1.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Helper\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_User_Helper' ) ) {

	/**
	 * Class for includin core pos user data.
	 */
	class WC_Pos_User_Helper implements Util\User_Interface {

		/**
		 * Pos outlet map table name.
		 *
		 * @var string $table_name Pos outlet map table name.
		 */
		public $table_name = '';

		/**
		 * Pos outlet table name.
		 *
		 * @var string $table_name_outlet Pos outlet table name.
		 */
		public $table_name_outlet = '';

		/**
		 * Logged in user id.
		 *
		 * @var int $puser_id Logged in user id.
		 */
		public $puser_id = '';

		/**
		 * Database object.
		 *
		 * @var object $db Database object.
		 */
		public $db;

		/**
		 * Class constructor.
		 *
		 * @param int $puser_id User id.
		 */
		public function __construct( $puser_id = '' ) {

			global $wpdb;

			$this->db = $wpdb;

			$this->table_name        = $this->db->prefix . 'woocommerce_pos_outlet_map';
			$this->table_name_outlet = $this->db->prefix . 'woocommerce_pos_outlets';
			$this->puser_id          = get_current_user_id();

			if ( ! empty( $puser_id ) ) {

				$this->puser_id = $puser_id;

			}

		}

		/**
		 * Get all pos users count.
		 *
		 * @param string $s Search string.
		 *
		 * @return int $pos_users Pos user count.
		 */
		public function pos_get_all_users_count( $s = '' ) {

			$s = apply_filters( 'wkwcpos_modify_search_term_for_getting_pos_user_count', $s );

			if ( ! empty( $s ) ) {

				$search_query = $s;
				$args         = array(
					'search'   => $search_query,
					'fields'   => 'all_with_meta',
					'role__in' => array( 'pos_user', '' ),
				);
				if ( '' !== $args['search'] ) {
					$args['search'] = '*' . $args['search'] . '*';
				}

				$args = apply_filters( 'users_list_table_query_args', $args );

				// Query the user IDs for this page.
				$wp_user_search = new \WP_User_Query( $args );

				$pos_users = $wp_user_search->get_total();

			} else {

				$args = array(
					'fields'   => 'all_with_meta',
					'role__in' => array( 'pos_user', '' ),
				);

				$args = apply_filters( 'users_list_table_query_args', $args );

				$wp_user_search = new \WP_User_Query( $args );

				$pos_users = $wp_user_search->get_total();

			}

			return apply_filters( 'wkwcpos_modify_pos_user_count', $pos_users, $s );

		}

		/**
		 * Get all pos users.
		 *
		 * @param string $s Search string.
		 * @param int    $perpage Pos users perpage.
		 * @param int    $offset Offset for limit.
		 */
		public function pos_get_all_users( $s = '', $perpage, $offset ) {

			$s = apply_filters( 'wkwcpos_modify_search_term_for_getting_pos_users', $s );

			$perpage = apply_filters( 'wkwcpos_modify_perpage_for_getting_pos_users', $perpage );

			$offset = apply_filters( 'wkwcpos_modify_offset_for_getting_pos_users', $offset );

			if ( ! empty( $s ) ) {

				$search_query = $s;
				$args         = array(
					'number'   => $perpage,
					'offset'   => $offset,
					'search'   => $search_query,
					'fields'   => 'all_with_meta',
					'role__in' => array( 'pos_user', '' ),
				);
				if ( '' !== $args['search'] ) {
					$args['search'] = '*' . $args['search'] . '*';
				}

				$args = apply_filters( 'users_list_table_query_args', $args );

				// Query the user IDs for this page.
				$wp_user_search = new \WP_User_Query( $args );

				$pos_users = $wp_user_search->get_results();

			} else {

				$args = array(
					'number'   => $perpage,
					'offset'   => $offset,
					'fields'   => 'all_with_meta',
					'role__in' => array( 'pos_user', '' ),
				);

				$args = apply_filters( 'users_list_table_query_args', $args );

				$wp_user_search = new \WP_User_Query( $args );

				$pos_users = $wp_user_search->get_results();

			}
			return apply_filters( 'wkwcpos_modify_pos_users', $pos_users, $s, $perpage, $offset );

		}

		/**
		 * Pos user outlet id by pos user id.
		 *
		 * @param int $puser_id Pos user id.
		 *
		 * @return int $response Outlet id.
		 */
		public function _get_pos_user_outlet( $puser_id ) {

			$table_name = $this->db->prefix . 'woocommerce_pos_outlet_map';

			$response = $this->db->get_var( $this->db->prepare( "SELECT outlet_id from $table_name WHERE user_id=%d", $puser_id ) );

			return apply_filters( 'wkwcpos_modify_outlet_id_for_pos_user', $response, $puser_id );

		}

		/**
		 * Get outlet id.
		 *
		 * @return int $outlet_id Outlet id.
		 */
		public function _get_pos_user_outlet_with_status() {

			$outlet_id = $this->db->get_var( $this->db->prepare( "select map.outlet_id from $this->table_name as map JOIN $this->table_name_outlet as outlet ON outlet.id=map.outlet_id where map.user_id=%d AND outlet.outlet_status=0", $this->puser_id ) );

			return apply_filters( 'wkwcpos_modify_enabled_outlet_id_for_pos_user', $outlet_id, $this->puser_id );

		}

	}

}
