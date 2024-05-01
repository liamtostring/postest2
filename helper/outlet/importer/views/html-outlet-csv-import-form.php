<?php
/**
 * Admin View: Outlet import form
 *
 * @package WooCommerce/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form class="wc-progress-form-content woocommerce-importer" enctype="multipart/form-data" method="post">
	<header>
		<h2><?php esc_html_e( 'Import outlets from a CSV file', 'wc_pos' ); ?></h2>
		<p><?php esc_html_e( 'This tool allows you to import (or merge) outlet data to your POS from a CSV file.', 'wc_pos' ); ?></p>
	</header>
	<section>
		<table class="form-table woocommerce-importer-options">
			<tbody>
				<tr>
					<th scope="row">
						<label for="upload">
							<?php esc_html_e( 'Choose a CSV file from your computer:', 'wc_pos' ); ?>
						</label>
					</th>
					<td>
						<?php
						if ( ! empty( $upload_dir['error'] ) ) {
							?>
							<div class="inline error">
								<p><?php esc_html_e( 'Before you can upload your import file, you will need to fix the following error:', 'wc_pos' ); ?></p>
								<p><strong><?php echo esc_html( $upload_dir['error'] ); ?></strong></p>
							</div>
							<?php
						} else {
							?>
							<input type="file" id="upload" name="import" size="25" />
							<input type="hidden" name="action" value="save" />
							<input type="hidden" name="max_file_size" value="<?php echo esc_attr( $bytes ); ?>" />
							<br>
							<small>
								<?php
								printf(
									/* translators: %s: maximum upload size */
									esc_html__( 'Maximum size: %s', 'wc_pos' ),
									esc_html( $size )
								);
								?>
							</small>
							<hr />
							<a href="<?php echo esc_url( WK_WC_POS_API . 'sample-data/sample_outlets.csv' ); ?>"><?php esc_html_e( 'Sample File', 'wc_pos' ); ?></a>
							<?php
						}
						?>
					</td>
				</tr>
				<tr>
					<th><label for="woocommerce-importer-update-existing"><?php esc_html_e( 'Update existing outlets', 'wc_pos' ); ?></label><br/></th>
					<td>
						<input type="hidden" name="update_existing" value="0" />
						<input type="checkbox" id="woocommerce-importer-update-existing" name="update_existing" value="1" />
						<label for="woocommerce-importer-update-existing"><?php esc_html_e( 'Existing outlets that match by ID will be updated. Outlets that do not exist will be skipped.', 'wc_pos' ); ?></label>
					</td>
				</tr>
				<tr class="woocommerce-importer-advanced hidden">
					<th>
						<label for="woocommerce-importer-file-url"><?php esc_html_e( 'Alternatively, enter the path to a CSV file on your server:', 'wc_pos' ); ?></label>
					</th>
					<td>
						<label for="woocommerce-importer-file-url" class="woocommerce-importer-file-url-field-wrapper">
							<code><?php echo esc_html( ABSPATH ) . ' '; ?></code><input type="text" id="woocommerce-importer-file-url" name="file_url" />
						</label>
					</td>
				</tr>
				<tr class="woocommerce-importer-advanced hidden">
					<th><label><?php esc_html_e( 'CSV Delimiter', 'wc_pos' ); ?></label><br/></th>
					<td><input type="text" name="delimiter" placeholder="," size="2" /></td>
				</tr>
				<tr class="woocommerce-importer-advanced hidden">
					<th><label><?php esc_html_e( 'Use previous column mapping preferences?', 'wc_pos' ); ?></label><br/></th>
					<td><input type="checkbox" id="woocommerce-importer-map-preferences" name="map_preferences" value="1" /></td>
				</tr>
			</tbody>
		</table>
	</section>
	<script type="text/javascript">
		jQuery(function() {
			jQuery( '.woocommerce-importer-toggle-advanced-options' ).on( 'click', function() {
				var elements = jQuery( '.woocommerce-importer-advanced' );
				if ( elements.is( '.hidden' ) ) {
					elements.removeClass( 'hidden' );
					jQuery( this ).text( jQuery( this ).data( 'hidetext' ) );
				} else {
					elements.addClass( 'hidden' );
					jQuery( this ).text( jQuery( this ).data( 'showtext' ) );
				}
				return false;
			} );
		});
	</script>
	<div class="wc-actions">
		<a href="#" class="woocommerce-importer-toggle-advanced-options" data-hidetext="<?php esc_attr_e( 'Hide advanced options', 'wc_pos' ); ?>" data-showtext="<?php esc_attr_e( 'Show advanced options', 'wc_pos' ); ?>"><?php esc_html_e( 'Show advanced options', 'wc_pos' ); ?></a>
		<button type="submit" class="button button-primary button-next" value="<?php esc_attr_e( 'Continue', 'wc_pos' ); ?>" name="save_step"><?php esc_html_e( 'Continue', 'wc_pos' ); ?></button>
		<?php wp_nonce_field( 'wkwcpos-csv-importer' ); ?>
	</div>
</form>
