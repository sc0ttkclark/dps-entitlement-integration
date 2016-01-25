<?php
/**
 * @package DPS\Entitlements
 */
namespace DPS\Entitlements;

/**
 * Class Singleton
 */
class Singleton {

	/**
	 * @var Singleton|Plugin|Server|Util Singleton instance
	 */
	private static $instance;

	/**
	 * Initialize the class
	 */
	protected function __construct() {

		// Hulk smash

	}

	/**
	 * Get singleton instance
	 *
	 * @return Singleton|Plugin|Server|Util
	 */
	public static function get_instance() {

		if ( ! static::$instance ) {
			// Get called class name
			$class_name = get_called_class();

			// Get args passed into call
			$args = func_get_args();

			// Build reflection class for passing dynamic args
			$reflect = new \ReflectionClass( $class_name );

			// Create new instance of class and pass dynamic args
			static::$instance = $reflect->newInstanceArgs( $args );
		}

		return static::$instance;

	}

}