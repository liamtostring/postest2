<?php
/**
 * Pos default functions.
 *
 * @package WooCommerce Point of Sale
 */

/**
 * Output a text input box.
 *
 * @param array $field Fields.
 */
function wkwcpos_text_input( $field ) {
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : '';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
	$field['min']           = isset( $field['min'] ) ? $field['min'] : '';
	$field['max']           = isset( $field['max'] ) ? $field['max'] : '';
	$field['required']      = isset( $field['required'] ) ? ( $field['required'] ? '*' : '' ) : '';
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	switch ( $data_type ) {
		case 'price':
			$field['class'] .= ' wc_input_price';
			$field['value']  = wc_format_localized_price( $field['value'] );
			break;
		case 'decimal':
			$field['class'] .= ' wc_input_decimal';
			$field['value']  = wc_format_localized_decimal( $field['value'] );
			break;
		case 'stock':
			$field['class'] .= ' wc_input_stock';
			$field['value']  = wc_stock_amount( $field['value'] );
			break;
		case 'url':
			$field['class'] .= ' wc_input_url';
			$field['value']  = esc_url( $field['value'] );
			break;

		default:
			break;
	}

	// Custom attribute handling.
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $value );
		}
	}

	echo '<div class="wc-pos-form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<div class="wc-pos-input-label">
			<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . ' <b> ' . esc_attr( $field['required'] ) . ' </b></label>
		</div>';

	echo '<div class="wc-pos-form-input-wrap">
		<input type="' . esc_attr( $field['type'] ) . '" min="' . esc_attr( $field['min'] ) . '"  max="' . esc_attr( $field['max'] ) . '" class="wc-pos-form-input ' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}
	echo '</div></div>';
}

/**
 * Output a hidden input box.
 *
 * @param array $field
 */

/**
 * Output a textarea input box.
 *
 * @param array $field Field.
 */
function wkwcpos_textarea_input( $field ) {
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : '';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['rows']          = isset( $field['rows'] ) ? $field['rows'] : 2;
	$field['cols']          = isset( $field['cols'] ) ? $field['cols'] : 20;
	$field['required']      = isset( $field['required'] ) ? ( $field['required'] ? '*' : '' ) : '';

	// Custom attribute handling.
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<div class="wc-pos-form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
	<div class="wc-pos-input-label">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . ' <b> ' . esc_attr( $field['required'] ) . ' </b></label>
	</div>';

	echo '<div class="wc-pos-form-input-wrap">
		<textarea class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . 'padding: 0 8px;"  name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" rows="' . esc_attr( $field['rows'] ) . '" cols="' . esc_attr( $field['cols'] ) . '" ' . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $field['value'] ) . '</textarea> ';

	if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</div></div>';
}

/**
 * Output a checkbox input box.
 *
 * @param array $field Field.
 */
function wkwcpos_checkbox( $field ) {
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : '';
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

	// Custom attribute handling.
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	echo '<input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/> ';

	if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</p>';
}

/**
 * Output a select input box.
 *
 * @param array $field Field.
 */
