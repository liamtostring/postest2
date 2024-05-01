<?php

namespace WKWC_POS\Templates\Admin;

use WP_List_Table;
use WKWC_POS\Helper\Product\WC_Pos_Products_Helper;
use WKWC_POS\Inc\WC_Pos_Errors;
use WKWC_POS\Helper\Outlet\Product\Outlet_Product_Helper;
use WKWC_POS\Helper\Outlet\WC_Pos_Outlet_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'WC_Pos_Products_List' ) ) {
	class WC_Pos_Products_List extends WP_List_Table {

		protected $product_obj;
		protected $error_obj;
		protected $pproduct_obj;
		protected $outlet_helper;
		protected $centralized_inventory_enabled = false;

		public function __construct() {
			$this->centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );
			if ( ! empty( $_REQUEST['s'] ) && ! wp_verify_nonce( isset( $_GET['_pos_nonce'] ) ? $_GET['_pos_nonce'] : '', '_pos_nonce_action' ) ) {
				wp_safe_redirect( wp_get_referer() );
				exit;
			}

			parent::__construct(
				array(
					'singular' => __( 'POS Product List', 'wc_pos' ),
					'plural'   => __( 'POS Product List', 'wc_pos' ),
					'ajax'     => false,
				)
			);
		}

		public function prepare_items() {
			$this->product_obj  = new WC_Pos_Products_Helper();
			$this->error_obj    = new WC_Pos_Errors();
			$this->pproduct_obj = new Outlet_Product_Helper();

			$columns = $this->get_columns();

			$sortable = $this->get_sortable_columns();

			$hidden = $this->get_hidden_columns();

			$this->process_bulk_action();

			$search_query = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

			$filtered_outlet = ! empty( $_GET['outlet'] ) ? sanitize_text_field( $_GET['outlet'] ) : '';

			$items = $this->product_obj->pos_get_all_product_by_count( $search_query, $filtered_outlet );

			$perpage     = get_option( 'pos_products_per_page', 20 );
			$total_items = array_values( $items )[0];

			$data = $this->table_data();

			$this->_column_headers = array( $columns, $hidden, $sortable );

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
			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'product_name';

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( 'asc' === $order ) ? $result : -$result;
		}

		/**
		 * Define the columns that are going to be used in the table.
		 *
		 * @return array $columns, the array of columns to use with the table
		 */
		public function get_columns() {
			$columns = array(
				'cb'             => '<input type="checkbox" />',

				'product_name'   => __( 'Product Name', 'wc_pos' ),

				'image'          => __( 'Image', 'wc_pos' ),

				'outlet'         => __( 'Outlet', 'wc_pos' ),

				'price'          => __( 'Price', 'wc_pos' ),

				'total_quantity' => __( 'Total Quantity', 'wc_pos' ),

				'status'         => __( 'Status', 'wc_pos' ),

				'barcode'        => __( 'Barcode', 'wc_pos' ),
			);

			if ( ! $this->centralized_inventory_enabled ) {
				$columns['assign_pos'] = __( 'Assigned POS Quantity', 'wc_pos' );
			}

			return apply_filters( 'wkwcpos_modify_product_list_column', $columns );
		}

		public function column_default( $item, $column_name ) {

			switch ( $column_name ) {
				case 'product_name':
				case 'image':
				case 'outlet':
				case 'price':
				case 'total_quantity':
				case 'status':
				case 'barcode':
				case 'assign_pos':
					return $item[ $column_name ];

				default:
					return $item[ $column_name ];
			}
		}

		/**
		 * Decide which columns to activate the sorting functionality on.
		 *
		 * @return array $sortable, the array of columns that can be sorted by the user
		 */
		public function get_sortable_columns() {
			return array(
				'product_name' => array( 'product_name', true ),

				'status'       => array( 'status', true ),
			);
		}

		public function get_hidden_columns() {
			return array();
		}

		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" id="pos_product_%s" name="pos_product[]" value="%s" />', $item['id'], $item['id'] );
		}

		private function table_data() {
			$perpage = get_option( 'pos_products_per_page', 20 );

			if ( isset( $_GET['paged'] ) ) {
				$page = intval( sanitize_text_field( $_GET['paged'] ) );
			} else {
				$page = 1;
			}

			$off = absint( ( $page - 1 ) * $perpage );

			$search_query = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

			$filtered_outlet = ! empty( $_GET['outlet'] ) ? sanitize_text_field( $_GET['outlet'] ) : '';

			$products = $this->product_obj->get_all_pos_products( $search_query, $off, $perpage, $filtered_outlet );

			$data = array();

			add_thickbox();

			if ( ! empty( $products ) ) {
				foreach ( $products as $product ) {
					$product_id    = $product['product_id'];
					$outlet_id     = $product['outlet_id'];
					$p_value       = wc_get_product( $product_id );
					$barcode_image = '';
					if ( $p_value && 'product' === $p_value->post_type ) {
						$image = $p_value->get_image( 'thumbnail', 'pos-product-icon=true' );
						$name  = $p_value->get_title();
						$price = wc_price( $p_value->get_price() );

						$stock        = $p_value->get_stock_quantity() ? $p_value->get_stock_quantity() : 0;
						$manage_stock = $p_value->get_manage_stock() ? $p_value->get_manage_stock() : '';
						if ( $manage_stock ) {

							$stock = (int) $stock;

						} else {

							$stock = $p_value->get_stock_status() ? $p_value->get_stock_status() : '';

						}

						$status      = $p_value->get_status();
						$barcode_id  = get_post_meta( $product_id, '_pos_barcode_id', true );
						$barcode_sku = get_post_meta( $product_id, '_pos_barcode_sku', true );
						if ( $p_value->is_type( 'variable' ) ) {
							?>

							<div id="pos-variable-product-barcode-thickbox-<?php echo $product_id; ?>" style="display:none;">
								<table class="wp-list-table widefat striped table-view-list">
									<thead>
										<tr>
											<th><?php echo esc_html__( 'Product', 'wc_pos' ); ?></th>
											<th><?php echo esc_html__( 'Barcode(By ID & SKU)', 'wc_pos' ); ?></th>
											<th><?php echo esc_html__( 'Action', 'wc_pos' ); ?></th>
										</tr>
									</thead>
									<tbody>
									<?php

									if ( $p_value->get_available_variations() ) {
										foreach ( $p_value->get_available_variations() as $key => $value ) {
											$variation_data = wc_get_product( $value['variation_id'] );
											$var_id         = $value['variation_id'];
											$title          = get_the_title( $var_id );
											$barcode_id     = get_post_meta( $var_id, '_pos_barcode_id', true );
											$barcode_sku    = get_post_meta( $var_id, '_pos_barcode_sku', true );
											?>
										<tr>
											<td><?php echo $variation_data->get_formatted_name(); ?></td>
											<td>
												<?php if ( ! empty( $barcode_id ) ) { ?>
													<span class="wkwc-pos-barcode" data-barcode-text="<?php echo esc_attr( esc_html__( 'By ID', 'wc_pos' ) ); ?>" data-barcode-value="<?php echo esc_attr( $barcode_id ); ?>"><svg></svg></span>
												<?php } else { ?>
													<h4><?php echo esc_html__( 'No Barcode Image By ID', 'wc_pos' ); ?></h4>
													<?php
												}
												if ( ! empty( $barcode_sku ) ) {
													?>
													<span class="wkwc-pos-barcode" data-barcode-text="<?php echo esc_attr( esc_html__( 'By SKU', 'wc_pos' ) ); ?>" data-barcode-value="<?php echo esc_attr( $barcode_sku ); ?>"><svg></svg></span>
													<?php } else { ?>
													<h4><?php echo esc_html__( 'No Barcode Image By SKU', 'wc_pos' ); ?></h4>
													<?php
													}
													do_action( 'WKWCPOS_add_custom_barcode_for_variation', $var_id );
													?>
											</td>
											<td>
													<?php
													$custom_data_value = apply_filters( 'WKWCPOS_add_custom_data_option', '', $var_id );
													$print_barcode     = '<a href="' . admin_url( 'admin.php?page=pos-products' ) . '" class="print-barcode button-primary" data-sku="' . $variation_data->get_sku() . '" data-title="' . $title . '" data-image-id="' . $barcode_id . '" data-image-sku="' . $barcode_sku . '"  alt="barcode" ' . $custom_data_value . ' style=""/>Print Barcode </a>';

													echo apply_filters( 'wkwcpos_add_print_variation_coloum_', $print_barcode, $var_id, $custom_data_value );
													?>
											</td>
										</tr>
									<?php } ?>
									<?php } ?>
									</tbody>
								</table>

							</div>

							<?php
							$barcode_image = '<a href="#TB_inline?width=600&height=auto&inlineId=pos-variable-product-barcode-thickbox-' . $product_id . '" title="' . __( 'Variations', 'wc_pos' ) . '" class="thickbox button-primary">' . __( 'barcode', 'wc_pos' ) . '</a>';
						} else {
							$barcode_image = '';
							if ( ! empty( $barcode_id ) ) {
								$barcode_image .= '<span class="wkwc-pos-barcode" data-barcode-text="' . esc_html__( 'By ID', 'wc_pos' ) . '" data-barcode-value="' . $barcode_id . '"><svg></svg></span>';
							}
							if ( ! empty( $barcode_sku ) ) {
								$barcode_image .= '<span class="wkwc-pos-barcode" data-barcode-text="' . esc_html__( 'By SKU', 'wc_pos' ) . '" data-barcode-value="' . $barcode_sku . '"><svg></svg></span>';
							}
						}
						$barcode_image = apply_filters( 'WKWCPOS_show_custom_barcode', $barcode_image, $product_id );

						$outlet = $this->pproduct_obj->get_pos_outlet_name( $outlet_id );
						$pstock = $this->pproduct_obj->get_total_stock( $product_id, $outlet_id );
						$pstock = ! empty( $pstock ) ? array_column( $pstock, 'stock' )[0] : '';

						if ( $p_value->is_type( 'variable' ) ) {
							?>
							<div id="pos-variable-product-thickbox-<?php echo $product_id; ?>" style="display:none;">
								<strong style="width:350px;display:inline-block;"><?php echo __( 'Product', 'wc_pos' ); ?>
									</strong><strong><?php echo __( 'Assigned POS Quantity', 'wc_pos' ); ?></strong>
								<hr>
								<?php
								if ( $p_value->get_available_variations() ) {
									foreach ( $p_value->get_available_variations() as $key => $value ) {
										$variation_data = wc_get_product( $value['variation_id'] );
										$pstock         = $this->pproduct_obj->get_pos_product_stock( $value['variation_id'], $outlet_id );
										?>
									<p><strong style="width:350px;display:inline-block;"><?php echo $variation_data->get_formatted_name(); ?>
										</strong><?php echo esc_html( $pstock ); ?></p>
										<?php
									}
								}
								?>
								<hr>
							</div>

							<?php
							$pstock_data = '<a href="#TB_inline?width=200&height=auto&inlineId=pos-variable-product-thickbox-' . $product_id . '" title="' . __( 'Variations', 'wc_pos' ) . '" class="thickbox button-primary">' . __( 'Variation stock', 'wc_pos' ) . '</a>';
						} else {
							$pstock_data = $pstock;
						}

						$pos_products_data = array(
							'id'             => $product_id,

							'outlet'         => $outlet,

							'image'          => $image,

							'product_name'   => $name,

							'price'          => $price,

							'total_quantity' => $stock,

							'status'         => $status,

							'barcode'        => $barcode_image,
						);

						if ( ! $this->centralized_inventory_enabled ) {
							$pos_products_data['assign_pos'] = $pstock_data;
						}

						$data[] = $pos_products_data;
					}
				}
			}

			return apply_filters( 'wkwcpos_modify_product_list_data', $data );
		}

		/**
		 * Bulk actions on list.
		 */
		public function get_bulk_actions() {
			return apply_filters(
				'wkwcpos_add_bulk_action_in_product_page',
				array(
					'generate_barcode'     => __( 'Generate Barcode by ID', 'wc_pos' ),
					'generate_barcode_sku' => __( 'Generate Barcode by SKU', 'wc_pos' ),
				)
			);
		}

		/**
		 * Process bulk actions.
		 */
		public function process_bulk_action() {
			$count = 0;
			if ( $this->current_action() === 'generate_barcode' ) {
				if ( isset( $_GET['pos_product'] ) && ! empty( $_GET['pos_product'] ) ) {
					if ( is_array( $_GET['pos_product'] ) ) {
						$ids             = $_GET['pos_product'];
						$generated_count = 0;
						foreach ( $ids as $id ) {
							$barcode = '';
							$product = wc_get_product( $id );
							if ( $product->is_type( 'variable' ) ) {
								$var = $product->get_available_variations();
								foreach ( $var as $key => $value ) {
									$var_id  = $value['variation_id'];
									$barcode = get_post_meta( $var_id, '_pos_barcode_id', true );

									$code = 'id' . $id . '&' . $var_id;

									if ( empty( $barcode ) ) {
										update_post_meta( $var_id, '_pos_barcode_id', $code );
										$generated_count++;
										$generated = true;
									} elseif ( $barcode !== $code ) {
										update_post_meta( $var_id, '_pos_barcode_id', $code );
										$generated_count++;
										$generated = true;
									}
								}
							} else {
								$barcode = get_post_meta( $id, '_pos_barcode_id', true );

								$code = 'id' . $id;

								if ( empty( $barcode ) ) {
									update_post_meta( $id, '_pos_barcode_id', $code );
									$generated_count++;
									$generated = true;

								} elseif ( $barcode !== $code ) {
										update_post_meta( $id, '_pos_barcode_id', $code );
										$generated_count++;
										$generated = true;
								}
							}
						}
						if ( true || ! empty( $generated_count ) ) {
							$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by ID successfully.', 'wc_pos' ) );
						}
					} else {
						$id      = $_GET['pos_product'];
						$barcode = '';
						$product = wc_get_product( $id );
						if ( $product->is_type( 'variable' ) ) {
							$var = $product->get_available_variations();
							foreach ( $var as $key => $value ) {
								$var_id  = $value['variation_id'];
								$barcode = get_post_meta( $var_id, '_pos_barcode_id', true );
								$title   = 'wkpos' . $id;

								$code = 'id' . $id . '&' . $var_id;
								if ( empty( $barcode ) ) {
									update_post_meta( $var_id, '_pos_barcode_id', $code );
									$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by ID successfully.', 'wc_pos' ) );
								} elseif ( $barcode !== $code ) {
									update_post_meta( $var_id, '_pos_barcode_id', $code );
									$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by ID successfully.', 'wc_pos' ) );
								}
							}
						} else {
							$barcode = get_post_meta( $id, '_pos_barcode_id', true );

							$code = 'id' . $id;
							if ( empty( $barcode ) ) {
								update_post_meta( $id, '_pos_barcode_id', $code );
								$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by ID successfully.', 'wc_pos' ) );
							} elseif ( $barcode !== $code ) {
								update_post_meta( $id, '_pos_barcode_id', $code );
								$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by ID successfully.', 'wc_pos' ) );
							}
						}
					}
				}
			}

			if ( $this->current_action() === 'generate_barcode_sku' ) {
				if ( isset( $_GET['pos_product'] ) && ! empty( $_GET['pos_product'] ) ) {
					if ( is_array( $_GET['pos_product'] ) ) {
						$ids = $_GET['pos_product'];
						foreach ( $ids as $id ) {
							$barcode = '';
							$product = wc_get_product( $id );
							if ( $product->is_type( 'variable' ) ) {
								$var = $product->get_available_variations();
								foreach ( $var as $key => $value ) {
									$var_id      = $value['variation_id'];
									$barcode     = get_post_meta( $var_id, '_pos_barcode_sku', true );
									$title       = 'wkpos' . $id;
									$var_product = wc_get_product( $var_id );
									if ( ! empty( $var_product->get_sku() ) ) {
										$code = 'sku' . $id . '&' . $var_product->get_sku();
										if ( empty( $barcode ) ) {
											update_post_meta( $var_id, '_pos_barcode_sku', $code );
											$generated = true;
										} elseif ( $barcode !== $code ) {
											update_post_meta( $var_id, '_pos_barcode_sku', $code );
											$generated = true;
										}
									}
								}
							} else {
								$barcode = get_post_meta( $id, '_pos_barcodes_sku', true );
								$title   = 'wkpos' . $id;

								if ( ! empty( $product->get_sku() ) ) {
									$code = 'sku' . $product->get_sku();
									if ( empty( $barcode ) ) {
										update_post_meta( $id, '_pos_barcode_sku', $code );
										$generated = true;
									} elseif ( $barcode !== $code ) {
										update_post_meta( $id, '_pos_barcode_sku', $code );
										$generated = true;
									}
								}
							}
						}
						if ( ! empty( $generated ) ) {
							$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by SKU successfully.', 'wc_pos' ) );
						}
					} else {
						$id      = $_GET['pos_product'];
						$barcode = '';
						$product = wc_get_product( $id );
						if ( $product->is_type( 'variable' ) ) {
							$var = $product->get_available_variations();
							foreach ( $var as $key => $value ) {
								$var_id      = $value['variation_id'];
								$barcode     = get_post_meta( $var_id, '_pos_barcode_sku', true );
								$var_product = wc_get_product( $var_id );
								if ( ! empty( $var_product->get_sku() ) ) {
									$code = 'sku' . $id . '&' . $var_product->get_sku();
									if ( empty( $barcode ) ) {
										update_post_meta( $var_id, '_pos_barcode_sku', $code );
										$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by SKU successfully.', 'wc_pos' ) );
									} elseif ( $barcode !== $code ) {
										update_post_meta( $var_id, '_pos_barcode_sku', $code );
										$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by SKU successfully.', 'wc_pos' ) );
									}
								}
							}
						} else {
							$barcode = get_post_meta( $id, '_pos_barcodes_sku', true );
							$title   = 'wkpos' . $id;

							if ( ! empty( $product->get_sku() ) ) {
								$code = 'sku' . $product->get_sku();
								if ( empty( $barcode ) ) {
									update_post_meta( $id, '_pos_barcode_sku', $code );
									$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by SKU successfully.', 'wc_pos' ) );
								} elseif ( $barcode !== $code ) {
									update_post_meta( $id, '_pos_barcode_sku', $code );
									$this->error_obj->wk_wc_pos_print_notification( __( 'Barcode generated by SKU successfully.', 'wc_pos' ) );
								}
							}
						}
					}
				}
			}
			do_action( 'wkwcpos_custom_bulk_action_in_product_page', $_GET );
			if ( $count > 0 ) {
				echo '<div class="notice notice-error is-dismissible">';
				echo '<p>' . __( 'Commision id is not valid.', 'wc_pos' ) . '</p>';
				echo '</div>';
			}
		}

		public function column_product_name( $item ) {
			$product     = wc_get_product( $item['id'] );
			$sku         = $product->get_sku();
			$barcode_id  = get_post_meta( $item['id'], '_pos_barcode_id', true );
			$barcode_sku = get_post_meta( $item['id'], '_pos_barcode_sku', true );

			$paged           = ! empty( $_REQUEST['paged'] ) ? $_REQUEST['paged'] : 1;
			$search_query    = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			$filtered_outlet = ! empty( $_GET['outlet'] ) ? sanitize_text_field( $_GET['outlet'] ) : '';

			$actions = apply_filters(
				'wkwcpos_add_coloum_action',
				array(
					'print_barcode'        => sprintf( '<a href="' . admin_url( 'admin.php?page=pos-products' ) . '" class="print-barcode" data-sku="%s" data-image-id="%s" data-image-sku="%s" data-title="%s">%s</a>', $sku, $barcode_id, $barcode_sku, get_the_title( $item['id'] ), esc_html__( 'Print Barcode', 'wc_pos' ) ),
					'generate_barcode'     => sprintf( '<a href="' . admin_url( 'admin.php?page=pos-products&action=generate_barcode&pos_product=%s&paged=%d&s=%s&outlet=%s' ) . '">%s</a>', $item['id'], $paged, $search_query, $filtered_outlet, esc_html__( 'Generate Barcode by ID', 'wc_pos' ) ),
					'generate_barcode_sku' => sprintf( '<a href="' . admin_url( 'admin.php?page=pos-products&action=generate_barcode_sku&pos_product=%s&paged=%d&s=%s&outlet=%s' ) . '">%s</a>', $item['id'], $paged, $search_query, $filtered_outlet, esc_html__( 'Generate Barcode by SKU', 'wc_pos' ) ),
				),
				$item
			);

			$product = wc_get_product( $item['id'] );

			if ( ! empty( $product ) && is_object( $product ) && $product->get_type() == 'variable' ) {
				unset( $actions['print_barcode'] );
			}

			return sprintf( '%1$s %2$s', $item['product_name'], $this->row_actions( $actions ) );
		}

		/**
		 * List Filters.
		 *
		 * @param string $which position of filter
		 */
		public function extra_tablenav( $which ) {
			$this->outlet_helper = new WC_Pos_Outlet_Helper();

			$all_outlets = $this->outlet_helper->pos_get_all_outlets();

			$selected_outlet = '';
			if ( 'top' === $which ) {
				if ( isset( $_GET['outlet'] ) ) {
					$selected_outlet = $_GET['outlet'];
				}
				?>
				<div class="alignleft actions bulkactions">
					<select name="outlet" class="pos_input_css">
						<option value=""><?php esc_html_e( 'Select Outlet', 'wc_pos' ); ?></option>

						<?php

						foreach ( $all_outlets as $key => $outlet ) {
							?>
							<option value="<?php echo esc_attr( $outlet->id ); ?>"
							<?php
							if ( $outlet->id == $selected_outlet ) {
								echo esc_attr( 'selected="selected"' );
							}
							?>
							><?php echo esc_html( $outlet->outlet_name ); ?></option>
							<?php
						}
						?>

					</select>

					<input type="submit" class="button" value="<?php esc_attr_e( 'Filter', 'wc_pos' ); ?>" />

				</div>
				<?php
			}
		}
	}
}


