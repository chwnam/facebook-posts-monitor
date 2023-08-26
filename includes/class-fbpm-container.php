<?php
/**
 * Facebook posts monitor
 * ----------------------
 *
 * class-fbpm-container.php
 *
 * FBPM_Conatiner 클래스. 메인 컨테이너.
 * 플러그인의 모든 요소를 담고 관리하는, 작고 간단한 컨테이너.
 */

if ( ! class_exists( 'FBPM_Container' ) ) {
	final class FBPM_Container {
		private static ?self $instance = null;

		/** @var FBPM_Module[] */
		private array $modules;

		private function __construct() {
			$this->modules = [];

			// Initialize all modules.
			$append_iter = new AppendIterator();

			if ( file_exists( __DIR__ . '/admin' ) ) {
				$append_iter->append(
					new RegexIterator(
						new RecursiveIteratorIterator( new RecursiveDirectoryIterator( __DIR__ . '/admin' ) ),
						'/\.php$/i',
						RegexIterator::MATCH
					)
				);
			}

			if ( file_exists( __DIR__ . '/front' ) ) {
				$append_iter->append(
					new RegexIterator(
						new RecursiveIteratorIterator( new RecursiveDirectoryIterator( __DIR__ . '/admin' ) ),
						'/\.php$/i',
						RegexIterator::MATCH
					)
				);
			}

			foreach ( $append_iter as $iter ) {
				/** @var SplFileInfo $iter */
				$this->init_module( $iter->getFilename() );
			}
		}

		public function init_module( string $file_name ): void {
			if ( str_starts_with( $file_name, 'class-fbpm-' ) ) {
				$module_name = substr( $file_name, 11, - 4 );
				$canon_name  = implode( '_', array_map( 'ucfirst', explode( '-', $module_name ) ) );
				$class_name  = "FBPM_" . $canon_name;

				if ( $module_name && class_exists( $class_name ) ) {
					$this->modules[ $module_name ] = new $class_name();
				}
			}
		}

		public function get_module( string $module_name ): FBPM_Module|null {
			return $this->modules[ $module_name ] ?? null;
		}

		public static function get_instance(): self {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
