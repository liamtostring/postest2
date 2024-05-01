<?php
/**
 * This file load the pos scripts.
 *
 * @package WooCommerce Point of Sale
 * @version 4.1.0
 *
 * @implements Assets_Interface
 */

namespace WKWC_POS\Includes\Front;

use Automattic\WooCommerce\Admin\Features\Features;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Script_Loader' ) ) {

	/**
	 * POS script loader class.
	 */
	class WC_Pos_Script_Loader implements Util\Assets_Interface {

		/**
		 *  POS init function.
		 */
		public function wk_wc_pos_Init() {
			add_action( 'admin_enqueue_scripts', array( $this, 'wk_wc_pos_EnqueueScripts_Admin' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'wk_wc_pos_EnqueueScripts_Front' ) );

		}

		/**
		 * Admin scripts and style enqueue.
		 */
		public function wk_wc_pos_EnqueueScripts_Admin() {

			global $wpdb;

			$pages = apply_filters( 'wkwcpos_add_custom_page_for_pos_style', array( 'pos-system', 'pos-outlets', 'pos-products', 'pos-orders', 'wc-pos-reports', 'wc-pos-invoice-templates', 'wc-pos-settings', 'wc-pos-extensions', 'wkwcpos-setup', 'wc-pos-support-and-services' ) );

			if ( ! empty( $_GET['page'] ) && 'wc-pos-reports' === $_GET['page'] ) { // phpcs:ignore

				$dependencies = apply_filters('wkwcpos_modify_enqueue_report_script_dependencies', array( 'wp-components', 'wc-components', 'react', 'react-dom' ));

				wp_enqueue_script( 'wk-wc-pos-admin-report', WK_WC_POS_API . '/assets/dist/adminReports/index.js', $dependencies, WK_WC_POS_VERSION, true );

				wp_enqueue_style( 'wk-wc-pos-admin-report-style', WK_WC_POS_API . '/assets/dist/adminReports/style.css', array(), WK_WC_POS_VERSION );

				$outlet_info = array();

				$outlets = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_pos_outlets" );


				if ( $outlets ) {
					foreach ( $outlets as $key => $outlet ) {

					$outlet_info[] = array(
							'id'    => $outlet->id,
							'title' => $outlet->outlet_name,
						);

					}
				}

				$currency = array(
					'currency_format_num_decimals' => esc_attr( wc_get_price_decimals() ),
					'currency_format_symbol'       => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES ),
					'currency_format_decimal_sep'  => esc_attr( wc_get_price_decimal_separator() ),
					'currency_format_thousand_sep' => esc_attr( wc_get_price_thousand_separator() ),
					'currency_format'              => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),
				);
				$wkwcpos_admin_report_localized_data = array(
										'api_nonce'                     => wp_create_nonce( 'api-ajaxnonce' ),
										'WK_GET_REPORT_ENDPOINT'        => WKWCPOS_SITE_URL . '/wp-json/adminpos/v1/get-reports',
										'WK_GET_SEARCH_REPORT_ENDPOINT' => WKWCPOS_SITE_URL . '/wp-json/adminpos/v1/get-search-reports',
										'outlet_info'                   => $outlet_info,
										'currency'                      => $currency,
										'translation'                   => $this->get_report_translation(),
				);

		$wkwcpos_admin_report_localized = apply_filters('wkwcpos_modify_admin_report_localized_data', $wkwcpos_admin_report_localized_data );
				wp_localize_script(
					'wk-wc-pos-admin-report',
					'wkwkcpos_admin_report_object',
					$wkwcpos_admin_report_localized
				);

			}

			if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) { // phpcs:ignore
				if ( 'wkwcpos-setup' !== $_GET['page'] ) { // phpcs:ignore
					if ( empty( get_option( 'wkwc_pos_setup_wizard_completed' ) ) ) {
						wp_safe_redirect( admin_url() . 'admin.php?page=wkwcpos-setup' );
						exit;
					}
				}
				wp_enqueue_media();

				wp_register_script( 'wk-wc-pos-barcode-script', WK_WC_POS_API . '/assets/js/min/JsBarcode.all.min.js', array(), WK_WC_POS_VERSION ); // phpcs:ignore

				wp_enqueue_script( 'wk-wc-pos-barcode-script' );

				wp_enqueue_style( 'wk-wc-pos-style', WK_WC_POS_API . 'assets/css/admin.css', array(), WK_WC_POS_VERSION );

				wp_enqueue_style( 'wk-wc-pos-form-style', WK_WC_POS_API . 'assets/dist/adminForms/style.css', array(), WK_WC_POS_VERSION );

				wp_enqueue_script( 'wk-wc-pos-select2-js', plugins_url() . '/woocommerce/assets/js/select2/select2.min.js', array( 'jquery' ), WK_WC_POS_VERSION ); // phpcs:ignore

				wp_enqueue_style( 'wk-wc-pos-select2-css', plugins_url() . '/woocommerce/assets/css/select2.css', array(), WK_WC_POS_VERSION );

				wp_enqueue_style( 'wk-wc-pos-woocommerce-admin-styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WK_WC_POS_VERSION );

				wp_enqueue_style( 'wk-wc-pos-jquery-ui-style', WC()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.min.css', array(), WK_WC_POS_VERSION );

				wp_enqueue_script( 'wk-wc-pos-admin-script', WK_WC_POS_API . '/assets/js/plugin-pos.js', array( 'jquery' ), WK_WC_POS_VERSION ); // phpcs:ignore

				wp_register_script( 'wkwcpos-outlet-import', WK_WC_POS_API . '/assets/js/outlet-import.js', array( 'jquery' ), WK_WC_POS_VERSION, true );

				$footer_left          = sprintf( __( 'If you like <strong>Point of sale</strong> from <strong><a href="https://webkul.com/" target="_blank" class="wc-rating-link" data-rated="Thanks :)">Webkul</a></strong> please leave us a <a href="https://codecanyon.net/item/wordpress-woocommerce-pos-system-point-of-sale/21254976" target="_blank" class="wc-rating-link" data-rated="Thanks :)">★★★★★</a> rating. A huge thanks in advance!', 'wc_pos' ) );
				$barcode_page_preview = get_option( '_pos_barcode_print_page_preview' );

				$translation = array(
					'order_search'   => esc_html__( 'Search by order id', 'wc_pos' ),
					'user_search'    => esc_html__( 'Search by User Name', 'wc_pos' ),
					'outlet_search'  => esc_html__( 'Search by Outlet Name', 'wc_pos' ),
					'product_search' => esc_html__( 'Search by Product Name', 'wc_pos' ),
					'invoice_search' => esc_html__( 'Search by Invoice Name', 'wc_pos' ),
					'payment_search' => esc_html__( 'Search by Payment Name', 'wc_pos' ),
				);
				$localized   = array(
					'api_admin_ajax'    => admin_url( 'admin-ajax.php' ),
					'pos_api_nonce'     => wp_create_nonce( 'api-ajaxnonce' ),
					'site_url'          => site_url(),
					'site_version'      => WK_WC_POS_VERSION,
					'footer_left'       => $footer_left,
					'admin_translation' => $translation,
					'pos_path'          => ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos',
				);
				wp_localize_script(
					'wk-wc-pos-admin-script',
					'wk_wc_apipos_script',
					$localized
				);

				wp_enqueue_script( 'wk-wc-pos-barcode-gen-script', WK_WC_POS_API . 'assets/js/barcode.js', array(), WK_WC_POS_VERSION ); // phpcs:ignore

				wp_localize_script(
					'wk-wc-pos-barcode-gen-script',
					'wkposObj',
					array(
						'page_preview'   => get_option( '_pos_barcode_print_page_preview' ),
						'barcode_config' => array(
							'_pos_barcode_width'         => ! empty( get_option( '_pos_barcode_width' ) ) ? get_option( '_pos_barcode_width' ) : '0.8',
							'_pos_barcode_height'        => ! empty( get_option( '_pos_barcode_height' ) ) ? get_option( '_pos_barcode_height' ) : '20',
							'_pos_barcode_bg_color'      => ! empty( get_option( '_pos_barcode_bg_color' ) ) ? get_option( '_pos_barcode_bg_color' ) : '#ffffff',
							'_pos_barcode_display_title' => ! empty( get_option( '_pos_barcode_display_title' ) ) ? get_option( '_pos_barcode_display_title' ) : 'enable',
							'_pos_barcode_text_position' => ! empty( get_option( '_pos_barcode_text_position' ) ) ? get_option( '_pos_barcode_text_position' ) : 'bottom',
							'_pos_barcode_text_size'     => ! empty( get_option( '_pos_barcode_text_size' ) ) ? get_option( '_pos_barcode_text_size' ) : '14',
						),
					)
				);
				$dependencies = array( 'wp-components', 'wc-components', 'wkwcpos-navigation', 'wp-date', 'wp-i18n', 'moment', 'react', 'react-dom', 'wp-hooks', 'wp-util' );
				$dependencies = array( 'wp-components', 'wp-i18n', 'react', 'react-dom', 'wp-hooks' );

				wp_register_script( 'wkwcpos-invoice-script', WK_WC_POS_API . '/assets/dist/invoice/index.js', $dependencies, WK_WC_POS_VERSION, true );

				wp_register_style( 'wkwcpos-invoice-style', WK_WC_POS_API . '/assets/dist/invoice/style.css', array(), WK_WC_POS_VERSION );

				wp_set_script_translations( 'wkwcpos-invoice-script', 'wc_pos' );

			}

		}

		/**
		 * Enqueue front scripts.
		 */
		public function wk_wc_pos_EnqueueScripts_Front() {

			global $wp;
			$js_file_version  = WK_WC_POS_VERSION;
			$css_file_version = WK_WC_POS_VERSION;

			$query_vars   = $wp->query_vars;
			$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

			// Global scripts.
			wp_register_script( 'wk-wc-pos-barcode-script', WK_WC_POS_API . '/assets/js/min/JsBarcode.all.min.js', array(), WK_WC_POS_VERSION ); // phpcs:ignore
			// End global scripts.

			if ( array_key_exists( $pos_endpoint, $wp->query_vars ) || ( array_key_exists( 'pagename', $query_vars ) && $pos_endpoint == $query_vars['pagename'] ) ) {

				wp_enqueue_style( 'wk-wc-pos-login-css', WK_WC_POS_API . '/assets/css/pos-login.css', array(), WK_WC_POS_VERSION );

				wp_enqueue_script( 'wk-wc-pos-login', WK_WC_POS_API . 'assets/js/min/pos-login.min.js', array( 'jquery' ), WK_WC_POS_VERSION ); // phpcs:ignore

				if ( is_user_logged_in() ) {

					?>

					<script type="text/javascript">

						var wkwcpos_variables = {
							PLUGIN_PATH: "<?php echo esc_url( WK_WC_POS_API ); ?>",
							HOME_URL: "<?php echo esc_url( WKWCPOS_HOME_URL ); ?>",
							POS_URL: "<?php echo esc_url( WKWCPOS_HOME_URL . '/' . $pos_endpoint ); ?>",
							POS_PATH: "<?php echo esc_html( $pos_endpoint ); ?>",
							WKWCPOS_SITE_URL: "<?php echo esc_url( WKWCPOS_SITE_URL ); ?>",
							WK_GET_SESSION_ID_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-session-id' ); ?>",
							WK_GET_ORDERS_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-orders' ); ?>",
							WK_GET_ALL_PRODUCTS_IDS_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-products-id' ); ?>",
							WK_PIN_PRODUCT_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/pin-product' ); ?>",
							WK_GET_POPULAR_PRODUCTS_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-products' ); ?>",
							WK_CREATE_CUSTOMER_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/create-customer' ); ?>",
							WK_GET_CUSTOMERS_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-customers' ); ?>",
							WK_DELETE_CUSTOMER_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/delete-customer' ); ?>",
							WK_CREATE_ORDER_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/create-order' ); ?>",
							WK_CREATE_OFFLINE_ORDER_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/create-offline-order' ); ?>",
							WK_CHECK_STOCK_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/check-stock' ); ?>",
							WK_CHECK_COUPON_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/check-coupon' ); ?>",
							WK_GET_TAX_DETAILS_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-tax-details' ); ?>",
							WK_GET_ALL_CATEGORIES_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-all-categories' ); ?>",
							WK_UPDATE_MANAGER_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/update-manager' ); ?>",
							WK_GET_COUNTRIES_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-countries' ); ?>",
							WK_GET_STATES_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-states' ); ?>",
							WK_GET_ALL_CURRENCIES_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/currencies' ); ?>",
							WK_GET_SALE_HISTORY_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-sale-history' ); ?>",
							WK_CREATE_DRAWER_PERDAY_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/create-drawer-perday' ); ?>",
							WK_GET_INVOICE_TEMPLATE_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-invoice-template' ); ?>",
							WK_GET_REPORT_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-reports' ); ?>",
							WK_GET_SEARCH_REPORT_ENDPOINT: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/get-search-reports' ); ?>",
							WK_CUSTOM_EMAILS: "<?php echo esc_url( WKWCPOS_SITE_URL . '/wp-json/pos/v1/custom_emails' ); ?>",
							WK_USER_PKEY: "<?php echo ! empty( get_option( 'wkwcpos_api_username' ) ) ? esc_attr( base64_encode( get_option( 'wkwcpos_api_username' ) ) ) : ''; ?>",
							WK_USER_PHASH: "<?php echo ! empty( get_option( 'wkwcpos_api_password' ) ) ? esc_attr( base64_encode( get_option( 'wkwcpos_api_password' ) ) ) : ''; ?>"
						};
					</script>

					<?php

					global $wpdb;

					$url = esc_attr( $_SERVER['REQUEST_URI'] ); // phpcs:ignore

					$outlet_payment = array();
					$dir            = wp_upload_dir();
					$user_id        = get_current_user_id();

					$user_data   = get_userdata( $user_id );
					$fname       = $user_data->user_firstname;
					$lname       = $user_data->user_lastname;
					$email       = $user_data->user_email;
					$profile_pic = get_user_meta( $user_id, 'shr_pic', true );

					if ( ! empty( $profile_pic ) ) {
						$url = $dir['baseurl'];

						$profile_pic = $url . $profile_pic;
					} else {
						$profile_pic = get_avatar_url( $user_id );
					}

					$outlet_id = $wpdb->get_var( $wpdb->prepare( "SELECT outlet_id FROM {$wpdb->prefix}woocommerce_pos_outlet_map WHERE user_id=%d", $user_id ) );

					$outlet_data = $wpdb->get_row( $wpdb->prepare( "SELECT * from {$wpdb->prefix}woocommerce_pos_outlets WHERE id = %d", $outlet_id ), ARRAY_A );

					if ( ! empty( $outlet_data ) ) {
						$outlet_payment = ! empty( $outlet_data['outlet_payment'] ) ? $outlet_data['outlet_payment'] : array();
						if ( ! empty( maybe_unserialize( $outlet_payment ) ) ) {
							$outlet_payment = implode( ', ', maybe_unserialize( $outlet_payment ) );
							$outlet_payment = $wpdb->get_results( "SELECT id, payment_slug, payment_name from {$wpdb->prefix}woocommerce_pos_payments WHERE payment_status=1 AND id IN ( $outlet_payment )", ARRAY_A );
							$outlet_payment = maybe_unserialize( $outlet_payment );

						} else {
							$outlet_payment = array();
						}
					}

					$value = array(
						'id'           => '0',
						'payment_slug' => 'card',
						'payment_name' => __( 'Card Payment', 'wc_pos' ),
					);

					array_push( $outlet_payment, $value );

					$logo_invoice = get_option( '_pos_invoice_logo' );

					if ( ! empty( $logo_invoice ) ) {
						$logo_invoice = $dir['baseurl'] . $logo_invoice;
					} else {
						$logo_invoice = WK_WC_POS_API . '/assets/images/17241-200.png';
					}
					$pos_screen_logo = get_option( '_pos_logo_to_pos_screen' );
					$user            = apply_filters(
						'wkwcpos_modify_default_user_data',
						array(
							'user_id'          => $user_id,
							'outlet_id'        => $outlet_id,
							'outlet_data'      => $outlet_data,
							'pos_user_phone'   => get_user_meta( $user_id, 'billing_phone', true ),
							'pos_user'         => $user_data,
							'logo_invoice'     => $logo_invoice,
							'fname'            => $fname,
							'lname'            => $lname,
							'email'            => $email,
							'profile_pic'      => $profile_pic,
							'payment_option'   => maybe_unserialize( $outlet_payment ),
							'tax_type'         => get_option( 'woocommerce_prices_include_tax' ),
							'tax_enabled'      => wc_tax_enabled(),
							'current_date'     => date_i18n( 'D M j, Y' ),
							'difference'       => ! empty( get_option( '_define_difference_after_absolute' ) ) ? intval( get_option( '_define_difference_after_absolute' ) ) : 5,
							'tax_display_cart' => get_option( 'woocommerce_tax_display_cart' ),
							'pos_screen_logo'  => $pos_screen_logo,
						)
					);

					$pos_variable_arr = wkwcpos_get_all_pos_variable();

					$logout_url = wp_logout_url( home_url( '/' . $pos_endpoint ) );

					wp_enqueue_style( 'wk-wc-pos-fontstyle-style', WK_WC_POS_API . 'assets/css/min/font-awesome.min.css', array(), WK_WC_POS_VERSION );

					wp_enqueue_style( 'wk-wc-pos-basic-style', WK_WC_POS_API . 'assets/css/min/basic.min.css', array(), WK_WC_POS_VERSION );

					wp_enqueue_style( 'wk-wc-pos-notifier-style', WK_WC_POS_API . 'assets/css/min/jquery-confirm.min.css', array(), WK_WC_POS_VERSION );

					wp_enqueue_script( 'wk-wc-pos-notifier-script', WK_WC_POS_API . 'assets/js/min/jquery-confirm.min.js', array( 'jquery' ), WK_WC_POS_VERSION ); // phpcs:ignore

					$centralized_inventory_enabled = apply_filters( 'wk_wc_pos_enable_centralized_inventory', false );

					wp_enqueue_script( 'wk-wc-pos-barcode-script' );

					wp_set_script_translations( 'wc-date', 'woocommerce' );

					$scripts = array(
						'wc-number',
						'wc-tracks',
						'wc-date',
						'wc-components',
						'wc-store-data',
						'wc-currency',
						'wc-navigation',
					);

					$scripts_map = array(
						'wc-store-data' => 'data',
					);

					foreach ( $scripts as $script ) {
						$script_path_name = isset( $scripts_map[ $script ] ) ? $scripts_map[ $script ] : str_replace( 'wc-', '', $script );

						try {
							$script_assets_filename = self::get_script_asset_filename( $script_path_name, 'index' );
							$script_assets          = require WC_ADMIN_ABSPATH . WC_ADMIN_DIST_JS_FOLDER . $script_path_name . '/' . $script_assets_filename;

							wp_register_script(
								$script,
								self::get_url( $script_path_name . '/index', 'js' ),
								$script_assets ['dependencies'],
								$js_file_version,
								true
							);

						} catch ( \Exception $e ) {
							// Avoid crashing WordPress if an asset file could not be loaded.
							wc_caught_exception( $e, __CLASS__ . '::' . __FUNCTION__, $script_path_name );
						}
					}
					wp_register_script( 'wkwcpos-navigation', WK_WC_POS_API . 'assets/dist/navigation/index.js', array(), WK_WC_POS_VERSION ); // phpcs:ignore

					wp_set_script_translations( 'wkwcpos-navigation', 'wc_pos' );

					$dependencies = apply_filters( 'wkwcpos_manage_script_dependencies', array( 'wc-components', 'wkwcpos-navigation', 'wp-i18n', 'wp-hooks' ) );

					wp_enqueue_script('wk-wc-pos-script',WK_WC_POS_API . 'assets/dist/app/index.js',$dependencies,WK_WC_POS_VERSION	); // phpcs:ignore

					wp_set_script_translations( 'wk-wc-pos-script', 'wc_pos' );

					wp_register_style(
						'wc-components',
						self::get_url( 'components/style', 'css' ),
						array(),
						WK_WC_POS_VERSION
					);

					wp_enqueue_style( 'wk-wc-pos-css', WK_WC_POS_API . 'assets/dist/app/style.css', array( 'wp-components', 'wc-components' ), WK_WC_POS_VERSION );

					$index_db_class = new WC_Pos_Index_Db();
					$index_tables   = $index_db_class->wc_pos_index_db_tables();

					$global_state   = new WC_Pos_Store();
					$redux_store    = $global_state->wc_pos_global_store();
					$redux_reducers = $global_state->wc_pos_reducers();

					$localize_array = array(
						'api_admin_ajax'                 => admin_url( 'admin-ajax.php' ),
						'pos_api_nonce'                  => wp_create_nonce( 'api-ajaxnonce' ),
						'logged_in'                      => $user,
						'logout_url'                     => $logout_url,
						'pos_tr'                         => $pos_variable_arr,
						'assets'                         => WK_WC_POS_API . '/assets',
						'wk_pos_validate_product_at_pay' => $centralized_inventory_enabled,
						'currency_format_num_decimals'   => esc_attr( wc_get_price_decimals() ),
						'currency_format_symbol'         => get_woocommerce_currency_symbol(),
						'currency_format_decimal_sep'    => esc_attr( wc_get_price_decimal_separator() ),
						'currency_format_thousand_sep'   => esc_attr( wc_get_price_thousand_separator() ),
						'auto_sync'                      => get_option( '_pos_auto_sync_offline_orders' ),
						'keyboard_enable'                => get_option( '_pos_enable_keyboard_shortcuts' ),
						'opening_drawer_enable'          => get_option( '_pos_opening_amount_drawer' ),
						'pos_invoice_option_enabled'     => get_option( '_pos_invoice_option' ),
						'printer_type'                   => ! empty( get_option( '_pos_printer_type' ) ) ? get_option( '_pos_printer_type' ) : 'a4',
						'pos_path'                       => ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos',
						'currency_format'                => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ),   // For accounting JS.
						'product_image_shape'            => ! empty( get_option( '_pos_product_image_view' ) ) ? get_option( '_pos_product_image_view' ) : 'circle',
						'index_tables'                   => $index_tables,
						'woocommerce_currency'           => get_woocommerce_currency(),
						'redux_store'                    => $redux_store,
						'redux_reducers'                 => $redux_reducers,
						'theme_mode'                     => get_option( '_pos_theme_mode', 'default' ),
						'is_tax_enable'                  => ! empty( get_option( 'woocommerce_calc_taxes' ) ) ? get_option( 'woocommerce_calc_taxes' ) : 'no',
						'is_coupon_enable'               => ! empty( get_option( 'woocommerce_enable_coupons' ) ) ? get_option( 'woocommerce_enable_coupons' ) : 'yes',
						'wkwcpos_order_prefix'           => get_option( '_pos_order_id_prefix', '' ),
						'wkwcpos_toast_duration'         => 2000,
					);
					$localize_array = apply_filters( 'wkwcpos_modify_localize_data', $localize_array );
					wp_localize_script( 'wk-wc-pos-script', 'apif_script', $localize_array );

					add_action(
						'wp_enqueue_scripts',
						function () {
							global $wp_styles;

							foreach ( $wp_styles->queue as $s ) {
								$pos_styles = array( 'wk-wc-pos-login-css', 'wk-wc-pos-fontstyle-style', 'wk-wc-pos-notifier-style', 'wk-wc-pos-css', 'wk-wc-pos-basic-style', 'wkposaddon-style', 'wkposaddon-flatpicker-style', 'wcpos-style', 'wc-components', 'wc-components-ie', 'wp-components' );
								$pos_styles = apply_filters( 'wkwcpos_add_custom_css', $pos_styles );
								if ( ! in_array( $s, $pos_styles, true ) ) {
									if ( isset( $wp_styles->registered[ $s ] ) && $wp_styles->registered[ $s ] ) {
										wp_deregister_style( $wp_styles->registered[ $s ]->handle );
									}
								}
							}
						},
						10000
					);

					do_action( 'wkwcpos_enqueue_pos_scripts' );

				}
			}
		}

		/**
		 * Get script asset filename.
		 *
		 * @param string $script_path_name Script path.
		 * @param string $file File name.
		 *
		 * @throws \Exception Exception in case script path is not readable.
		 */
		public static function get_script_asset_filename( $script_path_name, $file ) {

			$minification_supported = Features::exists( 'minified-js' );
			$script_min_filename    = $file . '.min.asset.php';
			$script_nonmin_filename = $file . '.asset.php';
			$script_asset_path      = WC_ADMIN_ABSPATH . WC_ADMIN_DIST_JS_FOLDER . $script_path_name . '/';

			// Check minification is supported first, to avoid multiple is_readable checks when minification is
			// not supported.
			if ( $minification_supported && is_readable( $script_asset_path . $script_min_filename ) ) {
				return $script_min_filename;
			} elseif ( is_readable( $script_asset_path . $script_nonmin_filename ) ) {
				return $script_nonmin_filename;
			} else {
				// could not find an asset file, throw an error.
				throw new \Exception( 'Could not find asset registry for ' . $script_path_name );
			}
		}

		/**
		 * Get file url.
		 *
		 * @param string $file File name.
		 * @param string $ext Extension of file.
		 *
		 * @return string plugin file url.
		 */
		public static function get_url( $file, $ext ) {
			$suffix = '';

			return plugins_url( self::get_path( $ext ) . $file . $suffix . '.' . $ext, WC_ADMIN_PLUGIN_FILE );
		}

		/**
		 * Get path from extension.
		 *
		 * @param string $ext Extension of file.
		 *
		 * @return string Admin dist css/js folder path.
		 */
		private static function get_path( $ext ) {
			return ( 'css' === $ext ) ? WC_ADMIN_DIST_CSS_FOLDER : WC_ADMIN_DIST_JS_FOLDER;
		}

		/**
		 * Get report translation string.
		 *
		 * @return array $report_translation Report translation string.
		 */
		private function get_report_translation() {
			$report_translation = array(
				'title'                         => esc_html__( 'Title', 'wc_pos' ),
				'sku'                           => esc_html__( 'SKU', 'wc_pos' ),
				'date'                          => esc_html__( 'Date', 'wc_pos' ),
				'id'                            => esc_html__( 'ID', 'wc_pos' ),
				'total'                         => esc_html__( 'Total', 'wc_pos' ),
				'netTotal'                      => esc_html__( 'Net Total', 'wc_pos' ),
				'refund'                        => esc_html__( 'Refund', 'wc_pos' ),
				'tax'                           => esc_html__( 'Tax', 'wc_pos' ),
				'totalOrder'                    => esc_html__( 'Total Order', 'wc_pos' ),
				'totalProducts'                 => esc_html__( 'Total Products', 'wc_pos' ),
				'status'                        => esc_html__( 'Status', 'wc_pos' ),
				'totalSale'                     => esc_html__( 'Total Sale', 'wc_pos' ),
				'netSale'                       => esc_html__( 'Net Sale', 'wc_pos' ),
				'totalNumberOfOrders'           => esc_html__( 'Total Number Of Orders', 'wc_pos' ),
				'orderByStatus'                 => esc_html__( 'Order By Status', 'wc_pos' ),
				'netOrder'                      => esc_html__( 'Net Order', 'wc_pos' ),
				'totalRefund'                   => esc_html__( 'Total Refund', 'wc_pos' ),
				'averageOrder'                  => esc_html__( 'Average Order', 'wc_pos' ),
				'totalUnitSold'                 => esc_html__( 'Total Unit Sold', 'wc_pos' ),
				'totalProductSale'              => esc_html__( 'Total Product Sale', 'wc_pos' ),
				'totalProductSold'              => esc_html__( 'Total Products Sold', 'wc_pos' ),
				'netProductSale'                => esc_html__( 'Net Product Sale', 'wc_pos' ),
				'totalNumberOfProducts'         => esc_html__( 'Total Number Of Products', 'wc_pos' ),
				'totalProductTax'               => esc_html__( 'Total Product Tax', 'wc_pos' ),
				'totalTax'                      => esc_html__( 'Total Tax', 'wc_pos' ),
				'totalCouponAmount'             => esc_html__( 'Total Coupon Amount', 'wc_pos' ),
				'couponType'                    => esc_html__( 'Coupon Type', 'wc_pos' ),
				'totalCouponAmountByCouponCode' => esc_html__( 'Total Coupon Amount By Coupon Code', 'wc_pos' ),
				'totalCouponApplied'            => esc_html__( 'Total Coupon Applied', 'wc_pos' ),
				'totalTaxAmount'                => esc_html__( 'Total Tax Amount', 'wc_pos' ),
				'totalShippingTaxAmount'        => esc_html__( 'Total Shipping Tax Amount', 'wc_pos' ),
				'totalShippingTax'              => esc_html__( 'Total Shipping Tax', 'wc_pos' ),
				'taxRate'                       => esc_html__( 'Tax Rate', 'wc_pos' ),
				'totalShippingAmount'           => esc_html__( 'Total Shipping Amount', 'wc_pos' ),
				'lineChart'                     => esc_html__( 'Line Chart', 'wc_pos' ),
				'barChart'                      => esc_html__( 'Bar Chart', 'wc_pos' ),
				'areaChart'                     => esc_html__( 'Area Chart', 'wc_pos' ),
				'charts'                        => esc_html__( 'Charts', 'wc_pos' ),
				'noRecordsFound'                => esc_html__( 'No records found', 'wc_pos' ),
				'reset'                         => esc_html__( 'Reset', 'wc_pos' ),
				'filters'                       => esc_html__( 'Filters', 'wc_pos' ),
				'filter'                        => esc_html__( 'Filter', 'wc_pos' ),
				'filterBy'                      => esc_html__( 'Filter By', 'wc_pos' ),
				'dateRange'                     => esc_html__( 'Date Range', 'wc_pos' ),
				'searching'                     => esc_html__( 'Searching...', 'wc_pos' ),
				'overview'                      => esc_html__( 'Overview', 'wc_pos' ),
				'products'                      => esc_html__( 'Products', 'wc_pos' ),
				'revenue'                       => esc_html__( 'Revenue', 'wc_pos' ),
				'orders'                        => esc_html__( 'Orders', 'wc_pos' ),
				'coupons'                       => esc_html__( 'Coupons', 'wc_pos' ),
				'code'                          => esc_html__( 'Code', 'wc_pos' ),
				'taxes'                         => esc_html__( 'Taxes', 'wc_pos' ),
				'orderStatus'                   => esc_html__( 'Order Status', 'wc_pos' ),
				'productAmount'                 => esc_html__( 'Product Amount', 'wc_pos' ),
				'productName'                   => esc_html__( 'Product Name', 'wc_pos' ),
				'couponAmount'                  => esc_html__( 'Coupon Amount', 'wc_pos' ),
				'couponCode'                    => esc_html__( 'Coupon Code', 'wc_pos' ),
				'orderTotal'                    => esc_html__( 'Order Total', 'wc_pos' ),
				'taxAmount'                     => esc_html__( 'Tax Amount', 'wc_pos' ),
				'refundAmount'                  => esc_html__( 'Refund Amount', 'wc_pos' ),
				'paymentMethod'                 => esc_html__( 'Payment Method', 'wc_pos' ),
				'noRecordsFoundForDownload'     => esc_html__( 'No records found for download.', 'wc_pos' ),
				'downloadxlsx'                  => esc_html__( 'Download XLSX', 'wc_pos' ),
				'noRecordsFound'                => esc_html__( 'No records found.', 'wc_pos' ),
				'noOptionsFound'                => esc_html__( 'No options found', 'wc_pos' ),
				'tableView'                     => esc_html__( 'Table View', 'wc_pos' ),
				'gridView'                      => esc_html__( 'Grid View', 'wc_pos' ),
				'removeFilter'                  => esc_html__( 'Remove filter', 'wc_pos' ),
				'addFilters'                    => esc_html__( 'Add Filters', 'wc_pos' ),
				'enterAtleastLetters'           => esc_html__( 'Enter at least 3 letters', 'wc_pos' ),
				'youCanNotAddMoreFilters'       => esc_html__( 'You can not add more filters.', 'wc_pos' ),
				'selectDateRange'               => esc_html__( 'Select Date Range', 'wc_pos' ),
				'include'                       => esc_html__( 'Include', 'wc_pos' ),
				'exclude'                       => esc_html__( 'Exclude', 'wc_pos' ), // phpcs:ignore
				'equal'                         => esc_html__( 'Equal', 'wc_pos' ),
				'completed'                     => esc_html__( 'Completed', 'wc_pos' ),
				'pending'                       => esc_html__( 'Pending', 'wc_pos' ),
				'processing'                    => esc_html__( 'Processing', 'wc_pos' ),
				'onHold'                        => esc_html__( 'On Hold', 'wc_pos' ),
				'cancelled'                     => esc_html__( 'Cancelled', 'wc_pos' ),
				'refunded'                      => esc_html__( 'Refunded', 'wc_pos' ),
				'greaterThan'                   => esc_html__( 'Greater Than', 'wc_pos' ),
				'lessThan'                      => esc_html__( 'Less than', 'wc_pos' ),
				'enterProductAmount'            => esc_html__( 'Enter product amount', 'wc_pos' ),
				'enterCouponAmount'             => esc_html__( 'Enter coupon amount', 'wc_pos' ),
				'enterOrderAmount'              => esc_html__( 'Enter order amount', 'wc_pos' ),
				'enterTaxAmount'                => esc_html__( 'Enter tax amount', 'wc_pos' ),
				'enterRefundAmount'             => esc_html__( 'Enter refund amount', 'wc_pos' ),
				'discount'                      => esc_html__( 'Discount', 'wc_pos' ),
				'all'                           => esc_html__( 'All', 'wc_pos' ),
				'filterByOutlet'                => esc_html__( 'Filter By Outlet', 'wc_pos' ),
				'partial_refunded'              => esc_html__( 'Partial Refunded', 'wc_pos' ),
				'failed'                        => esc_html__( 'Failed', 'wc_pos' ),
				'reset_to_default'              => esc_html__( 'Reset to default', 'wc_pos' ),

			);

			return apply_filters( 'wkwcpos_modify_report_translation_string', $report_translation );
		}
	}
}
