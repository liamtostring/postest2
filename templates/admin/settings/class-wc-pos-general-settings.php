<?php
/**
 * This file handles general settings template.
 *
 * @version 2.2.0
 * @package WooCommerce Point of Sale
 */

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Pos_General_Settings' ) ) {
	/**
	 * General Settings Template.
	 */
	class WC_Pos_General_Settings {

		/**
		 * Current page.
		 *
		 * @var string $current_page Current page.
		 */
		public $current_page;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			$this->current_page = isset( $_GET['page'] ) ? $_GET['page'] : '';

			$this->wk_wc_pos_get_general_settings_template();

			if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true ) {
				$this->wkwcpos_generate_new_manifest();
			}
		}

		public function wkwcpos_generate_new_manifest() {
			$form_url = 'admin.php?page=pos-system';

			require_once WK_WC_POS_PLUGIN_FILE . 'includes/class-wkwcpos-service-worker-activator.php';

			\WKWCPOS_Service_Worker_Activator::wkwcpos_generate_new_manifest_file( $form_url );
		}

		public function wk_wc_pos_get_general_settings_template() {
			settings_errors();

			$logo_invoice = get_option( '_pos_invoice_logo' );

			$icon48  = ! empty( get_option( '_pos_pwa_icon48' ) ) ? site_url( get_option( '_pos_pwa_icon48' ) ) : WK_WC_POS_API . 'assets/images/48.png';
			$icon96  = ! empty( get_option( '_pos_pwa_icon96' ) ) ? site_url( get_option( '_pos_pwa_icon96' ) ) : WK_WC_POS_API . 'assets/images/96.png';
			$icon144 = ! empty( get_option( '_pos_pwa_icon144' ) ) ? site_url( get_option( '_pos_pwa_icon144' ) ) : WK_WC_POS_API . 'assets/images/144.png';
			$icon196 = ! empty( get_option( '_pos_pwa_icon196' ) ) ? site_url( get_option( '_pos_pwa_icon196' ) ) : WK_WC_POS_API . 'assets/images/196.png'; ?>

			<div id="wrapper">

				<div id="dashboard_right_now" class="formcontainer pos pos-settings">

					<div class="wc-pos-wrapper">

						<div class="wc-pos-container">

							<form method="post" action="options.php">

								<?php settings_fields( 'pos-general-settings-group' ); ?>

								<?php do_settings_sections( 'pos-general-settings-group' ); ?>


								<div class="wc-pos-form-block">
									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'General Configurations', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">

										<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => 'wkwcpos_api_username',
													'value' => ! empty( get_option( 'wkwcpos_api_username' ) ) ? get_option( 'wkwcpos_api_username' ) : '',
													'label' => __( 'API Username', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'This will be the username for the connection to the API. This can be anything just used to connect our POS API.', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>
										</div>

										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => 'wkwcpos_api_password',
													'value' => $this->wkwcpos_generate_random_password( 30 ),
													'label' => __( 'API Password', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'This will be the password for the connection to the API. This can be anything just used to connect our POS API.', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>

										</div>

										<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_heading_login',
													'value' => ! empty( get_option( '_pos_heading_login' ) ) ? get_option( '_pos_heading_login' ) : '',
													'label' => __( 'POS Heading Login', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'This will be the primary heading on the login page.', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>
										</div>
										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_footer_brand_name',
													'value' => ! empty( get_option( '_pos_footer_brand_name' ) ) ? get_option( '_pos_footer_brand_name' ) : '',
													'label' => __( 'Brand name for footer section', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'This will be the brand name on login page in footer section.', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>

										</div>
										<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_footer_brand_link',
													'value' => ! empty( get_option( '_pos_footer_brand_link' ) ) ? get_option( '_pos_footer_brand_link' ) : '',
													'label' => __( 'Brand Link for footer section', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'This will be the brand name link on login page in footer section.', 'wc_pos' ),
													'type' => 'url',
												)
											);
											?>
										</div>
										<?php do_action( 'wkwcpos_inside_general_configuration', $this->current_page ); ?>

										<div class="options_group wc-pos-log-upload-wraper">
											<strong>
												<?php echo esc_html__( 'Invoice Logo', 'wc_pos' ); ?>
											</strong>
											<div class="wc-pos-log-upload-logo-wraper">
												<div class="wc-pos-log-upload-logo">
													<?php

													$dir = wp_upload_dir();
													if ( ! empty( $logo_invoice ) ) {
														?>
														<img src="<?php echo $dir['baseurl'] . $logo_invoice; ?>" alt='<?php esc_attr_e( 'Invoice Logo', 'wc_pos' ); ?>' class="logo-url" width="100">
														<?php
													} else {
														?>
														<img src="<?php echo WK_WC_POS_API . '/assets/images/17241-200.png'; ?>" alt='<?php esc_attr_e( 'Invoice Logo', 'wc_pos' ); ?>' class="logo-url" width="100">
														<?php
													}
													?>
												</div>
												<div class="wc-pos-log-upload-logo-button">
													<?php

														wkwcpos_text_input(
															array(
																'id'   => '_pos_invoice_logo',
																'value' => $logo_invoice,
																'label' => '',
																'type' => 'hidden',
															)
														);
													?>
													<?php

													wkwcpos_text_input(
														array(
															'id'   => '_pos_upload_logo',
															'value' => 'Upload',
															'label' => '',
															'desc_tip' => true,
															'type' => 'button',
															'class' => 'button-secondary',
														)
													);
													?>
												</div>
											</div>
										</div>
										<?php do_action( 'wkwcpos_after_manage_general_setting_form_fields' ); ?>
									</div>
								</div>

								<?php do_action( 'wkwcpos_after_general_configuration', $this->current_page ); ?>

								<div class="wc-pos-form-block">
									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'POS Panel Configurations', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_inventory_type',
													'value' => ! empty( get_option( '_pos_inventory_type' ) ) ? get_option( '_pos_inventory_type' ) : '',
													'label' => __( 'Select Inventory Type', 'wc_pos' ),
													'options' => array(
														'centralized_stock' => __( 'Centralized Stock Inventory', 'wc_pos' ),
														'master_stock'      => __( 'Master Stock Inventory', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'This is a inventory type which you want for your system.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_product_default_status',
													'value' => ! empty( get_option( '_pos_product_default_status' ) ) ? get_option( '_pos_product_default_status' ) : '',
													'label' => __( 'Default Product Status for Outlet', 'wc_pos' ),
													'options' => array(
														'enabled'  => __( 'Enabled', 'wc_pos' ),
														'disabled' => __( 'Disabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'By enabling this feature, all new created products will be enabled by default in all outlets', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_enable_zero_price_products',
													'value' => get_option( '_pos_enable_zero_price_products', 'disabled' ),
													'label' => __( 'Enable zero price products', 'wc_pos' ),
													'options' => array(
														'enabled'  => __( 'Enabled', 'wc_pos' ),
														'disabled' => __( 'Disabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'By enabling this feature, all the zero price products will show on the pos.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_unit_price_feature',
													'value' => ! empty( get_option( '_pos_unit_price_feature' ) ) ? get_option( '_pos_unit_price_feature' ) : '',
													'label' => __( 'Enable Unit Price Feature', 'wc_pos' ),
													'options' => array(
														'enabled'  => __( 'Enabled', 'wc_pos' ),
														'disabled' => __( 'Disabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'By enabling this feature, product if having weight, will be sold according to its weight in outlets.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_opening_amount_drawer',
													'value' => ! empty( get_option( '_pos_opening_amount_drawer' ) ) ? get_option( '_pos_opening_amount_drawer' ) : 'enabled',
													'label' => __( 'Opening Amount Drawer Status', 'wc_pos' ),
													'options' => array(
														'enabled'  => __( 'Enabled', 'wc_pos' ),
														'disabled' => __( 'Disabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'By enabling this feature opening amount drawer popup will show to input the opening amout otherwise it set the opening amount is zero (0).', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_invoice_option',
													'value' => ! empty( get_option( '_pos_invoice_option' ) ) ? get_option( '_pos_invoice_option' ) : 'enabled',
													'label' => __( 'POS Invoice Option', 'wc_pos' ),
													'options' => array(
														'enabled'  => __( 'Enabled', 'wc_pos' ),
														'disabled' => __( 'Disabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'If this feature enabled then posuser get the option to print the invoice on the POS otherwise posuser will not able to print the invoice.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_mails_at_pos_end',
													'value' => ! empty( get_option( '_pos_mails_at_pos_end' ) ) ? get_option( '_pos_mails_at_pos_end' ) : '',
													'label' => __( 'Enable Mails at POS end', 'wc_pos' ),
													'options' => array(
														'enabled'  => __( 'Enabled', 'wc_pos' ),
														'disabled' => __( 'Disabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'If you disable this option then customers and admin will not receive any mails after placing an order at POS end.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_enable_keyboard_shortcuts',
													'value' => ! empty( get_option( '_pos_enable_keyboard_shortcuts' ) ) ? get_option( '_pos_enable_keyboard_shortcuts' ) : 'enable',
													'label' => esc_html__( 'Enable Keyboard Shortcuts', 'wc_pos' ),
													'options' => array(
														'enable'  => esc_html__( 'Enable', 'wc_pos' ),
														'disable' => esc_html__( 'Disable', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => esc_html__( 'Enable keyboard shortcuts for managing Entire pos via keyaboard', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_logo_to_pos_screen',
													'value' => ! empty( get_option( '_pos_logo_to_pos_screen' ) ) ? get_option( '_pos_logo_to_pos_screen' ) : '',
													'label' => __( 'Enable POS Logo to POS screen header', 'wc_pos' ),
													'options' => array(
														'disabled' => __( 'Disabled', 'wc_pos' ),
														'enabled'  => __( 'Enabled', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'If you disable this option then POS screen will show only web username.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											$image_sizes = apply_filters(
												'wkwcpos_update_image_sizes',
												array(
													'circle'  => esc_html__( 'Circle', 'wc_pos' ),
													'square'  => esc_html__( 'Square', 'wc_pos' ),
													'rounded' => esc_html__( 'Rounded', 'wc_pos' ),
												)
											);
											wkwcpos_select(
												array(
													'id' => '_pos_product_image_view',
													'value' => ! empty( get_option( '_pos_product_image_view' ) ) ? get_option( '_pos_product_image_view' ) : 'circle',
													'label' => esc_html__( 'POS Product Image View', 'wc_pos' ),
													'options' => $image_sizes,
													'desc_tip' => true,
													'description' => esc_html__( 'Select The POS Product Image View As per your requirement', 'wc_pos' ),
												)
											);
											?>
										</div>
										<?php do_action( 'pos_manage_general_settings_custom_fields', $this->current_page ); ?>
									</div>
								</div>
								<?php do_action( 'wkwcpos_after_pos_panel_configuration', $this->current_page ); ?>

								<div class="wc-pos-form-block">
									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'Order & Stock Configurations', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">
										<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_low_stock_warn',
													'value' => ! empty( get_option( '_pos_low_stock_warn' ) ) ? get_option( '_pos_low_stock_warn' ) : '',
													'label' => __( 'Quantity For Low Stock Warning', 'wc_pos' ),
													'desc_tip' => true,
													'min'  => '1',
													'custom_attributes' => array( 'step=1' ),
													'description' => __( 'This will be the maximum quantity for products to show the low stock warnings.', 'wc_pos' ),
													'type' => 'number',

												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_define_difference_after_absolute',
													'value' => ! empty( get_option( '_define_difference_after_absolute' ) ) ? get_option( '_define_difference_after_absolute' ) : '5',
													'label' => __( 'Amount you want to increase after roundoff', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'Amount you want to increase after roundoff in POS payment custom Option.', 'wc_pos' ),
													'type' => 'number',
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_auto_sync_offline_orders',
													'value' => ! empty( get_option( '_pos_auto_sync_offline_orders' ) ) ? get_option( '_pos_auto_sync_offline_orders' ) : '',
													'label' => __( 'Auto Sync Offline Orders', 'wc_pos' ),
													'options' => array(
														'enable'  => __( 'Enable', 'wc_pos' ),
														'disable' => __( 'Disable', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'Offline orders will sync automatically when you will create any order online', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_load_woo_orders_on_outlet',
													'value' => get_option( '_pos_load_woo_orders_on_outlet', 'disable' ),
													'label' => __( 'Load WooCommerce Orders', 'wc_pos' ),
													'options' => array(
														'enable'  => __( 'Enable', 'wc_pos' ),
														'disable' => __( 'Disable', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => __( 'By enabling this feature, the woocommerce order will sync on the pos outlets.', 'wc_pos' ),
												)
											);
											?>
										</div>
										<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_order_id_prefix',
													'value' => get_option( '_pos_order_id_prefix', '' ),
													'label' => __( 'Order Id Prefix', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'Enter your order id prefix, e.g. #POS_470 Here 470 is order id and POS_ is prefix', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>

										</div>
										<?php do_action( 'wkwcpos_inside_order_and_stock_configurations', $this->current_page ); ?>
									</div>
								</div>
								<?php do_action( 'wkwcpos_after_order_and_stock_configurations', $this->current_page ); ?>

								<div class="wc-pos-form-block">

									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'Barcode Settings', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">

									<div class="options_group">

									<?php
									wkwcpos_text_input(
										array(
											'id'          => '_pos_barcode_bg_color',
											'value'       => ! empty( get_option( '_pos_barcode_bg_color' ) ) ? get_option( '_pos_barcode_bg_color' ) : '#ffffff',
											'label'       => esc_html__( 'Background Color', 'wc_pos' ),
											'desc_tip'    => true,
											'description' => esc_html__( 'This will be the barcode background color.', 'wc_pos' ),
											'type'        => 'color',
											'custom_attributes' => array( 'list=preset-colors' ),
										)
									);
									$preset_colors = apply_filters( 'wkwcpos_add_custom_color_in_color_box', array( '#FF7F50', '#F08080', '#FFA500', '#FFD700', '#FFFF00', '#00FF00', '#90EE90', '#00FFFF', '#AFEEEE', '#EE82EE', '#FFC0CB', '#FFFFFF' ) );
									?>
									<datalist id="preset-colors" >
									<?php
									foreach ( array_reverse( $preset_colors ) as $color ) {
										?>
											<option><?php echo $color; ?></option>
										<?php } ?>
									</datalist>
									</div>

									<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_barcode_width',
													'value' => ! empty( get_option( '_pos_barcode_width' ) ) ? get_option( '_pos_barcode_width' ) : '0.8',
													'label' => esc_html__( 'Width', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'This will incrase the barcode width.', 'wc_pos' ),
													'type' => 'range',
													'min'  => '0.5',
													'max'  => '2.0',
													'custom_attributes' => array( 'step=0.1' ),
												)
											);
											?>

										</div>
									<div class="options_group">

											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_barcode_display_title',
													'value' => ! empty( get_option( '_pos_barcode_display_title' ) ) ? get_option( '_pos_barcode_display_title' ) : 'circle',
													'label' => esc_html__( 'Display Title', 'wc_pos' ),
													'options' => array(
														'enable'  => esc_html__( 'Enable', 'wc_pos' ),
														'disable'  => esc_html__( 'Disable', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => esc_html__( 'Product title will show or hide by this setting.', 'wc_pos' ),
												)
											);
											?>

										</div>

									<div class="options_group">
											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_barcode_height',
													'value' => ! empty( get_option( '_pos_barcode_height' ) ) ? get_option( '_pos_barcode_height' ) : '20',
													'label' => esc_html__( 'Height', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'This will incrase the barcode width.', 'wc_pos' ),
													'type' => 'range',
													'min'  => '10',
													'max'  => '100',
													'custom_attributes' => array( 'step=1' ),
												)
											);
											?>

										</div>


										<div class="options_group">

											<?php
											wkwcpos_select(
												array(
													'id' => '_pos_barcode_text_position',
													'value' => ! empty( get_option( '_pos_barcode_text_position' ) ) ? get_option( '_pos_barcode_text_position' ) : 'circle',
													'label' => esc_html__( 'Text Position', 'wc_pos' ),
													'options' => array(
														'top'  => esc_html__( 'Top', 'wc_pos' ),
														'bottom'  => esc_html__( 'Bottom', 'wc_pos' ),
													),
													'desc_tip' => true,
													'description' => esc_html__( 'Barcode text will display in top or bottom.', 'wc_pos' ),
												)
											);
											?>

										</div>



										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_barcode_text_size',
													'value' => ! empty( get_option( '_pos_barcode_text_size' ) ) ? get_option( '_pos_barcode_text_size' ) : 14,
													'label' => esc_html__( 'Text Size', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'Barcode text size will change the title text & custom text size', 'wc_pos' ),
													'type' => 'range',
													'min'  => 10,
													'max'  => 50,
												)
											);
											?>


										</div>

										<div class="options_group">
										<?php
										wkwcpos_select(
											array(
												'id'       => '_pos_barcode_print_page_preview',
												'value'    => ! empty( get_option( '_pos_barcode_print_page_preview' ) ) ? get_option( '_pos_barcode_print_page_preview' ) : '',
												'label'    => __( 'Print Preview', 'wc_pos' ),
												'options'  => array(
													'portrait' => __( 'Portrait', 'wc_pos' ),
													'landscape' => __( 'Landscape', 'wc_pos' ),
												),
												'desc_tip' => true,
												'description' => __( 'Barcode Print preview will appear in this page format', 'wc_pos' ),
											)
										);
										?>
									</div>
									<div class="options_group wc-pos-text-size-reflector">
											<span class="wkwc-pos-barcode-reflect" data-barcode-text="<?php echo esc_html__( 'Barcode demo text', 'wc_pos' ); ?>" data-barcode-value="<?php echo esc_html__( 'Barcode demo text', 'wc_pos' ); ?>"><svg></svg></span>
										</div>

									<?php do_action( 'wkwcpos_manage_barcode_setting_form_fields', $_GET['page'] ); ?>
									</div>
								</div>

								<div class="wc-pos-form-block">

									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'Web APP Settings', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">

										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_pwa_name',
													'value' => ! empty( get_option( '_pos_pwa_name' ) ) ? get_option( '_pos_pwa_name' ) : '',
													'label' => esc_html__( 'Name', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'This will be the name for POS Progressive Web App.', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>

										</div>

										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_pwa_shortname',
													'value' => ! empty( get_option( '_pos_pwa_shortname' ) ) ? get_option( '_pos_pwa_shortname' ) : '',
													'label' => esc_html__( 'Short Name', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'This will be the short name for POS Progressive Web App.', 'wc_pos' ),
													'type' => 'text',
												)
											);
											?>

										</div>

										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_pwa_themecolor',
													'value' => ! empty( get_option( '_pos_pwa_themecolor' ) ) ? get_option( '_pos_pwa_themecolor' ) : '',
													'label' => esc_html__( 'Theme Color', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'This will be the theme color for POS Progressive Web App.', 'wc_pos' ),
													'type' => 'color',
												)
											);
											?>

										</div>

										<div class="options_group">

											<?php
											wkwcpos_text_input(
												array(
													'id'   => '_pos_pwa_bgcolor',
													'value' => ! empty( get_option( '_pos_pwa_bgcolor' ) ) ? get_option( '_pos_pwa_bgcolor' ) : '',
													'label' => esc_html__( 'Background Color', 'wc_pos' ),
													'desc_tip' => true,
													'description' => esc_html__( 'This will be the background color for POS Progressive Web App.', 'wc_pos' ),
													'type' => 'color',
												)
											);
											?>

										</div>

									<?php do_action( 'wkwcpos_manage_web_app_setting_form_fields', $_GET['page'] ); ?>
									</div>
								</div>
								<div class="wc-pos-form-block">

									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'Media Settings', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">

										<div class="options_group wc-pos-log-upload-wraper">
											<strong>
												<?php esc_html_e( 'App Icon (48x48)', 'wc_pos' ); ?>
											</strong>

											<div class="wc-pos-log-upload-logo-wraper">
												<div class="wc-pos-log-upload-logo">
													<img src="<?php echo esc_url( $icon48 ); ?>" alt='icon' class="image-url" width="48">
												</div>
												<div class="wc-pos-log-upload-logo-button">
													<input type="hidden" id="pos-pwa-icon48" name="_pos_pwa_icon48" value="<?php echo esc_attr( ! empty( get_option( '_pos_pwa_icon48' ) ) ? get_option( '_pos_pwa_icon48' ) : '' ); ?>" />
													<button data-id="pos-pwa-icon48" class="button-primary icon-uploader" /><?php esc_html_e( 'Upload Icon', 'wc_pos' ); ?></button>
												</div>
											</div>
										</div>
										<div class="options_group wc-pos-log-upload-wraper">
											<strong>
												<?php esc_html_e( 'App Icon (96x96)', 'wc_pos' ); ?>
											</strong>

											<div class="wc-pos-log-upload-logo-wraper">
												<div class="wc-pos-log-upload-logo">
													<img src="<?php echo esc_url( $icon96 ); ?>" alt='icon' class="image-url" width="96">
												</div>
												<div class="wc-pos-log-upload-logo-button">
													<input type="hidden" id="pos-pwa-icon96" name="_pos_pwa_icon96" value="<?php echo esc_attr( ! empty( get_option( '_pos_pwa_icon96' ) ) ? get_option( '_pos_pwa_icon96' ) : '' ); ?>" />
													<button data-id="pos-pwa-icon96" class="button-primary icon-uploader" /><?php esc_html_e( 'Upload Icon', 'wc_pos' ); ?></button>
												</div>
											</div>
										</div>
										<div class="options_group wc-pos-log-upload-wraper">
											<strong>
												<?php esc_html_e( 'App Icon (144x144)', 'wc_pos' ); ?>
											</strong>

											<div class="wc-pos-log-upload-logo-wraper">
												<div class="wc-pos-log-upload-logo">
													<img src="<?php echo esc_url( $icon144 ); ?>" alt='icon' class="image-url" width="144">
												</div>
												<div class="wc-pos-log-upload-logo-button">
													<input type="hidden" id="pos-pwa-icon144" name="_pos_pwa_icon144" value="<?php echo esc_attr( ! empty( get_option( '_pos_pwa_icon144' ) ) ? get_option( '_pos_pwa_icon144' ) : '' ); ?>" />
													<button data-id="pos-pwa-icon144" class="button-primary icon-uploader" /><?php esc_html_e( 'Upload Icon', 'wc_pos' ); ?></button>
												</div>
											</div>
										</div>
										<div class="options_group wc-pos-log-upload-wraper">
											<strong>
												<?php echo esc_html__( 'App Icon (196x196)', 'wc_pos' ); ?>
											</strong>
											<div class="wc-pos-log-upload-logo-wraper">
												<div class="wc-pos-log-upload-logo">
													<img src="<?php echo esc_url( $icon196 ); ?>" alt='icon' class="image-url" width="196">
												</div>
												<div class="wc-pos-log-upload-logo-button">
													<input type="hidden" id="pos-pwa-icon196" name="_pos_pwa_icon196" value="<?php echo esc_attr( ! empty( get_option( '_pos_pwa_icon196' ) ) ? get_option( '_pos_pwa_icon196' ) : '' ); ?>" />
													<button data-id="pos-pwa-icon196" class="button-primary icon-uploader" /><?php esc_html_e( 'Upload Icon', 'wc_pos' ); ?></button>
												</div>
											</div>
										</div>

											<?php do_action( 'wkwcpos_manage_media_setting_form_fields', $_GET['page'] ); ?>

									</div>
								</div>
								<div class="wc-pos-form-block-footer">
									<button type="submit" class="button-primary"><?php echo esc_html__( 'Save Configurations', 'wc_pos' ); ?></button>
								</div>
							</form>

						</div>

					</div>

				</div>

			</div>

			<?php
		}

		/**
		 * Generate random password.
		 *
		 * @param int $length Password length.
		 *
		 * @return $password Password.
		 */
		public function wkwcpos_generate_random_password( $length ) {
			if ( '' == get_option( 'wkwcpos_api_password' ) ) {
				$password = substr( str_shuffle( str_repeat( $x = 'fdfe012tswtrwerwe345etrte6789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil( $length / strlen( $x ) ) ) ), 1, $length );

				return $password;
			} else {
				$password = get_option( 'wkwcpos_api_password' );

				return $password;
			}
		}
	}
}
