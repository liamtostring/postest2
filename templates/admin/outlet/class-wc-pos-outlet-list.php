<?php
/**
 * POS outlet list class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Admin\Outlet;

use WP_List_Table;
use WKWC_POS\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WC_Pos_Outlet_List' ) ) {

	/**
	 * Pos outlet list.
	 */
	class WC_Pos_Outlet_List extends WP_List_Table {

		/**
		 * WordPress database object.
		 *
		 * @var object $db WordPress database object.
		 */
		protected $db = '';

		/**
		 * Pos outlet helper class instance.
		 *
		 * @var object $outlet_obj Pos outlet helper class instance.
		 */
		protected $outlet_obj = '';

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			global $wpdb;

			$this->db = $wpdb;

			parent::__construct(
				array(

					'singular' => __( 'POS List Outlets', 'wc_pos' ),
					'plural'   => __( 'POS List Outlets', 'wc_pos' ),
					'ajax'     => false,

				)
			);

		}

		/**
		 * Prepare items for listing.
		 */
		public function prepare_items() {

			$this->outlet_obj = new Helper\Outlet\WC_Pos_Outlet_Helper();

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$data = $this->table_data();

			$search_query = ! empty( $_POST['s'] ) ? $_POST['s'] : ''; // phpcs:ignore

			$total_items = $this->outlet_obj->pos_get_all_outlet_by_search_count( $search_query );

			$user = get_current_user_ID();

			$screen = get_current_screen();

			$perpage = $this->get_items_per_page( 'option_per_page', 20 );

			$this->_column_headers = array( $columns, $hidden, $sortable );

			if ( empty( $per_page ) || $per_page < 1 ) {

				$per_page = $screen->get_option( 'per_page', 'default' );

			}

			usort( $data, array( $this, 'usort_reorder' ) );

			$total_pages = ceil( $total_items / $perpage );

			$this->set_pagination_args(
				array(

					'total_items' => $total_items,

					'total_pages' => $total_pages,

					'per_page'    => $perpage,

				)
			);

			$this->items = $data;

		}

		/**
		 * Sort and reorder function.
		 *
		 * @param array $a First data.
		 * @param array $b Second data.
		 */
		public function usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'outlet_name'; // phpcs:ignore

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; // phpcs:ignore

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : -$result;

		}

		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns, Array of columns to use with the table
		 */
		public function get_columns() {

			return apply_filters(
				'wkwcpos_modify_outlet_list_columns',
				array(
					'cb'              => '<input type="checkbox" />',
					'outlet_name'     => __( 'Outlet Name', 'wc_pos' ),
					'outlet_address'  => __( 'Outlet Address', 'wc_pos' ),
					'outlet_payment'  => __( 'Payment Methods', 'wc_pos' ),
					'outlet_template' => __( 'Outlet Template', 'wc_pos' ),
					'outlet_status'   => __( 'Outlet Status', 'wc_pos' ),
					'outlet_report'   => __( 'Report', 'wc_pos' ),
				)
			);

		}

		/**
		 * Default columns.
		 *
		 * @param array  $item Columns array.
		 * @param string $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'outlet_name':
				case 'outlet_address':
				case 'outlet_payment':
				case 'outlet_template':
				case 'outlet_status':
					return $item[ $column_name ];

				case 'outlet_report':
					?><a href="<?php echo admin_url() . 'admin.php?page=wc-pos-reports&outlet_id=' . $item['outlet_id']; ?>" class = "button" ><?php esc_html_e( 'Outlet Report ', 'wc_pos' ); ?></a>
					<?php
					return '';

				default:
					return $item[ $column_name ];

			}

		}

		/**
		 * Decide which columns to activate the sorting functionality on.
		 *
		 * @return array $sortable Array of columns that can be sorted by the user.
		 */
		public function get_sortable_columns() {

			$sortable = array(
				'outlet_name' => array( 'outlet_name', true ),
			);

			return $sortable;

		}

		/**
		 * Get hidden columns.
		 */
		public function get_hidden_columns() {

			return array();

		}

		/**
		 * Checkbox column function
		 *
		 * @param array $item Data.
		 */
		public function column_cb( $item ) {

			return sprintf( '<input type="checkbox" id="outlet_id_%s" name="outlet_id[]" value="%s" />', $item['outlet_id'], $item['outlet_id'] );

		}

		/**
		 * Table data function.
		 */
		private function table_data() {

			$data = array();

			$perpage = get_option( 'posts_per_page', true );

			if ( isset( $_GET['paged'] ) ) { // phpcs:ignore

				$page = $_GET['paged']; // phpcs:ignore

			} else {

				$page = 1;
			}

			$off = ( $page - 1 ) * $perpage;

			$search_query = ! empty( $_POST['s'] ) ? $_POST['s'] : ''; // phpcs:ignore

			$pos_outlets = $this->outlet_obj->pos_get_all_outlet_by_search( $search_query, $off, $perpage );

			if ( ! empty( $pos_outlets ) ) {

				foreach ( $pos_outlets as $outlet ) {

					$outlet_id      = $outlet->id;
					$outlet_name    = $outlet->outlet_name;
					$outlet_address = $outlet->outlet_address;
					$outlet_status  = 0 == $outlet->outlet_status ? '<button class="pos-btn-green posever" disabled>' . esc_html__( 'Active', 'wc_pos' ) . '</button>' : '<button class="pos-btn-red posever" disabled>' . esc_html__( 'Deactive', 'wc_pos' ) . '</button>';
					$outlet_payment = maybe_unserialize( $outlet->outlet_payment );
					$response       = array();
					if ( is_array( $outlet_payment ) ) {

						$response = $this->db->get_results( " SELECT payment_name FROM `{$this->db->prefix}woocommerce_pos_payments` WHERE id IN ('" . implode( "','", $outlet_payment ) . "') GROUP BY payment_name" );
					}
					$payment_assign = array();
					if ( ! empty( $response ) ) {
						foreach ( $response as $payment ) {
							$payment_assign[] = $payment->payment_name;
						}
					} else {
						$payment_assign[] = '';
					}
					if ( ! empty( $payment_assign[0] ) ) {
						$payment_have = implode( ' ,', $payment_assign );
					} else {
						$payment_have = "<span style='color:red'>" . esc_html__( 'Not Assigned', 'wc_pos' ) . '</span>';
					}
					$outlet_invoice = "<span style='color:red'>" . esc_html__( 'Not Assigned', 'wc_pos' ) . '</span>';
					if ( 0 !== (int) $outlet->outlet_invoice ) {
						$outlet_invoice = $this->db->get_var( $this->db->prepare( " SELECT name FROM `{$this->db->prefix}woocommerce_pos_invoice_templates` WHERE id=%d", (int) $outlet->outlet_invoice ) );
					}

					$data[] = array(

						'outlet_id'       => $outlet_id,

						'outlet_name'     => $outlet_name,

						'outlet_address'  => ucwords( $outlet_address ),

						'outlet_payment'  => $payment_have,

						'outlet_template' => $outlet_invoice,

						'outlet_status'   => $outlet_status,

					);

				}
			}

			return apply_filters( 'wkwcpos_modify_outlet_list_data', $data );

		}

		/**
		 * Outlet column actions.
		 *
		 * @param array $item Data of a particular column.
		 */
		public function column_outlet_name( $item ) {

			$actions = array(

				'edit'          => sprintf( '<a href="admin.php?page=pos-outlets&outlet_action=edit&outlet_id=%s">%s</a>', $item['outlet_id'], esc_html__( 'Edit', 'wc_pos' ) ),
				'manage_outlet' => sprintf( '<a href="admin.php?page=pos-outlets&tab=manage-products&outlet_action=edit&outlet_id=%s">%s</a>', $item['outlet_id'], esc_html__( 'Manage Outlet', 'wc_pos' ) ),

			);

			return sprintf( '%1$s %2$s', $item['outlet_name'], $this->row_actions( $actions ) );

		}

		/**
		 * Bulk actions on list.
		 *
		 * @return array $actions Actions list.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'activate'   => esc_html__( 'Activate', 'wc_pos' ),
				'deactivate' => esc_html__( 'Deactivate', 'wc_pos' ),
				'delete'     => esc_html__( 'Delete', 'wc_pos' ),
			);
			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {

			$count  = 0;
			$delete = 0;

			$outlet_ids = isset( $_POST['outlet_id'] ) && ! empty( $_POST['outlet_id'] ) ? $_POST['outlet_id'] : ''; // phpcs:ignore

			if ( $this->current_action() === 'activate' ) {

				if ( empty( $outlet_ids ) ) {

					if ( is_array( $outlet_ids ) ) {

						foreach ( $outlet_ids as $outlet_id ) {

							$response = $this->outlet_obj->update_outlet_status( $this->current_action(), $outlet_id );

							if ( $response ) {
								$count++;
							}
						}
					}
				}
			} elseif ( $this->current_action() === 'deactivate' ) {

				if ( ! empty( $outlet_ids ) ) {

					if ( is_array( $outlet_ids ) ) {

						$count = 0;

						foreach ( $outlet_ids as $outlet_id ) {

							$response = $this->outlet_obj->update_outlet_status( $this->current_action(), $outlet_id );

							if ( $response ) {
								$count++;
							}
						}
					}
				}
			} elseif ( $this->current_action() === 'delete' ) {

				if ( ! empty( $outlet_ids ) ) {

					if ( is_array( $outlet_ids ) ) {

						$delete = 0;

						foreach ( $outlet_ids as $outlet_id ) {

							$outlet_id = intval( $outlet_id );

							$response = $this->outlet_obj->delete_vendor_outlet( $outlet_id );

							if ( $response ) {

								$delete++;

							}
						}
					}
				}
			}

			if ( $delete > 0 ) {
				echo  '<div class="notice notice-success is-dismissible">';
					echo '<p>' . $delete . __( ' outlet(s) deleted successfully.', 'wc_pos' ) . '</p>';
					echo '</div>';
			} elseif ( $count > 0 ) {
				echo  '<div class="notice notice-success is-dismissible">';
					echo '<p>' . $count . __( ' outlet(s) updated successfully.', 'wc_pos' ) . '</p>';
					echo '</div>';
			}
		}

	}

}

		$wc_pos_list_outlet = new WC_Pos_Outlet_List();

		$wc_pos_list_outlet->prepare_items();

?>
		<div class="wrap">

			<h1>
				<?php esc_html_e( 'Outlet List', 'wc_pos' ); ?>
				<a href="<?php echo admin_url() . 'admin.php?page=pos-outlets&action=add'; ?>" class="page-title-action pos_button_css"><?php esc_html_e( 'Add New', 'wc_pos' ); ?></a>
				<a href="<?php echo admin_url() . 'admin.php?page=pos-outlets&action=outlet-import'; ?>" class="page-title-action pos_button_css"><?php esc_html_e( 'Import', 'wc_pos' ); ?></a>
			</h1>

			<form method="POST">

				<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; //phpcs:ignore ?>" />

				<?php

				$wc_pos_list_outlet->search_box( esc_html__( 'Search', 'wc_pos' ), 'search-outlet' );

				$wc_pos_list_outlet->display();

				?>

			</form>

		</div>
