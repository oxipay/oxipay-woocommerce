<?php

include_once( 'config.php' );
include_once( 'xpay.php' );

function payment_finalisation($order_id) {
    $order = wc_get_order( $order_id );
    $status = $order->get_status();

    $response = wp_remote_get($config['XPAY_URL'] . $order_id . '.json');
    $body = json_decode( wp_remote_retrieve_body($response) );

    $processing_status = 

    Get the status of the order from XPay and handle accordingly
    switch ($processing_status) {

        case 'APPROVED':
            $order->add_order_note( __( 'Payment approved using ' . $config['XPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
            $order->payment_complete($response->id);
            woocommerce_empty_cart();

        case 'PENDING':
            $order->add_order_note( __( 'Payment pending using ' . $config['XPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
            $order->update_status('on-hold');

        case 'DECLINED':
            $order->add_order_note( __( 'Payment declined using ' . $config['XPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
            $order->update_status('failed');
    }

    return $order_id;

}
?>