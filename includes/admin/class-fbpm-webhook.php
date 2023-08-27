<?php

use JetBrains\PhpStorm\NoReturn;

if ( ! class_exists( 'FBPM_Webhook' ) ) {
	class FBPM_Webhook implements FBPM_Admin_Module {
		public function __construct() {
			add_action( 'admin_post_fbpm-webhook', array( $this, 'handle_webhook' ) );
			add_action( 'admin_post_nopriv_fbpm-webhook', array( $this, 'handle_webhook' ) );
		}

		#[NoReturn] public function handle_webhook(): void {
			$this->handle_challenge();
			exit;
		}

		public function get_webhook_url(): string {
			return admin_url( 'admin-post.php?action=fbpm-webhook' );
		}

		protected function handle_challenge(): void {
			$mode         = wp_unslash( $_REQUEST['hub_mode'] ?? '' );
			$challenge    = wp_unslash( $_REQUEST['hub_challenge'] ?? '' );
			$verify_token = wp_unslash( urldecode( $_REQUEST['hub_verify_token'] ?? '' ) );

			if ( 'subscribe' === $mode && 'fbpm-token' == $verify_token ) {
				echo $challenge;
			}
		}
	}
}