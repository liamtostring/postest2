<?php
/**
 * Admin View: Importer - Done!
 *
 * @package WooCommerce\Admin\Importers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wc-progress-form-content woocommerce-importer">
	<section class="woocommerce-importer-done">
		<?php
		$results = array();

		if ( 0 < $imported ) {
			$results[] = sprintf(
				/* translators: %d: outlets count */
				_n( '%s outlet imported', '%s outlets imported', $imported, 'wc_pos' ),
				'<strong>' . number_format_i18n( $imported ) . '</strong>'
			);
		}

		if ( 0 < $updated ) {
			$results[] = sprintf(
				/* translators: %d: outlets count */
				_n( '%s outlet updated', '%s outlets updated', $updated, 'wc_pos' ),
				'<strong>' . number_format_i18n( $updated ) . '</strong>'
			);
		}

		if ( 0 < $skipped ) {
			$results[] = sprintf(
				/* translators: %d: outlets count */
				_n( '%s outlet was skipped', '%s outlets were skipped', $skipped, 'wc_pos' ),
				'<strong>' . number_format_i18n( $skipped ) . '</strong>'
			);
		}

		if ( 0 < $failed ) {
			$results [] = sprintf(
				/* translators: %d: outlets count */
				_n( 'Failed to import %s outlet', 'Failed to import %s outlets', $failed, 'wc_pos' ),
				'<strong>' . number_format_i18n( $failed ) . '</strong>'
			);
		}

		if ( 0 < $failed || 0 < $skipped ) {
			$results[] = '<a href="#" class="woocommerce-importer-done-view-errors">' . __( 'View import log', 'wc_pos' ) . '</a>';
		}

		/* translators: %d: import results */
		echo wp_kses_post( __( 'Import complete!', 'wc_pos' ) . ' ' . implode( '. ', $results ) );
		?>
	</section>
	<section class="wc-importer-error-log" style="display:none">
		<table class="widefat wc-importer-error-log-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Product', 'wc_pos' ); ?></th>
					<th><?php esc_html_e( 'Reason for failure', 'wc_pos' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( count( $errors ) ) {
					foreach ( $errors as $error ) { // phpcs:ignore
						if ( ! is_wp_error( $error ) ) {
							continue;
						}
						$error_data = $error->get_error_data();
						?>
						<tr>
							<th><code><?php echo esc_html( $error_data['row'] ); ?></code></th>
							<td><?php echo esc_html( $error->get_error_message() ); ?></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</section>
	<script type="text/javascript">
		jQuery(function() {
			jQuery( '.woocommerce-importer-done-view-errors' ).on( 'click', function() {
				jQuery( '.wc-importer-error-log' ).slideToggle();
				return false;
			} );
		} );
	</script>
	<div class="wc-actions">
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=pos-outlets' ) ); ?>"><?php esc_html_e( 'View outlets', 'wc_pos' ); ?></a>
	</div>
</div>
