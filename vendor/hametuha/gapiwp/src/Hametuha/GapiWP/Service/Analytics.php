<?php

namespace Hametuha\GapiWP\Service;


/**
 * Google Analytics related functions
 *
 * @package Hametuha\GapiWP
 * @property-read string $consumer_key
 * @property-read string $consumer_secret
 * @property-read string $token
 * @property-read int $view_id
 * @property-read \Google_Client $client
 * @property-read \Google_Service_Analytics $ga
 */
class Analytics extends Prototype
{


	/**
	 * Google Client
	 *
	 * @ignore
	 * @var \Google_Client
	 */
	private $_client = null;

	/**
	 *
	 * @var \Google_Service_Analytics
	 */
	private $_ga = null;


	/**
	 * Constructor
	 *
	 * @param array $settings
	 */
	protected function __construct( array $settings = array() ) {
		if( is_admin() ){
			add_action('admin_menu', array($this, 'admin_menu'));
			add_action('admin_init', array($this, 'admin_init'));
		}
	}


	/**
	 * Register admin screen
	 */
	public function admin_menu(){
		$title = __('Google Analytics設定', 'gapiwp');
		$menu  = __('Analytics設定', 'gapiwp');
		add_options_page($title, $menu, 'manage_options', 'gapiwp-analytics', array($this, 'admin_render'));
	}


	/**
	 * Parse Token
	 */
	public function admin_init(){
		if( !defined('DOING_AJAX') || !DOING_AJAX ){
			if( 'gapiwp-analytics' == $this->input->get('page') ){
				// Load assets
				$css = $this->asset_url.'/css/admin-analytics.css';
				add_action('admin_enqueue_scripts', function() use ($css){
					wp_enqueue_style('gapiwp-admin', $css, array(), filemtime($this->base_dir.'/assets/css/admin-analytics.css'));
				});
				// Form send
				if( $this->input->verify_nonce('ga_token_update') ){
					// Update token
					$token = $this->input->post('consumer_key');
					update_option('gapiwp_key', $token);
					$secret = $this->input->post('consumer_secret');
					update_option('gapiwp_secret', $secret);
					// Try redirect
					if( $token && $secret && $this->client ){
						try{
							$this->client->setApprovalPrompt('force');
							$url = $this->client->createAuthUrl();
							wp_redirect($url);
							exit;
						}catch ( \Exception $e ){
							$this->show_message($e->getMessage(), true);
						}
					}
				}
				// Save token when callback
				try{
					if( ($code = $this->input->get('code')) && $this->client->authenticate($code) ){
						$token = $this->client->getAccessToken();
						update_option('gapiwp_token', $token);
						wp_redirect(admin_url('options-general.php?page=gapiwp-analytics&save_token=true'));
						exit;
					}
				}catch ( \Exception $e ){
					$this->show_message($e->getMessage(), true);
				}
				// Show success message
				if( 'true' === $this->input->get('save_token') ){
					$this->show_message(__('トークンの取得に成功しました', 'gapiwp'));
				}
				// Save view
				if( $this->input->verify_nonce('ga_account_save') ){
					update_option('gapiwp_view_id', $this->input->post('view'));
				}

			}
		}
	}

	/**
	 * Render admin screen
	 */
	public function admin_render(){
		include $this->template_dir.'/ga.php';
	}


	/**
	 * Get account list
	 *
	 * @return array|\Google_Service_Analytics_Accounts
	 */
	public function get_accounts(){
		static $ga_accounts = null;
		if( !is_null($ga_accounts) ){
			return $ga_accounts;
		}
		$ga_accounts = array();
		if( $this->ga ){
			try{
				$accounts = $this->ga->management_accounts->listManagementAccounts();
				if( count($accounts->getItems()) > 0 ){
					$ga_accounts = $accounts;
				}
			}catch (\Exception $e){
				// Do nothing.
				error_log($e->getMessage(), $e->getCode());
			}
		}
		return $ga_accounts;
	}

	/**
	 * Get Web Properties
	 *
	 * @param $account_id
	 * @return array
	 */
	public function get_properties($account_id) {
		$result = array();
		try {
			if ( $this->ga ) {
				$properties = $this->ga
					->management_webproperties
					->listManagementWebproperties( $account_id );
				$result     = $properties->getItems();
			}
		} catch ( \Exception $e ) {
			// Do nothing.
			error_log($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 * Get Views
	 *
 	 * @param $account_id
	 * @param $profile_id
	 * @return array
	 */
	public function get_views($account_id, $profile_id){
		$result = array();
		try{
            $views = $this->ga
                ->management_profiles
                ->listManagementProfiles($account_id, $profile_id);
            $result =$views->getItems();
		}catch ( \Exception $e ){
			// Do nothing.
			error_log($e->getMessage(), $e->getCode());
		}
		return $result;
	}

	/**
	 * Fetch data
	 *
	 * @param string $from
	 * @param string $to
	 * @param string $metrics
	 * @param array $args
	 *
	 * @return \Google_Service_Analytics_GaData|\WP_Error
	 */
	public function fetch($from, $to, $metrics, array $args, $view_id = null ){
		if( !$this->ga ){
			return new \WP_Error(500, __('Google Analtyisとの連携が完了していません。', 'gapiwp'));
		}
		if( is_null($view_id) ){
			$view_id = $this->view_id;
		}
		try{
			$result = $this->ga->data_ga->get('ga:'.$view_id, $from, $to, $metrics, $args);
		}catch ( \Exception $e ){
			$result = new \WP_Error($e->getCode(), $e->getMessage());
		}
		return $result;
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name){
		switch( $name ){
			case 'client':
				if( is_null($this->_client) && ($this->consumer_key && $this->consumer_secret) ){
					try{
						$this->_client = new \Google_Client();
						$this->_client->setClientId($this->consumer_key);
						$this->_client->setClientSecret($this->consumer_secret);
						$this->_client->setRedirectUri(admin_url('options-general.php?page=gapiwp-analytics'));
						$this->_client->setScopes(array(
							'https://www.googleapis.com/auth/analytics.readonly'
						));
						$this->_client->setAccessType('offline');
					}catch ( \Exception $e ){
						// Do nothing
					}
				}
				return $this->_client;
				break;
			case 'ga':
				try{
					if( $this->token && $this->client && is_null($this->_ga) ){
						$this->client->setAccessToken($this->token);
						if( $this->client->isAccessTokenExpired() ){
							// Refresh token if expired
							$token = json_decode($this->token);
							$this->client->refreshToken($token->refresh_token);
							update_option('gapiwp_token', $this->client->getAccessToken());
						}
						$this->_ga = new \Google_Service_Analytics($this->client);
					}
				}catch ( \Exception $e ){
					// Do nothing
				}
				return $this->_ga;
				break;
			case 'view_id':
				return get_option('gapiwp_view_id', 0);
				break;
			case 'consumer_key':
				return get_option('gapiwp_key', '');
				break;
			case 'consumer_secret':
				return get_option('gapiwp_secret', '');
				break;
			case 'token':
				return get_option('gapiwp_token', '');
				break;
			default:
				return parent::__get( $name);
				break;
		}
	}

} 