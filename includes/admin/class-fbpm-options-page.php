<?php
/**
 * 옵션 페이지 모듈.
 *
 * 관리자 > 설정 > FBPM 메뉴 페이지를 만들고, 거기에 여러 설정을 조절할 수 있는 UI를 제공한다.
 */

if ( ! class_exists( 'FBPM_Options_Page' ) ) {
	class FBPM_Options_Page implements FBPM_Admin_Module {
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_style' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_filter( 'wp_kses_allowed_html', array( $this, 'filter_allowed_html' ), 10, 2 );
		}

		public function enqueue_style( string $hook ): void {
			if ( 'settings_page_fbpm' === $hook ) {
				wp_enqueue_style(
					'fbpm-admin-options',
					plugins_url( 'public/css/admin/options.css', FBPM_MAIN ),
					array(),
					FBPM_VERSION
				);
			}
		}

		public function admin_menu(): void {
			add_submenu_page(
				'options-general.php',
				'페이스북 포스트 모니터 관리자 설정',
				'FB 포스트 모니터',
				'manage_options',
				'fbpm',
				array( $this, 'output_admin_menu' ),
			);
		}

		public function output_admin_menu(): void {
			$this->settings_sections();

			fbpm_template(
				'admin/options',
				array(
					'auth'         => fbpm()->settings->get_auth(),
					'redirect_uri' => fbpm()->auth->get_redirect_uri(),
					'webhook_url'  => fbpm()->webhook->get_webhook_url(),
				)
			);
		}

		/**
		 * `wp_kses()' 함수에서 폼 관련 태그를 적절히 처리하기 위한 설정을 보충.
		 *
		 * @param array  $allowed_tags
		 * @param string $context
		 *
		 * @return array
		 */
		public function filter_allowed_html( array $allowed_tags, string $context ): array {
			if ( 'input' === $context ) {
				$allowed_tags['input'] = array(
					'accept'              => true,
					'alt'                 => true,
					'autocomplete'        => true,
					'capture'             => true,
					'checked'             => true,
					'class'               => true,
					'dirname'             => true,
					'disabled'            => true,
					'form'                => true,
					'formaction'          => true,
					'formenctype'         => true,
					'formmethod'          => true,
					'formnovalidate'      => true,
					'formtarget'          => true,
					'height'              => true,
					'id'                  => true,
					'list'                => true,
					'max'                 => true,
					'maxlength'           => true,
					'min'                 => true,
					'minlength'           => true,
					'multiple'            => true,
					'name'                => true,
					'pattern'             => true,
					'placeholder'         => true,
					'popovertarget'       => true,
					'popovertargetaction' => true,
					'readonly'            => true,
					'required'            => true,
					'size'                => true,
					'src'                 => true,
					'step'                => true,
					'title'               => true,
					'type'                => true,
					'value'               => true,
					'width'               => true,
				);
			}

			return $allowed_tags;
		}

		protected function settings_sections(): void {
			$this->settings_section_oauth2();
		}

		protected function settings_section_oauth2(): void {
			$settings = fbpm()->settings;

			add_settings_section(
				'fbpm-oauth2',
				'자격 증명',
				function () {
					echo '<p class="description">' .
					     '<a href="https://developers.facebook.com/apps/" target="_blank">메타 개발자 페이지</a>' .
					     '에서 앱을 생성하세요. 그리고 선택된 앱의 앱 설정 &gt; 기본 설정 페이지를 참조하세요.' .
					     '</p>';
				},
				'fbpm'
			);

			add_settings_field(
				'fbpm-oauth2-app_id',
				'앱 ID',
				fn( $args ) => $this->render_field( $args ),
				'fbpm',
				'fbpm-oauth2',
				array(
					'label_for'   => 'fbpm-oauth2-app_id',
					'field'       => 'input',
					'attrs'       => array(
						'autocomplete' => 'off',
						'id'           => 'fbpm-oauth2-app_id',
						'class'        => 'text large-text',
						'name'         => 'fbpm_settings[app_id]',
						'type'         => 'text',
						'value'        => $settings->get_app_id(),
						'placeholder'  => '',
					),
					'extra_attrs' => array(
						'help_text' => '발급받은 앱 ID',
					),
				)
			);

			add_settings_field(
				'fbpm-oauth2-app_secret',
				'앱 시크릿 코드',
				fn( $args ) => $this->render_field( $args ),
				'fbpm',
				'fbpm-oauth2',
				array(
					'label_for'   => 'fbpm-oauth2-app_secret',
					'field'       => 'input',
					'attrs'       => array(
						'autocomplete' => 'off',
						'id'           => 'fbpm-oauth2-app_secret',
						'class'        => 'text large-text',
						'name'         => 'fbpm_settings[app_secret]',
						'type'         => 'text',
						'value'        => $settings->get_app_secret(),
						'placeholder'  => '',
					),
					'extra_attrs' => array(
						'help_text' => '발급받은 앱 시크릿 코드',
					),
				)
			);
		}

		protected function render_field( array $args ): void {
			if ( 'input' === ( $args['field'] ?? '' ) ) {
				$this->render_field_input( $args['attrs'] ?? [], $args['extra_attrs'] ?? [] );
			};
		}

		protected function render_field_input( array $attrs, array $extra_attrs ): void {
			$str_attributes = "";

			foreach ( $attrs as $key => $value ) {
				$sanitized_key = sanitize_key( $key );

				if ( $sanitized_key !== $key ) {
					continue;
				}

				$escaped_value = match ( $key ) {
					'class' => implode(
						' ',
						array_map(
							'sanitize_html_class',
							array_filter(
								preg_split( '/\s+/', $value )
							)
						)
					),
					default => esc_attr( $value ),
				};

				$str_attributes .= sprintf( ' %s="%s"', $sanitized_key, $escaped_value );
			}

			echo wp_kses( "<input$str_attributes>", 'input' );

			// Extra attrs.
			$help_text = trim( $extra_attrs['help_text'] ?? '' );

			if ( str_starts_with( $help_text, '<' ) ) {
				echo wp_kses( $help_text, 'user_description' );
			} else {
				echo '<p class="description">' . esc_html( $help_text ) . '</p>';
			}
		}
	}
}
