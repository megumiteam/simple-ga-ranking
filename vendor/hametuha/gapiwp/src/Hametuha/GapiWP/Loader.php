<?php

namespace Hametuha\GapiWP;

use Hametuha\GapiWP\Service\Analytics;

class Loader
{
	// Do not initialize
	private function __construct(){}

	/**
	 * Initialize
	 */
	public static function load(){
		Analytics::get_instance();
	}

	/**
	 * Get Google Analytics client
	 *
	 * @return Analytics
	 */
	public static function analytics(){
		return Analytics::get_instance();
	}
}

