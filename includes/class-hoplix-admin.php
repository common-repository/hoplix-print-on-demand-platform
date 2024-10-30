<?php

if ( ! defined( 'ABSPATH' ) ) exit;



class Hoplix_Admin{

    

    const MENU_TITLE_TOP = 'Hoplix';

	const PAGE_TITLE_DASHBOARD = 'Dashboard';

	const MENU_TITLE_DASHBOARD = 'Dashboard';

	const MENU_SLUG_DASHBOARD = 'hoplix-dashboard';

	const CAPABILITY = 'manage_options';

    

    public static function init() {

		$admin = new self;

		$admin->register_admin();

	}

    

    /**

     * Register admin scripts

     */

	public function register_admin() {



		add_action( 'admin_menu', array( $this, 'register_admin_menu_page' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_styles' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'add_global_style' ) );

    }

    

    /**

     * Loads stylesheets used in hoplix admin pages

     * @param $hook

     */

    public function add_admin_styles($hook) {

        

	    wp_enqueue_style( 'hoplix-global', plugins_url( '../assets/css/global.css', __FILE__ ) );

        

	    if ( strpos( $hook, 'hoplix-dashboard' ) !== false ) {

		    wp_enqueue_style( 'hoplix-dashboard', plugins_url( '../assets/css/dashboard.css', __FILE__ ) );

		    wp_enqueue_style( 'hoplix-settings', plugins_url( '../assets/css/settings.css', __FILE__ ) );
            
		    wp_enqueue_style( 'hoplix-order', plugins_url( '../assets/css/order.css', __FILE__ ) );

	    }

    }



	/**

	 * Loads stylesheet for hoplix toolbar element

	 */

    public function add_global_style() {

	    if ( is_user_logged_in() ) {

		    wp_enqueue_style( 'hoplix-global', plugins_url( '../assets/css/global.css', __FILE__ ) );

	    }

    }

    

    /**

	 * Loads scripts used in hoplix admin pages

	 * @param $hook

	 */

	public function add_admin_scripts($hook) {

		if ( strpos( $hook, 'hoplix-dashboard' ) !== false ) {

			//wp_enqueue_script( 'wp-color-picker' );

			wp_enqueue_script( 'hoplix-settings', plugins_url( '../assets/js/settings.js', __FILE__ ) );

			wp_enqueue_script( 'hoplix-connect', plugins_url( '../assets/js/connect.js', __FILE__ ) );

			wp_enqueue_script( 'hoplix-orders', plugins_url( '../assets/js/orders.js', __FILE__ ) );

			wp_enqueue_script( 'hoplix-block-loader', plugins_url( '../assets/js/block-loader.js', __FILE__ ) );

			wp_enqueue_script( 'hoplix-intercom', plugins_url( '../assets/js/intercom.min.js', __FILE__ ) );

		}

	}

    

    /**

     * Register admin menu pages

     */

	public function register_admin_menu_page() {



		add_menu_page(

			__( 'Dashboard', 'hoplix' ),

			self::MENU_TITLE_TOP,

			self::CAPABILITY,

			self::MENU_SLUG_DASHBOARD,

			array( 'Hoplix_Admin', 'route' ),

			Hoplix_Base::get_asset_url() . 'images/hoplix-menu-icon.png',

			58

		);

	}

    

    /**

     * Get the tabs used in hoplix admin pages

     * @return array

     * @throws HoplixException

     */

	public static function get_tabs() {



		$tabs = array(

			array( 'name' => __( 'Settings', 'hoplix' ), 'tab_url' => 'settings' ),

			array( 'name' => __( 'Order', 'hoplix' ), 'tab_url' => 'order' ),

		);



		if ( Hoplix_Integration::instance()->is_connected() ) {

			array_unshift( $tabs, array( 'name' => __( 'Dashboard', 'hoplix' ), 'tab_url' => false ) );

		} else {

			array_unshift( $tabs, array( 'name' => __( 'Connect', 'hoplix' ), 'tab_url' => false ) );

		}



		return $tabs;

	}

    

    /**

	 * Route the tabs

	 */

	public static function route() {



		$tabs = array(

			'dashboard' => 'Hoplix_Admin_Dashboard',

			'settings'  => 'Hoplix_Admin_Settings',

			'order'     => 'Hoplix_Admin_Order',

            'product'   => 'Hoplix_Admin_Product',

		);



		$tab = ( ! empty( sanitize_title_for_query( $_GET['tab'] ) ) ? sanitize_title_for_query( $_GET['tab'] ) : 'dashboard' );

		if ( ! empty( $tabs[ $tab ] ) ) {

			call_user_func( array( $tabs[ $tab ], 'view' ) );

		}

	}

    

    /**

	 * Load a template file. Extract any variables that are passed

	 * @param $name

	 * @param array $variables

	 */

	public static function load_template( $name, $variables = array() ) {



		if ( ! empty( $variables ) ) {

			extract( $variables );

		}

		$filename = plugin_dir_path( __FILE__ ) . 'templates/' . $name . '.php';

		if ( file_exists( $filename ) ) {

			include( $filename );

		}

	}

}