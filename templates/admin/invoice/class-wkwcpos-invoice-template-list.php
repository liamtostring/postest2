<?php
/**
 * POS invoice list template class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Admin\Invoice;

use WP_List_Table;
use WKWC_POS\Helper;
use WKWC_POS\Inc\WC_Pos_Errors;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}

if ( ! class_exists( 'WKWCPOS_Invoice_Template_List' ) ) {

	/**
	 * POS invoice list template class.
	 */
	class WKWCPOS_Invoice_Template_List extends WP_List_Table {

		/**
		 * Invoice helper class instance.
		 *
		 * @var object $invoice_helper Invoice helper class instance.
		 */
		public $invoice_helper = '';

		/**
		 * Error class instance.
		 *
		 * @var $error_obj Error class instance.
		 */
		public $error_obj = '';

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			parent::__construct(
				array(

					'singular' => __( 'Invoice Template', 'wc_pos' ),
					'plural'   => __( 'Invoice Templates', 'wc_pos' ),
					'ajax'     => false,

				)
			);

			$this->invoice_helper = new Helper\Invoice\WKWCPOS_Invoice_Helper();

			$this->error_obj = new WC_Pos_Errors();
		}

		/**
		 * Prepare items function.
		 */
		public function prepare_items() {

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$data = $this->table_data();

			$search = ! empty( $_POST['s'] ) ? $_POST['s'] : ''; // phpcs:ignore

			$total_items = $this->invoice_helper->wkwcpos_get_all_invoice_templates_count( $search );

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

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'created_at'; // phpcs:ignore

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; // phpcs:ignore

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : -$result;

		}

		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns Array of columns to use with the table.
		 */
		public function get_columns() {

			$columns = array(

				'cb'          => '<input type="checkbox" />',

				'name'        => esc_html__( 'Name', 'wc_pos' ),

				'created_at'  => esc_html__( 'Created At', 'wc_pos' ),

				'modified_at' => esc_html__( 'Modified At', 'wc_pos' ),

			);

			return apply_filters( 'wkwcpos_modify_invoice_columns', $columns );

		}

		/**
		 * Default columns.
		 *
		 * @param array  $item Columns array.
		 * @param string $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'id':
				case 'name':
				case 'created_at':
				case 'modified_at':
					return $item[ $column_name ];

				default:
					return print_r( $item[ $column_name ], true ); // phpcs:ignore

			}

		}

		/**
		 * Decide which columns to activate the sorting functionality on.
		 *
		 * @return array $sortable Array of columns that can be sorted by the user.
		 */
		public function get_sortable_columns() {

			return array(
				'name'        => array( 'name', true ),
				'created_at'  => array( 'created_at', true ),
				'modified_at' => array( 'modified_at', true ),
			);

		}

		/**
		 * Get hidden columns function.
		 *
		 * @return array hidden columns.
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

			return sprintf( '<input type="checkbox" name="id[]" value="%d" />', $item['id'], $item['id'] );

		}

		/**
		 * Table data function.
		 */
		private function table_data() {

			$data = array();

			$perpage = get_option( 'posts_per_page', true );

			$paged = $this->get_pagenum();

			$offset = ( $paged - 1 ) * $perpage;

			$search = ! empty( $_POST['s'] ) ? $_POST['s'] : ''; // phpcs:ignore

			$invoices = $this->invoice_helper->wkwcpos_get_all_invoice_templates( $search, $perpage, $offset );

			if ( ! empty( $invoices ) ) {

				foreach ( $invoices as $invoice ) {
					$data[] = array(
						'id'          => $invoice['id'],
						'name'        => $invoice['name'],
						'created_at'  => date_i18n( 'F j, Y H:i:s', strtotime( $invoice['created_at'] ) ),
						'modified_at' => date_i18n( 'F j, Y H:i:s', strtotime( $invoice['modified_at'] ) ),
					);
				}
			}

			return apply_filters( 'wkwcpos_modify_invoice_list_data', $data, $invoices );

		}

		/**
		 * Column name function.
		 *
		 * @param array $item Data of particular column.
		 */
		public function column_name( $item ) {

			$id = $item['id'];

			$actions = array(

				'edit'   => sprintf( '<a href="admin.php?page=%s&action=edit&id=%d">%s</a>', $_GET['page'], $item['id'], esc_html__( 'Edit', 'wc_pos' ) ), // phpcs:ignore

				'delete' => sprintf( '<a href="admin.php?page=%s&action=delete&id=%d">%s</a>', $_GET['page'], $item['id'], esc_html__( 'Delete', 'wc_pos' ) ), // phpcs:ignore

			);

			return sprintf( '%1$s %2$s', $item['name'], $this->row_actions( $actions ) );

		}

		/**
		 * Bulk actions on list.
		 *
		 * @return array $actions Bulk action list.
		 */
		public function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'wc_pos' ),
			);
			return $actions;
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {

			if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && ! empty( $_GET['id'] ) ) { // phpcs:ignore

				$success = false;

				$invoice_ids = $_GET['id']; // phpcs:ignore

				if ( is_array( $invoice_ids ) ) {

					foreach ( $invoice_ids as $invoice_id ) {
						$success = $this->invoice_helper->wkwcpos_delete_invoice_template( $invoice_id );
					}
				} else {
					$success = $this->invoice_helper->wkwcpos_delete_invoice_template( $invoice_ids );
				}

				if ( $success ) {

					$message = __( 'Invoice deleted successfully', 'wc_pos' );
					$this->error_obj->set_error_code( 0 );
					$this->error_obj->wk_wc_pos_print_notification( $message );
				} else {

					$message = __( 'Not a Valid Invoice.', 'wc_pos' );
					$this->error_obj->set_error_code( 1 );
					$this->error_obj->wk_wc_pos_print_notification( $message );
				}
			}
		}

	}

}

$wc_pos_list = new WKWCPOS_Invoice_Template_List();

$wc_pos_list->prepare_items();

?>
<div class="wrap">

	<h1><?php esc_html_e( 'Invoice Templates', 'wc_pos' ); ?> <a href="<?php echo admin_url() . 'admin.php?page=wc-pos-invoice-templates&action=add'; ?>" class="page-title-action pos_button_css"><?php _e( 'Add New', 'wc_pos' ); ?></a></h1>

	<form method="GET">

		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; // phpcs:ignore ?>" />

		<?php

		$wc_pos_list->search_box( __( 'Search', 'wc_pos' ), 'search-template' );

		$wc_pos_list->display();

		?>

	</form>

</div>
