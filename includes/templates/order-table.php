
<h2>Hoplix product orders</h2>
<?php if ( ! empty( $orders ) && $orders['count'] > 0 ): ?>

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

        <?php foreach ( $orders['results'] as $order ) : ?>

                <tr>
                    <td>
                        <?php
                        if ( $order['external_id'] ) {
	                        echo '<a href="' . esc_url( get_edit_post_link( $order['external_id'] ) ) . '">';
	                        echo '#' . esc_html( $order['external_id'] );
	                        echo '</a>';
                        } else {
	                        echo '#' . esc_html( $order['id'] );
                        }
                        ?>
                    </td>
                    <td>
	                    <?php echo esc_html( date('Y-m-d', strtotime($order['created'])) ); ?>
                    </td>
                    <td>
	                    <?php echo esc_html( $order['recipient']['name'] ); ?>
                    </td>
                    <td>
	                    <?php echo esc_html( ucfirst($order['status']) ); ?>
                    </td>
                    <td>
	                    <?php echo get_woocommerce_currency_symbol(); ?> <?php echo esc_html( $order['costs']['total'] ); ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(Hoplix_Base::get_hoplix_host()); ?>/account/orders?wc-order_id=<?php echo esc_attr($order['id']); ?>" target="_blank"><?php esc_html_e('Open in Hoplix', 'hoplix'); ?></a>
                    </td>
                </tr>

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

<?php else: ?>
    <div class="hoplix-latest-orders">
        <p><?php esc_html_e('Once your store gets some Hoplix product orders, they will be shown here!', 'hoplix'); ?></p>
    </div>
<?php endif; ?>