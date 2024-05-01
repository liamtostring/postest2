<?php

namespace WKWC_POS\Templates\Admin\User;

use WP_List_Table;
use WKWC_POS\Helper;
use WKWC_POS\Inc\WC_Pos_Errors;
use DateTime;
use WP_User;
use DateTimeZone;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}

if ( ! class_exists( 'WC_Pos_User_List' ) ) {

	class WC_Pos_User_List extends WP_List_Table {

		public $user_obj;

		public $error_obj;

		function __construct() {

			parent::__construct(
				array(

					'singular' => __( 'POS User List', 'wc_pos' ),
					'plural'   => __( 'POS User List', 'wc_pos' ),
					'ajax'     => false,

				)
			);

		}

		function prepare_items() {

			$this->user_obj = new Helper\User\WC_Pos_User_Helper();

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

			$totalitems = $this->user_obj->pos_get_all_users_count( $search );

			$screen                = get_current_screen();
			$option                = $screen->get_option( 'per_page', 'option' );
			$user                  = get_current_user_ID();
			$perpage               = ( get_user_meta( $user, $option, true ) ) ? get_user_meta( $user, $option, true ) : 20;
			$this->_column_headers = array( $columns, $hidden, $sortable );

			usort( $data, array( $this, 'usort_reorder' ) );

			$totalpages = ceil( $totalitems / $perpage );

			$this->set_pagination_args(
				array(

					'total_items' => $totalitems,

					'total_pages' => $totalpages,

					'per_page'    => $perpage,

				)
			);

			$this->items = $data;

		}

		function usort_reorder( $a, $b ) {

			$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'date_created';

			$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';

			$result = strcmp( $a[ $orderby ], $b[ $orderby ] );

			return ( $order === 'asc' ) ? $result : -$result;

		}

		/**
		* Define the columns that are going to be used in the table
		* @return array $columns, the array of columns to use with the table
		*/

		function get_columns() {

			$columns = array(

				'cb'           => '<input type="checkbox" />',

				'image'        => __( 'Image', 'wc_pos' ),
				'username'     => __( 'UserName', 'wc_pos' ),

				'outlet'       => __( 'Outlet', 'wc_pos' ),

				'status'       => __( 'Status', 'wc_pos' ),

				'date_created' => __( 'Date Created', 'wc_pos' ),

			);

			return apply_filters( 'wkwc_add_coloumn_user_table', $columns );

		}

		function column_default( $item, $column_name ) {

			switch ( $column_name ) {

				case 'image':
				case 'username':
				case 'outlet':
				case 'status':
				case 'date_created':
					return $item[ $column_name ];

				default:
					return print_r( $item[ $column_name ], true );

			}

		}

		/**
		* Decide which columns to activate the sorting functionality on
		* @return array $sortable, the array of columns that can be sorted by the user
		*/

		public function get_sortable_columns() {

			return $sortable = array(

				'username'     => array( 'username', true ),

				'outlet'       => array( 'outlet', true ),

				'date_created' => array( 'date_created', true ),

			);

		}
		public function get_hidden_columns() {

			return array();

		}

		function column_cb( $item ) {

			return sprintf( '<input type="checkbox" id="user_%s" name="user[]" value="%s" />', $item['id'], $item['id'] );

		}

		private function table_data() {

			global $wpdb;

			$data = array();

			$table_name = $wpdb->prefix . 'woocommerce_pos_outlets';

			$table_name_map = $wpdb->prefix . 'woocommerce_pos_outlet_map';

			$time_zone = get_option( 'timezone_string' );

			$screen  = get_current_screen();
			$option  = $screen->get_option( 'per_page', 'option' );
			$user    = get_current_user_ID();
			$perpage = ( get_user_meta( $user, $option, true ) ) ? get_user_meta( $user, $option, true ) : 20;

			$paged = $this->get_pagenum();

			$offset = ( $paged - 1 ) * $perpage;

			$search = '';

			if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
				$search = $_POST['s'];
			}

			$pos_users = $this->user_obj->pos_get_all_users( $search, $perpage, $offset );
			if ( ! empty( $pos_users ) ) :
				$pic = WK_WC_POS_API . '/assets/images/17241-200.png';
				foreach ( $pos_users as $u_key => $u_value ) {

					$short_pic = get_user_meta( $u_value->ID, 'shr_pic', true );

					if ( ! empty( $short_pic ) ) {
						$pic = wp_upload_dir()['baseurl'] . $short_pic;
					} else {
						$pic = get_avatar_url( $u_value->ID );
					}
					$user_pic     = '<img src=';
					$user_pic    .= $pic;
					$user_pic    .= " alt='profile pic' class='image-url' width='50'>";
					$my_role      = $u_value->roles;
					$date_created = $u_value->user_registered;
					$username     = $u_value->user_login;
					$status       = '<button class="pos-btn-red posever" disabled>' . esc_html__( 'Deactive', 'wc_pos' ) . '</button>';
					$custom_rule  = apply_filters( 'wkwc_allow_other_user', false, $my_role );

					if ( empty( $my_role ) || ( ! empty( $my_role ) && ( in_array( 'pos_user', $my_role ) || $custom_rule ) ) ) {

						if ( 0 === (int) $u_value->data->user_status ) {
							$status = ' <button class="pos-btn-green posever" disabled > ' . esc_html__( 'Active', 'wc_pos' ) . '</button>';

						}

						if ( ! empty( $time_zone ) ) {

							$date = new DateTime( $date_created, new DateTimeZone( 'UTC' ) );

							$date->setTimezone( new DateTimeZone( $time_zone ) );

							$date_created = $date->format( 'l, d - M - y H:i:s T' );

						}
						$pos_outlet_id = $wpdb->get_row( $wpdb->prepare( "select outlet_id from $table_name_map where user_id=%d", $u_value->ID ) );

						$outlet = '';

						if ( ! empty( $pos_outlet_id ) ) {

							$pos_outlet = $wpdb->get_row( $wpdb->prepare( "select outlet_name from $table_name where id=%d", $pos_outlet_id->outlet_id ) );

							if ( ! empty( $pos_outlet ) ) {
								$outlet = $pos_outlet->outlet_name;
							}
						}

						$data[] = array(

							'id'           => $u_value->ID,
							'image'        => $user_pic,

							'username'     => $username,

							'outlet'       => ! empty( $outlet ) ? $outlet : 'N / A',
							'status'       => $status,

							'date_created' => $date_created,

							'user_type'    => reset( $u_value->roles ),

						);

					}
				}

			endif;

			return apply_filters( 'add_custom_user_data', $data );

		}

		function column_image( $item ) {

			$id        = $item['id'];
			$nonce_url = wp_nonce_url( "users.php?action=delete&amp;user=$id", 'bulk-users' );

			$actions = array(

				'edit'   => sprintf( ' <a href=admin.php?page=pos-system&action=edit&pos_user=%s> %s </a>', $item['id'], esc_html__( 'Edit', 'wc_pos' ) ),

				'delete' => sprintf( ' <a href=admin.php?page=pos-system&action=delete&pos_user=%s>%s</a>', $item['id'], esc_html__( 'Delete', 'wc_pos' ) ),

			);

			return sprintf( ' %1$s %2$s', $item['image'], $this->row_actions( $actions ) );

		}

		/**
		* Bulk actions on list.
		*/
		public function get_bulk_actions() {
			$actions = array(
				'activate'   => __( 'Activate', 'wc_pos' ),
				'deactivate' => __( 'Deactivate', 'wc_pos' ),
			);
			return $actions;
		}

		/**
		* Process bulk actions.
		*/
		public function process_bulk_action() {

			global $wpdb;

			$activate_count   = 0;
			$deactivate_count = 0;

			$table_name_map = $wpdb->prefix . 'woocommerce_pos_outlet_map';

			if ( $this->current_action() === 'activate' ) {
				if ( isset( $_POST['user'] ) && ! empty( $_POST['user'] ) ) {
					if ( is_array( $_POST['user'] ) ) {
						$pos_users = $_POST['user'];
						foreach ( $pos_users as $user_id ) {
							$response = $wpdb->update( $wpdb->users, array( 'user_status' => 0 ), array( 'ID' => $user_id ) );

							if ( $response ) {
								$activate_count++;
							}
						}
					}
				}
			} elseif ( $this->current_action() === 'deactivate' ) {
				if ( isset( $_POST['user'] ) && ! empty( $_POST['user'] ) ) {
					if ( is_array( $_POST['user'] ) ) {
						$pos_users = $_POST['user'];
						foreach ( $pos_users as $user_id ) {
							$response = $wpdb->update( $wpdb->users, array( 'user_status' => 1 ), array( 'ID' => $user_id ) );

							if ( $response ) {
								$deactivate_count++;
							}
						}
					}
				}
			}

			if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && isset( $_GET['pos_user'] ) && ! empty( intval( $_GET['pos_user'] ) ) ) {
				$user = get_user_by( 'ID', $_GET['pos_user'] );
				if ( $user ) {

					if ( in_array( 'pos_user', (array) $user->roles ) ) {

						wp_delete_user( $_GET['pos_user'] );

						// check if pos user have outlet assigned
						$pos_outlet_id = $wpdb->get_row( $wpdb->prepare( "select outlet_id from $table_name_map where user_id=%d", intval( $_GET['pos_user'] ) ) );

						if ( $pos_outlet_id ) {

							$wpdb->update(
								$table_name_map,
								array(
									'user_id' => 0,
								),
								array(
									'outlet_id' => $pos_outlet_id->outlet_id,
									'user_id'   => $_GET['pos_user'],
								)
							);
						}

						$message = __( 'User deleted successfully', 'wc_pos' );
						$this->error_obj->set_error_code( 1 );
						$this->error_obj->wk_wc_pos_print_notification( $message );
					} else {

						$message = __( 'Not a Valid POS User . ', 'wc_pos' );
						$this->error_obj->set_error_code( 0 );
						$this->error_obj->wk_wc_pos_print_notification( $message );

					}
				} else {

					$message = __( 'Not a Valid POS User . ', 'wc_pos' );
					$this->error_obj->set_error_code( 0 );
					$this->error_obj->wk_wc_pos_print_notification( $message );

				}
				wp_safe_redirect( admin_url( 'admin.php?page=pos-system' ) );
				exit( 0 );
			}

			if ( $activate_count > 0 ) {
				echo '<div class="notice notice-success is-dismissible">';
					echo '<p>' . esc_html( $activate_count ) . esc_html__( ' user(s) activated successfully.', 'wc_pos' ) . '</p>';
					echo '</div>';
			} elseif ( $deactivate_count > 0 ) {
				echo '<div class="notice notice-success is-dismissible">';
					echo '<p>' . esc_html( $deactivate_count ) . esc_html__( ' user(s) deactivated successfully.', 'wc_pos' ) . '</p>';
					echo '</div>';
			}
		}

	}

}

$wc_pos_list = new WC_Pos_User_List();

$wc_pos_list->prepare_items();

?>
<div class="wrap">

	<h1><?php echo __( 'POS User list', 'wc_pos' ); ?> <a href="<?php echo admin_url() . 'admin.php?page=pos-system&action=add'; ?>" class="page-title-action pos_button_css"><?php _e( 'Add new', 'wc_pos' ); ?></a></h1>

	<form method="POST">

		<input type="hidden" name="page" value="<?php echo $_REQUEST['page']; ?>" />

		<?php

		$wc_pos_list->search_box( __( 'Search Users', 'wc_pos' ), 'search-user' );

		$wc_pos_list->display();

		?>

	</form>

</div>
