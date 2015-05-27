<?php

namespace Hametuha\GapiWP\Service;


use Hametuha\GapiWP\Pattern\Singleton;
use Hametuha\GapiWP\Utility\Input;


/**
 * Prototype
 *
 * @package Hametuha\GapiWP\Service
 *
 * @property-read Input $input
 * @property-read string $base_dir
 * @property-read string $template_dir
 * @property-read string $asset_url
 */
class Prototype extends Singleton
{



	/**
	 * Show message on admin screen
	 *
	 * @param string $msg
	 * @param bool $error
	 */
	protected function show_message($msg, $error = false){
		add_action("admin_notices", function() use($msg, $error){
			printf('<div class="%s"><p>%s</p></div>', $error ? 'error' : 'updated', esc_html($msg) );
		});
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		switch( $name ){
			case 'input':
				return Input::get_instance();
				break;
			case 'base_dir':
				return dirname(dirname(dirname(dirname(__DIR__))));
				break;
			case 'template_dir':
				return $this->base_dir.DIRECTORY_SEPARATOR.'templates';
				break;
			case 'asset_url':
				return str_replace(ABSPATH, home_url('/', is_ssl() ? 'https' : 'http'), $this->base_dir).'/assets';
				break;
			default:
				return null;
				break;
		}
	}
}
