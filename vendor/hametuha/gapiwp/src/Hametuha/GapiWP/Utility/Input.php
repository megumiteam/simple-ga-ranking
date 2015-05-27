<?php

namespace Hametuha\GapiWP\Utility;
use Hametuha\GapiWP\Pattern\Singleton;


/**
 * Input utility class
 *
 * @package Hametuha\GapiWP\Utility
 */
class Input extends Singleton
{

    /**
     * Return GET Request
     *
     * @param string $key
     * @return null|string|array
     */
    public function get($key){
        if( isset($_GET[$key]) ){
            return $_GET[$key];
        }else{
            return null;
        }
    }

    /**
     * Return POST Request
     *
     * @param string $key
     * @return null|string|array
     */
    public function post($key){
        if( isset($_POST[$key]) ){
            return $_POST[$key];
        }else{
            return null;
        }
    }

    /**
     * Return REQUEST
     *
     * @param string $key
     * @return null|string|array
     */
    public function request($key){
        if( isset($_REQUEST[$key]) ){
            return $_REQUEST[$key];
        }else{
            return null;
        }
    }

    /**
     * Return current request method
     *
     * @return bool
     */
    public function request_method(){
        if( isset($_SERVER['REQUEST_METHOD']) ){
            return $_SERVER['REQUEST_METHOD'];
        }else{
            return false;
        }
    }

    /**
     * Returns post body
     *
     * This method is useful for typical XML API.
     *
     * @return string
     */
    public function post_body(){
        return file_get_contents('php://input');
    }

	/**
	 * Get remote address
	 *
	 * @return bool|string
	 */
	public function remote_ip(){
		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
	}

	/**
	 * Verify nonce
	 *
	 * @param string $action
	 * @param string $key Default '_wpnonce'
	 *
	 * @return bool
	 */
	public function verify_nonce($action, $key = '_wpnonce'){
		$nonce = $this->request($key);
		return $nonce && wp_verify_nonce($this->request($key), $action);
	}
}
