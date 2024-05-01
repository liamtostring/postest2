<?php
/**
 * Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their pos.
 *
 * @package     WooCommerce Point of Sale/Admin
 * @version     4.1.0
 */

namespace WKWC_POS\Includes\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WKWCPOS_Setup_Wizard class.
 */
class WKWCPOS_Setup_Wizard {

	/**
	 * Constructor of the class.
	 */
	public function __construct() {

		if ( apply_filters( 'wkwcpos_enable_setup_wizard', true ) && current_user_can( 'manage_woocommerce' ) ) {

			add_action( 'admin_menu', array( $this, 'wkwcpos_admin_menus' ) );
			add_action( 'admin_init', array( $this, 'wkwcpos_setup_wizard' ) );
			add_action( 'wkwcpos_setup_wizard_scripts', array( $this, 'wkwcpos_enqueue_wizard_scripts' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public function wkwcpos_admin_menus() {
		add_dashboard_page( '', '', 'manage_options', 'wkwcpos-setup', '' );
	}

	/**
	 * Register/enqueue scripts and styles for the Setup Wizard.
	 *
	 * Hooked onto 'admin_enqueue_scripts'.
	 */
	public function wkwcpos_enqueue_wizard_scripts() {
		$dependencies = array( 'wp-components', 'wp-i18n', 'react', 'react-dom', 'wp-hooks' );
		wp_enqueue_script( 'wkwcpos-setup-wizard-script', WK_WC_POS_API . '/assets/dist/setupWizard/index.js', $dependencies, WK_WC_POS_VERSION, true );

		wp_localize_script(
			'wkwcpos-setup-wizard-script',
			'wkwcpos_sw',
			array(
				'site_url'           => esc_url( home_url() ),
				'api_admin_ajax'     => esc_url( admin_url( 'admin-ajax.php' ) ),
				'import_outlet_url'  => esc_url( admin_url( '/admin.php?page=pos-outlets&action=outlet-import' ) ),
				'dashboard_url'      => esc_url( admin_url( 'index.php' ) ),
				'add_outlet_url'     => esc_url( admin_url( '/admin.php?page=pos-outlets&action=add' ) ),
				'review_setting_url' => esc_url( admin_url( '/admin.php?page=wc-pos-settings' ) ),
				'review_setting_url' => esc_url( admin_url( '/admin.php?page=wc-pos-settings' ) ),
				'translations'       => array(
					'welcome'                     => esc_html__( 'Welcome', 'wc_pos' ),
					'webkul_pos'                  => esc_html__( 'Webkul Point of Sale', 'wc_pos' ),
					'for_woocommerce'             => esc_html__( 'for WooCommerce', 'wc_pos' ),
					'start_selling'               => __( "You're ready to start selling", 'wc_pos' ),
					'letsgo'                      => __( "Let's go!", 'wc_pos' ),
					'default_customer'            => esc_html__( 'Default Customer', 'wc_pos' ),
					'general_settings'            => esc_html__( 'General Settings', 'wc_pos' ),
					'api_username'                => esc_html__( 'API Username', 'wc_pos' ),
					'api_password'                => esc_html__( 'API Password', 'wc_pos' ),
					'select_inventory_type'       => esc_html__( 'Select Inventory Type', 'wc_pos' ),
					'master_stock_inventory'      => esc_html__( 'Master Stock Inventory', 'wc_pos' ),
					'centralized_stock_inventory' => esc_html__( 'Centralized Stock Inventory', 'wc_pos' ),
					'next'                        => esc_html__( 'Next', 'wc_pos' ),
					'web_app_settings'            => esc_html__( 'Web App Settings', 'wc_pos' ),
					'name'                        => esc_html__( 'Name', 'wc_pos' ),
					'short_name'                  => esc_html__( 'Short Name', 'wc_pos' ),
					'theme_color'                 => esc_html__( 'Theme Color', 'wc_pos' ),
					'background_color'            => esc_html__( 'Background Color', 'wc_pos' ),
					'user_name'                   => esc_html__( 'User Name', 'wc_pos' ),
					'email'                       => esc_html__( 'Email', 'wc_pos' ),
					'password'                    => esc_html__( 'Password', 'wc_pos' ),
					'telephone'                   => esc_html__( 'Telephone', 'wc_pos' ),
					'ready_to_go'                 => esc_html__( 'Ready to Go', 'wc_pos' ),
					'next_step'                   => esc_html__( 'NEXT STEP', 'wc_pos' ),
					'create_some_outlets'         => esc_html__( 'Create some outlets', 'wc_pos' ),
					'ready_to_add_outlets'        => __( "You're ready to add outlet to your store.", 'wc_pos' ),
					'create_a_outlets'            => esc_html__( 'Create a outlet', 'wc_pos' ),
					'have_existing_pos'           => esc_html__( 'HAVE AN EXISTING POS?', 'wc_pos' ),
					'import_outlets'              => esc_html__( 'Import outlets', 'wc_pos' ),
					'transfer_existing_outlet'    => esc_html__( 'Transfer existing outlets to your new store — just import a CSV file.', 'wc_pos' ),
					'you_can_also'                => esc_html__( 'YOU CAN ALSO:', 'wc_pos' ),
					'visit_dashboard'             => esc_html__( 'Visit Dashboard', 'wc_pos' ),
					'review_settings'             => esc_html__( 'Review Settings', 'wc_pos' ),
					'checkout_our'                => esc_html__( 'Checkout our', 'wc_pos' ),
					'user_guide'                  => esc_html__( 'User Guide', 'wc_pos' ),
					'and'                         => esc_html__( 'and', 'wc_pos' ),
					'video'                       => esc_html__( 'Video', 'wc_pos' ),
					'to_learn_about'              => esc_html__( 'to learn more about.', 'wc_pos' ),
					'username_validation'         => esc_html__( 'Username can not be empty', 'wc_pos' ),
					'email_validation'            => esc_html__( 'Please input a valid email address', 'wc_pos' ),
					'telephone_validation'        => esc_html__( 'Input a valid telephone number', 'wc_pos' ),
					'password_validation'         => esc_html__( 'Password field can not be empty', 'wc_pos' ),
					'week_pass_validation'        => esc_html__( 'Weak password, Do you want to continue?', 'wc_pos' ),
					'please_wait'                 => esc_html__( 'Please wait...', 'wc_pos' ),
					'next'                        => esc_html__( 'Next', 'wc_pos' ),
					'begin_wizard'                => esc_html__( 'Begin Wizard', 'wc_pos' ),
					'ready'                       => esc_html__( 'Ready', 'wc_pos' ),
				),
				'api_endpoints'      => array(
					'get_setup_wizard_data'  => home_url( 'wp-json' ) . '/adminpos/v1/get-setup-wizard-data',
					'post_setup_wizard_data' => home_url( 'wp-json' ) . '/adminpos/v1/save-setup-wizard-data',

				),
				'assets'             => array(
					'pos_logo'      => WK_WC_POS_API . 'assets/images/main.webp',
					'pos_tick_mark' => WK_WC_POS_API . 'assets/images/tick.jpg',
				),
			)
		);

		wp_enqueue_style( 'wkwcpos-setup-wizard-style', WK_WC_POS_API . 'assets/dist/setupWizard/style.css', array(), WK_WC_POS_VERSION );

	}

	/**
	 * Show the setup wizard.
	 */
	public function wkwcpos_setup_wizard() {

		if ( empty( $_GET['page'] ) || 'wkwcpos-setup' !== $_GET['page'] ) { // phpcs:ignore
			return;
		}

		ob_start();
		$this->setup_wizard_header();
		exit;
	}

	/**
	 * Setup Wizard Header.
	 */
	public function setup_wizard_header() {
		set_current_screen();
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta name="viewport" content="width=device-width" />
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?php esc_html_e( 'WooCommerce Point of Sale &rsaquo; Setup Wizard', 'wc_pos' ); ?></title>
			<?php do_action( 'wkwcpos_setup_wizard_scripts' ); ?>
			<?php wp_print_scripts( 'wkwcpos-setup' ); ?>
			<?php do_action( 'admin_print_styles' ); ?>
			<?php do_action( 'admin_head' ); ?>
		</head>
		<body class="wc-setup wp-core-ui">
			<div id="pos-setup-wizard"></div>
		</body>
		<?php
		do_action( 'admin_print_footer_scripts' );
	}

}
