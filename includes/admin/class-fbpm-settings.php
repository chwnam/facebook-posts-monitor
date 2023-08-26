<?php
/**
 * Facebook posts monitor
 * ----------------------
 *
 * admin/class-fbpm-settings.php
 *
 * 옵션 관리자.
 * 플러그인의 관리하는 모든 옵션 데이터를 정의하고, 간단하게 불러올 수 있도록 래퍼 함수를 제공한다.
 */

if ( ! class_exists( 'FBPM_Settings' ) ) {
	class FBPM_Settings implements FBPM_Admin_Module {
		public function __construct() {
			add_action( 'init', array( $this, 'register_settings' ) );
		}

		public function register_settings(): void {
			register_setting(
				'fbpm',
				'fbpm_settings',
				array(
					'type'              => 'object',
					'description'       => '',
					'sanitize_callback' => [ $this, 'sanitize_settings' ],
					'show_in_rest'      => false,
					'default'           => static::get_default_settings(),
				)
			);
		}

		public function sanitize_settings( mixed $value ): array {
			return static::get_default_settings();
		}

		public static function get_default_settings(): array {
			return array(
			);
		}
	}
}
