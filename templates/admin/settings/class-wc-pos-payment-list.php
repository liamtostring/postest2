<?php

namespace WKWC_POS\Templates\Admin\Settings;

use WKWC_POS\Inc\WC_Pos_Errors;
use WP_List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WC_Pos_Payment_List' ) ) {
	class WC_Pos_Payment_List extends WP_List_Table {

		public $user_obj  = '';
		public $error_obj = '';
		public $wpdb      = '';

		public function __construct() {
			global $wpdb;
			$this->wpdb = $wpdb;
			parent::__construct(
				array(
					'singular' => __( 'POS Payment List', 'wc_pos' ),
					'plural'   => __( 'POS Payment(s) List', 'wc_pos' ),
					'ajax'     => false,
				)
			);
		}

		public function prepare_items() {
			$this->error_obj = new WC_Pos_Errors();

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$data = $this->table_data();

			$search = '';

			if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
				$search = $_POST['s'];
			}
			$totalItems = 0;
			if ( $search != '' ) {
				$totalItems = $this->wpdb->get_var( "SELECT COUNT(id) FROM {$this->wpdb->prefix}woocommerce_pos_payments where payment_name LIKE '%$search%'" );
			} else {
				$totalItems = $this->wpdb->get_var( "SELECT COUNT(id) FROM {$this->wpdb->prefix}woocommerce_pos_payments" );
			}

			$user = get_current_user_ID();

			$screen = get_current_screen();

			$perpage = $this->get_items_per_page( 'option_per_page', 20 );

			$this->_column_headers = array( $columns, $hidden, $sortable );

			if ( empty( $per_page ) || $per_page < 1 ) {
				$per_page = $screen->get_option( 'per_page', 'default' );
			}

			usort( $data, array( $this, 'usort_reorder' ) );

			$totalPages = 0;

			if ( $totalItems > 0 ) {
				$totalPages = ceil( $totalItems / $perpage );
			}

			$currentPage = $this->get_pagenum();

			$this->set_pagination_args(
				array(
					'total_items' => $totalItems,

					'total_pages' => $totalPages,

					'per_page'    => $perpage,
				)
			);

			$this->items = $data;
		}

		public function usort_reorder( $a, $b ) {
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'paymentname';

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( $order === 'asc' ) ? $result : -$result;
		}

		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			return apply_filters(
				'wkwcpos_modify_payment_list_column',
				array(
					'cb'          => '<input type="checkbox" />',

					'paymentname' => __( 'Payment Name', 'wc_pos' ),

					'decription'  => __( 'Description', 'wc_pos' ),

					'status'      => __( 'Status', 'wc_pos' ),
				)
			);
		}

		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'paymentname':
				case 'decription':
				case 'status':
					return $item[ $column_name ];

				default:
					return print_r( $item[ $column_name ], true );
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on.
		 *
		 * @return array $sortable, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return $sortable = array(
				'paymentname' => array( 'paymentname', true ),

				'status'      => array( 'status', true ),
			);
		}

		public function get_hidden_columns() {
			return array();
		}

		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="payment_%s" name="payment[]" value="%s" />', $item['id'], $item['id'] );
		}

		private function table_data() {
			$data = array();

			$perpage = get_option( 'posts_per_page', true );

			$paged = $this->get_pagenum();

			$offset = ( $paged - 1 ) * $perpage;

			$search = '';

			if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
				$search = $_POST['s'];
			}
			if ( $search != '' ) {
				$payment_methods = $this->wpdb->get_results( "SELECT id, payment_name, payment_description, payment_status FROM {$this->wpdb->prefix}woocommerce_pos_payments where payment_name LIKE '%$search%'" );
			} else {
				$payment_methods = $this->wpdb->get_results( "SELECT id, payment_name, payment_description, payment_status FROM {$this->wpdb->prefix}woocommerce_pos_payments" );
			}

			if ( ! empty( $payment_methods ) ) :

				foreach ( $payment_methods as $u_key => $u_value ) {
					$data[] = array(
						'id'          => $u_value->id,

						'paymentname' => $u_value->payment_name,

						'decription'  => $u_value->payment_description,

						'status'      => $u_value->payment_status == 1 ? '<button class="pos-btn-green posever" disabled>' . esc_html( 'Enable', 'wc_pos' ) . '</button>' : '<button class="pos-btn-red posever" disabled>' . esc_html( 'Disable', 'wc_pos' ) . '</button>',
					);
				}

			endif;

			return apply_filters(
				'wkwcpos_modify_payment_list_data',
				$data
			);
		}

		public function column_paymentname( $item ) {

			$actions = array(
				'edit'   => sprintf( '<a href="admin.php?page=wc-pos-settings&tab=payment-option&action=edit&payment_id=%s">%s</a>', $item['id'], esc_html__( 'Edit', 'wc_pos' ) ),

				'delete' => sprintf( '<a href="admin.php?page=wc-pos-settings&tab=payment-option&action=delete&payment_id=%s">%s</a>', $item['id'], esc_html__( 'Delete', 'wc_pos' ) ),
			);

			return sprintf( '%1$s %2$s', $item['paymentname'], $this->row_actions( $actions ) );
		}

		/**
		 * Bulk actions on list.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'enable'  => __( 'Enable', 'wc_pos' ),
				'disable' => __( 'Disable', 'wc_pos' ),
				'delete'  => __( 'Delete', 'wc_pos' ),
			);

			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			if ( $this->current_action() === 'enable' ) {
				if ( isset( $_POST['payment'] ) && ! empty( $_POST['payment'] ) ) {
					if ( is_array( $_POST['payment'] ) ) {
						$payments = $_POST['payment'];
						foreach ( $payments as $payment ) {
							$this->wpdb->query( $this->wpdb->prepare( "UPDATE {$this->wpdb->prefix}woocommerce_pos_payments SET payment_status = %d WHERE id = %d", 1, $payment ) );
						}
						$message = __( 'Payment Method(s) updated successfully', 'wc_pos' );
						$this->error_obj->set_error_code( 0 );
						$this->error_obj->wk_wc_pos_print_notification( $message );
					}
				}
			} elseif ( $this->current_action() === 'disable' ) {
				if ( isset( $_POST['payment'] ) && ! empty( $_POST['payment'] ) ) {
					if ( is_array( $_POST['payment'] ) ) {
						$payments = $_POST['payment'];
						foreach ( $payments as $payment ) {
							$this->wpdb->query( $this->wpdb->prepare( "UPDATE {$this->wpdb->prefix}woocommerce_pos_payments SET payment_status = %d WHERE id = %d", 0, $payment ) );
						}
						$message = __( 'Payment Method(s) updated successfully', 'wc_pos' );
						$this->error_obj->set_error_code( 0 );
						$this->error_obj->wk_wc_pos_print_notification( $message );
					}
				}
			} elseif ( $this->current_action() === 'delete' ) {
				if ( isset( $_POST['payment'] ) && ! empty( $_POST['payment'] ) ) {
					if ( is_array( $_POST['payment'] ) ) {
						$payments = $_POST['payment'];
						foreach ( $payments as $payment ) {
							$this->wpdb->query( $this->wpdb->prepare( "DELETE FROM {$this->wpdb->prefix}woocommerce_pos_payments WHERE id = %d", $payment ) );
						}
						$message = __( 'Payment Method(s) deleted successfully', 'wc_pos' );
						$this->error_obj->set_error_code( 0 );
						$this->error_obj->wk_wc_pos_print_notification( $message );
					}
				}
			}

			if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['payment_id'] ) && ! empty( intval( $_GET['payment_id'] ) ) ) {
				if ( $_GET['payment_id'] ) {
					$this->wpdb->query( $this->wpdb->prepare( "DELETE FROM {$this->wpdb->prefix}woocommerce_pos_payments WHERE id = %d", $_GET['payment_id'] ) );

					$message = __( 'Payment Method deleted successfully', 'wc_pos' );
					$this->error_obj->set_error_code( 0 );
					$this->error_obj->wk_wc_pos_print_notification( $message );
				} else {
					$message = __( 'Not a Valid Payment Method.', 'wc_pos' );
					$this->error_obj->set_error_code( 1 );
					$this->error_obj->wk_wc_pos_print_notification( $message );
				}
			}
		}
	}
}

$wc_pos_list = new WC_Pos_Payment_List();

$wc_pos_list->prepare_items();

?>
<div class="wrap">

	<h1><?php echo __( 'Payment Method(s)', 'wc_pos' ); ?> <a href="<?php echo admin_url() . 'admin.php?page=wc-pos-settings&tab=payment-option&action=add'; ?>" class="page-title-action pos_button_css"><?php _e( 'Add New', 'wc_pos' ); ?></a></h1>

	<form method="POST">

		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

		<?php

		$wc_pos_list->search_box( __( 'Search Payment', 'wc_pos' ), 'search-payment' );

		$wc_pos_list->display();

		?>

	</form>

</div>
