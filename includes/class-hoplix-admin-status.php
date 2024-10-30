<?php

if ( ! defined( 'ABSPATH' ) ) exit;



class Hoplix_Admin_Status {



	const HP_STATUS_OK = 1;

	const HP_STATUS_WARNING = 0;

	const HP_STATUS_FAIL = -1;

	const HP_STATUS_NOT_CONNECTED = 2;



	const API_KEY_SEARCH_STRING = 'Hoplix';

	const HP_WEBHOOK_NAME = 'Hoplix Integration';

	const HP_REMOTE_REQUEST_URL = 'hook/woocommerce?store=1';

	const HP_REMOTE_REQUEST_TOPIC = 'woo.plugin.test';

	const HP_STATUS_ISSUE_COUNT = 'hoplix_status_issue_count';

	const HP_CACHED_CHECKLIST = 'hoplix_cached_checklist';

    const STATUS_CONNECTED_DISPLAYABLE_METHODS = array(

        'check_HP_webhooks',

        'check_HP_sync_errors',

        'check_HP_API_key',

        'check_WC_API_access'

    );

    

    public static $_instance;

    

    public static function getChecklistItems() {

	    return array(

            array(

                'name'        => __( 'Connection to Hoplix API', 'hoplix' ),

                'description' => __( 'Is your store successfully connected to Hoplix API?', 'hoplix' ),

                'method'      => 'check_HP_API_connect',

            ),

            array(

                'name'        => __( 'Hoplix API key is set', 'hoplix' ),

                'description' => __( 'Your store needs access to Hoplix API to use most of it\'s features like order export and product import.', 'hoplix' ),

                'method'      => 'check_HP_API_key',

            ),

            array(

                'name'        => __( 'WordPress Permalinks', 'hoplix' ),

                'description' => __( 'WooCommerce API will not work unless your permalinks in Settings > Permalinks are set up correctly. Make sure you that they are NOT set to "plain".', 'hoplix' ),

                'method'      => 'check_permalinks',

            ),

            array(

                'name'        => __( 'WordPress version', 'hoplix' ),

                'description' => __( 'WordPress should always be updated to the latest version. Updates can be installed from your WordPress admin dashboard.', 'hoplix' ),

                'method'      => 'check_WP_version',

            ),

            array(

                'name'        => __( 'WooCommerce Webhooks', 'hoplix' ),

                'description' => __( 'Hoplix requires WooCommerce webhooks to be set up to quickly capture you incoming orders, products updates etc.', 'hoplix' ),

                'method'      => 'check_HP_webhooks',

            ),

            array(

                'name'        => __( 'WooCommerce API keys are set', 'hoplix' ),

                'description' => __( 'Hoplix needs access to your WooCommerce API for the integration to work - otherwise we can\'t sync your store, push or pull your products etc.', 'hoplix' ),

                'method'      => 'check_WC_API_access',

            ),

            array(

                'name'        => __( 'WooCommerce authentication URL access', 'hoplix' ),

                'description' => __( 'Hoplix needs access to WooCommerce API authorize page. This sometimes may get blocked due to hosts having unnecessarily intrusive security checks in place that prevent WooCommerce API authentication from working (for example mod_security rule #1234234). If this check fails, you will not be able authorize Hoplix app.', 'hoplix' ),

                'method'      => 'check_WC_auth_url_access',

            ),

            array(

                'name'        => __( 'WordPress remote requests', 'hoplix' ),

                'description' => __( 'WordPress needs to be able to connect to Hoplix server to call webhooks. If this check fails, contact your hosting support.', 'hoplix' ),

                'method'      => 'check_remote_requests',

            ),

            array(

                'name'        => __( 'WordPress Site URL', 'hoplix' ),

                'description' => __( 'If your currently setup WordPress site URL is redirected to another URL the integration might not work correctly. Typically this happens with incorrect http to https redirects. Go to Settings > General to fix this.' , 'hoplix' ),

                'method'      => 'check_site_url_redirect',

            ),

            array(

                'name'        => __( 'Recent store sync errors', 'hoplix' ),

                'description' => __( 'Hoplix will connect to your store\'s API regularly and sync your latest products, orders etc. If there have been any recent issues with sync, this check will fail.', 'hoplix' ),

                'method'      => 'check_HP_sync_errors',

            ),

            array(

                'name'        => __( 'Write permissions', 'hoplix' ),

                'description' => __( 'Make the uploads directory writable. This is required for mockup generator product push to work correctly. Contact your hosting provider if you need help with this.', 'hoplix' ),

                'method'      => 'check_uploads_write',

            ),

            array(

                'name'        => __( 'PHP memory limit', 'hoplix' ),

                'description' => __( 'Set PHP allocated memory limit to at least 128mb. Contact your hosting provider if you need help with this.', 'hoplix' ),

                'method'      => 'check_PHP_memory_limit',

            ),

            array(

                'name'        => __( 'PHP script time limit', 'hoplix' ),

                'description' => __( 'Set PHP script execution time limit to at least 30 seconds. This is required to successfully push products with many variants. Contact your hosting provider if you need help with this.', 'hoplix' ),

                'method'      => 'check_PHP_time_limit',

            ),

            array(

                'name'        => __( 'W3 Total Cache DB Cache', 'hoplix' ),

                'description' => __( 'If you are using W3 Total Cache, the database caching feature needs to be disabled since it can cause issues with product push to store.', 'hoplix' ),

                'method'      => 'check_W3_db_cache',

                'silent'      => true,

            ),

            array(

                'name'        => __( 'WP SpamShield', 'hoplix' ),

                'description' => __( 'If you are using WP SpamShield, you might experience problems connecting to Hoplix and pushing products.', 'hoplix' ),

                'method'      => 'check_wp_spamshield',

                'silent'      => true,

            ),

        );

    }

    

