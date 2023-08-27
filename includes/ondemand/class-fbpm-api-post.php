<?php
/**
 * Facebook 포스트 API 담당
 */

if ( ! class_exists( 'FBPM_Api_Post' ) ) {
	class FBPM_Api_Post implements FBPM_Module {
		private string $user_id;
		private string $access_token;
		private int    $expires_at;

		public function __construct( array $auth = array() ) {
			$this->user_id      = $auth['user_id'] ?? '';
			$this->access_token = $auth['access_token'] ?? '';
			$this->expires_at   = $auth['expires_at'] ?? 0;
		}

		public function get() {
			// https://developers.facebook.com/docs/graph-api/reference/v17.0/user/posts
				// https://developers.facebook.com/docs/graph-api/reference/post

			// TODO:

			$fields = implode(
				',',
				[
					'id',
					'created_time',
					'message',
					'link',
					'updated_time',
				]
			);

			$url      = "https://graph.facebook.com/v17.0/$this->user_id/posts?access_token=$this->access_token&fields=$fields";
			$response = wp_remote_get( $url );

			$code = wp_remote_retrieve_response_code( $response );
			if ( 200 !== $code ) {
				wp_die( 'Wrong response' );
			}

			return json_decode( wp_remote_retrieve_body( $response ), true );
		}
	}
}
