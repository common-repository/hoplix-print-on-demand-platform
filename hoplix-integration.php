<?php

/**

Plugin Name: Hoplix Print On Demand Platform

Plugin URI: https://wordpress.org/plugins/hoplix-print-on-demand-platform/

Description: Print on Demand Application for sell merchanding product on your store!

Version: 1.0.4

Author: Hoplix

Author URI: http://www.hoplix.com

License: GPL2 http://www.gnu.org/licenses/gpl-2.0.html

Text Domain: hoplix

WC requires at least: 3.0.0

WC tested up to: 8.7.0

WordPress tested up to: 6.6.1

*/



if ( ! defined( 'ABSPATH' ) ) exit;

class Hoplix_Base{

    

    const VERSION = '1.0.4';

	const HP_HOST = 'https://www.hoplix.com/';

	const HP_API_HOST = 'https://api.hoplix.com/v1/';

    //const HP_DEV_API_HOST = 'https://testapi.hoplix.com/';

    

    /**

     * Construct the plugin.

     */

    public function __construct() {

        add_action( 'plugins_loaded', array( $this, 'init' ) );


        add_action( 'before_woocommerce_init', function() {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
            }
        } );
        
    }



    /**

     * Initialize the plugin.

     */

    public function init() {



        if (!class_exists('WC_Integration')) {

            return;

        }

        //load required classes

        require_once 'includes/class-hoplix-integration.php';

        require_once 'includes/class-hoplix-admin.php';

        require_once 'includes/class-hoplix-admin-dashboard.php';

        require_once 'includes/class-hoplix-admin-settings.php';

        require_once 'includes/class-hoplix-admin-status.php';

        require_once 'includes/class-hoplix-admin-order.php';
        

        


        //launch init

        Hoplix_Admin::init();



       //hook ajax callbacks

       add_action( 'wp_ajax_save_hoplix_settings', array( 'Hoplix_Admin_Settings', 'save_hoplix_settings' ) );

       add_action( 'wp_ajax_get_hoplix_orders', array( 'Hoplix_Admin_Dashboard', 'render_orders_ajax' ) );

       add_action( 'wp_ajax_get_hoplix_stats', array( 'Hoplix_Admin_Dashboard', 'render_stats_ajax' ) );

	   add_action( 'wp_ajax_get_hoplix_script', array( 'Hoplix_Admin_Settings', 'render_script_ajax' ) );

	   add_action( 'wp_ajax_export_order_hoplix', array( 'Hoplix_Admin_Order', 'export_order_hoplix' ) );
        
       add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 20);

    }

    

    /**

	 * @return string

	 */

	public static function get_hoplix_api_host() {

		if ( defined( 'HP_DEV_API_HOST' ) ) {

			return HP_DEV_API_HOST;

		}



		return self::HP_API_HOST;

	}

    

    /**

	 * @return string

	 */

    public static function get_hoplix_host(){

        if ( defined( 'HP_DEV_API_HOST' ) ) {

			return "https://test.hoplix.com";

		}

        return self::HP_HOST;

    }

    

   /**

   * @return string

   */

    public static function get_asset_url() {

      return trailingslashit(plugin_dir_url(__FILE__)) . 'assets/';

    }

    

    private function rest_api_init()

    {

        // REST API was included starting WordPress 4.4.

        if ( ! class_exists( 'WP_REST_Server' ) ) {

            return;

        }



        // Init REST API routes.

        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 20);

    }



    public function register_rest_routes()

    {

        require_once 'includes/class-hoplix-rest-api-controller.php';



        $hoplixRestAPIController = new Hoplix_REST_API_Controller();

        $hoplixRestAPIController->register_routes();

    }

}



new Hoplix_Base();

?>
