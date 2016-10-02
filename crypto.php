<?php
/**
 * Created by PhpStorm.
 * User: trowri
 * Date: 2/10/2016
 * Time: 3:24 PM
 */

/**
 * Generates a HMAC based on the merchants api key and the request
 * @param $query
 * @param $api_key
 * @return mixed
 */
function oxipay_sign($query, $api_key ) {
    $clear_text = '';
    ksort($query);
    foreach ($query as $key => $value) {
        $clear_text .= $key . $value;
    }
    //WooCommerce v3 requires &. Refer: http://stackoverflow.com/questions/31976059/woocommerce-api-v3-authentication-issue
    $secret = $api_key . '&';
    $hash = base64_encode( hash_hmac( "sha256", $clear_text, $secret, true ));
    return str_replace('+', '', $hash);
}

/**
 * validates and associative array that contains a hmac signature against an api key
 * @param $query array
 * @param $api_key string
 * @return bool
 */
public function oxipay_checksign($query, $api_key) {
    $actualSignature = $query['x_signature'];
    unset($query['x_signature']);
    $expectedSignature = oxipay_sign($query, $api_key);
    return $actualSignature == $expectedSignature;
}
?>