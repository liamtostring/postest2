<?php
/**
 * POS customer list report admin class file.
 *
 * @package  WooCommerce Point Of Sale API
 * @version 2.1.0
 */

namespace WKWC_POS\Templates\Admin\Reports;

use WKWC_POS\Helper\Outlet\WC_Pos_Outlet_Helper;

use WP_List_table;
use WP_User_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * POS customer list report admin class.
 */
class WC_Pos_Report_Customer_List extends WP_List_Table {


	/**
	 * Database object.
	 *
	 * @var object $db Worderpress database object.
	 */
	public $db;

	/**
	 * Pos operator.
	 *
	 * @var string $pos_operator Operator like '=', '>', '<' etc.
	 */
	public $pos_operator = '';

	/**
	 * POS outlet id.
	 *
	 * @var int $outlet_id POS outlet id.
	 */
	public $outlet_id;

	/**
	 * POS payment operator.
	 *
	 * @var string $pos_pay_operator Operator like '=', '>', '<' etc.
	 */
	public $pos_pay_operator = '';

	/**
	 * POS payment method.
	 *
	 * @var string $pos_payment POS payment method.
	 */
	public $pos_payment = '';

	/**
	 * POS outlet map table name.
	 *
	 * @var string $outlet_map_table POS outlet map table name.
	 */
	public $outlet_map_table = '';

	/**
	 * POS outlet table name.
	 *
	 * @var string $outlet_table POS outlet table name.
	 */
	public $outlet_table = '';

	/**
	 * Outlet helper class.
	 *
	 * @var object $outlet_helper Oulet helper class object.
	 */
	public $outlet_helper;


	/**
	 * Constructor of the class.
	 */
	public function __construct() {

		global $wpdb;

		$this->db = $wpdb;

		$this->outlet_map_table = $this->db->prefix . 'woocommerce_pos_outlet_map';

		$this->outlet_table = $this->db->prefix . 'woocommerce_pos_outlets';

		$this->outlet_helper = new WC_Pos_Outlet_Helper();

		parent::__construct(
			array(
				'singular' => 'customer',
				'plural'   => 'customers',
				'ajax'     => false,
			)
		);

		if ( isset( $_GET['outlet_id'] ) && ! empty( $_GET['outlet_id'] ) && (int) $_GET['outlet_id'] > 0 ) { // phpcs:ignore
			$this->outlet_id    = (int) $_GET['outlet_id']; // phpcs:ignore
			$this->pos_operator = '=';
		} else {
			$this->outlet_id    = 0;
			$this->pos_operator = '>';
			$this->outlet_id    = apply_filters( 'wkwcpos_change_user_value', $this->outlet_id );
			$this->pos_operator = apply_filters( 'wkwcpos_change_user_operatior_value', $this->pos_operator );
		}

		if ( isset( $_GET['payment'] ) && ! empty( $_GET['payment'] ) ) { // phpcs:ignore
			$this->pos_payment      = $_GET['payment']; // phpcs:ignore
			$this->pos_pay_operator = '=';
		} else {
			$this->pos_payment      = 'All Method';
			$this->pos_pay_operator = '!=';
		}
	}

	/**
	 * No customers found text.
	 */
	public function no_items() {
		_e( 'No customers found.', 'wc_pos' );
	}

