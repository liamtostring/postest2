<?php
/**
 * Plugin Name: WooCommerce Point of Sale
 * Plugin URI: https://store.webkul.com/woocommerce-point-of-sale.html
 * Description: WooCommerce Point of sale module will create offline and online stock management for the products and orders.
 * Version: 5.0.0
 * Author: Webkul
 * Author URI: https://webkul.com
 * License: license.txt included with plugin
 * License URI: https://store.webkul.com/license.html
 * Domain Path: languages/
 * Text Domain: wc_pos
 * Requires at least: 5.0
 * Tested up to: 6.3
 * WC requires at least: 6.0
 * WC tested up to: 7.9
 * Requires PHP: 7.3
 * Tested up to PHP: 8.1
 * Requires Plugins: woocommerce
 *
 * @package WooCommerce Point of Sale
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants.

! defined( 'WK_WC_POS_API' ) && define( 'WK_WC_POS_API', plugin_dir_url( __FILE__ ) );

! defined( 'WK_WC_POS_BASENAME' ) && define( 'WK_WC_POS_BASENAME', plugin_basename( __FILE__ ) );

! defined( 'WKWCPOS_SITE_URL' ) && define( 'WKWCPOS_SITE_URL', site_url() );

! defined( 'WKWCPOS_HOME_URL' ) && define( 'WKWCPOS_HOME_URL', wp_parse_url( site_url(), PHP_URL_PATH ) ? wp_parse_url( site_url(), PHP_URL_PATH ) : '' );

! defined( 'WK_WC_POS_PLUGIN_FILE' ) && define( 'WK_WC_POS_PLUGIN_FILE', plugin_dir_path( __FILE__ ) );

! define( 'WK_WC_POS_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

! defined( 'WK_WC_POS_VERSION' ) && define( 'WK_WC_POS_VERSION', '5.0.0' );


if ( ! function_exists( 'wk_wc_pos_check_woocommerce_is_activated' ) ) {
	/**
	 * Check woocommerce is activated.
	 */
	function wk_wc_pos_check_woocommerce_is_activated() {
		load_plugin_textdomain( 'wc_pos', false, basename( dirname( __FILE__ ) ) . '/languages' );

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'wk_wc_pos_activate_woocommerce_admin_notice_in_pos' );
		} else {
			new WKWC_POS();
		}
	}

	add_action( 'plugins_loaded', 'wk_wc_pos_check_woocommerce_is_activated' );
}


/**
 * Admin notice function for WooCommerce not found.
 */
function wk_wc_pos_activate_woocommerce_admin_notice_in_pos() {
	?>
	<div class="error">
		<p>
			<?php _ex( 'WooCommerce Point of Sale is enabled but not effective. It requires <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce Plugin</a> in order to work.', 'Alert Message: WooCommerce requires', 'wc_pos' ); ?>
		</p>
	</div>
	<?php
}

if ( ! function_exists( 'wk_wc_pos_install_schema' ) ) {
	/**
	 * Schema install callback.
	 */
	function wk_wc_pos_install_schema() {

		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', 'wk_wc_pos_activate_woocommerce_admin_notice_in_pos' );
		} else {

			require_once plugin_dir_path( __FILE__ ) . '/install.php';
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-wkwcpos-service-worker-activator.php';

			$obj = new WK_WC_POS_Install_Schema();
			$obj->wk_wc_pos_create_tables();

			WKWCPOS_Service_Worker_Activator::wkwcpos_activate();

			if ( get_option( 'wkwcpos_setup_finished', false ) ) {
				WC_Admin_Notices::add_custom_notice( 'wkwcpos-setup-install', wkwcpos_setup_wizard_notice() );

			}
		}

	}

	register_activation_hook( __FILE__, 'wk_wc_pos_install_schema' );
}

register_deactivation_hook(
	__FILE__,
	function () {

		if ( class_exists( 'WC_Admin_Notices' ) ) {

			WC_Admin_Notices::remove_notice( 'wkwcpos-setup-install' );
		}
	}
);

/**
 * Setup wizard notice function.
 */
function wkwcpos_setup_wizard_notice() {
	ob_start();
	?>
	<p>
		<?php _e( '<strong>Welcome to WooCommerce Point of Sale</strong> &#8211; You&lsquo;re almost ready to start selling :)', 'wc_pos' ); ?>
	</p>
	<p class="submit">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wkwcpos-setup' ) ); ?>"
			class="button-primary"><?php esc_html_e( 'Run the Setup Wizard', 'wc_pos' ); ?></a> <a
			class="button-secondary skip"
			href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-hide-notice', 'wkwcpos-setup-install' ), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce' ) ); ?>"><?php esc_html_e( 'Skip setup', 'wc_pos' ); ?></a>
	</p>
	<?php
	$output = ob_get_contents();
	ob_end_clean();

	return $output;
}


