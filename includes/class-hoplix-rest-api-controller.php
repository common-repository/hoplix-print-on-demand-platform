<?php


 
/**

 * API class

 */

class Hoplix_REST_API_Controller extends WC_REST_Controller

{

    /**

     * Endpoint namespace.

     *

     * @var string

     */

    protected $namespace = 'wc/v2';



    /**

     * Route base.

     *

     * @var string

     */

    protected $rest_base = 'hoplix';

    

    

    /**

     * Register the REST API routes.

     */

    public function register_routes() {

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/access', array(

            array(

                'methods' => WP_REST_Server::EDITABLE,

                'callback' => array( $this, 'set_hoplix_access' ),

                'permission_callback' => array( $this, 'get_items_permissions_check' ),

                'show_in_index' => false,

                'args' => array(

                    'accessKey' => array(

                        'required' => false,

                        'type' => 'string',

                        'description' => __( 'Hoplix access key', 'hoplix' ),

                    ),

                    'secretKey' => array(

                        'required' => false,

                        'type' => 'string',

                        'description' => __( 'Hoplix secret key', 'hoplix' ),

                    ),

                    'storeId' => array(

                        'required' => false,

                        'type' => 'integer',

                        'description' => __( 'Store Identifier', 'hoplix' ),

                    ),

                ),

            )

        ) );



        register_rest_route( $this->namespace, '/' . $this->rest_base . '/version', array(

            array(

                'methods' => WP_REST_Server::READABLE,

                'permission_callback' => array( $this, 'get_items_permissions_check' ),

                'callback' => array( $this, 'get_version' ),

                'show_in_index' => false,

            )

        ) );



        register_rest_route( $this->namespace, '/' . $this->rest_base . '/store_data', array(

            array(

                'methods' => WP_REST_Server::READABLE,

                'permission_callback' => array( $this, 'get_items_permissions_check' ),

                'callback' => array( $this, 'get_store_data' ),

                'show_in_index' => true,

            )

        ) );
        
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/store_data_info', array(

            array(

                'methods' => WP_REST_Server::READABLE,

                'permission_callback' => array( $this, 'get_items_permissions_check' ),

                'callback' => array( $this, 'get_hoplix_info' ),

                'show_in_index' => true,

            )

        ) );

    }

    

    /**

     * @param WP_REST_Request $request

     * @return array

     */

    public static function set_hoplix_access( $request )

    {

        $error = false;



	    $options = get_option( 'woocommerce_hoplix_settings', array() );



        $api_key        = $request->get_param('hoplix_key');

        $api_secretkey  = $request->get_param('hoplix_secret');

        $store_id       = $request->get_param('hoplix_store_id');
        
        $paymentAgreed  =   $request->get_param('hoplix_paymentagreed');

        $store_id       = intval( $store_id );
        
        


        if ( ! is_string( $api_key ) || strlen( $api_key ) == 0 || $store_id == 0 ) {

            $error = 'Failed to update access data';

        }



	    $options['hoplix_key']      = $api_key;

	    $options['hoplix_secret']   = $api_secretkey;

	    $options['hoplix_store_id'] = $store_id;
        
        $options['hoplix_paymentagreed']   =   $paymentAgreed;


        Hoplix_Integration::instance()->update_settings( $options );



        return array(

            'error' => $error,

        );

    }

    

    /**

     * Get necessary store data

     * @return array

     */

    public static function get_store_data() {

        return array(

            'website'   => get_site_url(),

            'version'   => WC()->version,

            'name'      => get_bloginfo( 'title', 'display' )

        );

    }
    /**

     * Get necessary hoplix information

     * @return array

     */

    public static function get_hoplix_info() {

        return array(

            'website'   => get_site_url(),

            'version'   => WC()->version,

            'name'      => get_bloginfo( 'title', 'display' ),
            
            'options'   => get_option( 'woocommerce_hoplix_settings', array() ),

        );

    }

    

    /**

     * Check whether a given request has permission to read hoplix endpoints.

     *

     * @param  WP_REST_Request $request Full details about the request.

     * @return WP_Error|boolean

     */

    public function get_items_permissions_check( $request ) {

        if ( ! wc_rest_check_user_permissions( 'read' ) ) {

            return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );

        }



        return true;

    }

    

    /**

     * Check if a given request has access to update a product.

     *

     * @param  WP_REST_Request $request Full details about the request.

     * @return WP_Error|boolean

     */

    public function update_item_permissions_check( $request ) {

        $params = $request->get_url_params();

        $product = wc_get_product( (int) $params['product_id'] );



        if ( empty( $product ) && ! wc_rest_check_post_permissions( 'product', 'edit', $product->get_id() ) ) {

            return new WP_Error( 'woocommerce_rest_cannot_edit', __( 'Sorry, you are not allowed to edit this resource.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );

        }



        return true;

    }

}