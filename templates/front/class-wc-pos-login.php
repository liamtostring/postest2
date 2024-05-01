<?php
/**
 * Template Name: Login Page pos.
 *
 * Login Page Template.
 *
 * @package  WooCommerce Point Of Sale API *
 * @since 1.0.0
 * @version  1.0.0
 */

namespace WKWC_POS\Templates\Front;

use WKWC_POS\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Pos_Login' ) ) {
	/**
	 * Pos login class.
	 */
	class WC_Pos_Login extends Helper\User\WC_Pos_User_Helper {

		/**
		 * Login page heading.
		 *
		 * @var string $pos_heading Login page heading.
		 */
		protected $pos_heading;

		/**
		 * Login page brand name.
		 *
		 * @var string $pos_brand_name Login page brand name.
		 */
		protected $pos_brand_name;

		/**
		 * Login page brand link.
		 *
		 * @var string $pos_brand_link Login page brand link.
		 */
		protected $pos_brand_link;

		/**
		 * Current logged in pos user data.
		 *
		 * @var object $user Current logged in pos user data.
		 */
		protected $user;

		/**
		 * Constructor of the class.
		 */
		public function __construct() {

			parent::__construct();

			$this->pos_heading = ! empty( get_option( '_pos_heading_login' ) ) ? esc_html( get_option( '_pos_heading_login' ) ) : esc_html__( 'Point Of Sale System', 'wc_pos' );

			$this->pos_brand_name = ! empty( get_option( '_pos_footer_brand_name' ) ) ? esc_html( get_option( '_pos_footer_brand_name' ) ) : esc_html__( 'Webkul', 'wc_pos' );

			$this->pos_brand_link = ! empty( get_option( '_pos_footer_brand_link' ) ) ? esc_url( get_option( '_pos_footer_brand_link' ) ) : esc_url( 'https://store.webkul.com/woocommerce-point-of-sale.html' );

			if ( is_user_logged_in() ) {
				$user_id    = get_current_user_id();
				$this->user = get_userdata( $user_id );
			}
			$this->wk_wc_get_pos_login_template();
		}

		/**
		 * Get pos login template.
		 */
		public function wk_wc_get_pos_login_template() {
			?>

			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<title><?php echo get_bloginfo(); ?></title>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<meta name="viewport" content="width=device-width,user-scalable=yes,initial-scale=1,maximum-scale=5">
				<meta name="keywords" content="WooCommerce Pos, Point of sale" />
				<link rel="profile" href="http://gmpg.org/xfn/11">
				<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
				<link as="style" rel="stylesheet preload prefetch" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700" type="text/css" crossorigin="anonymous"/>
				<link rel="manifest" href="<?php echo esc_url( site_url( '/wkwcpos-manifest.json' ) ); ?>" />
				<script src ="https://polyfill.io/v3/polyfill.min.js?features=es2020"></script>
			<?php wp_head(); ?>
			</head>

			<body>
				<!-- section -->
			<?php
			switch ( true ) {
				case ( ! empty( $this->user ) && in_array( 'pos_user', $this->user->roles, true ) || apply_filters( 'wkwcpos_allow_pos_access_for_custom_role', false, $this->user ) ):
					?>
					<div id="app"></div>

						<div class="invoice-printer" id="invoice-print">

							<div class="invoice-body" id="invoice-body"></div>

						</div>
						<?php do_action( 'wk_wc_pos_after_pos_body_action' ); ?>
					<?php
					break;

				default:
					// phpcs:ignore
					if ( isset( $_GET['login'] ) && 'failed' === $_GET['login'] ) :
						?>
					<div class="pos_error">
						<strong><?php esc_html_e( 'FAILED: Try again!', 'wc_pos' ); ?></strong>
					</div>
					<?php endif; ?>
					<div class="pos_error wkwcpos-login-error" style="display:none">
						<strong ><?php esc_html_e( 'Please enter all fields for login.', 'wc_pos' ); ?></strong>
					</div>
					<section class="pos_loginForm">

					<div class="pos-form-wrap">

						<div class='pos-login-wrap'>

							<div class="home-div">

								<h2><b><?php echo esc_html( $this->pos_heading ); ?></b></h2>
								<?php do_action( 'wkwcpos_before_login_div' ); ?>
								<div class="text-center login-div">

									<h2><b><?php echo esc_html__( 'LOGIN TO YOUR ACCOUNT', 'wc_pos' ); ?></b></h2>



									<?php
									$pos_endpoint = ! empty( get_option( '_pos_endpoint_name' ) ) ? get_option( '_pos_endpoint_name' ) : 'pos';

									// Login form arguments.
									$args = array(
										'echo'           => true,
										'redirect'       => home_url( '/' . $pos_endpoint . '/' ),
										'form_id'        => 'posloginform',
										'label_username' => __( 'Username', 'wc_pos' ),
										'label_password' => __( 'Password', 'wc_pos' ),
										'label_remember' => __( 'Remember Me', 'wc_pos' ),
										'label_log_in'   => __( 'Log In', 'wc_pos' ),
										'id_username'    => 'user_login',
										'id_password'    => 'user_pass',
										'id_remember'    => 'rememberme',
										'id_submit'      => 'pos-submit',
										'remember'       => true,
										'value_username' => '',
										'value_password' => '',
										'value_remember' => true,
									);

									// Calling the login form.
									wp_login_form( $args );
									?>
								</div>
									<?php do_action( 'wkwcpos_after_login_div' ); ?>

								<div class="pos-footer">
									<h3><?php echo esc_html__( 'POS System', 'wc_pos' ); ?></h3>
									<p><?php echo esc_html__( 'A Product of', 'wc_pos' ); ?> <a href="<?php echo esc_url( $this->pos_brand_link ); ?>" target="_blank"><b><?php echo esc_html( $this->pos_brand_name ); ?></b></a></p>
								</div>

							</div>
						</div>
					</div>
					<span class="pos-version">
						<?php
						/* translators: %1$f pos version,  %2$s webkul url */
						echo wp_sprintf( esc_html__( 'version - %1$0.1f - %2$s', 'wc_pos' ), WK_WC_POS_VERSION, "<a href='" . esc_url( 'https://webkul.com/' ) . "'>Webkul</a>" );
						?>
						</span>

					</section>
					<?php
					break;
			}
			?>

			</body>

			<!-- Prompt a message in the browser if users disabled JS -->
			<noscript><?php esc_html_e( 'Your browser does not support JavaScript!', 'wc_pos' ); ?></noscript>

			</html>

				<?php
		}
	}
}
