<?php

namespace YahnisElsts\PluginUpdateChecker\v5p6;

if ( ! class_exists( Autoloader::class, false ) ):

	class Autoloader {
		const DEFAULT_NS_PREFIX = 'YahnisElsts\\PluginUpdateChecker\\';

		private $prefix;
		private $rootDir;
		private $libraryDir;

		private $staticMap;

		public function __construct() {
			$this->rootDir = dirname( __DIR__ ) . '/';

			$namespaceWithSlash = __NAMESPACE__ . '\\';
			$this->prefix = $namespaceWithSlash;

			$this->libraryDir = $this->rootDir . 'Puc/';
			$this->staticMap = array(
				$namespaceWithSlash . 'PucFactory'           => 'Puc/v5p6/PucFactory.php',
				$namespaceWithSlash . 'UpdateChecker'        => 'Puc/v5p6/UpdateChecker.php',
				'Parsedown'                                  => 'vendor/ParsedownModern.php',
				'PucReadmeParser'                            => 'vendor/PucReadmeParser.php',
			);

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		public function autoload( $className ) {
			if ( isset( $this->staticMap[ $className ] ) && file_exists( $this->rootDir . $this->staticMap[ $className ] ) ) {
				include( $this->rootDir . $this->staticMap[ $className ] );
				return;
			}

			if ( strpos( $className, $this->prefix ) === 0 ) {
				$path = substr( $className, strlen( $this->prefix ) );
				$path = str_replace( '\\', '/', $path );
				$path = $this->rootDir . $path . '.php';

				if ( file_exists( $path ) ) {
					include $path;
				}
			}
		}
	}

endif;
