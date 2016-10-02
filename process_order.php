<?php
include_once( 'config.php' );
$json = file_get_contents('php://input');
$post = json_decode($json);

$order = wc_get_order( $post->order_id );
$status = $order->get_status();

$processing_status = $post->result; //need to check this 

switch ($processing_status) {

    case 'APPROVED':
        $order->add_order_note( __( 'Payment approved using ' . $config['OXIPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
        $order->payment_complete($response->id);
        woocommerce_empty_cart();

    case 'PENDING':
        $order->add_order_note( __( 'Payment pending using ' . $config['OXIPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
        $order->update_status('on-hold');

    case 'DECLINED':
        $order->add_order_note( __( 'Payment declined using ' . $config['OXIPAY_DISPLAYNAME'] . '. Your Order ID is '. $order->id, 'woocommerce' ) );
        $order->update_status('failed');
}

?>
