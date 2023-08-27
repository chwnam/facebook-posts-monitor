<?php
/**
 * Plugin Name:       페이스북 포스트 모니터
 * Version:           0.0.0
 * Description:       페이스북 포스트를 워드프레스로 가져올 수 있습니다.
 * Plugin URI:        https://github.com/chwnam/fbpm
 * Requires at least:
 * Requires PHP:      8.0
 * Author:            changwoo
 * Author URI:        https://blog.changwoo.pe.kr
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fbpm
 */

require_once __DIR__ . '/vendor/autoload.php';

const FBPM_MAIN    = __FILE__;
const FBPM_VERSION = '0.0.0';

fbpm();

if ( ! function_exists( 'fbpm_test' ) ) {
	function fbpm_test(): string {
		return '<div><pre>' . print_r( fbpm()->api_post->get(), true ) . '</pre></div>';
	}

	add_shortcode( 'fbpm_test', 'fbpm_test' );
}