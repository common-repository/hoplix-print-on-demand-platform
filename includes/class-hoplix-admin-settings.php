<?php

if ( ! defined( 'ABSPATH' ) ) exit;



class Hoplix_Admin_Settings {

    public static $_instance;

    /**

     * @return array

     */

	public static function getIntegrationFields()

    {
        if( Hoplix_Integration::instance()->get_option( 'hoplix_paymentagreed' ) == 1 ){
            
            $itemsPayment  =   [

                        'credit-card'   =>  'Credit card',

                        'wallet'        =>  'wallet',
                        
                        'paymentagreed' =>  'Payment agreed'

                    ];
            
        }else{
            
            $itemsPayment  =   [

                        'credit-card'   =>  'Credit card',

                        'wallet'        =>  'wallet',

                    ];
            
        }
        return array(

            'hoplix_key' => array(

                'title'         =>  __( 'Hoplix account API key', 'hoplix' ),

                'type'          =>  'text',

                'desc_tip'      =>  true,

                'description'   =>  __( 'Your account Hoplix API key. Create account on Hoplix', 'hoplix' ),

                'default'       =>  false,

            ),

            'hoplix_secret' => array(

                'title'         =>  __( 'Hoplix account Secret key', 'hoplix' ),

                'type'          =>  'text',

                'desc_tip'      =>  true,

                'description'   =>  __( 'Your account Hoplix Secret key. Create account on Hoplix', 'hoplix' ),

                'default'       =>  false,

            ),

            'hoplix_shipping_method' => array(

                'title'         =>  __( 'Shipping method' ),

                'type'          =>  'dropdown',

                'desc_tip'      =>  true,

                'description'   =>  __( 'Choose witch method use for shippings' ),

                'default'       =>  'economy_tracked',

                'selected'      =>  Hoplix_Integration::instance()->get_option( 'hoplix_shipping_method' ),

                'items'         =>  [

                    'tracked'   =>  'Economy',

                    'express'   =>  'Express'

                ],

                

            ),

            'hoplix_payment_method' => array(

                    'title'         =>  __( 'Payment method' ),

                    'type'          =>  'dropdown',

                    'desc_tip'      =>  true,

                    'description'   =>  __( 'Choose witch method use for pay exported order' ),

                    'default'       =>  'credit-card',

                    'selected'      =>  Hoplix_Integration::instance()->get_option( 'hoplix_payment_method' ),

                    'items'         =>  $itemsPayment,

            ),

        );

    }

    

    /**

	 * @return array

	 */

	public static function getAllFields() {

		return array_merge(self::getIntegrationFields());

    }



	/**

	 * @return Hoplix_Admin_Settings

	 */

	public static function instance() {



		if ( is_null( self::$_instance ) ) {

			self::$_instance = new self();

		}



		return self::$_instance;

	}



	/**

	 * Setup the view

	 */

	public static function view() {



		$settings = self::instance();

		$settings->render();

	}

    

    /**

	 * Display the view

	 */

	public function render() {



		Hoplix_Admin::load_template( 'header', array( 'tabs' => Hoplix_Admin::get_tabs() ) );



		echo '<form method="post" name="hoplix_settings" action="' . esc_url( admin_url( 'admin-ajax.php?action=save_hoplix_settings' ) ) . '">';



		// Integration settings

		$integration_settings = $this->setup_fields( __('Integration settings', 'hoplix'), '', self::getIntegrationFields() );

		Hoplix_Admin::load_template( 'setting-group', $integration_settings );



		//preferences settings

		Hoplix_Admin::load_template( 'ajax-loader', array( 'action' => 'get_hoplix_script', 'message' => 'Loading your preferences...' ) );



		Hoplix_Admin::load_template( 'setting-submit', array( 'nonce' => wp_create_nonce( 'hoplix_settings' ), 'disabled' => true ) );



        echo '</form>';



		Hoplix_Admin::load_template( 'footer' );

	}

    

    /**

     * @param string $title Settings section title

     * @param string $description Section description

     * @param array $fields

     *

     * @return array

     */

    public function setup_fields($title, $description = '', $fields = [])

    {

        $fieldGroup = array(

            'title'       => $title,

            'description' => $description,

            'settings'    => $fields,

        );



        foreach ( $fieldGroup['settings'] as $key => $setting ) {

            if ( $setting['type'] !== 'title' ) {

                $fieldGroup['settings'][ $key ]['value'] = Hoplix_Integration::instance()->get_option( $key, $setting['default'] );

            }

        }



        return $fieldGroup;

	}

    

    /**

     * Display the ajax content for carrier settings

     * @throws HoplixException

     */

	public static function render_script_ajax() {

		$enable_submit = 'Hoplix_Settings.enable_submit_btn();';

		Hoplix_Admin::load_template( 'inline-script', array( 'script' => $enable_submit ) );

		exit;

	}

    

    /**

     * Ajax endpoint for saving the settings

     * @throws HoplixException

     */

	public static function save_hoplix_settings() {

        if ( ! empty( $_POST ) ) {
            
            $paymentAgreed  =   Hoplix_Integration::instance()->get_option( 'hoplix_paymentagreed' );

            $options = array();

            //build save options list

            foreach ( self::getAllFields() as $key => $field ) {



				if ( $field['type'] == 'checkbox' ) {

					if ( isset( $_POST[ $key ] ) ) {

						$options[ sanitize_title_for_query( $key ) ] = 'yes';

					} else {

						$options[ sanitize_title_for_query( $key ) ] = 'no';

					}

				} else {

					if ( isset( $_POST[ $key ] ) ) {

						$options[ sanitize_title_for_query( $key ) ] = sanitize_text_field( $_POST[ $key ] );

					}

				}

			}

            
            $options['hoplix_paymentagreed']    =   $paymentAgreed;
            //save integration settings

			Hoplix_Integration::instance()->update_settings( $options );

            

            die('OK');

        }

        

    }

    

}



?>
