<?php
/**
 * POS outlet product list class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Admin\Outlet;

use WP_List_Table;
use WKWC_POS\Helper\Outlet\Product\Outlet_Product_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

}

if ( ! class_exists( 'WC_Pos_Outlet_Product_List' ) ) {

	/**
	 * POS outlet product list class.
	 */
	class WC_Pos_Outlet_Product_List extends WP_List_Table {

		/**
		 * Database object.
		 *
		 * @var object $wpdb Database object.
		 */
		protected $wpdb = '';

		/**
		 * Outlet helper class instance.
		 *
		 * @var object $product_obj Outlet helper class instance.
		 */
		protected $product_obj = '';

		/**
		 * Centralized inventory enabled.
		 *
		 * @var bool $centralized_inventory_enabled Is centralized inventory enabled.
		 */
		protected $centralized_inventory_enabled = false;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			global $wpdb;

			$this->wpdb = $wpdb;

			if ( ! empty( $_REQUEST['s'] ) && ! wp_verify_nonce( isset( $_GET['_pos_nonce'] ) ? $_GET['_pos_nonce'] : '', '_pos_nonce_action' ) ) { // phpcs:ignore

				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			$this->centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

			parent::__construct(
				array(

					'singular' => __( 'POS Outlet Product List', 'wc_pos' ),
					'plural'   => __( 'POS Outlet Product List', 'wc_pos' ),
					'ajax'     => false,

				)
			);

		}

		/**
		 * Prepare table items.
		 */
		public function prepare_items() {

			$this->product_obj = new Outlet_Product_Helper();

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$search_query = ! empty( $_REQUEST['s'] ) ? $_REQUEST['s'] : ''; // phpcs:ignore

			$total_items = $this->product_obj->get_count_outlet_product( $search_query );

			$data = $this->table_data();

			$screen = get_current_screen();

			$perpage = get_option( 'pos_outlet_manage_products_per_page', 20 );

			$this->_column_headers = array( $columns, $hidden, $sortable );

			if ( empty( $per_page ) || $per_page < 1 ) {

				$per_page = $screen->get_option( 'per_page', 'default' );

			}

			$perpage = apply_filters( 'wkwc_pos_total_items_per_page', $perpage );

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
		 * Sort data.
		 *
		 * @param array $a First data.
		 * @param array $b Second data.
		 *
		 * @return array $result Sorted data.
		 */
		public function usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'product_name'; // phpcs:ignore
			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc'; // phpcs:ignore

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : -$result;

		}


		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns, Array of columns to use with the table.
		 */
		public function get_columns() {

			$columns['cb']           = '<input type="checkbox" />';
			$columns['image']        = __( 'Image', 'wc_pos' );
			$columns['product_name'] = __( 'Product Name', 'wc_pos' );
			$columns['price']        = __( 'Price', 'wc_pos' );

			if ( ! $this->centralized_inventory_enabled ) {
				$columns['master_stock'] = __( 'Master Product Stock', 'wc_pos' );
			}

			$columns['available_stock'] = __( 'Available Product Stock', 'wc_pos' );
			$columns['pos_status']      = __( 'POS Status', 'wc_pos' );

			if ( ! $this->centralized_inventory_enabled ) {
				$columns['assign_pos'] = __( 'Assigned POS Stock', 'wc_pos' );
			}

			return apply_filters( 'WKPOS_outlet_coloums', $columns ); // phpcs:ignore

		}

		/**
		 * Default columns.
		 *
		 * @param array  $item Data that will show in table.
		 * @param string $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'image':
				case 'product_name':
				case 'price':
				case 'master_stock':
				case 'available_stock':
				case 'pos_status':
				case 'assign_pos':
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

			$sortable = array(
				'product_name' => array( 'product_name', true ),
			);
			$sortable = apply_filters( 'wkwc_shortable_columns', $sortable );
			return $sortable;

		}

		/**
		 * Get hidden columns.
		 *
		 * @return array hidden columns.
		 */
		public function get_hidden_columns() {

			return array( 'status_send' );

		}

		/**
		 * Checkbox column.
		 *
		 * @param array $item Data of a particular row of a table.
		 *
		 * @return string Checkbox html design.
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="store_%s" name="pos_pro[]" value="%s" />', $item['id'], $item['id'] );

		}

		/**
		 * Table data.
		 */
		private function table_data() {

			$outlet_id = intval( $_GET['outlet_id'] ); // phpcs:ignore

			$user = get_current_user_id();

			$perpage = get_option( 'pos_outlet_manage_products_per_page', 20 );

			$perpage = apply_filters( 'wkwc_pos_total_items_per_page', $perpage );

			if ( isset( $_GET['paged'] ) ) { // phpcs:ignore

				$page = intval( sanitize_text_field( $_GET['paged'] ) ); // phpcs:ignore

			} else {

				$page = 1;
			}

			$off = absint( ( $page - 1 ) * $perpage );

			if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) { // phpcs:ignore

				$search_query = $_REQUEST['s']; // phpcs:ignore

				$outlet_products = $this->product_obj->get_all_products_by_search( $search_query, $off, $perpage );

			} else {

				$outlet_products = $this->product_obj->get_all_products_by_vendor( $off, $perpage );

			}

			$data = array();

			$pos_status = '';

			if ( ! empty( $outlet_products ) ) {

				add_thickbox();

				foreach ( $outlet_products as $product ) {

					if ( isset( $product['ID'] ) ) {

						$product_id = $product['ID'];

					} elseif ( $product['product_id'] ) {

						$product_id = $product['product_id'];

					}

					$p_value = wc_get_product( intval( $product_id ) );

					$image        = $p_value->get_image( 'thumbnail', 'pos-product-icon=true' );
					$name         = $p_value->get_title();
					$price        = $p_value->get_price_html();
					$stock        = $p_value->get_stock_quantity() ? $p_value->get_stock_quantity() : 0;
					$manage_stock = $p_value->get_manage_stock() ? $p_value->get_manage_stock() : '';

					$pstatus = $this->product_obj->get_pos_product_status( $product_id, $_GET['outlet_id'] ); // phpcs:ignore

					if ( ! $this->centralized_inventory_enabled ) {

						$total_stock = $this->product_obj->get_product_total_stock( $product_id );

						$pstock       = $this->product_obj->get_pos_product_stock( $product_id );
						$master_stock = $this->product_obj->get_pos_product_master_stock( $product_id );

					} else {

						if ( $manage_stock ) {
							$pstock = (int) $stock;
						} else {
							$pstock = $p_value->get_stock_status() ? $p_value->get_stock_status() : '';
						}
					}

					if ( ! empty( $total_stock ) ) {

						$total_stock = wp_list_pluck( $total_stock, 'pos_stock' );
						$total_stock = array_sum( $total_stock );

					} else {

						$total_stock = 0;

					}

					if ( ! empty( $pstock ) ) {
						$pstock = $pstock;
					}

					if ( ! $this->centralized_inventory_enabled ) {

						$remaining_stock = ! $p_value->is_type( 'variable' ) && ! $p_value->is_type( 'grouped' ) ? intval( $master_stock ) - intval( $total_stock ) : '&ndash;';

						$remaining_stock = $remaining_stock != '&ndash' ? apply_filters( 'wkwcpos_get_warehouse_stock', $remaining_stock, $product_id, $_GET['outlet_id'] ) : '&ndash;'; // phpcs:ignore

					} else {

						$remaining_stock = ! $p_value->is_type( 'variable' ) && ! $p_value->is_type( 'grouped' ) ? $pstock : '&ndash;';

					}

					if ( 'enabled' === $pstatus ) {

						$pos_status = '<span class="posever pos-green">' . ucwords( $pstatus ) . '</span>';

						if ( ! $this->centralized_inventory_enabled ) {

							$assign_pos = '<input type="number" data-outlet-id="' . $_GET['outlet_id'] . '" data-product-id="' . $product_id . '" class="pos_pro_stock" value="' . $pstock . '">';
						}
					} else {

						$pos_status = '<span class="posever pos-disable">Disabled</span>';

						if ( ! $this->centralized_inventory_enabled ) {

							$assign_pos = '<input type="number" disabled="true" class="input-disable" value="' . $pstock . '">';
						}
					}

					if ( $p_value->is_type( 'variable' ) ) {

						?>
			<div id="pos-variable-product-thickbox-<?php echo $product_id; ?>" style="display:none;">
			<p><strong style="width:300px;display:inline-block;"><?php echo __( 'Product', 'wc_pos' ); ?>
			</strong><strong style="width:80px;display:inline-block;"><?php echo __( 'Master Stock', 'wc_pos' ); ?>
			</strong><strong style="width:80px;display:inline-block;"><?php echo __( 'Available Stock', 'wc_pos' ); ?>
			</strong><strong style="width:100px;display:inline-block;"><?php echo __( 'Assigned POS Quantity', 'wc_pos' ); ?></strong></p>
			<hr>
						<?php
						if ( $p_value->get_available_variations() ) {
							$m_stock = 0;
							foreach ( $p_value->get_available_variations() as $key => $value ) {

								$variation_data = wc_get_product( $value['variation_id'] );

								$woo_stock = $variation_data->get_stock_quantity() ? $variation_data->get_stock_quantity() : 0;

								if ( ! $this->centralized_inventory_enabled ) {

									$pvstock = $this->product_obj->get_pos_product_stock( $value['variation_id'], $_GET['outlet_id'] );

									$m_stock = $this->product_obj->get_pos_variable_product_master_stock( $value['variation_id'] );

									$total_pos_stock = $this->wpdb->get_var( $this->wpdb->prepare( "SELECT SUM(pos_stock) FROM {$this->wpdb->prefix}woocommerce_pos_outlet_product_map WHERE product_id =%d", $value['variation_id'] ) );

									$a_stock = intval( $m_stock ) - intval( $total_pos_stock );

									$a_stock = apply_filters( 'wkwcpos_get_warehouse_stock', $a_stock, $value['variation_id'], $_GET['outlet_id'] );

								} else {

									$variation_manage_stock = $variation_data->get_manage_stock() ? $variation_data->get_manage_stock() : '';

									if ( $variation_manage_stock ) {
										$pvstock = (int) $woo_stock;
									} else {
										$pvstock = $variation_data->get_stock_status() ? $variation_data->get_stock_status() : '';
									}

									$a_stock = $pvstock;

								}

								?>
				<p><strong style="width:300px;display:inline-block;">
								<?php echo $variation_data->get_formatted_name(); ?>
				</strong>
				<span style="width:80px;display:inline-block;"><?php echo $m_stock; ?></span>
				<span style="width:80px;display:inline-block;"><?php echo $a_stock; ?></span>
				<input style="width: 80px;display:inline-block;vertical-align:top;" type="number" data-outlet-id="<?php echo intval( $_GET['outlet_id'] ); ?>" data-product-id="<?php echo $value['variation_id']; ?>" class="pos_pro_stock thick" value="<?php echo $pvstock; ?>"></p>
								<?php
							}
						}
						?>
				<hr>
						<?php echo __( '<blockquote><p><strong>Important: </strong>Please note all the stock management for variations will be saved on focus and blur.</p></blockquote>', 'wc_pos' ); ?>
				<a href="javascript:void(0)" class="button-primary" id="pos-variation-thickbox-close" onClick="window.location.reload();"><?php esc_html_e( 'Ok', 'wc_pos' ); ?></a>
			</div>

						<?php

						if ( ! $this->centralized_inventory_enabled ) {

							if ( $pstatus == 'enabled' ) {

								$assign_pos = '<a href="#TB_inline?width=600&modal=true&height=auto&inlineId=pos-variable-product-thickbox-' . $product_id . '" title="Variations" class="thickbox posthickbox button-primary">' . __( 'Set variation stock', 'wc_pos' ) . '</a>';

							} else {

								$assign_pos = '<a href="javascript:void(0)" title="Variations" class="button-primary input-disable">' . __( 'Set variation stock', 'wc_pos' ) . '</a>';
							}
						}
					}

					$pos_data = array(

						'id'              => $product_id,

						'image'           => $image,

						'product_name'    => $name,

						'price'           => $price,

						'available_stock' => ! empty( $remaining_stock ) ? $remaining_stock : 0,

						'pos_status'      => $pos_status,

					);

					if ( ! $this->centralized_inventory_enabled ) {

						$pos_data['master_stock'] = ! empty( $master_stock ) ? intval( $master_stock ) : 0;
						$pos_data['assign_pos']   = $assign_pos;

					}

					$data[] = $pos_data;

					$pos_status = '';

				}
			}

			return apply_filters( 'WKWC_pos_outlet_list_products', $data ); // phpcs:ignore

		}

		/**
		 * Image column.
		 *
		 * @param array $item Data of a particular row of a table.
		 *
		 * @return array row actions.
		 */
		public function column_image( $item ) {

			$actions = array(

				'edit' => sprintf( '<a href="post.php?post=%d&action=edit">%s</a>', $item['id'], esc_html__( 'Edit', 'wc_pos' ) ),

			);

			return sprintf( '%1$s %2$s', $item['image'], $this->row_actions( $actions ) );

		}

		/**
		 * Get bulk actions.
		 */
		public function get_bulk_actions() {

			$actions = array(

				'enabled'  => __( 'Enable', 'wc_pos' ),
				'disabled' => __( 'Disable', 'wc_pos' ),
			);

			return $actions;

		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {

			$outlet_id = $_GET['outlet_id']; // phpcs:ignore

			if ( 'enabled' === $this->current_action() || 'disabled' === $this->current_action() ) {

				if ( isset( $_REQUEST['pos_pro'] ) && ! empty( $_REQUEST['pos_pro'] ) && is_array( $_REQUEST['pos_pro'] ) ) { // phpcs:ignore

					$product_ids = $_REQUEST['pos_pro']; // phpcs:ignore

					foreach ( $product_ids as $product_id ) {

						$check_pro = $this->product_obj->get_pos_product_status( $product_id, $outlet_id );

						if ( empty( $check_pro ) ) {

							$check_pro = $this->product_obj->save_pos_product_status( $product_id, $outlet_id );

						} else {

							$check_pro = $this->product_obj->update_pos_product_status( $product_id, $outlet_id, $this->current_action() );

						}
					}
				}
			}

		}

	}

}