    /**

     * @return Hoplix_Admin_Status

     */

	public static function instance() {

        if ( ! function_exists( 'get_plugins' ) ) {

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        }



		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();

		}



		return self::$_instance;

	}



	/**

	 * Setup the view variables

	 */

	public static function view() {



		$status = self::instance();

		$status->render();

	}



	/**

	 * Render the view

	 */

	public function render() {

        Hoplix_Admin::load_template( 'header', array( 'tabs' => Hoplix_Admin::get_tabs() ) );



		$checklist = self::get_checklist( true );

		if ( $checklist ) {

			Hoplix_Admin::load_template( 'status-table', array( 'checklist' => $checklist ) );

		} else {

			Hoplix_Admin::load_template( 'ajax-loader', array( 'action' => 'get_hoplix_status_checklist', 'message' => __( 'Testing your store (this may take up to 30 seconds)...', 'hoplix' ) ) );

		}



		Hoplix_Admin::load_template( 'footer' );

	}



	/**

	 * Build the content for status page

	 */

	public static function render_status_table_ajax() {



		$checklist = self::get_checklist();

		Hoplix_Admin::load_template( 'status-table', array( 'checklist' => $checklist ) );



		exit;

	}



    /**

     * Run the tests

     * @param bool $only_cached_results

     * @return array

     * @throws HoplixException

     */

	public static function get_checklist( $only_cached_results = false ) {



		$status = self::instance();



		$list = get_transient( Hoplix_Admin_Status::PF_CACHED_CHECKLIST );



		if ( $only_cached_results || $list ) {

			return $list;

		}



		$list                   = array();

		$list['overall_status'] = true;

		$issueCount             = 0;



		foreach ( self::getChecklistItems() as $item ) {

			$list_item                = array();

			$list_item['name']        = $item['name'];

			$list_item['description'] = $item['description'];



			if ( method_exists( $status, $item['method'] ) ) {

				$list_item['status'] = $status->{$item['method']}();



				// Do not display status for methods that are depending on connection status to Hoplix

				if ( ! Hoplix_Integration::instance()->is_connected(true) && in_array( $item['method'], self::STATUS_CONNECTED_DISPLAYABLE_METHODS ) ) {

				    continue;

                }



				if ( $status->should_result_be_visible( $list_item['status'], $item ) ) {

					$list['items'][] = $list_item;

				}



				if ( $list_item['status'] == self::HP_STATUS_FAIL || $list_item['status'] == self::HP_STATUS_NOT_CONNECTED ) {

					$list['overall_status'] = false;

					$issueCount ++;

				}

			}

		}



		set_transient( Hoplix_Admin_Status::HP_CACHED_CHECKLIST, $list, MINUTE_IN_SECONDS );

		set_transient( Hoplix_Admin_Status::HP_STATUS_ISSUE_COUNT, $issueCount, HOUR_IN_SECONDS );



		return $list;

	}



	/**

	 * Execute only one test

	 * @param $method

	 * @return mixed

	 */

	public function run_single_test( $method ) {

		if ( method_exists( $this, $method ) ) {

			return $this->{$method}();

		}

		return false;

	}



	/**

	 * @param $status

	 * @param bool $item

	 *

	 * @return int

	 */

	private function should_result_be_visible( $status, $item = false ) {



		if ( ! isset( $item['silent'] ) || ( $item['silent'] === true && $status === self::HP_STATUS_FAIL ) ) {   //silent items are only shown on FAIL

			return true;

		}



		return false;

	}



    /**

     * Function for checking if thumbnails are resized

     */

	private function check_uploads_write() {



		$upload_dir = wp_upload_dir();

		if ( is_writable( $upload_dir['basedir'] ) ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}



	/**

	 * @return int

	 */

	private function check_PHP_memory_limit() {



		$memory_limit = ini_get( 'memory_limit' );



		if ( preg_match( '/^(\d+)(.)$/', $memory_limit, $matches ) ) {

			if ( $matches[2] == 'M' ) {

				$memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB

			} else if ( $matches[2] == 'K' ) {

				$memory_limit = $matches[1] * 1024; // nnnK -> nnn KB

			}

		}



		$ok = ( $memory_limit >= 128 * 1024 * 1024 ); // at least 128M?



		if ( $ok ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}


	/**

	 * @return int

	 */

	private function check_HP_webhooks() {

	    global $wpdb;



		// Get the webhooks

		// In version 3.3 the webhooks are stored in separate table, before that they were stored in posts table

		if ( version_compare( WC()->version, '3.3', '<' ) ) {



			// Query args

			$args = array(

				'post_type'           => 'shop_webhook',

				'nopaging'            => true,

				'ignore_sticky_posts' => true,

				's'                   => self::HP_WEBHOOK_NAME,

				'post_status'         => 'published',

			);



			$webhook_results = new WP_Query( $args );

			$webhooks        = $webhook_results->posts;

			$count           = count( $webhooks ) > 0;

		} else {

			$count = $wpdb->get_var(

				$wpdb->prepare( "SELECT COUNT(*) as webhook_count FROM {$wpdb->prefix}wc_webhooks WHERE name = %s",

					self::HP_WEBHOOK_NAME

				) );

		}



		if ( $count > 0 ) {

			return self::HP_STATUS_OK;

		}



        return self::HP_STATUS_FAIL;

	}



	/**

	 * @return int

	 */

	private function check_WC_API_access() {



		global $wpdb;



		//if any keys are set

		$count    = $wpdb->get_var(

		    "SELECT COUNT(*) as key_count FROM {$wpdb->prefix}woocommerce_api_keys"

        );



		if ( $count == 0 ) {

			return self::HP_STATUS_FAIL;

		}



		// Get the API key with matching description

        $hoplixKey = esc_sql( $wpdb->esc_like( wc_clean( self::API_KEY_SEARCH_STRING ) ) );

		$queryArg = '%'. $wpdb->esc_like( $hoplixKey ) . '%';



        $key = $wpdb->get_row(

            $wpdb->prepare(

                "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE %s ORDER BY last_access LIMIT 1",

                $queryArg

            )

        );



		if ( ! empty( $key ) && $key->permissions == 'read_write' ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_WARNING;

	}



	/**

	 * @return int

	 */

	private function check_HP_API_key() {



		$option = get_option( 'woocommerce_hoplix_settings', array() );

		if ( ! empty( $option['hoplix_key'] ) && strlen( $option['hoplix_key'] ) == 36  && !empty( ! $option['hoplix_secret'] ) && strlen( $option['hoplix_secret'] ) == 36) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}



    /**

     * @return int

     * @throws HoplixException

     */

	private function check_PF_API_connect() {



		if ( Hoplix_Integration::instance()->is_connected(true) ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_NOT_CONNECTED;

	}



	/**

	 * @return int

	 */

	private function check_PHP_time_limit() {

		$time_limit = ini_get( 'max_execution_time' );



		if ( !$time_limit || $time_limit >= 30 ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}



	/**

	 * @return int

	 */

	private function check_HP_sync_errors() {



		$sync_log = get_option( Hoplix_Request_log::HP_OPTION_INCOMING_API_REQUEST_LOG, array() );

		if ( empty( $sync_log ) ) {

			return self::HP_STATUS_OK;    //no results means no errors

		}



		$sync_log = array_reverse( $sync_log );

		$sync_log = array_slice( $sync_log, 0, 6 );   //we only care about last to syncs



		foreach ( $sync_log as $sl ) {

			if ( ! empty( $sl['result'] ) && $sl['result'] == 'ERROR' ) {

				return self::HP_STATUS_FAIL;

			}

		}



		return self::HP_STATUS_OK;

	}



	/**

	 * @return int

	 */

	private function check_W3_db_cache() {



		if ( ! is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {

			return self::HP_STATUS_OK;

		}



		$w3tc_config_file =  plugin_dir_path( __DIR__ ) . 'w3tc-config/master.php';

		if ( file_exists( $w3tc_config_file ) && is_readable( $w3tc_config_file ) ) {

			$content = @file_get_contents( $w3tc_config_file );

			$config  = @json_decode( substr( $content, 14 ), true );



			if ( is_array( $config ) && ! empty( $config['dbcache.enabled'] ) ) {

				return ! $config['dbcache.enabled'];

			}

		}



		return self::HP_STATUS_OK;

	}



	/**

	 * @return int

	 */

	private function check_permalinks() {



		$permalinks = get_option( 'permalink_structure', false );



		if ( $permalinks && strlen( $permalinks ) > 0 ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}



	/**

	 * @return int

	 */

	private function check_wp_spamshield() {



		if ( ! is_plugin_active( 'wp-spamshield/wp-spamshield.php' ) ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}



	/**

	 * @return int

	 */

	private function check_remote_requests() {



		// Setup request args.

		$http_args = array(

			'method'      => 'POST',

			'timeout'     => MINUTE_IN_SECONDS,

			'redirection' => 0,

			'httpversion' => '1.0',

			'blocking'    => true,

			'user-agent'  => sprintf( 'WooCommerce/%s Hookshot (WordPress/%s)', WC_VERSION, $GLOBALS['wp_version'] ),

			'body'        => trim( json_encode( array( 'test' => true ) ) ),

			'headers'     => array( 'Content-Type' => 'application/json' ),

			'cookies'     => array(),

		);



		// Add custom headers.

		$http_args['headers']['X-WC-Webhook-Source'] = home_url( '/' ); // Since 2.6.0.

		$http_args['headers']['X-WC-Webhook-Topic']  = self::PF_REMOTE_REQUEST_TOPIC;



		// Webhook away!

		$response = wp_safe_remote_request( Hoplix_Base::get_hoplix_api_host() . self::HP_REMOTE_REQUEST_URL, $http_args );



		if ( is_wp_error( $response ) ) {

			return self::HP_STATUS_FAIL;

		}



		return self::HP_STATUS_OK;

	}



	/**

	 * @return int

	 */

	private function check_WC_auth_url_access() {

		$url       = home_url( '/' ) . 'wc-auth/v1/authorize?app_name=Hoplix&scope=read_write&user_id=1&return_url=https%3A%2F%2Fwww.hoplix.com%2Fdashboard%2Fwoocommerce%2Freturn&callback_url=https%3A%2F%2Fapi.hoplix.com%2Fhook%2Fwoocommerce-auth-callback';

		$http_args = array(

			'timeout'    => 60,

			'method'     => 'GET',

			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36',

		);



		$response = wp_safe_remote_get( $url, $http_args );



		if ( is_wp_error( $response ) ) {

			return self::HP_STATUS_FAIL;

		}



		$code = $response['response']['code'];



		if ( $code == 200 ) {

			return self::HP_STATUS_OK;

		}



		return self::HP_STATUS_FAIL;

	}



    /**

     * @return int

     */

	private function check_site_url_redirect()

    {



        $regular_site_url_req = wp_remote_head( get_option( 'siteurl' ) . '/wp-json/', array('redirection' => 0));



        //need to not trigger issues for subfolder wordpress setups

        $slashed_site_url_req = wp_remote_head( trailingslashit(get_option( 'siteurl' )), array('redirection' => 0));



        if (is_wp_error($regular_site_url_req) || is_wp_error($slashed_site_url_req)) {

            return self::PF_STATUS_FAIL;

        }



        /** @var WP_HTTP_Requests_Response $response */

        $regular_response = $regular_site_url_req['http_response'];



        /** @var WP_HTTP_Requests_Response $slashed_response */

        $slashed_response = $slashed_site_url_req['http_response'];



        if ($regular_response->get_status() == 200 || $slashed_response->get_status() == 200) {

            return self::HP_STATUS_OK;

        }



        if (in_array($regular_response->get_status(), array(301, 302, 303, 307))) {

            return self::HP_STATUS_FAIL;

        }



        return self::HP_STATUS_WARNING;

    }

    

}