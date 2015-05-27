<?php

namespace Hametuha\GapiWP\Pattern;


/**
 * Singleton Class
 *
 * @package Hametuha\GapiWP\Pattern
 */
abstract class Singleton
{

	/**
	 * Instances
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor
	 *
	 * If required, override this function
	 *
	 * @param array $settings
	 */
	protected function __construct( array $settings = array() ){
		// Override this if required.
	}

	/**
	 * Get singleton instance
	 *
	 * @param array $settings
	 *
	 * @return static
	 */
	public static function get_instance( array $settings = array() ){
		$class_name = get_called_class();
		if( !isset(self::$instances[$class_name]) ){
			self::$instances[$class_name] = new $class_name($settings);
		}
		return self::$instances[$class_name];
	}
}
