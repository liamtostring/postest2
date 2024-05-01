<?php
/**
 * This file handles helper config class.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 */

namespace WKWC_POS\Helper\Invoice;

use WKWC_POS\Inc\WC_Pos_Errors;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WKWCPOS_Invoice_Helper' ) ) {

	/**
	 * Class for includin core pos user data.
	 */
	class WKWCPOS_Invoice_Helper {

		/**
		 * Table name.
		 *
		 * @var string $table_name Table name.
		 */
		public $table_name = '';

		/**
		 * Database object.
		 *
		 * @var object $db Database object.
		 */
		public $db;

		/**
		 * Error class object.
		 *
		 * @var object $error_obj Error class object.
		 */
		public $error_obj;

		/**
		 * Class constructor.
		 */
		public function __construct() {
			global $wpdb;

			$this->db = $wpdb;

			$this->error_obj = new WC_Pos_Errors();

			$this->table_name = $this->db->prefix . 'woocommerce_pos_invoice_templates';
		}

		/**
		 * Get invoice template count function.
		 *
		 * @param string $search Search string.
		 *
		 * @return int Invoice template count.
		 */
		public function wkwcpos_get_all_invoice_templates_count( $search = '' ) {

			$result = $this->db->get_var( $this->db->prepare( "SELECT count(*) from $this->table_name WHERE name LIKE %s", '%' . $search . '%' ) );

			return apply_filters( 'wkwcpos_modify_all_invoice_template_count', $result, $search );

		}

		/**
		 * Get all invoice template.
		 *
		 * @param string $search Search string.
		 * @param int    $perpage Invoice perpage count.
		 * @param int    $offset Offset.
		 *
		 * @return array|object Invoice.
		 */
		public function wkwcpos_get_all_invoice_templates( $search = '', $perpage, $offset ) {

			return $this->db->get_results( $this->db->prepare( "SELECT * from $this->table_name WHERE name LIKE %s limit %d OFFSET %d", '%' . $search . '%', $perpage, $offset ), ARRAY_A );

		}

		/**
		 * Get inoice template by invoice id.
		 *
		 * @param int $id Invoice id.
		 *
		 * @return array|object Invoice
		 */
		public function wkwcpos_get_invoice_template( $id ) {

			$result = $this->db->get_row( $this->db->prepare( "SELECT * from $this->table_name WHERE id=%d", $id ), ARRAY_A );

			return $result;

		}

		/**
		 * Delete invoice.
		 *
		 * @param int $id Invoice id.
		 *
		 * @return int|bool Number of rows deleted or false on error.
		 */
		public function wkwcpos_delete_invoice_template( $id ) {

			return $this->db->delete(
				$this->table_name,
				array(
					'id' => $id,
				),
				array( '%d' )
			);

		}

		/**
		 * Save invoice template
		 *
		 * @param array $post_data Post data.
		 */
		public function wkwcpos_save_invoice_template( $post_data ) {

			$name = ! empty( $post_data['_wkwcpos_invoice_name'] ) ? sanitize_text_field( $post_data['_wkwcpos_invoice_name'] ) : '';

			$id = ! empty( $_GET['id'] ) ? esc_attr( $_GET['id'] ) : ''; // phpcs:ignore

			if ( ! empty( $name ) ) {
				if ( ! empty( $id ) ) {

					$name_exists = $this->db->get_var( $this->db->prepare( "SELECT id FROM $this->table_name WHERE name=%s AND id!=%d", $name, $id ) );

					$name_exists = apply_filters( 'wkwcpos_modify_update_get_invoice_result', $name_exists, $post_data, $id );

					if ( ! empty( $name_exists ) ) {
						$this->error_obj->set_error_code( 1 );
						$this->error_obj->wk_wc_pos_print_notification( __( 'Name already exists', 'wc_pos' ) );
					} else {

						$inovice_update_data = array(
							'data' => array(
								'name'        => $name,
								'modified_at' => current_time( 'Y-m-d H:i:s' ),
							),
							'type' => array( '%s', '%s' ),
						);

						$inovice_update_data = apply_filters( 'wkwcpos_modify_inovice_data_before_update', $inovice_update_data, $post_data, $id );

						$this->db->update(
							$this->table_name,
							$inovice_update_data['data'],
							array( 'id' => $id ),
							$inovice_update_data['type'],
							array( '%d' )
						);

						$result = 'updated';

					}
				} else {

					$name_exists = $this->db->get_var( $this->db->prepare( "SELECT id FROM $this->table_name WHERE name=%s", $name ) );

					$name_exists = apply_filters( 'wkwcpos_modify_insert_get_invoice_result', $name_exists, $post_data );

					if ( ! empty( $name_exists ) ) {
						$this->error_obj->set_error_code( 1 );
						$this->error_obj->wk_wc_pos_print_notification( __( 'Name already exists', 'wc_pos' ) );
					} else {

						$inovice_insert_data = array(
							'data' => array(
								'name'         => $name,
								'invoice_html' => '',
								'created_at'   => current_time( 'Y-m-d H:i:s' ),
								'modified_at'  => current_time( 'Y-m-d H:i:s' ),
							),
							'type' => array( '%s', '%s', '%s', '%s' ),
						);

						$inovice_insert_data = apply_filters( 'wkwcpos_modify_inovice_data_before_insert', $inovice_insert_data, $post_data, $id );

						$this->db->insert(
							$this->table_name,
							$inovice_insert_data['data'],
							$inovice_insert_data['type']
						);

						$id     = $this->db->insert_id;
						$result = 'created';
					}
				}
			} else {
				$this->error_obj->set_error_code( 1 );
				$this->error_obj->wk_wc_pos_print_notification( __( 'Enter invoice name', 'wc_pos' ) );
			}

			if ( $this->error_obj->get_error_code() == 0 ) {
				wp_safe_redirect( admin_url( "admin.php?page={$_GET['page']}&action=edit&id={$id}&result={$result}" ) ); // phpcs:ignore
				exit();
			}

		}

		/**
		 * Save invoice html.
		 *
		 * @param array $data Invoice data.
		 *
		 * @return bool
		 */
		public function wkwcpos_save_invoice_html( $data ) {

			$invoice_html = ! empty( $data['invoice_html'] ) ? $data['invoice_html'] : '';

			$id = ! empty( $data['id'] ) ? $data['id'] : '';

			if ( ! empty( $id ) && ! empty( $data ) ) {
				$this->db->update(
					$this->table_name,
					array(
						'invoice_html' => $invoice_html,
						'modified_at'  => current_time( 'Y-m-d H:i:s' ),
					),
					array( 'id' => $id ),
					array( '%s', '%s' ),
					array( '%d' )
				);

				return true;
			}

			return false;

		}

	}

}
