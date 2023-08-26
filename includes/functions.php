<?php
/**
 * 각종 유틸리티 함수의 집합.
 */

if ( ! function_exists( 'fbpm' ) ) {
	function fbpm(): FBPM_Container {
		return FBPM_Container::get_instance();
	}
}

if ( ! function_exists( 'fbpm_template' ) ) {
	function fbpm_template( string $tmpl_name, array $context = array(), bool $return = false ): string {
		$extensions = array( '.php' );
		$output     = '';

		foreach ( $extensions as $extension ) {
			$file_path = dirname( FBPM_MAIN ) . "/includes/templates/{$tmpl_name}{$extension}";

			if ( file_exists( $file_path ) ) {
				if ( $return ) {
					ob_start();
				}

				load_template( $file_path, false, $context );

				if ( $return ) {
					$output = ob_get_clean();
				}

				break;
			}
		}

		return $output;
	}
}
