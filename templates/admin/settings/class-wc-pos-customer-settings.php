<?php
/**
 * @author Webkul
 * @version 2.2.0
 * This file handles customer setting template
 */

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Customer_Settings' ) ) {

	/**
	 *
	 */
	class WC_Pos_Customer_Settings {

		function __construct() {

			$this->wk_wc_pos_verify_customer_settings_nonce();

			$this->wk_wc_pos_get_customer_settings_template();

		}

		public function wk_wc_pos_get_customer_settings_template() {

			$countries = apply_filters( 'get_countries_list', $_GET['page'] );

			$args = array(
				'meta_key'   => 'deault_customer_pos',
				'meta_value' => '1',
			);

			$pos_customer = get_users( $args );

			if ( $pos_customer ) {

				foreach ( $pos_customer as $customer ) {

					$id           = $customer->ID;
					$fname        = get_user_meta( $id, 'first_name', true );
					$lname        = get_user_meta( $id, 'last_name', true );
					$company      = get_user_meta( $id, 'billing_company', true );
					$postcode     = get_user_meta( $id, 'billing_postcode', true );
					$address1     = get_user_meta( $id, 'billing_address_1', true );
					$address2     = get_user_meta( $id, 'billing_address_2', true );
					$billing_city = get_user_meta( $id, 'billing_city', true );
					$country      = get_user_meta( $id, 'billing_country', true );
					$phone        = get_user_meta( $id, 'billing_phone', true );
					$email        = $customer->user_email;
					$password     = $customer->user_pass;
				}
			}

			?> <div id="wrapper">
	<div id="dashboard_right_now" class="formcontainer pos pos-settings">
		<div class="wc-pos-wrapper">
			<div class="wc-pos-container">
				<form method="post" action="">
					<?php wp_nonce_field( 'pos_dcustomer_action', 'pos_dcustomer_nonce_field' ); ?>
					<div class="wc-pos-form-block">
						<div class="wc-pos-form-block-header">
							<span><?php esc_html_e( 'Customer Details', 'wc_pos' ); ?></span>
						</div>
						<div class="wc-pos-form-block-body">
							<div class="options_group">
							<?php
								wkwcpos_text_input(
									array(
										'id'    => '_pos_defcustomer_fname',
										'value' => ! empty( $fname ) ? $fname : '',
										'label' => __( 'First Name', 'wc_pos' ),
									)
								);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_lname',
													'value' => ! empty( $lname ) ? $lname : '',
													'label' => __( 'Last Name', 'wc_pos' ),
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_email',
													'value' => ! empty( $email ) ? $email : '',
													'label' => __( 'Email', 'wc_pos' ),
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_password',
													'value' => ! empty( $password ) ? $password : wp_generate_password(),
													'label' => __( 'Password', 'wc_pos' ),
													'desc_tip' => true,
													'description' => __( 'This will be the default password for the customers that will be added from the POS panel.', 'wc_pos' ),
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_telephone',
													'value' => ! empty( $phone ) ? $phone : '',
													'label' => __( 'Telephone', 'wc_pos' ),
												)
											);
							?>
							</div> <?php do_action( 'wkwcpos_manage_default_customer_details_form_fields', $_GET['page'] ); ?>
						</div>
					</div>
					<div class="wc-pos-form-block">
						<div class="wc-pos-form-block-header">
							<span><?php esc_html_e( 'Customer Address', 'wc_pos' ); ?></span>
						</div>
						<div class="wc-pos-form-block-body">
							<div class="options_group">
							<?php
											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_company',
													'value' => ! empty( $company ) ? $company : '',
													'label' => __( 'Company', 'wc_pos' ),
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_textarea_input(
												array(
													'id'   => '_pos_defcustomer_address1',
													'value' => ! empty( $address1 ) ? $address1 : '',
													'label' => __( 'Address1', 'wc_pos' ),
													'type' => 'text',
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_textarea_input(
												array(
													'id'   => '_pos_defcustomer_address2',
													'value' => ! empty( $address2 ) ? $address2 : '',
													'label' => __( 'Address2', 'wc_pos' ),
													'type' => 'text',
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_city',
													'value' => ! empty( $billing_city ) ? $billing_city : '',
													'label' => __( 'City', 'wc_pos' ),
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php

											wkwcpos_text_input(
												array(
													'id' => '_pos_defcustomer_postcode',
													'value' => ! empty( $postcode ) ? $postcode : '',
													'label' => __( 'Postcode', 'wc_pos' ),
												)
											);
							?>
							</div>
							<div class="options_group">
							<?php
											wkwcpos_select(
												array(
													'id' => '_pos_store_country',
													'value' => ! empty( $country ) ? $country : '',
													'label' => __( 'Country', 'wc_pos' ),
													'options' => $countries,
													'desc_tip' => true,
													'description' => __( 'Display country list.', 'wc_pos' ),
												)
											);
							?>
							</div> <?php do_action( 'wkwcpos_manage_default_customer_address_form_fields', $_GET['page'] ); ?>
						</div>
					</div>
					<div class="wc-pos-form-block-footer">
					<?php

					if ( ! empty( $pos_customer ) ) {
						?>
						<input type="hidden" name="default_customer_id" value="<?php echo $id; ?>">
						<input type="submit" class="button button-primary" name="update_default_customer"
							value="<?php esc_html_e( 'Update Customer', 'wc_pos' ); ?>">
						<?php

					} else {
						?>
						<input type="submit" class="button button-primary" name="save_default_customer"
							value="<?php esc_html_e( 'Save Customer', 'wc_pos' ); ?>">
						<?php
					}
					?>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
			<?php

		}

		public function wk_wc_pos_verify_customer_settings_nonce() {

			if ( isset( $_POST['save_default_customer'] ) || isset( $_POST['update_default_customer'] ) ) {

				if ( ! isset( $_POST['pos_dcustomer_nonce_field'] ) || ! wp_verify_nonce( $_POST['pos_dcustomer_nonce_field'], 'pos_dcustomer_action' ) ) {

					print __( 'Sorry, your nonce did not verify.', 'wc_pos' );
					exit;

				} else {

					do_action( 'pos_save_default_customer', $_POST );

				}
			}

		}

	}

}
