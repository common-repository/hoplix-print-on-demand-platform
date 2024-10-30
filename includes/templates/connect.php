<div class="hoplix-connect">



    <div class="hoplix-connect-inner">



        <h1><?php esc_html_e('Connect to Hoplix', 'hoplix'); ?></h1>



        <img src=" <?php echo esc_url(Hoplix_Base::get_asset_url() . 'images/connect.png'); ?>" class="connect-image" alt="connect to hoplix">



        <?php

        //$issues  =   "";

        if ( ! empty( $issues ) ) {

            ?>

            <p><?php esc_html_e('To connect your store to Hoplix, fix the following errors:', 'hoplix'); ?></p>

            <div class="hoplix-notice">

                <ul>

                    <?php

                    foreach ( $issues as $issue ) {

                        echo '<li>' . wp_kses_post( $issue ) . '</li>';

                    }

                    ?>

                </ul>

            </div>

            <?php

            $url = '#';

        } else {

            ?>

            <p class="connect-description"><?php esc_html_e('You\'re almost done! Just 2 more steps to have your WooCommerce store connected to Hoplix for automatic order fulfillment and import product.', 'hoplix'); ?></p><?php

            //$url = Hoplix_Base::get_hoplix_host() . '/woocommerce-print-on-demand-integration?website=' . urlencode( trailingslashit( get_home_url() ) ) . '&key=' . urlencode( $consumer_key ) . '&returnUrl=' . urlencode( get_admin_url( null,'admin.php?page=' . Hoplix_Admin::MENU_SLUG_DASHBOARD ) );
            $url = Hoplix_Base::get_hoplix_host() . '/woocommerce-print-on-demand-integration?website=' . trailingslashit( get_home_url() )  . '&key=' . $consumer_key . '&returnUrl=' .  get_admin_url( null,'admin.php?page=' . Hoplix_Admin::MENU_SLUG_DASHBOARD ) ;

        }



        echo '<a href="' . esc_url($url) . '" class="button button-primary hoplix-connect-button ' . ( ! empty( $issues ) ? 'disabled' : '' ) . '" target="_blank">' . esc_html__('Connect', 'hoplix') . '</a>';

        ?>



        <img src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ) ?>" class="loader hidden" width="20px" height="20px" alt="loader"/>

        

        <script type="text/javascript">

            jQuery(document).ready(function () {

                Hoplix_Connect.init('<?php echo esc_url( admin_url( 'admin-ajax.php?action=ajax_force_check_connect_status' ) ); ?>');

            });

        </script>

    </div>

</div>
