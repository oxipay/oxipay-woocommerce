<?php

include_once( 'config.php' );

add_filter( 'woocommerce_thankyou_order_id',array($this,'finalised_payment'));

function finalised_payment($order_id, $processing_status) {
    $order = wc_get_order( $order_id );
    $status = $order->get_status();

    $response = wp_remote_get($config['XPAY_URL'] . $order_id . '.json');
    $body = json_decode( wp_remote_retrieve_body($response) );



    $processing_status = 

    // Get the status of the order from XPay and handle accordingly
    switch ($processing_status) {

        case 'APPROVED':
            $order->payment_complete($response->id);
            woocommerce_empty_cart();

        case 'PENDING':
            $order->update_status('on-hold');

        case 'DECLINED':
            $order->update_status('failed');
    }

    return $order_id;

}
?>