function wkwcpos_select( $field ) {
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : '';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
	$field['required']      = isset( $field['required'] ) ? ( $field['required'] ? '*' : '' ) : '';

	// Custom attribute handling.
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {
		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<div class="wc-pos-form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
	<div class="wc-pos-input-label">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . ' <b> ' . esc_attr( $field['required'] ) . ' </b></label>
		</div>';

	echo '<div class="wc-pos-form-input-wrap">
	<select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="padding: 0 8px;' . esc_attr( $field['style'] ) . '" ' . implode( ' ', $custom_attributes ) . '>';
	if ( is_array( $field['value'] ) ) {
		foreach ( $field['options'] as $key => $value ) {
			if ( in_array( $key, $field['value'] ) ) {
				echo '<option value="' . esc_attr( $key ) . '" selected=selected>' . esc_html( $value ) . '</option>';
			} else {
				echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
			}
		}
	} else {
		foreach ( $field['options'] as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( esc_attr( $field['value'] ), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
		}
	}

	echo '</select> ';
	if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}
	echo '</div></div>';
}

/**
 * Output a radio input box.
 */
function wkwcpos_get_all_pos_variable() {
	$data = array(
		'something_went_wrong'                          => esc_html__( 'Something Went Wrong', 'wc_pos' ),
		'email_send'                                    => esc_html__( 'Email Send Successfully', 'wc_pos' ),
		'refund_text'                                   => esc_html__( 'Refunded Amount', 'wc_pos' ),
		'product_name_text'                             => esc_html__( 'Product Name', 'wc_pos' ),
		'empty_default_customer'                        => esc_html__( 'Please select customer for this order', 'wc_pos' ),
		'note_text'                                     => esc_html__( 'Note ', 'wc_pos' ),
		'currency'                                      => esc_html__( 'USD', 'wc_pos' ),
		'warning_text'                                  => esc_html__( 'Warning!', 'wc_pos' ),
		'success_text'                                  => esc_html__( 'Success', 'wc_pos' ),
		'cart_added_success_msg'                        => esc_html__( 'Cart has been added succesfully.', 'wc_pos' ),
		'cart_deleted_success_msg'                      => esc_html__( 'Cart has been deleted succesfully.', 'wc_pos' ),
		'already_products_in_cart_for_hold'             => esc_html__( 'There are products in your current cart, kindly process them first.', 'wc_pos' ),
		'validated_text'                                => esc_html__( 'This field is validated', 'wc_pos' ),
		'order_note_enter_text'                         => esc_html__( 'Enter Order Note', 'wc_pos' ),
		'order_note_text'                               => esc_html__( 'Order Note', 'wc_pos' ),
		'order_heading_text'                            => esc_html__( 'Order ID', 'wc_pos' ),
		'order_date_heading_text'                       => esc_html__( 'Order Date', 'wc_pos' ),
		'customer_heading_text'                         => esc_html__( 'Customer', 'wc_pos' ),
		'text_empty_cart'                               => esc_html__( 'The cart is empty', 'wc_pos' ),
		'text_empty_hold'                               => esc_html__( 'You can not put empty cart on hold!', 'wc_pos' ),
		'discount_text'                                 => esc_html__( 'Discount', 'wc_pos' ),
		'tax_text'                                      => esc_html__( 'Tax', 'wc_pos' ),
		'balance_text'                                  => esc_html__( 'Change', 'wc_pos' ),
		'total_text'                                    => esc_html__( 'Total', 'wc_pos' ),
		'payment_text'                                  => esc_html__( 'Payable Amount', 'wc_pos' ),
		'notice_text'                                   => esc_html__( 'Notice', 'wc_pos' ),
		'coupon_text'                                   => esc_html__( 'Coupon', 'wc_pos' ),
		'apply_text'                                    => esc_html__( 'Apply', 'wc_pos' ),
		'delete_text'                                   => esc_html__( 'Delete', 'wc_pos' ),
		'edit_text'                                     => esc_html__( 'Edit', 'wc_pos' ),
		'add_customer_text'                             => esc_html__( 'Add Customer', 'wc_pos' ),
		'button_cart'                                   => esc_html__( 'Add to cart', 'wc_pos' ),
		'proceed_text'                                  => esc_html__( 'Proceed', 'wc_pos' ),
		'order_notify_text'                             => esc_html__( 'This process will generate an order depending upon the status Online or Offline. Do you still wanna do it.?', 'wc_pos' ),
		'no_note_text'                                  => esc_html__( 'No note', 'wc_pos' ),
		'err_text'                                      => esc_html__( 'Error', 'wc_pos' ),
		'cancel_btn_text'                               => esc_html__( 'Cancel', 'wc_pos' ),
		'coupon_validate_text'                          => esc_html__( 'Please do not keep coupon code field empty.', 'wc_pos' ),
		'product_validate_text'                         => esc_html__( 'Please do not keep any field empty.', 'wc_pos' ),
		'text_item_detail'                              => esc_html__( 'Hold Order', 'wc_pos' ),
		'hold_cart_text'                                => esc_html__( 'Hold Cart', 'wc_pos' ),
		'cart_detail'                                   => esc_html__( 'Cart', 'wc_pos' ),
		'select_category_text'                          => esc_html__( 'Select a category from the list below', 'wc_pos' ),
		'list_category_text'                            => esc_html__( 'Listing Category', 'wc_pos' ),
		'currency_not_availabel_txt'                    => esc_html__( 'Currency Not available', 'wc_pos' ),
		'pos_currency_text'                             => esc_html__( 'POS Currency', 'wc_pos' ),
		'form_field_validate'                           => esc_html__( 'Above form fields are not correct', 'wc_pos' ),
		'updating_account_text'                         => esc_html__( 'Updating account', 'wc_pos' ),
		'account_update_success_text'                   => esc_html__( 'Account has been updated successfully', 'wc_pos' ),
		'customer_update_success_text'                  => esc_html__( 'Customer has been updated successfully', 'wc_pos' ),
		'customer_added_success_text'                   => esc_html__( 'Customer has been added successfully', 'wc_pos' ),
		'setting_update_success_text'                   => esc_html__( 'Setting has been updated successfully', 'wc_pos' ),
		'confirmation_text'                             => esc_html__( 'Confirmation', 'wc_pos' ),
		'create_new_customer_text'                      => esc_html__( 'Creating New Customer', 'wc_pos' ),
		'update_existing_customer_text'                 => esc_html__( 'Updating Existing Customer', 'wc_pos' ),
		'drawer_text'                                   => esc_html__( 'Please enter opening amount for cash drawer.', 'wc_pos' ),
		'drawer_closed_text'                            => esc_html__( 'Drawer Closed Successfully.', 'wc_pos' ),
		'closing_drawer_text'                           => esc_html__( 'Closing drawer...', 'wc_pos' ),
		'drawer_validate_text'                          => esc_html__( 'Please enter valid opening cash amount.', 'wc_pos' ),
		'delete_customer_title_text'                    => esc_html__( 'Delete Customer', 'wc_pos' ),
		'deleting_customer_title_text'                  => esc_html__( 'Deleting Customer', 'wc_pos' ),
		'save_customer_text'                            => esc_html__( 'Save Customer', 'wc_pos' ),
		'search_order_text'                             => esc_html__( 'Search order by id, customer', 'wc_pos' ),
		'offline_search_order_text'                     => esc_html__( 'Search offline order by id', 'wc_pos' ),
		'printInvoice_text'                             => esc_html__( 'Print Invoice', 'wc_pos' ),
		'sale_summary_text'                             => esc_html__( 'Sale Summary', 'wc_pos' ),
		'sub_total_text'                                => esc_html__( 'Sub Total', 'wc_pos' ),
		'unit_text'                                     => esc_html__( 'Unit(s)', 'wc_pos' ),
		'logout_heading'                                => esc_html__( 'Logout', 'wc_pos' ),
		'confirm_logout_text'                           => esc_html__( 'Are you sure you want to logout?', 'wc_pos' ),
		'processing_order_text'                         => esc_html__( 'Processing Order', 'wc_pos' ),
		'error_coupon_offline'                          => esc_html__( 'Warning: Coupon is either invalid, expired or reached its usage limit!', 'wc_pos' ),
		'searching_product_text'                        => esc_html__( 'Searching Products', 'wc_pos' ),
		'error_load_products'                           => esc_html__( 'Could not load products', 'wc_pos' ),
		'text_loading_populars'                         => esc_html__( 'Loading Populars', 'wc_pos' ),
		'error_load_populars'                           => esc_html__( 'Could not load popular products', 'wc_pos' ),
		'text_loading_orders'                           => esc_html__( 'Loading Orders', 'wc_pos' ),
		'error_load_orders'                             => esc_html__( 'Could not load orders', 'wc_pos' ),
		'text_loading_customers'                        => esc_html__( 'Loading Customers', 'wc_pos' ),
		'error_load_customers'                          => esc_html__( 'Could not load customers', 'wc_pos' ),
		'error_offline_customer'                        => esc_html__( 'Cannot add, edit, delete customer in offline mode', 'wc_pos' ),
		'error_offline_account'                         => esc_html__( 'Cannot update account in offline mode', 'wc_pos' ),
		'text_loading_categories'                       => esc_html__( 'Loading Categories', 'wc_pos' ),
		'error_load_categories'                         => esc_html__( 'Could not load categories', 'wc_pos' ),
		'text_loading'                                  => esc_html__( 'Loading...', 'wc_pos' ),
		'button_upload'                                 => esc_html__( 'Upload File', 'wc_pos' ),
		'text_product_options'                          => esc_html__( "Fill the product's options", 'wc_pos' ),
		'error_keyword'                                 => esc_html__( 'Old Password is wrong.', 'wc_pos' ),
		'error_products'                                => esc_html__( 'No products found', 'wc_pos' ),
		'text_online_mode'                              => esc_html__( 'You have successfully entered online mode', 'wc_pos' ),
		'error_enter_online'                            => esc_html__( 'You can not enter online mode as you are disconnected', 'wc_pos' ),
		'text_offline_mode'                             => esc_html__( 'You are About to enter offline mode', 'wc_pos' ),
		'error_no_category_product'                     => esc_html__( 'No products found in this category', 'wc_pos' ),
		'error_no_customer'                             => esc_html__( 'No customer found', 'wc_pos' ),
		'error_no_category_order'                       => esc_html__( 'No orders found ', 'wc_pos' ),
		'text_select_customer'                          => esc_html__( 'You have successfully selected a customer for checkout', 'wc_pos' ),
		'error_customer_add'                            => esc_html__( 'You can not add a new customer as you are offline right now', 'wc_pos' ),
		'text_remove_customer'                          => esc_html__( 'You have successfully selected guest for checkout', 'wc_pos' ),
		'error_checkout'                                => esc_html__( 'Add atleast one product to checkout', 'wc_pos' ),
		'text_balance_due'                              => esc_html__( 'Balance due:', 'wc_pos' ),
		'text_order_success'                            => esc_html__( 'The order has been successfully placed', 'wc_pos' ),
		'text_sync_order'                               => esc_html__( 'Sync. all offline orders', 'wc_pos' ),
		'sync_process_text'                             => esc_html__( 'Syncing Offline Order(s)', 'wc_pos' ),
		'sync_success_text'                             => esc_html__( 'All orders are synced', 'wc_pos' ),
		'text_sync_single_order'                        => esc_html__( 'Sync Offline Orders', 'wc_pos' ),
		'text_no_orders'                                => esc_html__( 'No Orders Available', 'wc_pos' ),
		'order_note_empty'                              => esc_html__( 'order note empty', 'wc_pos' ),
		'error_sync_orders'                             => esc_html__( 'You can not synchronize orders as you are offline right now', 'wc_pos' ),
		'text_another_cart'                             => esc_html__( 'You have selected another cart for checkout', 'wc_pos' ),
		'text_cart_deleted'                             => esc_html__( 'The current cart has been successfully deleted', 'wc_pos' ),
		'text_current_deleted'                          => esc_html__( 'The cart has been successfully deleted', 'wc_pos' ),
		'text_cart_empty'                               => esc_html__( 'The cart is empty', 'wc_pos' ),
		'text_cart_add'                                 => esc_html__( 'The current cart is put on hold and a new cart successfully added', 'wc_pos' ),
		'text_option_required'                          => esc_html__( 'Fill all the required options to continue adding this product to the cart', 'wc_pos' ),
		'text_product_not_added'                        => esc_html__( '%product-name% could not be added to the cart', 'wc_pos' ),
		'text_no_quantity'                              => esc_html__( 'The quantity for %product-name% is not available', 'wc_pos' ),
		'cash_payment_title'                            => esc_html__( 'Cash Payment', 'wc_pos' ),
		'text_all_products'                             => esc_html__( 'All Products', 'wc_pos' ),
		'invalid_percentage'                            => esc_html__( 'Invalid Percentage value, must be less than or equal to 100', 'wc_pos' ),
		'invalid_discount'                              => esc_html__( 'Discount cannot be applied due to invalid total amount', 'wc_pos' ),
		'coupon_code_enter_text'                        => esc_html__( 'Enter Coupon Code', 'wc_pos' ),
		'apply_coupon_text'                             => esc_html__( 'Apply Coupon', 'wc_pos' ),
		'apply_coupon_error'                            => esc_html__( 'First add product in cart', 'wc_pos' ),
		'applying_coupon_text'                          => esc_html__( 'Applying Coupon', 'wc_pos' ),
		'coupon_applied_text'                           => esc_html__( 'Coupon applied successfully', 'wc_pos' ),
		'barcode_enter_text'                            => esc_html__( 'Enter Barcode', 'wc_pos' ),
		'offline_text'                                  => esc_html__( 'Offline', 'wc_pos' ),
		'online_text'                                   => esc_html__( 'Online', 'wc_pos' ),
		'coupon_offline_notification'                   => esc_html__( 'Coupon cannot be applied on offline mode', 'wc_pos' ),
		'coupon_remove_notification'                    => esc_html__( 'Coupon removed successfully', 'wc_pos' ),
		'coupon_remove_error_notification'              => esc_html__( 'Error occurred while removing Coupon. Please try again', 'wc_pos' ),
		'text_search'                                   => esc_html__( 'Search', 'wc_pos' ),
		'text_option_notifier'                          => esc_html__( 'Select Variation from the list below', 'wc_pos' ),
		'text_low_stock'                                => esc_html__( 'This product is having low stock.', 'wc_pos' ),
		'text_special_price'                            => esc_html__( 'This product is on discount.', 'wc_pos' ),
		'text_cust_delete'                              => esc_html__( 'Are you sure you want to delete customer?', 'wc_pos' ),
		'change_customer_text'                          => esc_html__( 'Want to Change the Customer?', 'wc_pos' ),
		'change_customer_title_text'                    => esc_html__( 'Change Customer', 'wc_pos' ),
		'okay_text'                                     => esc_html__( 'Okay', 'wc_pos' ),
		'invalid_paid_amt'                              => esc_html__( 'Entered amount cannot be paid', 'wc_pos' ),
		'error_no_orders'                               => esc_html__( 'No orders available', 'wc_pos' ),
		'text_loading_shipping_cost'                    => esc_html__( 'Calculating Shipping Cost', 'wc_pos' ),
		'shipping_text'                                 => esc_html__( 'Shipping', 'wc_pos' ),
		'tax_total_text'                                => esc_html__( 'Tax Total', 'wc_pos' ),
		'add_product_add'                               => esc_html__( 'Add New Product', 'wc_pos' ),
		'add_product_name'                              => esc_html__( 'Product Name', 'wc_pos' ),
		'add_product_price'                             => esc_html__( 'Product Price', 'wc_pos' ),
		'add_product_quantity'                          => esc_html__( 'Product Quantity', 'wc_pos' ),
		'validate_product_quantity'                     => esc_html__( 'Please enter a valid Quantity', 'wc_pos' ),
		'validate_product_price'                        => esc_html__( 'Please enter a valid Price', 'wc_pos' ),
		'add_text'                                      => esc_html__( 'Add', 'wc_pos' ),
		'validate_product_name_len'                     => esc_html__( 'Product name length must be more 3', 'wc_pos' ),
		'reset'                                         => esc_html__( 'Reset', 'wc_pos' ),
		'reset_cart'                                    => esc_html__( 'Reset Cart', 'wc_pos' ),
		'orders'                                        => esc_html__( 'Orders', 'wc_pos' ),
		'home'                                          => esc_html__( 'Home', 'wc_pos' ),
		'cashier'                                       => esc_html__( 'Cashier', 'wc_pos' ),
		'cash_text'                                     => esc_html__( 'Cash', 'wc_pos' ),
		'card_text'                                     => esc_html__( 'Other Payments', 'wc_pos' ),
		'customers'                                     => esc_html__( 'Customers', 'wc_pos' ),
		'settings'                                      => esc_html__( 'Settings', 'wc_pos' ),
		'opening_drawer_amount'                         => esc_html__( 'Opening Drawer Amount', 'wc_pos' ),
		'today_cash_sale'                               => esc_html__( 'Today Cash Sale', 'wc_pos' ),
		'today_card_sale'                               => esc_html__( 'Today Other Payment Sale', 'wc_pos' ),
		'time'                                          => esc_html__( 'Time', 'wc_pos' ),
		'order_total'                                   => esc_html__( 'Order Total', 'wc_pos' ),
		'payment_mode'                                  => esc_html__( 'Payment Mode', 'wc_pos' ),
		'drawer_amount_details'                         => esc_html__( 'Drawer Account Summary', 'wc_pos' ),
		'opening_amount'                                => esc_html__( 'Opening Amount', 'wc_pos' ),
		'today_sale'                                    => esc_html__( 'Today Sale', 'wc_pos' ),
		'expected_amount_in_drawer'                     => esc_html__( 'Expected Amount in Drawer', 'wc_pos' ),
		'counted_drawer_amount'                         => esc_html__( 'Counted Drawer Amount', 'wc_pos' ),
		'remarks'                                       => esc_html__( 'Remarks', 'wc_pos' ),
		'closing_drawer_detail'                         => esc_html__( 'Closing Drawer Detail', 'wc_pos' ),
		'difference_between_closing_and_opening_Amount' => esc_html__( 'Difference', 'wc_pos' ),
		'close_drawer'                                  => esc_html__( 'Close Drawer', 'wc_pos' ),
		'cash_balance'                                  => esc_html__( 'Cash balance', 'wc_pos' ),
		'date'                                          => esc_html__( 'Date', 'wc_pos' ),
		'card_sale'                                     => esc_html__( 'Other Payments', 'wc_pos' ),
		'cash_sale'                                     => esc_html__( 'Cash Sale', 'wc_pos' ),
		'total_sale'                                    => esc_html__( 'Total Sale', 'wc_pos' ),
		'drawer_note'                                   => esc_html__( 'Drawer Note', 'wc_pos' ),
		'close_counter'                                 => esc_html__( 'Close Counter', 'wc_pos' ),
		'today_cash'                                    => esc_html__( 'Today Cash', 'wc_pos' ),
		'sale_history'                                  => esc_html__( 'Sale History', 'wc_pos' ),
		'hold_sale'                                     => esc_html__( 'Hold Sale', 'wc_pos' ),
		'offline_sale'                                  => esc_html__( 'Offline Sale', 'wc_pos' ),
		'customer_name'                                 => esc_html__( 'Customer Name', 'wc_pos' ),
		'customer_phone'                                => esc_html__( 'Customer Phone', 'wc_pos' ),
		'customer_email'                                => esc_html__( 'Customer Email', 'wc_pos' ),
		'address'                                       => esc_html__( 'Address', 'wc_pos' ),
		'first_name'                                    => esc_html__( 'First name', 'wc_pos' ),
		'last_name'                                     => esc_html__( 'Last name', 'wc_pos' ),
		'billing_email'                                 => esc_html__( 'Billing Email', 'wc_pos' ),
		'address_1'                                     => esc_html__( 'Address 1', 'wc_pos' ),
		'address_2'                                     => esc_html__( 'Address 2', 'wc_pos' ),
		'country'                                       => esc_html__( 'Country', 'wc_pos' ),
		'state'                                         => esc_html__( 'State', 'wc_pos' ),
		'city'                                          => esc_html__( 'City', 'wc_pos' ),
		'pincode'                                       => esc_html__( 'Pincode', 'wc_pos' ),
		'phone'                                         => esc_html__( 'Phone', 'wc_pos' ),
		'customer_name_empty_validation'                => esc_html__( 'Customer name cannot be empty', 'wc_pos' ),
		'customer_phone_empty_validation'               => esc_html__( 'Customer phone cannot be empty', 'wc_pos' ),
		'customer_email_empty_validation'               => esc_html__( 'Customer email cannot be empty', 'wc_pos' ),
		'customer_phone_type_validation'                => esc_html__( 'Only Numbers allowed', 'wc_pos' ),
		'customer_phone_valid_validation'               => esc_html__( 'Not a valid phone number', 'wc_pos' ),
		'customer_email_valid_validation'               => esc_html__( 'Customer email is not valid', 'wc_pos' ),
		'customer_pincode_valid_validation'             => esc_html__( 'Customer pincode is not valid', 'wc_pos' ),
		'customer_city_valid_validation'                => esc_html__( 'Customer city is not valid', 'wc_pos' ),
		'customer_billing_email_valid_validation'       => esc_html__( 'Customer billing email is not valid', 'wc_pos' ),
		'pos_first_name_empty_validation'               => esc_html__( 'First name is not valid', 'wc_pos' ),
		'pos_last_name_empty_validation'                => esc_html__( 'Last name is not valid', 'wc_pos' ),
		'pos_email_empty_validation'                    => esc_html__( 'Pos manager email cannot be empty', 'wc_pos' ),
		'pos_email_valid_validation'                    => esc_html__( 'Pos manager email is not valid', 'wc_pos' ),
		'pos_old_pswd_empty_validation'                 => esc_html__( 'Old password cannot be empty', 'wc_pos' ),
		'pos_new_pswd_empty_validation'                 => esc_html__( 'New password cannot be empty', 'wc_pos' ),
		'pos_cnf_pswd_empty_validation'                 => esc_html__( 'Confirm password cannot be empty', 'wc_pos' ),
		'pos_cnf_pswd_same_validation'                  => esc_html__( 'Both passwords are not same', 'wc_pos' ),
		'account_settings'                              => esc_html__( 'Account Settings', 'wc_pos' ),
		'other_settings'                                => esc_html__( 'Other Settings', 'wc_pos' ),
		'keyboard_settings'                             => esc_html__( 'KeyBoard Shortcuts', 'wc_pos' ),
		'email_text'                                    => esc_html__( 'Email', 'wc_pos' ),
		'previous_password'                             => esc_html__( 'Previous Password', 'wc_pos' ),
		'new_password'                                  => esc_html__( 'New Password', 'wc_pos' ),
		'confirm_password'                              => esc_html__( 'Confirm Password', 'wc_pos' ),
		'update_account'                                => esc_html__( 'Update Account', 'wc_pos' ),
		'select_currency'                               => esc_html__( 'Select Currency', 'wc_pos' ),
		'select_invoice_printer'                        => esc_html__( 'Select Invoice Printer', 'wc_pos' ),
		'select_language'                               => esc_html__( 'Select Language', 'wc_pos' ),
		'english'                                       => esc_html__( 'English', 'wc_pos' ),
		'save_settings'                                 => esc_html__( 'Save Settings', 'wc_pos' ),
		'only_letters'                                  => esc_html__( 'Only letters', 'wc_pos' ),
		'tendered'                                      => esc_html__( 'Tendered', 'wc_pos' ),
		'split_payment_text'                            => esc_html__( 'Split Payment', 'wc_pos' ),
		'change'                                        => esc_html__( 'Change', 'wc_pos' ),
		'clear'                                         => esc_html__( 'C', 'wc_pos' ),
		'other_payment_title'                           => esc_html__( 'Other Payments', 'wc_pos' ),
		'subtotal_text'                                 => esc_html__( 'Subtotal', 'wc_pos' ),
		'order_summary'                                 => esc_html__( 'Order Summary', 'wc_pos' ),
		'tax_text'                                      => esc_html__( 'Tax', 'wc_pos' ),
		'order_text'                                    => esc_html__( 'Order', 'wc_pos' ),
		'customer_text'                                 => esc_html__( 'Customer', 'wc_pos' ),
		'confirm_payment'                               => esc_html__( 'Confirm Payment', 'wc_pos' ),
		'generate_invoice'                              => esc_html__( 'Generate Invoice', 'wc_pos' ),
		'order_note_text'                               => esc_html__( 'Add order note here', 'wc_pos' ),
		'sync_orders'                                   => esc_html__( 'Sync Orders', 'wc_pos' ),
		'no_sync_orders'                                => esc_html__( ' No offline order to Sync', 'wc_pos' ),
		'reloading_text'                                => esc_html__( 'Reloading data', 'wc_pos' ),
		'loading_categories_text'                       => esc_html__( 'Loading Categories', 'wc_pos' ),
		'loading_countries_text'                        => esc_html__( 'Loading Countries', 'wc_pos' ),
		'loading_states_text'                           => esc_html__( 'Loading States', 'wc_pos' ),
		'loading_currencies'                            => esc_html__( 'Loading Currencies', 'wc_pos' ),
		'loading_sale_text'                             => esc_html__( 'Loading sales', 'wc_pos' ),
		'upadting_manager_text'                         => esc_html__( 'Updating Manager details', 'wc_pos' ),
		'loading_tax_text'                              => esc_html__( 'Loading Tax', 'wc_pos' ),
		'grand_total_text'                              => esc_html__( 'Grand Total', 'wc_pos' ),
		'remove_not_valid_products'                     => esc_html__( ' out of stock now, please remove them from cart.', 'wc_pos' ),
		'is'                                            => esc_html__( ' is', 'wc_pos' ),
		'are'                                           => esc_html__( ' are', 'wc_pos' ),
		'payment_detail'                                => esc_html__( 'Payment Detail', 'wc_pos' ),
		'pay_by_cash_text'                              => esc_html__( 'Pay By Cash', 'wc_pos' ),
		'pay_by_card_text'                              => esc_html__( 'Pay By Other', 'wc_pos' ),
		'discount_title_text'                           => esc_html__( 'Apply discount to sale', 'wc_pos' ),
		'pay_text'                                      => esc_html__( 'Pay', 'wc_pos' ),
		'customer_delete_success_text'                  => esc_html__( 'Customer has been deleted successfully', 'wc_pos' ),
		'quantity_text'                                 => esc_html__( 'Quantity', 'wc_pos' ),
		'unit_price_text'                               => esc_html__( 'Unit Price', 'wc_pos' ),
		'total_price_text'                              => esc_html__( 'Total Price', 'wc_pos' ),
		'send_to'                                       => esc_html__( 'Do you want to send this order via mail to: ', 'wc_pos' ),
		'customer_name'                                 => esc_html__( 'Customer name : ', 'wc_pos' ),
		'email_text1'                                   => esc_html__( ' Email : ', 'wc_pos' ),
		'send_email'                                    => esc_html__( 'Send order email', 'wc_pos' ),
		'total_change'                                  => esc_html__( 'Total Change', 'wc_pos' ),
		'title'                                         => get_bloginfo( 'name' ),
		'customer_search_placeholder'                   => esc_html__( 'Search customer by name, email', 'wc_pos' ),
		'keyboard_shortcuts'                            => esc_html__( 'Keyboard Shortcuts', 'wc_pos' ),
		'keyboard_shortcuts_notice'                     => esc_html__( 'Note : Keyboard Shortcut is currently disabled from Admin', 'wc_pos' ),
		'header_section'                                => esc_html__( 'Header Section', 'wc_pos' ),
		'search'                                        => esc_html__( 'Search', 'wc_pos' ),
		'full_screen'                                   => esc_html__( 'Full Screen', 'wc_pos' ),
		'menu_section'                                  => esc_html__( 'Menu Section', 'wc_pos' ),
		'home'                                          => esc_html__( 'Home', 'wc_pos' ),
		'customers'                                     => esc_html__( 'Customers', 'wc_pos' ),
		'cashier'                                       => esc_html__( 'Cashier', 'wc_pos' ),
		'orders'                                        => esc_html__( 'Orders', 'wc_pos' ),
		'report'                                        => esc_html__( 'Report', 'wc_pos' ),
		'setting'                                       => esc_html__( 'Setting', 'wc_pos' ),
		'reset'                                         => esc_html__( 'Reset', 'wc_pos' ),
		'cart_section'                                  => esc_html__( 'Cart Section', 'wc_pos' ),
		'custom_product'                                => esc_html__( 'Custom Product', 'wc_pos' ),
		'remove_cart'                                   => esc_html__( 'Remove Cart', 'wc_pos' ),
		'barcode_scan'                                  => esc_html__( 'Barcode Scan', 'wc_pos' ),
		'apply_coupon'                                  => esc_html__( 'Apply Coupon', 'wc_pos' ),
		'hold_cart'                                     => esc_html__( 'Hold Cart', 'wc_pos' ),
		'pay'                                           => esc_html__( 'PAY', 'wc_pos' ),
		'hold_data'                                     => esc_html__( 'Hold', 'wc_pos' ),
		'offline_order'                                 => esc_html__( 'Offline Order', 'wc_pos' ),
		'pay_section'                                   => esc_html__( 'Pay Section', 'wc_pos' ),
		'select_first'                                  => esc_html__( 'For Selecting First value of Payment', 'wc_pos' ),
		'change_method'                                 => esc_html__( 'For Changing Payment Method', 'wc_pos' ),
		'final_pay'                                     => esc_html__( 'Generate Reciept', 'wc_pos' ),
		'delete_payment'                                => esc_html__( 'Remove Payment', 'wc_pos' ),
		'customer_section'                              => esc_html__( 'Customer Section', 'wc_pos' ),
		'customer_search'                               => esc_html__( 'Customer Search', 'wc_pos' ),
		'order_section'                                 => esc_html__( 'Order Section (Online / Offline)', 'wc_pos' ),
		'order_search'                                  => esc_html__( 'Order Search', 'wc_pos' ),
		'order_print'                                   => esc_html__( 'Order Print', 'wc_pos' ),
		'order_sync'                                    => esc_html__( 'Offline Order Sync', 'wc_pos' ),
		'paid'                                          => esc_html__( 'Paid', 'wc_pos' ),
		'refund'                                        => esc_html__( 'Refund', 'wc_pos' ),
		'total'                                         => esc_html__( 'Total', 'wc_pos' ),
		'close_reciept'                                 => esc_html__( 'Close Reciept', 'wc_pos' ),
		'add_text'                                      => esc_html__( 'Add', 'wc_pos' ),
		'order_status_text'                             => esc_html__( 'Order Status', 'wc_pos' ),
		'expand_screen'                                 => esc_html__( 'Expand Screen', 'wc_pos' ),
		'collapse_screen'                               => esc_html__( 'Collapse Screen', 'wc_pos' ),
		'keyborad_shortcuts'                            => esc_html__( 'Keyboard Shortcuts', 'wc_pos' ),
		'hold_carts'                                    => esc_html__( 'Hold Carts', 'wc_pos' ),
		'you_are_offline'                               => esc_html__( 'You are offline', 'wc_pos' ),
		'you_are_online'                                => esc_html__( 'You are online', 'wc_pos' ),
		'offline_sale'                                  => esc_html__( 'Offline sale/orders', 'wc_pos' ),
		'dark_mode'                                     => esc_html__( 'Dark Mode', 'wc_pos' ),
		'light_mode'                                    => esc_html__( 'Light Mode', 'wc_pos' ),
		'add_new_products'                              => esc_html__( 'Add New Product', 'wc_pos' ),
		'clear_cart'                                    => esc_html__( 'Clear Cart', 'wc_pos' ),
		'scan_barcode'                                  => esc_html__( 'Scan Barcode', 'wc_pos' ),
		'remove_product'                                => esc_html__( 'Remove Product', 'wc_pos' ),
		'select_customer'                               => esc_html__( 'Select the customer first.', 'wc_pos' ),
		'pin'                                           => esc_html__( 'Pin', 'wc_pos' ),
		'unpin'                                         => esc_html__( 'Unpin', 'wc_pos' ),
		'can_not_add_pin'                               => esc_html__( 'You can not pin/unpin product in offline mode.', 'wc_pos' ),
		'enter_valid_price'                             => esc_html__( 'Enter a valid price.', 'wc_pos' ),
		'not_process_offline_in_centralized_inventory'  => esc_html__( 'Cannot process orders with centralized inventory at offline mode.', 'wc_pos' ),
		'offline_order_present'                         => esc_html__( 'There are offline orders present, kindly sync them first!', 'wc_pos' ),
		'entered_tendered_same_as_total'                => esc_html__( 'Please enter tendered amount same as total amount.', 'wc_pos' ),
		'add_product_to_cart_first'                     => esc_html__( 'Please add products to the cart first!', 'wc_pos' ),
		'no_stock_available'                            => esc_html__( 'No stock available.', 'wc_pos' ),
		'product_not_found'                             => esc_html__( 'Sorry! Product not found.', 'wc_pos' ),
		'for'                                           => esc_html__( ' for', 'wc_pos' ),
		'coupon_not_applicable_for_cart'                => esc_html__( 'This coupon is not applicable for product in cart.', 'wc_pos' ),
		'coupon_already_applied'                        => esc_html__( 'This coupon is already applied.', 'wc_pos' ),
		'all_product_imported'                          => esc_html__( 'All Products Imported Succesfully.', 'wc_pos' ),
		'discount_less_than'                            => esc_html__( 'Discount amount should be less than or equal to', 'wc_pos' ),
		'valid_discount_amount'                         => esc_html__( 'Enter valid discount amount.', 'wc_pos' ),
		'out_of_stock'                                  => esc_html__( 'Out of stock', 'wc_pos' ),
	);

	$data = apply_filters( 'wkwc_add_custom_key_to_translate', $data );

	return $data;
}
