<?php
/**
 * WooCommerce POS API setup.
 *
 * @package  WooCommerce Point Of Sale API
 * @version    1.0.0
 */

namespace WKWC_POS\Api\Includes\Misc;

use WKWC_POS\Api\Inc\WKWCPOS_API_Error_Handler;
use WKWC_POS\Api\Includes\WKWCPOS_API_Authentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Update manager api class.
 */
class WKWCPOS_API_Update_Manager {

	/**
	 * Base Name.
	 *
	 * @var string the route base
	 */
	public $base = 'update-manager';

	/**
	 * Namespace Name.
	 *
	 * @var string the route namespace
	 */
	public $namespace = 'pos/v1';

	/**
	 * Error class object.
	 *
	 * @var object $error Error class object.
	 */
	public $error;

	/**
	 * Authentication class object.
	 *
	 * @var object $authentication Authentication class object.
	 */
	public $authentication;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->error          = new WKWCPOS_API_Error_Handler();
		$this->authentication = new WKWCPOS_API_Authentication();
	}

	/**
	 * Update pos manager API Callback.
	 *
	 * @param array $request Request arrray.
	 *
	 * @return bool $response|$exception.
	 */
	public function update_pos_manager( $request ) {

		try {

			$error    = '';
			$response = array();

			$user_id = $request['logged_in_user_id'];

			$validate_auth_key = $this->authentication->wkwcpos_authenticate_request( $user_id );

			if ( 'ok' !== $validate_auth_key ) {
				return array(
					'session_id'             => $validate_auth_key,
					'status'                 => 401,
					'invalid_auth_key_error' => __( 'Please provide valid Auth Key.', 'wc_pos' ),
					'success'                => false,
				);
			}

			$user_arr = '';

			$user_first_name = isset( $request['pos-user-fname'] ) ? sanitize_text_field( $request['pos-user-fname'] ) : '';
			$user_last_name  = isset( $request['pos-user-lname'] ) ? sanitize_text_field( $request['pos-user-lname'] ) : '';

			$oldpwd = isset( $request['pos-user-ppwd'] ) ? sanitize_text_field( $request['pos-user-ppwd'] ) : '';

			$npwd = isset( $request['pos-user-npwd'] ) ? sanitize_text_field( $request['pos-user-npwd'] ) : '';

			$cpwd = isset( $request['pos-user-cpwd'] ) ? sanitize_text_field( $request['pos-user-cpwd'] ) : '';

			if ( ! empty( $npwd ) && ! empty( $cpwd ) && $npwd == $cpwd ) {
					$user_arr = $npwd;
			}

			if ( ! empty( $user_id ) ) {

				$pos_user = $user_id;

			}

			if ( ! empty( $user_first_name ) ) {
				update_user_meta( $pos_user, 'first_name', $user_first_name );
			}

			if ( ! empty( $user_last_name ) ) {
				update_user_meta( $pos_user, 'last_name', $user_last_name );
			}

			if ( ! empty( $user_arr ) ) {
				$user_data = get_userdata( $pos_user );

				if ( wp_check_password( $oldpwd, $user_data->user_pass, $pos_user ) && ! empty( $user_arr ) ) {

					$user = new \WP_User( $pos_user );

					wp_set_password( $user_arr, $pos_user );

					// Log-in again.
					wp_set_auth_cookie( $pos_user );
					wp_set_current_user( $pos_user );
					do_action( 'wp_login', $user->user_login, $user );

				} else {
					$error = esc_html__( 'Old Password is wrong.', 'wc_pos' );
				}
			}

			$url = '';

			if ( isset( $_FILES['pos-user-profile-image'] ) && empty( $error ) ) {

				$file = $_FILES['pos-user-profile-image']; // phpcs:ignore

				$type = $file['type'];

				$image_ext = array( 'image/png', 'image/jpeg', 'image/jpg' );

				if ( in_array( $type, $image_ext, true ) ) {

					$file_tmp = ( isset( $file['tmp_name'] ) ) ? sanitize_text_field( $file['tmp_name'] ) : '';

					$ext = explode( '/', $type );

					$ext = isset( $ext[1] ) ? $ext[1] : 'png';

					$file_name = 'profile_image_' . $pos_user . '.' . $ext;

					$uploaded = wp_upload_bits( $file_name, null, file_get_contents( $file_tmp ) ); // phpcs:ignore

					if ( ! empty( $uploaded ) ) {

						$url = isset( $uploaded['url'] ) ? $uploaded['url'] : '';

						$dir = wp_upload_dir();

						$folder_url = explode( $dir['baseurl'], $url );

						if ( isset( $folder_url[1] ) ) {

							update_user_meta(
								$user_id,
								'shr_pic',
								$folder_url[1]
							);
						}
					} else {
						$error = esc_html__( 'File not uploaded!', 'wc_pos' );
					}
				} else {

					$error = esc_html__( 'File type not supported!', 'wc_pos' );
				}
			}

			if ( ! empty( $error ) ) {
				$response = array(
					'response' => false,
					'error'    => $error,
					'data'     => array(),
				);
			} else {
				$response = array(
					'response' => true,
					'error'    => '',
					'data'     => array(
						'first_name' => $user_first_name,
						'last_name'  => $user_last_name,
						'url'        => $url,
					),
				);
			}

			return apply_filters( 'wkwcpos_modify_update_manager_response_at_pos', $response, $request );

		} catch ( \Exception $e ) {

			return $this->error->set( 'exception', $e );
		}
	}
}
