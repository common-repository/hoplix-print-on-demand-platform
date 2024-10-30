<?php

if ( ! defined( 'ABSPATH' ) ) exit;



class Hoplix_Integration

{

    const HP_API_CONNECT_STATUS = 'hoplix_api_connect_status';

    const HP_CONNECT_ERROR = 'hoplix_connect_error';



	public static $_instance;



	public static function instance() {

		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();

		}



		return self::$_instance;

	}



	public function __construct() {

		self::$_instance = $this;

	}



    /**

     * @return Hoplix_Client

     * @throws HoplixException

     */

	public function get_client() {



		require_once 'class-hoplix-client.php';

		$client = new Hoplix_Client( $this->get_option( 'hoplix_key' ), $this->get_option( 'hoplix_secret' ), $this->get_option( 'disable_ssl' ) == 'yes' );



		return $client;

	}



    /**

     * Check if the connection to hopix is working

     * @param bool $force

     * @return bool

     * @throws HoplixException

     */

	public function is_connected( $force = false ) {



		$api_key    =   $this->get_option( 'hoplix_key' );

        $api_secret =   $this->get_option( 'hoplix_secret' );



		//dont need to show error - the plugin is simply not setup

		if ( empty( $api_key ) || empty( $api_secret )) {

			return false;

		}



		//validate length, show error

		if ( strlen( $api_key ) != 32 || strlen( $api_secret ) != 32 ) {

			$message      = 'Invalid API key - the key must be 32 characters long. Please ensure that your API key in <a href="%s">Settings</a> matches the one in your <a href="%s">Hoplix dashboard</a>.';

			$settings_url = admin_url( 'admin.php?page=hoplix-dashboard&tab=settings' );

			$hoplix_url = Hoplix_Base::get_hoplix_host() . 'account/';

			$this->set_connect_error(sprintf( $message, $settings_url, $hoplix_url ) );



			return false;

		}



		//show connect status from cache

		if ( ! $force ) {

			$connected = get_transient( self::HP_API_CONNECT_STATUS );

			if ( $connected && $connected['status'] == 1 ) {

				$this->clear_connect_error();



				return true;

			} else if ( $connected && $connected['status'] == 0 ) {    //try again in a minute

				return false;

			}

		}



		$client   = $this->get_client();

		$response = false;



		//attempt to connect to hoplix to verify the API key

		try {

			$storeData = $client->get( 'wc-store' );            

			if ( ! empty( $storeData ) && $storeData['type'] == 'woocommerce') {

				$response = true;

				$this->clear_connect_error();

				set_transient( self::HP_API_CONNECT_STATUS, array( 'status' => 1 ) );  //no expiry

			} elseif ( $storeData['type'] != 'woocommerce' ) {

				$message      = 'Invalid API key. This API key belongs to another store. Please copy the correct key from <a href="%s">Hoplix account settings</a> and enter it in the <a href="%s">Hoplix plugin settings</a>';

				$settings_url = admin_url( 'admin.php?page=hoplix-dashboard&tab=settings' );

				$hoplix_url = Hoplix_Base::get_hoplix_api_host() . 'account/woocommerce';

				$this->set_connect_error( sprintf( $message, $settings_url, $hoplix_url ) );

				set_transient( self::HP_API_CONNECT_STATUS, array( 'status' => 0 ), MINUTE_IN_SECONDS );  //try again in 1 minute

			}

		} catch ( Exception $e ) {



			if ( $e->getCode() == 201 ) {

				$message      = 'Invalid API key. Please ensure that your API key in <a href="%s">Hoplix plugin settings</a> matches the one in your <a href="%s">Hoplix account settings</a>.';

				$settings_url = admin_url( 'admin.php?page=hoplix-dashboard&tab=settings' );

				$hoplix_url = Hoplix_Base::get_hoplix_host() . 'account/woocommerce';

				$this->set_connect_error( sprintf( $message, $settings_url, $hoplix_url ) );

				set_transient( self::HP_API_CONNECT_STATUS, array( 'status' => 0 ), MINUTE_IN_SECONDS );  //try again in 1 minute

			} else {

				$this->set_connect_error( 'Could not connect to Hoplix API. Please try again later. (Error ' . $e->getCode() . ': ' . $e->getMessage() . ')' );

			}



			//do nothing

			set_transient( self::HP_API_CONNECT_STATUS, array( 'status' => 0 ), MINUTE_IN_SECONDS );  //try again in 1 minute

		}



		return $response;

	}



	/**

	 * Update connect error message

	 * @param string $error

	 */

	public function set_connect_error($error = '') {

		update_option( self::HP_CONNECT_ERROR, $error );

	}



	/**

	 * Get current connect error message

	 */

	public function get_connect_error() {

		return get_option( self::HP_CONNECT_ERROR, false );

	}



	/**

	 * Remove option used for storing current connect error

	 */

	public function clear_connect_error() {

		delete_option( self::HP_CONNECT_ERROR );

	}



    /**

     * AJAX call endpoint for connect status check

     * @throws HoplixException

     */

	public static function ajax_force_check_connect_status() {

		if ( Hoplix_Integration::instance()->is_connected( true ) ) {

			die( 'OK' );

		}



		die( 'FAIL' );

	}



	/**

	 * Wrapper method for getting an option

	 * @param $name

	 * @param array $default

	 * @return bool

	 */

	public function get_option( $name, $default = array() ) {

		$options  = get_option( 'woocommerce_hoplix_settings',  $default  );

		if ( ! empty( $options[ $name ] ) ) {

			return $options[ $name ];

		}



		return false;

	}



	/**

	 * Save the setting

	 * @param $settings

	 */

	public function update_settings( $settings ) {

		update_option( 'woocommerce_hoplix_settings', $settings );

	}

}