<?php

namespace WKWC_POS\Templates\Admin\Settings;

/*
*
* This file handles addition of new payment method.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Manage_Payment' ) ) {
	class WC_Pos_Manage_Payment {

		public function __construct() {
			$this->verify_nonce();

			$this->wkwcpos_get_add_payment_template();
		}

		public function wkwcpos_get_add_payment_template() {
			$status = 1;

			$status_list = array( __( 'Disable', 'wc_pos' ), __( 'Enable', 'wc_pos' ) );

			if ( ! empty( $_GET['payment_id'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
				global $wpdb;

				$payment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_pos_payments where id = %d", $_GET['payment_id'] ) );

				if ( ! empty( $payment ) ) {
					$pos_payment_name        = $payment->payment_name;
					$pos_payment_description = $payment->payment_description;
					$status                  = $payment->payment_status;
					$pos_payment_slug        = $payment->payment_slug;
				}
			}
			if ( ! empty( $_POST ) && isset( $_POST['save-payment'] ) ) {
					$pos_payment_name        = ( isset( $_POST['_pos_payment_name'] ) ) ? sanitize_text_field( $_POST['_pos_payment_name'] ) : '';
					$status                  = ( isset( $_POST['_pos_status'] ) ) ? sanitize_text_field( $_POST['_pos_status'] ) : '';
					$pos_payment_slug        = ( isset( $_POST['_pos_payment_slug'] ) ) ? sanitize_text_field( $_POST['_pos_payment_slug'] ) : '';
					$pos_payment_description = ( isset( $_POST['_pos_payment_description'] ) ) ? sanitize_text_field( $_POST['_pos_payment_description'] ) : '';
			}
			?>

			<div class="wrap">


				<div id="wrapper">

					<div id="dashboard_right_now" class="formcontainer pos pos-settings">



							<div class="wc-pos-wrapper">

								<div class="wc-pos-container">
									<form action="" method="post">


									<?php wp_nonce_field( 'wkwcpos_action', 'wkwcpos_nonce_field' ); ?>
									<div class="wc-pos-form-block">
									<div class="wc-pos-form-block-header">
										<span><?php esc_html_e( 'Add payment method', 'wc_pos' ); ?></span>


									</div>
									<div class="wc-pos-form-block-body">

									<div class="options_group">

										<?php
										wkwcpos_text_input(
											array(
												'id'       => '_pos_payment_name',
												'value'    => ! empty( $pos_payment_name ) ? $pos_payment_name : '',
												'label'    => __( 'Payment Name', 'wc_pos' ),
												'desc_tip' => true,
												'description' => __( 'POS Payment Gateway Name.', 'wc_pos' ),
												'type'     => 'text',
											)
										);
										?>

									</div>

									<div class="options_group">
										<?php
										wkwcpos_text_input(
											array(
												'id'       => '_pos_payment_slug',
												'value'    => ! empty( $pos_payment_slug ) ? $pos_payment_slug : '',
												'label'    => __( 'Payment Slug', 'wc_pos' ),
												'desc_tip' => true,
												'description' => __( 'POS payment Gateway Slug.', 'wc_pos' ),
												'type'     => 'text',
											)
										);
										?>

									</div>


									<div class="options_group">
										<?php
										wkwcpos_textarea_input(
											array(
												'id'       => '_pos_payment_description',
												'value'    => ! empty( $pos_payment_description ) ? $pos_payment_description : '',
												'label'    => __( 'Payment Description', 'wc_pos' ),
												'desc_tip' => true,
												'description' => __( 'Description about POS payment Gateway.', 'wc_pos' ),
											)
										);
										?>
									</div>


									<div class="options_group">

										<?php

										wkwcpos_select(
											array(
												'id'       => '_pos_status',
												'label'    => __( 'Status', 'wc_pos' ),
												'value'    => $status,
												'options'  => $status_list,
												'desc_tip' => true,
												'description' => __( 'Select Status for the pos payment.', 'wc_pos' ),
											)
										);

										?>

									</div>
									<?php

									do_action( 'wkwcpos_manage_payment_form_fields' );

									?>
									</div>
								</div>
								<div class="wc-pos-form-block-footer">
									<?php
									if ( isset( $_GET['payment_id'] ) && ! empty( $_GET['payment_id'] ) ) {

										?>
										<input type="hidden" name="_pos_payment_id" value="<?php echo $_GET['payment_id']; ?>">
										<button type="submit" name="update-payment" class="button-primary"><?php echo __( 'Update Payment', 'wc_pos' ); ?></button>
										<?php

									} else {

										?>
										<button type="submit" name="save-payment" class="button-primary"><?php echo __( 'Save Payment', 'wc_pos' ); ?></button>
										<?php
									}
									?>
								</div>

								</form>

							</div>
						</div>

					</div>
				</div>
			</div>
			<?php
		}

		public function verify_nonce() {
			if ( isset( $_POST['save-payment'] ) || isset( $_POST['update-payment'] ) ) {
				if ( ! isset( $_POST['wkwcpos_nonce_field'] ) || ! wp_verify_nonce( $_POST['wkwcpos_nonce_field'], 'wkwcpos_action' ) ) {
					echo __( 'Sorry, your nonce did not verify.', 'wc_pos' );
					exit;
				} else {
					do_action( 'wkwcpos_save_pos_payment', $_POST );
				}
			}
		}
	}
}
