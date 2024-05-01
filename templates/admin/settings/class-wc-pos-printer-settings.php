<?php
/**
 * @author Webkul
 *
 * @version 2.2.0
 * This file handles general settings template
 */

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Pos_Printer_Settings' ) ) {
	/**
	 * Printer Settings Template.
	 */
	class WC_Pos_Printer_Settings {
		public function __construct() {
			$this->wk_wc_pos_get_printer_settings_template();

		}

		public function wk_wc_pos_get_printer_settings_template() {

			?>
					<div id="wrapper">

						<div id="dashboard_right_now" class="formcontainer pos pos-settings">

							<div class="wc-pos-wrapper">

								<div class="wc-pos-container">

									<form method="post" action="options.php">

										<?php settings_fields( 'pos-printer-settings-group' ); ?>
										<?php settings_errors(); ?>

										<?php do_settings_sections( 'pos-printer-settings-group' ); ?>

										<div class="wc-pos-form-block">
											<div class="wc-pos-form-block-header">
												<span><?php esc_html_e( 'Printers List', 'wc_pos' ); ?></span>
											</div>
											<div class="wc-pos-form-block-body">
												<div class="options_group">

													<?php
													$printer_array = array(
														'a4'   => esc_html__( 'A4 Printer', 'wc_pos' ),
														'a3'   => esc_html__( 'A3 printer', 'wc_pos' ),
														'T88V' => esc_html__( 'Epson TM-T88V Thermal Printer', 'wc_pos' ),
														'a5'   => esc_html__( 'A5 Printer', 'wc_pos' ),
														'a6'   => esc_html__( 'A6 Printer', 'wc_pos' ),
													);
													$printer_array = apply_filters( 'wcpos_add_printer', $printer_array );
													wkwcpos_select(
														array(
															'id' => '_pos_printer_type',
															'value' => ! empty( get_option( '_pos_printer_type' ) ) ? get_option( '_pos_printer_type' ) : '',
															'label' => esc_html__( 'Select Default Printer Type', 'wc_pos' ),
															'options' => $printer_array,
															'desc_tip' => true,
															'description' => esc_html__( 'This is a Printer type which you want for your system.', 'wc_pos' ),
														)
													);
													?>

												</div>
											</div>
										</div>
										<div class="wc-pos-form-block-footer">
											<button type="submit" class="button-primary"><?php echo esc_html__( 'Save', 'wc_pos' ); ?></button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
							<?php
		}
	}
}


