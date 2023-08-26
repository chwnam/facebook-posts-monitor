<?php
/**
 * 인증 모듈
 *
 * 페이스북 인증을 위한 모듈
 */

use JetBrains\PhpStorm\NoReturn;

if ( ! class_exists( 'FBPM_Auth' ) ) {
	class FBPM_Auth implements FBPM_Module {
		public function __construct(
			private string $app_id = "",
			private string $app_secret = ""
		) {
			add_action( 'admin_post_fbpm-authorize', array( $this, 'admin_post_authorize' ) );
			add_action( 'admin_post_fbpm-auth-redirect', array( $this, 'admin_post_auth_redirect' ) );
		}

		public function get_app_id(): string {
			return $this->app_id;
		}

		public function get_app_secret(): string {
			return $this->app_secret;
		}

		public function get_redirect_uri(): string {
			return admin_url( 'admin-post.php?action=fbpm-auth-redirect' );
		}

		#[NoReturn]
		public function admin_post_authorize(): void {
			check_admin_referer( 'fbpm-authorize' );

			$redirect_url = add_query_arg(
				urlencode_deep(
					[
						'client_id'    => $this->app_id,
						'redirect_uri' => $this->get_redirect_uri(),
						'state'        => $this->generate_state(),
						'scope'        => 'public_profile,user_posts',
					]
				),
				'https://www.facebook.com/v17.0/dialog/oauth'
			);

			wp_redirect( $redirect_url );
			exit;
		}

		#[NoReturn]
		public function admin_post_auth_redirect(): void {
			$state = wp_unslash( $_REQUEST['state'] ?? '' );
			$code  = wp_unslash( $_REQUEST['code'] ?? '' );

			if ( ! $code || ! $this->verify_state( $state ) ) {
				wp_die( 'Unexpected response.' );
			}

			$access_token_data   = $this->request_access_token( $code );
			$token_detailed_info = $this->debug_access_token( $access_token_data['access_token'] );

			fbpm()->settings->update_auth( $token_detailed_info );

			wp_safe_redirect( admin_url( 'options-general.php?page=fbpm' ) );
			exit;
		}

		/**
		 * @param string $code
		 *
		 * @return array{
		 *     access_token: string,
		 *     token_type: string,
		 *     expires_in: int,
		 * }
		 * @link https://developers.facebook.com/docs/facebook-login/guides/access-tokens#apptokens
		 */
		protected function request_access_token( string $code ): array {
			$url = add_query_arg(
				urlencode_deep(
					[
						'client_id'     => $this->app_id,
						'client_secret' => $this->app_secret,
						'redirect_uri'  => $this->get_redirect_uri(),
						'code'          => $code,
					]
				),
				'https://graph.facebook.com/v17.0/oauth/access_token'
			);

			$response    = wp_remote_get( $url );
			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );

			if ( 200 !== $status_code ) {
				wp_die( 'Access token request failed.' );
			}

			$data         = json_decode( $body );
			$access_token = $data->access_token ?? '';
			$token_type   = $data->token_type ?? '';
			$expires_in   = (int) ( $data->expires_in ?? '0' );

			return compact( 'access_token', 'token_type', 'expires_in' );
		}

		/**
		 * @param string $access_token
		 *
		 * @return array{
		 *     app_id: string,
		 *     application: string,
		 *     data_access_expires_at: int,
		 *     expires_at: int,
		 *     is_valid: bool,
		 *     issued_at: int,
		 *     scopes: string[],
		 *     type: string,
		 *     user_id: string,
		 * }
		 * @link   https://developers.facebook.com/docs/graph-api/reference/v17.0/debug_token
		 */
		protected function debug_access_token( string $access_token ): array {
			$url = add_query_arg(
				[
					'input_token'  => $access_token,
					'access_token' => $access_token,
				],
				'https://graph.facebook.com/debug_token'
			);

			$response    = wp_remote_get( $url );
			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );

			if ( 200 !== $status_code ) {
				wp_die( 'Debug token request failed.' );
			}

			$decoded = json_decode( $body, true );

			return $decoded['data'] ?? [];
		}

		protected function generate_state(): string {
			$nonce = wp_create_nonce( 'fbpm-auth-redirect' );
			$time  = time();
			$hash  = wp_hash( "$nonce:$time" );

			return "$nonce:$time:$hash";
		}

		protected function verify_state( string $state ): bool {
			[ $nonce, $time, $hash ] = explode( ':', $state, 3 );

			return wp_verify_nonce( $nonce, 'fbpm-auth-redirect' ) &&
			       hash_equals( $hash, wp_hash( "$nonce:$time" ) ) &&
			       ( time() - $time ) < ( 5 * MINUTE_IN_SECONDS );
		}
	}
}