	/**
	 * Output the report.
	 */
	public function output_report() {
		$this->prepare_items();

		echo '<div id="poststuff" class="woocommerce-reports-wide">';

		if ( ! empty( $_GET['link_orders'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'link_orders' ) ) { // phpcs:ignore
			$linked = wc_update_new_customer_past_orders( absint( $_GET['link_orders'] ) );

			/* translators: %s Previous order linked */
			echo '<div class="updated"><p>' . wp_sprintf( _n( '%s previous order linked', '%s previous orders linked', intval( $linked ), 'wc_pos' ), intval( $linked ) ) . '</p></div>';
		}

		if ( ! empty( $_GET['refresh'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'refresh' ) ) {  // phpcs:ignore
			$user_id = absint( $_GET['refresh'] );
			$user    = get_user_by( 'id', $user_id );

			delete_user_meta( $user_id, '_money_spent' );
			delete_user_meta( $user_id, '_order_count' );

			/* translators: %s User display name */
			echo '<div class="updated"><p>' . wp_sprintf( esc_html__( 'Refreshed stats for %s', 'wc_pos' ), esc_html( $user->display_name ) ) . '</p></div>';
		}

		$outlets = $this->outlet_helper->pos_get_all_outlets();

		echo "<form action='' method = 'GET' style='float: left;margin: 5px;'>";

		echo '<div><strong>' . esc_html__( 'Select Outlet', 'wc_pos' ) . '</strong></div>';

		echo '<select name="outlet_id">';
		echo '<option value="0">' . esc_html__( 'All Outlet', 'wc_pos' ) . '</option>';

		foreach ( $outlets as $key => $outlet ) {

			$current_outlet_id = $outlet->id;
			$outlet_name       = $outlet->outlet_name;

			echo '<option value="' . intval( $current_outlet_id ) . '" ' . selected( intval( $this->outlet_id ), intval( $current_outlet_id ), true ) . '>' . esc_html( $outlet_name ) . '</option>';

		}
		echo '</select>';
		echo '<span> </span>';
		echo '<input type="submit" class ="button" value = "' . esc_html__( 'Apply', 'wc_pos' ) . '"> ';

		echo "<input type='hidden' value='wc-pos-reports' name='page'>";
		echo "<input type='hidden' value='customer' name='tab'>";

		$this->search_box( __( 'Search customers', 'wc_pos' ), 'customer_search' );
		$this->display();

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Default columns
	 *
	 * @param WP_User $user User object.
	 * @param string  $column_name Column name.
	 *
	 * @return string
	 */
	public function column_default( $user, $column_name ) {
		switch ( $column_name ) {
			case 'customer_name':
				if ( $user->last_name && $user->first_name ) {
					return $user->last_name . ', ' . $user->first_name;
				} else {
					return '-';
				}

				// no break.
			case 'username':
				return $user->user_login;

			case 'location':
				$state_code   = get_user_meta( $user->ID, 'billing_state', true );
				$country_code = get_user_meta( $user->ID, 'billing_country', true );

				$state   = isset( WC()->countries->states[ $country_code ][ $state_code ] ) ? WC()->countries->states[ $country_code ][ $state_code ] : $state_code;
				$country = isset( WC()->countries->countries[ $country_code ] ) ? WC()->countries->countries[ $country_code ] : $country_code;

				$value = '';

				if ( $state ) {
					$value .= $state . ', ';
				}

				$value .= $country;

				if ( $value ) {
					return $value;
				} else {
					return '-';
				}

				// no break.
			case 'email':
				return '<a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>';

			case 'spent':
				return wc_price( $this->wc_get_customer_total_pos_spent( $user->ID ) );

			case 'orders':
				return $this->wc_get_customer_pos_order_count( $user->ID );

			case 'last_order':
				$orders = $this->wc_get_customer_pos_order_last_date( $user->ID );

				if ( ! empty( $orders ) ) {
					$order = (array) $orders[0];
					$date  = date_i18n( 'F j, Y', strtotime( $order['post_date'] ) );

					return '<a href="' . admin_url( 'post.php?post=' . $order['ID'] . '&action=edit' ) . '">' . _x( '#', 'hash before order number', 'wc_pos' ) . $order['ID'] . '</a> &ndash; ' . $date;
				} else {
					return '-';
				}

				break;

			case 'wc_actions':
				ob_start();
				?><p>
					<?php
					do_action( 'woocommerce_admin_user_actions_start', $user );

					$actions = array();

					$actions['refresh'] = array(
						'url'    => wp_nonce_url( add_query_arg( 'refresh', $user->ID ), 'refresh' ),
						'name'   => __( 'Refresh stats', 'wc_pos' ),
						'action' => 'refresh',
					);

					$actions['edit'] = array(
						'url'    => admin_url( 'user-edit.php?user_id=' . $user->ID ),
						'name'   => __( 'Edit', 'wc_pos' ),
						'action' => 'edit',
					);

					$orders = wc_get_orders(
						array(
							'limit'    => 1,
							'status'   => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
							'customer' => array( array( 0, $user->user_email ) ),
						)
					);

					if ( $orders ) {
						$actions['link'] = array(
							'url'    => wp_nonce_url( add_query_arg( 'link_orders', $user->ID ), 'link_orders' ),
							'name'   => __( 'Link previous orders', 'wc_pos' ),
							'action' => 'link',
						);
					}

					$actions = apply_filters( 'woocommerce_admin_user_actions', $actions, $user );

					foreach ( $actions as $action ) {
						printf( '<a class="button tips %s" href="%s" data-tip="%s">%s</a>', esc_attr( $action['action'] ), esc_url( $action['url'] ), esc_attr( $action['name'] ), esc_attr( $action['name'] ) );
					}

					do_action( 'woocommerce_admin_user_actions_end', $user );
					?>
				</p>
				<?php
				$user_actions = ob_get_contents();
				ob_end_clean();

				return $user_actions;
		}

		return '';
	}

	/**
	 * Get customer total spent.
	 *
	 * @param int $user_id User id.
	 *
	 * @return float $total_spents Total spents.
	 */
	public function wc_get_customer_total_pos_spent( $user_id ) {

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$get_sum_query = "SELECT SUM(meta2.meta_value)
			FROM {$this->db->prefix}posts as posts
			LEFT JOIN {$this->db->postmeta} AS meta ON posts.ID = meta.post_id
			LEFT JOIN {$this->db->postmeta} AS meta2 ON posts.ID = meta2.post_id
			LEFT JOIN {$this->db->postmeta} AS meta3 ON posts.ID = meta3.post_id
			LEFT JOIN {$this->db->postmeta} AS meta4 ON posts.ID = meta4.post_id
			WHERE   meta.meta_key       = '_customer_user'
			AND     meta.meta_value     = '" . esc_sql( $user_id ) . "'
			AND     posts.post_type     = 'shop_order'
			AND     posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
			AND     meta2.meta_key      = '_order_total'
			AND     meta3.meta_key			= '_wk_wc_pos_outlet'
			AND 		meta3.meta_value		$this->pos_operator  $this->outlet_id
			AND     meta4.meta_key			= '_payment_method'
			AND 		meta4.meta_value		$this->pos_pay_operator '" . $this->pos_payment . "'";

		$total_spents = $this->db->get_var( $get_sum_query );

		return (float) $total_spents;
	}

	/**
	 * Get pos orders count of the customer.
	 *
	 * @param int $user_id Customer id.
	 *
	 * @return int $count Order count.
	 */
	public function wc_get_customer_pos_order_count( $user_id ) {

		$query = "SELECT COUNT(*)
		FROM {$this->db->prefix}posts as posts
		LEFT JOIN {$this->db->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$this->db->postmeta} AS meta2 ON posts.ID = meta2.post_id
		LEFT JOIN {$this->db->postmeta} AS meta3 ON posts.ID = meta3.post_id

		WHERE   meta.meta_key = '_customer_user'
		AND     posts.post_type = 'shop_order'
		AND     posts.post_status IN ( '" . implode( "','", array_map( 'esc_sql', array_keys( wc_get_order_statuses() ) ) ) . "' )
		AND     meta2.meta_key			= '_wk_wc_pos_outlet'
		AND 		meta2.meta_value		$this->pos_operator $this->outlet_id
		AND     meta3.meta_key			= '_payment_method'
		AND 		meta3.meta_value		$this->pos_pay_operator '" . $this->pos_payment . "'
		AND     meta.meta_value = '" . esc_sql( $user_id ) . "'";

		$count = $this->db->get_var( $query );

		return (int) $count;
	}

	/**
	 * Get customer's last order data.
	 *
	 * @param int $user_id Customer id.
	 *
	 * @return array $last_order Last order data.
	 */
	public function wc_get_customer_pos_order_last_date( $user_id ) {

		$query      = "SELECT *
		FROM {$this->db->prefix}posts as posts
		LEFT JOIN {$this->db->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$this->db->postmeta} AS meta2 ON posts.ID = meta2.post_id
		LEFT JOIN {$this->db->postmeta} AS meta3 ON posts.ID = meta3.post_id
		WHERE   meta.meta_key = '_customer_user'
		AND     posts.post_type = 'shop_order'
		AND     posts.post_status IN ( '" . implode( "','", array_map( 'esc_sql', array_keys( wc_get_order_statuses() ) ) ) . "' )
		AND     meta2.meta_key			= '_wk_wc_pos_outlet'
		AND 		meta2.meta_value		$this->pos_operator  $this->outlet_id
		AND     meta3.meta_key			= '_payment_method'
		AND 		meta3.meta_value		$this->pos_pay_operator '" . $this->pos_payment . "'
		AND     meta.meta_value = '" . esc_sql( $user_id ) . "'
		ORDER BY posts.ID DESC
		LIMIT 1";
		$last_order = $this->db->get_results( $query );

		return $last_order;

	}

	/**
	 * Get columns.
	 *
	 * @return array $columns Columns.
	 */
	public function get_columns() {
		$columns = array(
			'customer_name' => __( 'Name (Last, First)', 'wc_pos' ),
			'username'      => __( 'Username', 'wc_pos' ),
			'email'         => __( 'Email', 'wc_pos' ),
			'location'      => __( 'Location', 'wc_pos' ),
			'orders'        => __( 'Orders', 'wc_pos' ),
			'spent'         => __( 'Money spent', 'wc_pos' ),
			'last_order'    => __( 'Last order', 'wc_pos' ),
			'wc_actions'    => __( 'Actions', 'wc_pos' ),
		);

		return $columns;
	}

	/**
	 * Order users by name.
	 *
	 * @param WP_User_Query $query Wp user query.
	 *
	 * @return WP_User_Query
	 */
	public function order_by_last_name( $query ) {
		$s = ! empty( $_REQUEST['s'] ) ? stripslashes( $_REQUEST['s'] ) : ''; // phpcs:ignore

		$query->query_from   .= " LEFT JOIN {$this->db->usermeta} as meta2 ON ({$this->db->users}.ID = meta2.user_id) ";
		$query->query_where  .= " AND meta2.meta_key = 'last_name' ";
		$query->query_orderby = ' ORDER BY meta2.meta_value, user_login ASC ';

		if ( $s ) {
			$query->query_from   .= " LEFT JOIN {$this->db->usermeta} as meta3 ON ({$this->db->users}.ID = meta3.user_id)";
			$query->query_where  .= " AND ( user_login LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' OR user_nicename LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' OR meta3.meta_value LIKE '%" . esc_sql( str_replace( '*', '', $s ) ) . "%' ) ";
			$query->query_orderby = ' GROUP BY ID ' . $query->query_orderby;
		}

		return $query;
	}

	/**
	 * Prepare customer list items.
	 */
	public function prepare_items() {
		$current_page = absint( $this->get_pagenum() );
		$per_page     = 20;

		/*
		 * Init column headers.
		 */
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

		add_action( 'pre_user_query', array( $this, 'order_by_last_name' ) );

		/**
		 * Get users.
		 */
		$admin_users = new WP_User_Query(
			array(
				'role'   => 'administrator',
				'fields' => 'ID',
			)
		);

		$manager_users = new WP_User_Query(
			array(
				'role'   => 'shop_manager',
				'fields' => 'ID',
			)
		);

		$query = new WP_User_Query(
			array(
				'exclude' => array_merge( $admin_users->get_results(), $manager_users->get_results() ), // phpcs:ignore
				'number'  => $per_page,
				'offset'  => ( $current_page - 1 ) * $per_page,
			)
		);

		$this->items = $query->get_results();

		remove_action( 'pre_user_query', array( $this, 'order_by_last_name' ) );

		/*
		 * Pagination.
		 */
		$this->set_pagination_args(
			array(
				'total_items' => $query->total_users,
				'per_page'    => $per_page,
				'total_pages' => ceil( $query->total_users / $per_page ),
			)
		);
	}
}
