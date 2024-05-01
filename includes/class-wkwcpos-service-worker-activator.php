<?php
/**
 * Service worker activator class.
 *
 * @version    1.0.0
 * @package WooCommerce Point of Sale
 */

if ( ! class_exists( 'WKWCPOS_Service_Worker_Activator' ) ) {

	/**
	 * Service worker activator class.
	 */
	class WKWCPOS_Service_Worker_Activator {

		/**
		 * Activate service worker function.
		 */
		public static function wkwcpos_activate() {

			if ( ! class_exists( 'WooCommerce' ) ) {

				wp_die();

			} else {
				self::wkwcpos_write_service_worker();
			}

		}

		/**
		 * Write service worker function.
		 */
		public static function wkwcpos_write_service_worker() {

			global $tmp_sw;

			$tmp_sw = file_get_contents( WK_WC_POS_DIR . '/library/wkwcpos-sw.js' );
			$tmp_sw = str_replace( 'SUB_PATH', get_option( 'siteurl' ) . '/?subId=', $tmp_sw );
			$tmp_sw = str_replace( 'DEBUG_VAR', true, $tmp_sw );

			$form_url = 'admin.php?page=pos-system';

			self::wkwcpos_generate_new_manifest_file( $form_url );

			self::wkwcpos_write_file( $form_url, $tmp_sw, 'wkwcpos-sw.js' );

		}

		/**
		 * Generate new manifest file function.
		 *
		 * @param string $form_url Form url.
		 */
		public static function wkwcpos_generate_new_manifest_file( $form_url ) {

			$pwa_name    = ! empty( get_option( '_pos_pwa_name' ) ) ? get_option( '_pos_pwa_name' ) : 'Progressive Web App for WooCommerce POS';
			$short_name  = ! empty( get_option( '_pos_pwa_shortname' ) ) ? get_option( '_pos_pwa_shortname' ) : 'PWA for WooCommerce POS';
			$theme_color = ! empty( get_option( '_pos_pwa_themecolor' ) ) ? get_option( '_pos_pwa_themecolor' ) : '#a333c8';
			$bg_color    = ! empty( get_option( '_pos_pwa_bgcolor' ) ) ? get_option( '_pos_pwa_bgcolor' ) : '#a333c8';
			$icon48      = ! empty( get_option( '_pos_pwa_icon48' ) ) ? site_url( get_option( '_pos_pwa_icon48' ) ) : WK_WC_POS_API . 'assets/images/48.png';
			$icon96      = ! empty( get_option( '_pos_pwa_icon96' ) ) ? site_url( get_option( '_pos_pwa_icon96' ) ) : WK_WC_POS_API . 'assets/images/96.png';
			$icon144     = ! empty( get_option( '_pos_pwa_icon144' ) ) ? site_url( get_option( '_pos_pwa_icon144' ) ) : WK_WC_POS_API . 'assets/images/144.png';
			$icon196     = ! empty( get_option( '_pos_pwa_icon196' ) ) ? site_url( get_option( '_pos_pwa_icon196' ) ) : WK_WC_POS_API . 'assets/images/196.png';

			$manifest_data = '{
                "name": "' . $pwa_name . '",
                "short_name": "' . $short_name . '",
                "start_url": "' . site_url( ! empty( get_option( '_pos_endpoint_name' ) ) ? '/' . get_option( '_pos_endpoint_name' ) : '/pos' ) . '",
                "display": "standalone",
                "orientation": "landscape",
                "theme_color": "' . $theme_color . '",
                "background_color": "' . $bg_color . '",
                "gcm_sender_id": "103953800507",
                "icons": [
                    {
                        "src": "' . $icon48 . '",
                        "sizes": "48x48",
                        "type": "image/png"
                    },
                    {
                        "src": "' . $icon96 . '",
                        "sizes": "96x96",
                        "type": "image/png"
                    },
                    {
                        "src": "' . $icon144 . '",
                        "sizes": "144x144",
                        "type": "image/png"
                    },
                    {
                        "src": "' . $icon196 . '",
                        "sizes": "196x196",
                        "type": "image/png"
                    }
                ]
            }';

			self::wkwcpos_write_file( $form_url, $manifest_data, 'wkwcpos-manifest.json' );

			return true;

		}

		/**
		 * Writing service worker file.
		 *
		 * @param string $form_url Form url.
		 * @param string $file_content Manifest data.
		 * @param string $filename Filename.
		 */
		public static function wkwcpos_write_file( $form_url, $file_content, $filename ) {

			global $wp_filesystem;

			$method  = '';
			$context = ABSPATH;

			if ( ! self::wkwcpos_file_init( $form_url, $method, ABSPATH ) ) {
				return false;
			}

			$target_dir = $wp_filesystem->find_folder( $context );

			$target_file = trailingslashit( $target_dir ) . $filename;

			require_once ABSPATH . 'wp-admin/includes/file.php';

			if ( ! $wp_filesystem->put_contents( $target_file, $file_content, FS_CHMOD_FILE ) ) {
				return new WP_Error( 'writing_error', 'Error when writing file' );
			}

			return $file_content;

		}

		/**
		 * Initializing service worker file.
		 *
		 * @param string $form_url Form url.
		 * @param string $method Method.
		 * @param string $context Context.
		 * @param string $fields Fields.
		 */
		public static function wkwcpos_file_init( $form_url, $method, $context, $fields = null ) {

			include_once ABSPATH . 'wp-admin/includes/file.php';
			$creds = request_filesystem_credentials( $form_url, $method, false, $context, $fields );
			if ( false === $creds ) {
				return false;
			}
			if ( ! WP_Filesystem( $creds ) ) {
				request_filesystem_credentials( $form_url, $method, true, $context, $fields );
				return false;
			}

			return true;

		}

	}

}
