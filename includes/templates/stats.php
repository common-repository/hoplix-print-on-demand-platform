<div class="hoplix-stats">
	<div class="hoplix-stats-item">

		<h4><?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_html($stats['orders_today']['total']); ?></h4>

        <b>

            <?php

            echo esc_html($stats['orders_today']['orders']);

            echo ' ' . _n('ORDER', 'ORDERS', $stats['orders_today']['orders'], 'hoplix' );

            ?>

        </b>

        <?php esc_html_e('today', 'hoplix'); ?>

	</div>

	<div class="hoplix-stats-item">

        <h4>

            <?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_html($stats['orders_last_7_days']['total']); ?>

	        <?php echo '<span class="dashicons dashicons-arrow-' . esc_attr($stats['orders_last_7_days']['trend']) .'-alt"></span>'; ?>

        </h4>

        <b>

            <?php

            echo esc_html($stats['orders_last_7_days']['orders']);

            echo ' ' . _n( 'ORDER', 'ORDERS', $stats['orders_last_7_days']['orders'], 'hoplix' );

            ?>

        </b>

        <?php esc_html_e('last 7 days', 'hoplix'); ?>

	</div>

	<div class="hoplix-stats-item">

        <h4>

            <?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_html($stats['orders_last_28_days']['total']); ?>

	        <?php echo '<span class="dashicons dashicons-arrow-' . esc_attr($stats['orders_last_28_days']['trend']) .'-alt"></span>'; ?>

        </h4>

        <b>

	        <?php

	        echo esc_html($stats['orders_last_28_days']['orders']);

	        echo ' ' . _n( 'ORDER', 'ORDERS', $stats['orders_last_28_days']['orders'], 'hoplix' );

	        ?>

        </b> <?php esc_html_e('last 28 days', 'hoplix'); ?>

	</div>

    <?php /*

	<div class="hoplix-stats-item">

        <h4>

            <?php echo esc_html(get_woocommerce_currency_symbol($stats['currency'])) . ' ' . esc_attr($stats['profit_last_28_days']); ?>

	        <?php echo '<span class="dashicons dashicons-arrow-' . esc_attr($stats['profit_trend_last_28_days']) .'-alt"></span>'; ?>

        </h4>

        <b><?php esc_html_e('PROFIT', 'hoplix'); ?></b> <?php esc_html_e('last 28 days', 'hoplix'); ?>

	</div> */ ?>

</div>