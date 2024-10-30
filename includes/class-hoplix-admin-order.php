<?php

if ( ! defined( 'ABSPATH' ) ) exit;



class Hoplix_Admin_Order {

    

    public static $_instance;

    

    /**

	 * @return Hoplix_Admin_Order

	 */

	public static function instance() {



		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();

		}



		return self::$_instance;

	}

    

    

    /**

     * Show the view

     * @throws HoplixException

     */

	public static function view() {



		$orders = self::instance();

        $dashboard  =   Hoplix_Admin_Dashboard::instance();

        $api_key = Hoplix_Integration::instance()->get_option( 'hoplix_key' );

		$connect_status = Hoplix_Integration::instance()->is_connected();

        if ( $connect_status ) {

			$orders->render();

		} else if(!$connect_status && strlen($api_key) > 0) {

			$dashboard->render_connect_error();

		} else {

			$orders->render();

		}

	}

    

    

    /**

	 * Display the dashboard

	 */

	public function render() {



		Hoplix_Admin::load_template( 'header', array( 'tabs' => Hoplix_Admin::get_tabs() ) );

		$orders     = $this->_get_orders_ids();

        $orderswc   = $this->_get_orders_wc();

		$error = false;

		if ( ! $error ) {

            echo '<form method="post" name="hoplix_order_export" action="' . esc_url( admin_url( 'admin-ajax.php?action=export_order_hoplix' ) ) . '"></form>';
            
            
            Hoplix_Admin::load_template( 'order-table-wc', array( 'orderswc' => $orderswc, 'orders' => $orders ) );

			

            Hoplix_Admin::load_template( 'ajax-loader', array( 'action' => 'get_hoplix_orders', 'message' => __( 'Loading your orders...', 'hoplix' ) ) );

            



		} else {

			Hoplix_Admin::load_template( 'error', array( 'error' => $error->get_error_message('hoplix') ) );

		}



        Hoplix_Admin::load_template( 'quick-links' );



		if ( isset( $_GET['sync-in-progress'] ) ) {

			$emit_auth_response = 'Hoplix_Connect.send_return_message();';

			Hoplix_Admin::load_template( 'inline-script', array( 'script' => $emit_auth_response ) );

		}



		Hoplix_Admin::load_template( 'footer' );

	}

    
    /**

	 * Get Hoplix orders from the API only ids of woocommerce for exclude already exported orders

	 * @return array

	 */
    private function _get_orders_ids(){
        try {

			$orders = Hoplix_Integration::instance()->get_client()->get( 'wc-orders-ids' );
            if(!is_array($orders)){
                $orders =   array();
            }
        } catch (HoplixApiException $e) {
            
            return new WP_Error('hoplix', 'Could not connect to Hoplix API. Please try again later!');

		} catch (HoplixException $e) {

			return new WP_Error('hoplix', 'Could not connect to Hoplix API. Please try again later!');

		}
        
        return $orders;
    }
    
    /**

	 * Get Hoplix orders from the API

	 * @return mixed

	 */

	private function _get_orders($only_cached_results = false) {



		$orders = get_transient( 'hoplix_orders' );



		if ( $only_cached_results || $orders ) {

			return $orders;

		}



		try {

			$order_data = Hoplix_Integration::instance()->get_client()->get( 'wc-orders' );



			if ( ! empty( $order_data ) ) {



				foreach ( $order_data as $key => $order ) {



					if(strtolower($order['status']) == 'pending' && strtolower($order['payment-status']) == 'ok') {

						$order_data[$key]['status'] = 'Waiting for fulfillment';

					}elseif(strtolower($order['status']) == 'pending' && strtolower($order['payment-status']) == 'pe'){
                        
                        $order_data[$key]['status'] = 'Waiting for payment';
                        
                    }else{
                        
                       $order_data[$key]['status'] =  $order['status'];
                           
                    }

				}

			}



			$orders = array( 'count' => count( $order_data ), 'results' => $order_data );

			set_transient( 'hoplix_orders', $orders, MINUTE_IN_SECONDS * 5 ); //cache for 5 minute

		} catch (HoplixApiException $e) {

			return new WP_Error('hoplix', 'Could not connect to Hoplix API. Please try again later!');

		} catch (HoplixException $e) {

			return new WP_Error('hoplix', 'Could not connect to Hoplix API. Please try again later!');

		}



		return $orders;

	}

    

    /**

     * Get Woocommerce orders

     * @param bool $only_cached_results

	 * @return mixed

     */

    private function _get_orders_wc(){

        $params =   array('limit' => -1,

                           'type' => 'shop_order',

                           'status' => 'processing',

                        );

        $query = new WC_Order_Query( array('limit' => -1,

                                           'type' => 'shop_order',

                                           'status' => 'processing',

                                        ) );
        $orders =   array();
        
        foreach($query->get_orders() as $order){
            
            foreach($order->get_items() as $item_key=>$item){
                
                    $product = wc_get_product($item->get_product_id());
                
                    if(strpos($product->get_sku(), "HP") !== false){
                        
                        $orders[]   =   array("id"          =>  $order->get_id(),
                                              "date"        =>  $order->get_date_created(),
                                              "customer"    =>   $order->get_address()["first_name"]." ".$order->get_address()["last_name"],
                                              "status"      =>  $order->get_status(),
                                              "amount"      =>  $order->get_formatted_order_total(),
                                             );
                        
                    }
            }
            
        };

        return $orders;

    }
    
    

    /**

     * Ajax endpoint for post order details to hoplix api

     * @throws HoplixException

     */

    public static function export_order_hoplix(){

        if ( ! empty( $_POST ) ) {

            $order = wc_get_order( sanitize_title_for_query( $_POST["id"] ) );

            $shipping   =   array("name" 		=> $order->get_address()["first_name"], 

                    "surname" 		=> $order->get_address()["last_name"],

                    "address" 		=> $order->get_address()["address_1"],

                    "address-more"  => $order->get_address()["address_2"],

                    "zip-code" 		=> $order->get_address()["postcode"],

                    "city" 			=> $order->get_address()["city"],

                    "province" 		=> $order->get_address()["state"],

                    "country-code" 	=> $order->get_address()["country"]

                     );

            $products   =   array();

            foreach($order->get_items() as $item_key=>$item){

                $product = wc_get_product($item->get_product_id());

                    if(strpos($product->get_sku(), "HP") !== false){
                        $products[] =   array("campaign-id" => $product->get_meta("hoplix-campaign-id"),

                                              "product-id" => str_replace("HP","",$product->get_sku()),

                                              "product-color" => (!empty($item->get_product()->get_data()["attributes"]["pa_color"])) ? $item->get_product()->get_data()["attributes"]["pa_color"] : $item->get_product()->get_data()["attributes"]["hp_color"],

                                              "product-size" => (!empty($item->get_product()->get_data()["attributes"]["pa_size"])) ? strtoupper($item->get_product()->get_data()["attributes"]["pa_size"]) : $item->get_product()->get_data()["attributes"]["hp_size"],

                                              "quantity" => $item->get_quantity()

                                             );
                        
                    }
                
            }

            $orderExportData    =   array("type"    =>   "woocommerce",
                                          "order"   =>  array("products" => $products,

                                                         "shipping-mode" => Hoplix_Integration::instance()->get_option( 'hoplix_shipping_method' ),

                                                         "order_reference" => $order->get_id(),

                                                         "payment-method" => Hoplix_Integration::instance()->get_option( 'hoplix_payment_method' ),

                                                         "shipping-info" => $shipping,

                                                         )
                                         );
            
            
            
            $return =   Hoplix_Integration::instance()->get_client()->post( 'create-order', $orderExportData, array());
            
            if($return  ==  200){
                echo "OK";
            }else{
                echo 'Could not connect to Hoplix API. Please try again later!';
            }

        };

        

    }

    

}



?>