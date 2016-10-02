<?php

include_once( 'config.php' );
include_once( 'oxipay.php' );

function payment_finalisation($order_id, $oxipay_response) {
    $order = wc_get_order( $order_id );
    $status = $order->get_status();

    $response = wp_remote_get($config['OXIPAY_URL']);                         // Retrieve raw respnose from HTTP request
    $oxipay_response = json_decode( wp_remote_retrieve_body($response) );     // Get body of raw response; convert from json to array
    $status = oxipay_response."result";                                       // Only interested in the value of result (string)

    // Get the status of the order from XPay and handle accordingly
    switch ($oxipay_response) {

        case "completed":
            $order->add_order_note( __( 'Payment approved using ' . $config['OXIPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
            $order->payment_complete($response->id);
            woocommerce_empty_cart();

        case "failed":
            $order->add_order_note( __( 'Payment pending using ' . $config['OXIPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
            $order->update_status('on-hold');

        case "pending":
            $order->add_order_note( __( 'Payment declined using ' . $config['OXIPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
            $order->update_status('failed');
    }

    return $order_id;
}


?>