if ( ! class_exists( 'WKWC_POS' ) ) {
	/**
	 * Point of sale main class.
	 */
	final class WKWC_POS {

		/**
		 * Instance of main class.
		 *
		 * @var object $instance Instance of pos main class.
		 */
		private static $instance = null;

		/**
		 * Assigning the instance of this class in $instance.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor of the class.
		 */
		public function __construct() {
			$args         = array(
				'meta_key'   => 'deault_customer_pos',
				'meta_value' => '1',
			);
			$pos_customer = get_users( $args );
			if ( $pos_customer ) {

				foreach ( $pos_customer as $customer ) {

					$id    = $customer->ID;
					$fname = get_user_meta( $id, 'first_name', true );
				}
			}
			$pwa_app_name = get_option( '_pos_pwa_name' );

			add_filter(
				'wk_wc_pos_enable_centralized_inventory',
				function ( $centralized_inventory_enabled ) {
					if ( empty( get_option( '_pos_inventory_type' ) ) || get_option( '_pos_inventory_type' ) == 'master_stock' ) {
						return false;
					}

					return true;
				},
				10,
				1
			);

			$this->includes();

			add_action( 'woocommerce_order_refunded', array( $this, 'wkwcpos_insert_stats_entry_on_refund' ), 10, 2 );
			add_filter( 'plugin_row_meta', array( __CLASS__, 'pos_plugin_row_meta' ), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'wkpccs_plugin_settings_link' ) );
		}

		/**
		 * Plugin settings link
		 *
		 * @param array $links Links Array.
		 * @return array $links
		 */
		public function wkpccs_plugin_settings_link( $links ) {

			ob_start();
			?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wkwcpos-setup' ) ); ?>"><?php esc_html_e( 'Configuration', 'wc_pos' ); ?></a>
			<?php
				array_unshift( $links, ob_get_contents() );
				ob_end_clean();
				return $links;

		}


		/**
		 * Add pos plugin and doc link in plugin row meta.
		 *
		 * @param string $links Links.
		 * @param string $file Filename.
		 *
		 * @return array links and row_meta.
		 */
		public static function pos_plugin_row_meta( $links, $file ) {
			if ( WK_WC_POS_BASENAME !== $file ) {
				return $links;
			}

			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'wkwc_pos_docs_url', 'https://webkul.com/blog/woocommerce-pos/' ) ) . '" aria-label="' . esc_attr__( 'View WooCommerce Point Of Sale documentation', 'wc_pos' ) . '">' . esc_html__( 'Docs', 'wc_pos' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'wkwc_pos_community_support_url', 'https://webkul.uvdesk.com/' ) ) . '" aria-label="' . esc_attr__( 'Visit Webkul community forums', 'wc_pos' ) . '">' . esc_html__( 'Community support', 'wc_pos' ) . '</a>',
				'review'  => '<a href="' . esc_url( 'https://marketplace.webkul.com/knowledgebase/how-to-rate/' ) . '" aria-label="' . esc_attr__( 'Review the product', 'wc_pos' ) . '">' . esc_html__( 'Review', 'wc_pos' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		/**
		 * Including the required files.
		 */
		private function includes() {
			require_once WK_WC_POS_PLUGIN_FILE . 'includes/wc-pos-file-handler.php';
			require_once WK_WC_POS_PLUGIN_FILE . 'includes/wc-pos-defaults.php';
		}

		/**
		 * Inserting refund data on woocommerce refund.
		 *
		 * @param int $order_id Order id.
		 * @param int $refund_id Refund_id.
		 */
		public function wkwcpos_insert_stats_entry_on_refund( $order_id, $refund_id ) {

			$outlet_id = get_post_meta( $order_id, '_wk_wc_pos_outlet', true );

			if ( ! empty( $outlet_id ) ) {

				global $wpdb;
				$order  = new WC_Order( $order_id );
				$refund = new WC_Order_Refund( $refund_id );

				$data   = array(
					'order_id'           => $refund->get_id(),
					'parent_id'          => $order->get_id(),
					'date_created'       => $refund->get_date_created()->date( 'Y-m-d H:i:s' ),
					'date_created_gmt'   => gmdate( 'Y-m-d H:i:s', $refund->get_date_created()->getTimestamp() ),
					'num_items_sold'     => self::get_num_items_sold( $refund ),
					'total_sales'        => $refund->get_total(),
					'tax_total'          => $refund->get_total_tax(),
					'shipping_total'     => $refund->get_shipping_total(),
					'net_total'          => self::get_net_total( $refund ),
					'status'             => self::normalize_order_status( $refund->get_status() ),
					'customer_id'        => $order->get_customer_id(),
					'returning_customer' => self::is_returning_customer( $order ),
					'outlet_id'          => $outlet_id,
				);
				$format = array(
					'%d',
					'%d',
					'%s',
					'%s',
					'%d',
					'%f',
					'%f',
					'%f',
					'%f',
					'%s',
					'%d',
					'%d',
					'%d',
				);

				// Update or add the information to the DB.
				$wpdb->replace( $wpdb->prefix . 'woocommerce_pos_order_stats', $data, $format );

			}

		}

		/**
		 * Get number of items sold among all orders.
		 *
		 * @param object $order WC_Order object.
		 * @return int
		 */
		protected static function get_num_items_sold( $order ) {
			$num_items = 0;

			$line_items = $order->get_items( 'line_item' );
			foreach ( $line_items as $line_item ) {
				$num_items += $line_item->get_quantity();
			}

			return $num_items;
		}

		/**
		 * Get the net amount from an order without shipping, tax, or refunds.
		 *
		 * @param object $order WC_Order object.
		 * @return float
		 */
		protected static function get_net_total( $order ) {
			$net_total = floatval( $order->get_total() ) - floatval( $order->get_total_tax() ) - floatval( $order->get_shipping_total() );
			return (float) $net_total;
		}

		/**
		 * Check to see if an order's customer has made previous orders or not.
		 *
		 * @param object $order WC_Order object.
		 *
		 * @return bool
		 */
		protected static function is_returning_customer( $order ) {
			$customer_id = $order->get_customer_id();

			if ( ! $customer_id ) {
				return false;
			}

			return wc_get_customer_order_count( $customer_id ) > 1;
		}

		/**
		 * Maps order status provided by the user to the one used in the database.
		 *
		 * @param string $status Order status.
		 *
		 * @return string
		 */
		protected static function normalize_order_status( $status ) {
			return 'wc-' . trim( $status );
		}

	}
}
