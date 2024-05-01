<?php
/**
 * POS Search Report API.
 *
 * @package  WooCommerce Point Of Sale API.
 * @since    4.3.0
 */

namespace WKWC_POS\Api\Includes\Reports;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * POS Search Report API.
 *
 * @class WKWCPOS_API_Search_Reports.
 */
class WKWCPOS_API_Search_Reports {

	/**
	 * Base Name.
	 *
	 * @var string the route base.
	 */
	public $base = '/get-search-reports';

	/**
	 * Namespace Name.
	 *
	 * @var string The route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Database object.
	 *
	 * @var object $db Database object.
	 */
	protected $db = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Get report search data.
	 *
	 * @param object|array $request Request object.
	 *
	 * @return array $search_data Search data.
	 */
	public function get_report_search_data( $request ) {

		$search_data = array();

		$type   = isset( $request['type'] ) ? $request['type'] : '';
		$search = isset( $request['search'] ) ? $request['search'] : '';

		if ( ! empty( $type ) && ! empty( $search ) ) {

			$search = esc_sql( $search );

			$args = array(
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'asc',
				'post_status'    => 'publish',
				's'              => $search,
			);

			switch ( $type ) {
				case 'product_name':
					$args['post_type'] = 'product';

					$products = get_posts( $args );

					foreach ( $products as $product ) {
						$search_data[] = array(
							'value' => $product->post_title,
							'title' => $product->post_title,
						);
					}
					break;
				case 'coupon_code':
					$args['post_type'] = 'shop_coupon';

					$coupons = get_posts( $args );

					foreach ( $coupons as $coupon ) {
						$search_data[] = array(
							'value' => $coupon->post_title,
							'title' => $coupon->post_title,
						);

					}
					break;

				case 'payment_method':
					$payment_methods = $this->db->get_results( $this->db->prepare( "SELECT payment_name FROM {$this->db->prefix}woocommerce_pos_payments WHERE payment_name LIKE %s", '%' . $search . '%' ), ARRAY_A );

					foreach ( $payment_methods as $payment_method ) {
						$search_data[] = array(
							'value' => $payment_method['payment_name'],
							'title' => $payment_method['payment_name'],
						);
					}

					$cash = esc_html__( 'Cash', 'wc_pos' );
					$card = esc_html__( 'Card', 'wc_pos' );

					if ( false !== strpos( strtolower( $cash ), strtolower( $search ) ) ) {
						$search_data[] = array(
							'value' => strtolower( $cash ),
							'title' => $cash,
						);
					}

					if ( false !== strpos( strtolower( $card ), strtolower( $search ) ) ) {
						$search_data[] = array(
							'value' => strtolower( $card ),
							'title' => $card,
						);
					}

					break;
			}
		}

		$search_data = apply_filters( 'wkwcpos_modify_report_search_result', $search_data, $request, $type, $search );

		return $search_data;
	}
}
