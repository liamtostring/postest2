<?php
/**
 * @author Webkul
 * @version 4.1.0
 * This file handles pos theme setting template
 */

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Appearance' ) ) {

	/**
	 *
	 */
	class WC_Pos_Appearance {
		protected $selected_theme;
		function __construct() {
			$this->selected_theme = get_option( '_pos_theme_mode', 'default' );
			$this->wk_wc_pos_appearance_template();
		}


		public function wk_wc_pos_appearance_template() {
			?>
			<div id="wrapper">
				<div id="dashboard_right_now" class="formcontainer pos pos-settings">
					<div class="wc-pos-wrapper">
						<div class="wc-pos-container">
							<?php settings_errors(); ?>

							<form method="post" action="options.php">

								<?php settings_fields( 'pos-appearance-settings-group' ); ?>

								<?php do_settings_sections( 'pos-appearance-settings-group' ); ?>
								<div class="wc-pos-form-block">
									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'POS Themes', 'wc_pos' ); ?></span>
									</div>
									<div class="wc-pos-form-block-body">
										<div class="options_group wc-pos-full-width-input">
												<div class="wc-pos-appearance-theme-mode">
													<div class="wc-pos-theme-item <?php echo ( 'default' === $this->selected_theme ) ? 'active-theme' : ''; ?>"  data-mode="default">
														<label for="_pos_theme_mode_default">
															<img src="<?php echo WK_WC_POS_API . '/assets/images/default-mode.png'; ?>">
															<input type="radio"  <?php checked( $this->selected_theme, 'default', true ); ?> value="default" id="_pos_theme_mode_default" name="_pos_theme_mode">
															<span><?php esc_html_e( 'OS Deafult Mode', 'wc_pos' ); ?></span>
														</label>
													</div>
													<div class="wc-pos-theme-item <?php echo ( 'light' === $this->selected_theme ) ? 'active-theme' : ''; ?>" data-mode="light">
														<label for="_pos_theme_mode_light">
															<img src="<?php echo WK_WC_POS_API . '/assets/images/light-mode.png'; ?>">
															<input type="radio" <?php checked( $this->selected_theme, 'light', true ); ?> value="light" id="_pos_theme_mode_light" name="_pos_theme_mode">
															<span><?php esc_html_e( 'Light Mode', 'wc_pos' ); ?></span>
														</label>
													</div>
													<div class="wc-pos-theme-item <?php echo ( 'dark' === $this->selected_theme ) ? 'active-theme' : ''; ?>" data-mode="dark">
														<label for="_pos_theme_mode_dark">
															<img src="<?php echo WK_WC_POS_API . '/assets/images/dark-mode.png'; ?>">
															<input type="radio"  <?php checked( $this->selected_theme, 'dark', true ); ?> value="dark" id="_pos_theme_mode_dark" name="_pos_theme_mode">
															<span><?php esc_html_e( 'Dark Mode', 'wc_pos' ); ?></span>
														</label>
													</div>
												</div>
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