$pos_outlet_products = new WC_Pos_Outlet_Product_List();

$pos_outlet_products->prepare_items();

?>
<div class="wrap">

	<form method="GET" action=''>
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; // phpcs:ignore?>" />
		<input type="hidden" name="tab" value="<?php echo $_REQUEST['tab'];  // phpcs:ignore?>" />
		<input type="hidden" name="outlet_action" value="<?php echo $_REQUEST['outlet_action']; // phpcs:ignore?>" />
		<input type="hidden" id="outlet_id" name="outlet_id" value="<?php echo $_REQUEST['outlet_id']; // phpcs:ignore?>" />
	<?php

		wp_nonce_field( '_pos_nonce_action', '_pos_nonce', false );
	if ( ! apply_filters( 'wk_wc_pos_enable_centralized_inventory', false ) ) {
		?>

	<button type="button" id="sync_products" class="button pos_button_css"><?php echo esc_html__( 'Sync Variable Product', 'wc_pos' ); ?></button>
		<?php
			echo wc_help_tip( 'sync all variation products to point of sale' );
			echo "<span id='sync' style='color: green;'></span>";
	}
	$pos_outlet_products->search_box( __( 'Search', 'wc_pos' ), 'search-id' );
	?>
	</form>
	<form method="GET" action=''>
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; // phpcs:ignore ?>" />
		<input type="hidden" name="tab" value="<?php echo $_REQUEST['tab']; // phpcs:ignore?>" />
		<input type="hidden" name="outlet_action" value="<?php echo $_REQUEST['outlet_action']; // phpcs:ignore?>" />
		<input type="hidden" id="outlet_id" name="outlet_id" value="<?php echo $_REQUEST['outlet_id']; // phpcs:ignore ?>" />

	<?php
	$pos_outlet_products->display();
	?>
	</form>

</div>
