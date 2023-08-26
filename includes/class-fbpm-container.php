<?php
/**
 * 메인 컨테이너
 *
 * 플러그인의 모든 요소를 담고 관리하는, 작고 간단한 컨테이너.
 */

if ( ! class_exists( 'FBPM_Container' ) ) {
	/**
	 * @property-read FBPM_Auth         $auth
	 * @property-read FBPM_Options_Page $options_page
	 * @property-read FBPM_Settings     $settings
	 */
	final class FBPM_Container {
		private static ?self $instance = null;

		/** @var FBPM_Module[] */
		private array $modules = [];

		/** @var string[] */
		private array $failed_modules = [];

		private function __construct() {
			$targets = array(
				array( __DIR__ . '/admin', false ),
				array( __DIR__ . '/front', false ),
				array( __DIR__ . '/ondemand', true ),
			);

			foreach ( $targets as [$path, $ondemand] ) {
				if ( file_exists( $path ) && is_dir( $path ) && is_executable( $path ) ) {
					$iterator = new RegexIterator(
						new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) ),
						'%/class-fbpm-[a-z0-9\-]+\.php$%i',
						RegexIterator::MATCH
					);
					foreach ( $iterator as $iter ) {
						/** @var SplFileInfo $iter */
						$this->init_module( $iter->getFilename(), $ondemand );
					}
				}
			}
		}

		public function __get( string $prop ) {
			return $this->get_module( $prop );
		}

		public function get_module( string $module_name ): FBPM_Module|null {
			if ( isset( $this->modules[ $module_name ] ) ) {
				$module = $this->modules[ $module_name ];
				if ( $module instanceof Closure ) {
					$this->modules[ $module_name ] = $module();
				}
				return $this->modules[ $module_name ];
			} elseif ( ! in_array( $module_name, $this->failed_modules, true ) ) {
				$canon_name = implode( '_', array_map( 'ucfirst', explode( '-', $module_name ) ) );
				$class_name = "FBPM_" . $canon_name;

				if ( class_exists( $class_name ) && isset( class_implements( $class_name )['FBPM_Module'] ) ) {
					$this->modules[ $module_name ] = new $class_name( ...$this->get_constructor( $class_name )() );
					return $this->modules[ $module_name ];
				} else {
					$this->failed_modules[] = $module_name;
				}
			}

			return null;
		}

		protected function init_module( string $file_name, bool $ondemand ): void {
			$module_name = substr( $file_name, 11, - 4 );
			$canon_name  = implode( '_', array_map( 'ucfirst', explode( '-', $module_name ) ) );
			$class_name  = "FBPM_" . $canon_name;

			if ( class_exists( $class_name ) && isset( class_implements( $class_name )['FBPM_Module'] ) ) {
				$this->modules[ $module_name ] = $ondemand ?
					fn() => new $class_name( ...$this->get_constructor( $class_name )() ) :
					new $class_name( ...$this->get_constructor( $class_name )() );
			}
		}

		protected function get_constructor( string $class_name ): Closure {
			$constructors = array(
				'FBPM_Auth' => fn() => [
					$this->settings->get_app_id(),
					$this->settings->get_app_secret(),
				],
			);

			return $constructors[ $class_name ] ?? fn() => [];
		}

		public static function get_instance(): self {
			if ( ! self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
}