?>

	<div id="printBarcode">
		<div class="wc-pos-barcode-print-wrapper">
			<div class="header">
				<button type="button" class="close" data-dismiss="modal"><span class="dashicons dashicons-dismiss"></span></button>
				<h3><?php esc_html_e( 'Enter the number of barcode slips', 'wc_pos' ); ?></h3>
			</div>
			<div>
				<?php $form_class = apply_filters( 'WKWCPOS_custom_barcode_genrate', 'wc-pos-barcode-generate' ); ?>
				<form class="<?php echo esc_attr( $form_class ); ?>" action="" method="post">
						<div class="wc-pos-form-wrapper">
						<?php
						if ( apply_filters( 'WKWCPOS_custom_barcode_genrate_option', true ) ) {
							?>
							<label class="wc-pos-form-input-label" for="barcode-quantity"><?php esc_html_e( 'Barcode By', 'wc_pos' ); ?></label>
							<select name="type" id="barcode-type" class="wc-pos-form-input" style="width:100%">
								<option value='sku'><?php esc_html_e( 'SKU', 'wc_pos' ); ?></option>
								<option value='id'><?php esc_html_e( 'ID', 'wc_pos' ); ?></option>
								<?php echo apply_filters( 'WKWCPOS_add_custom_option', '' ); ?>
							</select>
							<?php } ?>
						</div>
						<div class="wc-pos-form-wrapper">
							<label class="wc-pos-form-input-label" for="barcode-quantity"><?php esc_html_e( 'Quantity', 'wc_pos' ); ?></label>
							<input type="number" min="1" name="quantity" id="barcode-quantity" class="wc-pos-form-input">
						</div>
						<?php
							do_action( 'wkwcpos_add_extra_content_after_quantity' );
						?>
					</div>
					<div class="col-sm-12 text-center">
						<input type="submit" class="barcode-print" value="<?php esc_attr_e( 'Print', 'wc_pos' ); ?>">
					</div>
				</form>
			</div>
		</div>
	</div>
