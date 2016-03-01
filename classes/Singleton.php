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
	protected static $instance;

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
			// Create new instance of class and pass dynamic args
			static::$instance = new static( func_get_arg(0), func_get_arg(1) );
		}

		return static::$instance;

	}

}