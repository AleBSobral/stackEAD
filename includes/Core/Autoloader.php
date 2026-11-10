<?php
namespace StackEAD\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PSR-4 Compliant Autoloader for StackEAD Core.
 */
class Autoloader {

	/**
	 * Base namespace to match.
	 *
	 * @var string
	 */
	protected static string $prefix = 'StackEAD\\';

	/**
	 * Base directory corresponding to namespace prefix.
	 *
	 * @var string
	 */
	protected static string $base_dir = '';

	/**
	 * Register the autoloader.
	 */
	public static function register(): void {
		self::$base_dir = plugin_dir_path( dirname( __DIR__ ) ) . 'includes/';

		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload handler.
	 *
	 * @param string $class
	 */
	public static function autoload( string $class ): void {
		$len = strlen( self::$prefix );
		if ( strncmp( self::$prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );

		// If starts with "Core\", strip it since includes/ contains subdirectories directly
		if ( str_starts_with( $relative_class, 'Core\\' ) ) {
			$file_path = self::$base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		} else {
			$file_path = self::$base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
		}

		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}
}
