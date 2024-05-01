<?php
/**
 * POS report stock list class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version 2.1.0
 */

namespace WKWC_POS\Templates\Admin\Reports;

use WP_List_table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * WC_Report_Stock.
 */
class WC_Report_Stock extends WP_List_Table {

	/**
	 * Max items.
	 *
	 * @var int
	 */
	protected $max_items;

	public $outlet;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'stock',
				'plural'   => 'stock',
				'ajax'     => false,
			)
		);

		if ( isset( $_GET['outlet_id'] ) && ! empty( $_GET['outlet_id'] ) && $_GET['outlet_id'] > 0 ) {
			global $wpdb;
			$outlet_map   = $wpdb->prefix . 'woocommerce_pos_outlet_map';
			$this->outlet = $_GET['outlet_id'];
		} else {
			$this->outlet = 0;
		}
	}

	/**
	 * No items found text.
	 */
	public function no_items() {
		_e( 'No products found.', 'wc_pos' );
	}

	/**
	 * Don't need this.
	 *
	 * @param string $position
	 */
	public function display_tablenav( $position ) {
		if ( 'top' !== $position ) {
			parent::display_tablenav( $position );
		}
	}

	/**
	 * Output the report.
	 */
	public function output_report() {
		$this->prepare_items();

		$this->display();
	}

	/**
	 * Get column value.
	 *
	 * @param mixed  $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		global $product;
		global $wpdb;

		$product_map = $wpdb->prefix . 'woocommerce_pos_outlet_product_map';

		$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

		if ( $this->outlet > 0 ) {

			if ( ! $centralized_inventory_enabled ) {

				$pos_stock = $wpdb->get_var( "SELECT pos_stock FROM $product_map WHERE `outlet_id` = $this->outlet AND `product_id` = $item->id " );

				$pos_stock = $pos_stock && ! empty( $pos_stock ) ? $pos_stock : 0;

			}

			if ( $item->parent ) {
				if ( ! $centralized_inventory_enabled ) {
					$stock = get_post_meta( $item->id, '_pos_variation_master_stock', true );
				} else {
					$stock = 'yes' === $item->manage_stock ? (int) $item->stock : $item->stock_status;

				}
			} else {
				if ( ! $centralized_inventory_enabled ) {
					$stock = get_post_meta( $item->id, '_pos_master_stock', true );
				} else {

					$stock = 'yes' === $item->manage_stock ? (int) $item->stock : $item->stock_status;
				}
			}
		} else {

			if ( ! $centralized_inventory_enabled ) {
				$pos_stock = $wpdb->get_var( "SELECT pos_stock FROM $product_map WHERE `product_id` = $item->id " );

				$pos_stock = $pos_stock && ! empty( $pos_stock ) ? $pos_stock : 0;

			}

			if ( $item->parent ) {

				if ( ! $centralized_inventory_enabled ) {
					$stock = get_post_meta( $item->id, '_pos_variation_master_stock', true );
				} else {
					$stock = get_post_meta( $item->id, '_stock', true );
					$stock = 'yes' === $item->manage_stock ? (int) $item->stock : $item->stock_status;
				}
			} else {
				if ( ! $centralized_inventory_enabled ) {
					$stock = get_post_meta( $item->id, '_pos_master_stock', true );
				} else {
					$stock = 'yes' === $item->manage_stock ? (int) $item->stock : $item->stock_status;
				}
			}
		}

		if ( ! $product || $product->get_id() !== $item->id ) {
			$product = wc_get_product( $item->id );

			if ( 'variable' === $product->get_type() && 0 === (int) $item->parent && ! $centralized_inventory_enabled ) {
				return;
			}
		}

		if ( ! $product ) {
			return;
		}

		switch ( $column_name ) {
			case 'product':
				if ( $sku = $product->get_sku() ) {
					echo esc_html( $sku ) . ' - ';
				}

				echo esc_html( $product->get_name() );

				// Get variation data.
				if ( $product->is_type( 'variation' ) ) {
					echo '<div class="description">' . wp_kses_post( wc_get_formatted_variation( $product, true ) ) . '</div>';
				}
				break;

			case 'parent':
				if ( $item->parent ) {
					echo esc_html( get_the_title( $item->parent ) );
				} else {
					echo '-';
				}
				break;

			case 'stock_status':
				if ( $this->outlet > 0 ) {
					if ( ! $centralized_inventory_enabled ) {

						if ( $pos_stock > 0 ) {
							$stock_html = '<mark class="instock">' . __( 'In stock', 'wc_pos' ) . '</mark>';
						} else {
							$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'wc_pos' ) . '</mark>';
						}
					} else {
						if ( $stock > 0 || 'instock' === $stock ) {
							$stock_html = '<mark class="instock">' . __( 'In stock', 'wc_pos' ) . '</mark>';
						} else {
							$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'wc_pos' ) . '</mark>';
						}
					}
				} else {
					if ( ! $centralized_inventory_enabled ) {
						if ( $pos_stock > 0 ) {
							$stock_html = '<mark class="instock">' . __( 'In stock', 'wc_pos' ) . '</mark>';
						} else {
							$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'wc_pos' ) . '</mark>';
						}
					} else {

						if ( $stock > 0 || 'instock' === $stock ) {
							$stock_html = '<mark class="instock">' . __( 'In stock', 'wc_pos' ) . '</mark>';
						} else {
							$stock_html = '<mark class="outofstock">' . __( 'Out of stock', 'wc_pos' ) . '</mark>';
						}
					}
				}
				echo apply_filters( 'woocommerce_admin_stock_html', $stock_html, $product );
				break;

			case 'master_stock':
				echo esc_html( $stock );
				break;

			case 'assign_stock':
				echo isset( $pos_stock ) ? esc_html( $pos_stock ) : '-';
				break;

			case 'total_stock':
				echo esc_html( $stock );
				break;

			case 'wc_actions':
				?><p>
					<?php
					$actions   = array();
					$action_id = $product->is_type( 'variation' ) ? $item->parent : $item->id;

					$actions['edit'] = array(
						'url'    => admin_url( 'post.php?post=' . $action_id . '&action=edit' ),
						'name'   => __( 'Edit', 'wc_pos' ),
						'action' => 'edit',
					);

					if ( $product->is_visible() ) {
						$actions['view'] = array(
							'url'    => get_permalink( $action_id ),
							'name'   => __( 'View', 'wc_pos' ),
							'action' => 'view',
						);
					}

					$actions = apply_filters( 'woocommerce_admin_stock_report_product_actions', $actions, $product );

					foreach ( $actions as $action ) {
						printf(
							'<a class="button tips %1$s" href="%2$s" data-tip="%3$s">%4$s</a>',
							esc_attr( $action['action'] ),
							esc_url( $action['url'] ),
							sprintf( esc_attr__( '%s product', 'wc_pos' ), $action['name'] ),
							esc_html( $action['name'] )
						);
					}
					?>
				</p>
				<?php
				break;
		}
	}

	/**
	 * Get columns.
	 *
	 * @return array $columns Columns.
	 */
	public function get_columns() {
		$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

		if ( $this->outlet > 0 ) {
			$columns['product'] = __( 'Product', 'wc_pos' );
			$columns['parent']  = __( 'Parent', 'wc_pos' );

			if ( ! $centralized_inventory_enabled ) {
				$columns['master_stock'] = __( 'Master Stock', 'wc_pos' );
				$columns['assign_stock'] = __( 'Assign POS Stock', 'wc_pos' );
			} else {
				$columns['total_stock'] = __( 'Total Stock', 'wc_pos' );
			}

			$columns['stock_status'] = __( 'Stock status', 'wc_pos' );
			$columns['wc_actions']   = __( 'Actions', 'wc_pos' );
		} else {
			$columns['product'] = __( 'Product', 'wc_pos' );
			$columns['parent']  = __( 'Parent', 'wc_pos' );

			if ( ! $centralized_inventory_enabled ) {
				$columns['master_stock'] = __( 'Master Stock', 'wc_pos' );
			} else {
				$columns['total_stock'] = __( 'Total Stock', 'wc_pos' );
			}

			$columns['stock_status'] = __( 'Stock status', 'wc_pos' );
			$columns['wc_actions']   = __( 'Actions', 'wc_pos' );
		}

		return $columns;
	}

	/**
	 * Prepare customer list items.
	 */
	public function prepare_items() {
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page          = absint( $this->get_pagenum() );
		$per_page              = apply_filters( 'woocommerce_admin_stock_report_products_per_page', 50 );

		$this->get_items( $current_page, $per_page, $this->outlet );

		/*
		 * Pagination.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $this->max_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->max_items / $per_page ),
			)
		);
	}
}
