<?php

namespace WKWC_POS\Templates\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'WC_Pos_Mass_Assign_Outlet_Products' ) ) {

  /**
   *
   */
  class WC_Pos_Mass_Assign_Outlet_Products {

    function __construct() {

      $this->wk_wc_pos_get_mass_assign_outlet_products();

    }

    public function wk_wc_pos_get_mass_assign_outlet_products() {

      $outlet = intval( $_GET['outlet_id'] );
      ?>

      <div id="wrapper">

        <div id="dashboard_right_now" class="formcontainer pos pos-settings pos-outletbulk-settings">

          <div class="inside">

            <div class="main">

              <div class="wc-product-import-section">

                <div class="wc-ep-info-bar">

                  <img class="wc-loader-image" src="<?php echo WK_WC_POS_API . 'assets/images/loader.gif'; ?>">

                </div>

                <div class="wc-product-import-section-body">

                </div>

              </div>

              <div class="options_group">

                <label for="_pos_outlet_assign_all"><?php echo __( 'Outlet Assign All Products', 'wc_pos' ); ?></label>
                <input type="checkbox" id="_pos_outlet_assign_all" name="_pos_outlet_assign_all">
                <span><?php echo __( 'Assign all product to pos', 'wc_pos' ); ?></span>

              </div>

              <input type="hidden" name="outlet_pos" id="outlet_pos" value="<?php echo $outlet; ?>">

              <div class="options_group">

                <?php
                wkwcpos_text_input( array(
                  'id'                => '_pos_outlet_assign_qty',
                  'value'             => '',
                  'label'             => __( 'Outlet Assign Quantity', 'wc_pos' ),
                  'desc_tip'          => true,
                  'description'       => __( 'POS Outlet Assign default quantity.', 'wc_pos' ),
                  'type'              => 'text'
                ) );
                ?>

              </div>

              <?php submit_button( __( 'Bulk Assign', 'wc_pos' ) ); ?>

            </div>

          </div>

        </div>

      </div>

      <?php

    }

  }

}
