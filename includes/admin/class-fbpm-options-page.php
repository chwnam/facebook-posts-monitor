<?php
/**
 * Facebook posts monitor
 * ----------------------
 *
 * admin/class-fbpm-options-page.php
 *
 * 옵션 페이지 모듈.
 *
 * 관리자 > 설정 > FBPM 메뉴 페이지를 만들고, 거기에 여러 설정을 조절할 수 있는 UI를 제공한다.
 */

if ( ! class_exists( 'FBPM_Options_Page' ) ) {
	class FBPM_Options_Page implements FBPM_Admin_Module {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
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
			fbpm_template( 'admin/options' );
		}
	}
}
