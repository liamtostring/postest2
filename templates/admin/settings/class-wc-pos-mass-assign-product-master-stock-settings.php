<?php

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Mass_Assign_Product_Master_Stock_Settings' ) ) {

	/**
	 *
	 */
	class WC_Pos_Mass_Assign_Product_Master_Stock_Settings {


		function __construct() {

			$this->wk_wc_pos_get_mass_assign_settings_template();

		}

		public function wk_wc_pos_get_mass_assign_settings_template() {

			?>

			<div id="wrapper">

				<div id="dashboard_right_now" class="formcontainer pos pos-settings pos-masterbulk-settings">

					<div class="wc-pos-wrapper">

						<div class="wc-pos-container">

						<div class="wc-pos-form-block">
							<div class="wc-pos-form-block-header">
								<span><?php esc_html_e( 'Assign Bulk Mass', 'wc_pos' ); ?></span>
							</div>
							<div class="wc-pos-form-block-body">

								<div class="options_group">

										<?php
										wkwcpos_text_input(
											array(
												'id'       => '_pos_master_assign_qty',
												'value'    => '',
												'label'    => __( 'Enter Quantity ', 'wc_pos' ),
												'desc_tip' => true,
												'description' => __( 'Enter the quantity that you want to assign to each outlet', 'wc_pos' ),
												'type'     => 'text',
											)
										);
										?>

									</div>
									<div class="options_group">

										<?php echo __( '<blockquote><p><strong>Important: </strong>For example, If you have 100 stock of product P1 in WooCommerce store, You have 2 outlet O1 and O2 and you enter 20 product to assign stock in each outlet, after assigning master stock value will be 140 of product P1, POS stock value of product P1 will be 20, POS stock of product P1 will be 20 and WooCommerce stock is 140 of product P1. </p></blockquote>', 'wc_pos' ); ?>

									</div>
									<div class="options_group wc-pos-full-width-input">
									<div class="wc-product-import-section">

										<div class="wc-ep-info-bar">

											<img class="wc-loader-image" src="<?php echo WK_WC_POS_API . 'assets/images/loader.gif'; ?>">

										</div>

										<div class="wc-product-import-section-body">

										</div>

									</div>
								</div>
								<?php do_action( 'wkwcpos_manage_pos_mass_assign_form_fields', $_GET['page'] ); ?>
									</div>
								</div>
								<div class="wc-pos-form-block-footer">
									<button type="submit" class="button-primary mass-assign-master-stock"><?php echo esc_html__( 'Bulk Assign', 'wc_pos' ); ?></button>
								</div>

								<?php do_action( 'wkwcpos_add_pos_mass_assign_form_fields_after_submit_button', $_GET['page'] ); ?>

						</div>
					</div>

				</div>

			</div>

			<?php

		}
	}
}
