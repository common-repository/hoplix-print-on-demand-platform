

<h2>Export your order to Hoplix</h2>

<pre>

<?php  

    /*$query = new WC_Order_Query( array(

	'limit' => -1,

	'type' => 'shop_order',

	'status' => 'processing',

    ) );

    $processing_orders = $query->get_orders();*/

    //$wcOrders    =   wc_get_order(array());  

    //var_dump($orderswc);

?>

</pre>

<?php if ( ! empty( $orderswc ) ): ?>



    <table class="wp-list-table widefat fixed striped hoplix-latest-orders">

        <thead>

            <tr>

                <th class="col-order"><?php esc_html_e('Order', 'hoplix'); ?></th>

                <th class="col-date"><?php esc_html_e('Date', 'hoplix'); ?></th>

                <th class="col-from"><?php esc_html_e('From', 'hoplix'); ?></th>

                <th class="col-status"><?php esc_html_e('Status', 'hoplix'); ?></th>

                <th class="col-total"><?php esc_html_e('Total', 'hoplix'); ?></th>

                <th class="col-actions"><?php esc_html_e('Actions', 'hoplix'); ?></th>

            </tr>

        </thead>

        <tbody>

            <?php 

            foreach ( $orderswc as $wcorder ) : 

                if(is_array($orders) && !in_array($wcorder->get_id(),$orders)) :

                    

            ?>

            <tr>

                <td>

                    <?php 

                    echo '<a href="' . esc_url( get_edit_post_link($wcorder->get_id())) . '">';

                    echo '#' . esc_html($wcorder->get_id());

                    echo '</a>';

                    ?>

                    

                </td>

                <td>

                    <?php echo date("d-m-Y", strtotime($wcorder->get_date_created())); ?>

                </td>

                <td>

                    <?php echo $wcorder->get_address()["first_name"]." ".$wcorder->get_address()["last_name"]; ?>

                </td>

                <td><?php echo $wcorder->get_status(); ?></td>

                <td><?php echo $wcorder->get_formatted_order_total(); ?></td>

                <td>
                    <p class="hoplix-submit">
                        
                        <input name="save" id="<?php echo $wcorder->get_id(); ?>" class="button-primary woocommerce-save-button hoplix-woocommerce-export-order" type="button" value="<?php esc_attr_e('Export Order', 'hoplix'); ?>"/>

                        <span class="loader-wrap loader-wrap-<?php echo $wcorder->get_id(); ?>">

                            <img src="<?php echo esc_url( admin_url( 'images/spinner-2x.gif' ) ) ?>" class="loader" width="20px" height="20px" alt="loader"/>

                            <span class="pass">

                                <span class="dashicons dashicons-yes"></span>

                                <?php esc_html_e('Exported successfully', 'hoplix'); ?>

                            </span>

                            <span class="fail">

                            </span>

                        </span>
                        
                    </p>
                </td>

            </tr>

                <?php endif; ?>

            <?php endforeach; ?>

        </tbody>

        <tfoot>

            <tr>

                <th class="col-order"><?php esc_html_e('Order', 'hoplix'); ?></th>

                <th class="col-date"><?php esc_html_e('Date', 'hoplix'); ?></th>

                <th class="col-from"><?php esc_html_e('From', 'hoplix'); ?></th>

                <th class="col-status"><?php esc_html_e('Status', 'hoplix'); ?></th>

                <th class="col-total"><?php esc_html_e('Total', 'hoplix'); ?></th>

                <th class="col-actions"><?php esc_html_e('Actions', 'hoplix'); ?></th>

            </tr>

        </tfoot>

    </table>

<script type="text/javascript">

    jQuery(document).ready(function () {

        Hoplix_Orders.init_submit();

    });

</script>

<?php else: ?>

    <div class="hoplix-latest-orders">

        <p><?php esc_html_e('Once your store gets some Hoplix product orders, they will be shown here!', 'hoplix'); ?></p>

    </div>

<?php endif; ?>