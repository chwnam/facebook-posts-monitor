<?php
/**
 * 옵션 관리자
 *
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
					'sanitize_callback' => [ FBPM_Settings::class, 'sanitize_settings' ],
					'show_in_rest'      => false,
					'default'           => static::get_default_settings(),
				)
			);

			register_setting(
				'fbpm',
				'fbpm_auth',
				array(
					'type'              => 'object',
					'description'       => 'Authorization data',
					'sanitize_callback' => [ FBPM_Settings::class, 'sanitize_auth' ],
					'show_in_rest'      => false,
					'default'           => static::get_default_auth(),
				),
			);
		}

		public function get_settings(): array {
			return get_option( 'fbpm_settings' );
		}

		public function get_app_id(): string {
			return $this->get_settings_value( 'app_id' );
		}

		public function get_app_secret(): string {
			return $this->get_settings_value( 'app_secret' );
		}

		public function get_settings_value( string $key ): mixed {
			static $default = null;

			$option = $this->get_settings();

			if ( isset( $option[ $key ] ) ) {
				return $option[ $key ];
			}

			if ( ! $default ) {
				$default = self::get_default_settings();
			}

			return $default[ $key ] ?? null;
		}

		/**
		 * @return array{
		 *      app_id: string,
		 *      application: string,
		 *      data_access_expires_at: int,
		 *      expires_at: int,
		 *      is_valid: bool,
		 *      issued_at: int,
		 *      scopes: string[],
		 *      type: string,
		 *      user_id: string,
		 *  }
		 */
		public function get_auth(): array {
			return get_option( 'fbpm_auth' );
		}

		public function update_auth( array $data ): void {
			update_option( 'fbpm_auth', $data, false );
		}

		public static function sanitize_settings( mixed $value ): array {
			$default = static::get_default_settings();

			if ( ! is_array( $value ) ) {
				return $default;
			}

			$sanitized = static::get_default_settings();

			$sanitized['app_id'] = sanitize_text_field( $value['app_id'] ?? $default['app_id'] );

			$sanitized['app_secret'] = sanitize_text_field( $value['app_secret'] ?? $default['app_secret'] );

			return $sanitized;
		}

		public static function sanitize_auth( mixed $value ): array {
			$default = static::get_default_auth();

			if ( ! is_array( $value ) ) {
				return $default;
			}

			$sanitized = static::get_default_auth();

			$sanitized['app_id'] = sanitize_text_field( $value['app_id'] ?? $default['app_id'] );

			$sanitized['application'] = sanitize_text_field( $value['application'] ?? $default['application'] );

			$sanitized['data_access_expires_at'] = absint(
				$value['data_access_expires_at'] ?? $default['data_access_expires_at']
			);

			$sanitized['expires_at'] = absint( $value['expires_at'] ?? $default['expires_at'] );

			$sanitized['is_valid'] = boolval( $value['is_valid'] ?? $default['is_valid'] );

			$sanitized['issued_at'] = absint( $value['issued_at'] ?? $default['issued_at'] );

			$sanitized['scopes'] = array_map( 'sanitize_text_field', $value['scopes'] ?? $default['scopes'] );

			$sanitized['type'] = sanitize_text_field( $value['type'] ?? $default['type'] );

			$sanitized['user_id'] = sanitize_text_field( $value['user_id'] ?? $default['user_id'] );

			return $sanitized;
		}

		public static function get_default_settings(): array {
			return array(
				'app_id'     => '',
				'app_secret' => '',
			);
		}

		/**
		 * @return array{
		 *      app_id: string,
		 *      application: string,
		 *      data_access_expires_at: int,
		 *      expires_at: int,
		 *      is_valid: bool,
		 *      issued_at: int,
		 *      scopes: string[],
		 *      type: string,
		 *      user_id: string,
		 *  }
		 */
		public static function get_default_auth(): array {
			return array(
				'app_id'                 => '',
				'application'            => '',
				'data_access_expires_at' => 0,
				'expires_at'             => 0,
				'is_valid'               => false,
				'issued_at'              => 0,
				'scopes'                 => [],
				'type'                   => '',
				'user_id'                => '',
			);
		}
	}
}
