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

if ( ! class_exists( 'WC_Pos_Endpoint_Settings' ) ) {
	/**
	 * Printer Settings Template.
	 */
	class WC_Pos_Endpoint_Settings {
		public function __construct() {
			$this->wk_wc_pos_get_ean_settings_template();

		}

		public function wk_wc_pos_get_ean_settings_template() {

			?>
					<div id="wrapper">

						<div id="dashboard_right_now" class="formcontainer pos pos-settings">

							<div class="wc-pos-wrapper">

								<div class="wc-pos-container">
									<?php
										settings_errors();
									?>

									<form method="post" action="options.php">

										<?php
											settings_fields( 'pos-endpoint-settings-group' );
											flush_rewrite_rules();
										?>

										<?php do_settings_sections( 'pos-endpoint-settings-group' ); ?>
										<div class="wc-pos-form-block">
											<div class="wc-pos-form-block-header">
												<span><?php esc_html_e( 'POS Endpoint', 'wc_pos' ); ?></span>
											</div>
											<div class="wc-pos-form-block-body">
												<div class="options_group wc-pos-full-width-input">
															<?php
																wkwcpos_text_input(
																	array(
																		'id'          => '_pos_endpoint_name',
																		'value'       => ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos',
																		'label'       => esc_html__( 'Endpoint', 'wc_pos' ),
																		'desc_tip'    => true,
																		'description' => esc_html__( 'Set POS Endpoint as per your requirement.', 'wc_pos' ),
																		'type'        => 'text',
																	)
																);
															?>
												</div>
												<?php do_action( 'wkwcpos_add_extra_endpoint_settings' ); ?>
												<div class="options_group wc-pos-full-width-input">
													<?php
															$url           = esc_url( WKWCPOS_SITE_URL . '/wp-admin/options-permalink.php' );
															$permalink_url = "<a href='" . $url . "'>" . esc_html__( 'Permalink', 'wc_pos' ) . '</a>';
													?>
															<p>
																<label>
																	<?php echo esc_html__( 'Note', 'wc_pos' ); ?>
															</label>
																<span class="note"><?php echo __( 'Kindly save the ' . $permalink_url . ' once after changing the POS Endpoint. ', 'wc_pos' ); ?></span>
															</p>


												</div>
											</div>
										</div>
										<div class="wc-pos-form-block-footer">
											<button type="submit" class="button-primary "><?php echo esc_html__( 'Save', 'wc_pos' ); ?></button>
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


