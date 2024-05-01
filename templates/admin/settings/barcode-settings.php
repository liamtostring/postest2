<?php

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

settings_errors();

?>

<div id="wrapper">

	<div id="dashboard_right_now" class="formcontainer pos pos-settings">

		<div class="inside">

			<div class="main">

				<form method="post" action="options.php">

					<?php

					settings_fields( 'pos-barcode-settings-group' );

					do_settings_sections( 'pos-barcode-settings-group' );

					?>

					<div class="options_group">

						<?php
						wkwcpos_text_input(
							array(
								'id'          => '_pos_barcode_width',
								'value'       => ! empty( get_option( '_pos_barcode_width' ) ) ? get_option( '_pos_barcode_width' ) : '',
								'label'       => __( 'Barcode Width', 'wc_pos' ),
								'desc_tip'    => true,
								'description' => __( 'Enter the width of the barcode in pixels.', 'wc_pos' ),
								'type'        => 'number',
							)
						);
						?>

					</div>

					<?php submit_button( __( 'Save Barcode', 'wc_pos' ) ); ?>

				</form>
			</div>
	</div>

</div>
