<?php

namespace WKWC_POS\Templates\Admin;

use WP_List_Table;
use WKWC_POS\Inc\WC_Pos_Errors;
use WKWC_POS\Helper\Order\WC_Pos_Orders_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WC_Pos_Outlet_Order_List' ) ) {
	class WC_Pos_Outlet_Order_List extends WP_List_Table {

		protected $wpdb;
		protected $table_name;
		protected $table_name_post;
		protected $order_obj;
		protected $error_obj;

		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;

			$this->table_name = $this->wpdb->prefix . 'postmeta';

			$this->table_name_post = $this->wpdb->prefix . 'posts';

			if ( ! empty( $_REQUEST['s'] ) && ! wp_verify_nonce( isset( $_GET['_pos_nonce'] ) ? $_GET['_pos_nonce'] : '', '_pos_nonce_action' ) ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			parent::__construct(
				array(
					'singular' => __( 'POS Order List', 'wc_pos' ),
					'plural'   => __( 'POS Order List', 'wc_pos' ),
					'ajax'     => false,
				)
			);
		}

		public function prepare_items() {
			$this->order_obj = new WC_Pos_Orders_Helper();
			$this->error_obj = new WC_Pos_Errors();

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$search_query = '';
			$outlet       = '';

			if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
				$search_query = intval( sanitize_text_field( $_GET['s'] ) );
			}

			if ( isset( $_GET['pos-select-outlet'] ) && ! empty( $_GET['pos-select-outlet'] ) ) {
				$outlet = intval( sanitize_text_field( $_GET['pos-select-outlet'] ) );
			}

			$items = $this->order_obj->pos_get_all_order_by_search_count( $search_query, $outlet );

			$data = $this->table_data();

			$total_items = $items;

			$screen = get_current_screen();

			$perpage = get_option( 'pos_orders_per_page', 20 );

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

		public function usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'order_id';

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc';

			$order = ( $order === 'asc' ) ? 'asc' : 'desc';
			if ( 'asc' == $order ) {

				if ( $a[ $orderby ] == $b[ $orderby ] ) {
					return 1;
				} elseif ( $a[ $orderby ] < $b[ $orderby ] ) {
					return -1;
				} else {
					return 1;
				}
			} else {
				if ( $a[ $orderby ] == $b[ $orderby ] ) {
					return 1;
				} elseif ( $a[ $orderby ] > $b[ $orderby ] ) {
					return -1;
				} else {
					return 1;
				}
			}

			// return
		}

		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			return apply_filters(
				'wkwcpos_modify_order_list_column',
				array(
					'order'          => __( 'Order', 'wc_pos' ),

					'pos_agent'      => __( 'POS Outlet', 'wc_pos' ),

					'customer'       => __( 'Customer', 'wc_pos' ),

					'total_quantity' => __( 'Total Quantity', 'wc_pos' ),

					'status'         => __( 'Status', 'wc_pos' ),

					'total'          => __( 'Total', 'wc_pos' ),

					'date_added'     => __( 'Date Added', 'wc_pos' ),
				)
			);
		}

		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'order':
				case 'pos_agent':
				case 'customer':
				case 'total_quantity':
				case 'status':
				case 'total':
				case 'date_added':
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
				'order'      => array( 'order', true ),

				'date_added' => array( 'date_added', true ),

				'pos_agent'  => array( 'pos_agent', true ),

				'customer'   => array( 'customer', true ),
			);
		}

		public function get_hidden_columns() {
			return array();
		}

		private function table_data() {
			$data = array();

			$perpage = get_option( 'pos_orders_per_page', 20 );

			if ( isset( $_GET['paged'] ) ) {
				$page = intval( sanitize_text_field( $_GET['paged'] ) );
			} else {
				$page = 1;
			}
			$search_query = '';
			$outlet       = '';
			if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
				$search_query = intval( sanitize_text_field( $_GET['s'] ) );
			}

			if ( isset( $_GET['pos-select-outlet'] ) && ! empty( $_GET['pos-select-outlet'] ) ) {
				$outlet = intval( sanitize_text_field( $_GET['pos-select-outlet'] ) );
			}

			$offset = ( $page - 1 ) * $perpage;

			$pos_orders = $this->order_obj->pos_get_all_orders( $perpage, $offset, $search_query, $outlet );

			if ( ! empty( $pos_orders ) ) {
				foreach ( $pos_orders as $pos_order_k => $pos_order_v ) {
					$order_id       = $pos_order_v->ID;
					$order_link     = '<a href="' . get_edit_post_link( $order_id ) . '">' . $this->order_obj->get_prefixed_order_number( $order_id, 'hash' ) . '</a>';
					$order          = wc_get_order( $order_id );
					$pos_agent      = $this->order_obj->pos_get_outlet_name_by_id( get_post_meta( $order_id, '_wk_wc_pos_outlet', true ) );
					$pos_customer   = '<div style="display:flex; align-items:center"><img width="25" style="border-radius:50%" src="' . get_avatar_url( $order->get_user_id() ) . '">' . get_the_author_meta( 'user_login', $order->get_user_id() ) . '</div>';
					$total_quantity = $order->get_item_count();
					$status         = $order->get_status();
					$date_created   = wc_format_datetime( $order->get_date_created() );
					$order_total    = $order->get_formatted_order_total();
					$status         = "<mark class='order-status status-" . $status . " tips'><span>" . ucwords( $status ) . '</span></mark>';

					$data[] = array(
						'order_id'       => $order_id,

						'order'          => $order_link,

						'pos_agent'      => ! empty( $pos_agent ) ? $pos_agent : 'N/A',

						'customer'       => $pos_customer,

						'total_quantity' => $total_quantity,

						'status'         => $status,

						'total'          => $order_total,

						'date_added'     => $date_created,
					);
				}
			}

			return apply_filters( 'wc_pos_orders_list_table_data_filter', $data );
		}

		public function extra_tablenav( $which ) {
			$nonce  = wp_create_nonce();
			$outlet = '';
			if ( isset( $_GET['pos-select-outlet'] ) && ! empty( $_GET['pos-select-outlet'] ) ) {
				$outlet = intval( sanitize_text_field( $_GET['pos-select-outlet'] ) );
			}
			if ( 'top' === $which ) {
				?>

				<div class="alignleft actions bulkactions">

					<select name="pos-select-outlet" id="pos-select-outlet" class="pos_input_css" style="min-width:200px;">

						<option value=""><?php echo __( 'Select Outlet', 'wc_pos' ); ?></option>

						<?php

						$pos_outlets = $this->order_obj->pos_get_all_outlets_name_and_id();

						foreach ( $pos_outlets as $u_key => $u_value ) {
							?>
							<option value="<?php echo esc_attr( $u_value['id'] ); ?>" <?php selected( $outlet, intval( $u_value['id'] ), true ); ?>><?php echo ! empty( $u_value['id'] ) ? $u_value['outlet_name'] : 'N/A'; ?></option>
							<?php
						}
						?>
					</select>

					<input type="hidden" name="pos_nonce" value="<?php echo $nonce; ?>">

					<?php submit_button( __( 'Select Outlet', 'wc_pos' ), 'button', 'select-agent', false ); ?>

				</div>

				<?php
			}
		}

		public function column_order( $item ) {
			$actions = array(
				'edit' => sprintf( '<a href="' . get_edit_post_link( $item['order_id'] ) . '">' . __( 'Edit', 'wc_pos' ) . '</a>' ),
			);

			return sprintf( '%1$s %2$s', $item['order'], $this->row_actions( $actions ) );
		}
	}
}
