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
function oxipay_sign($query, $api_key )
{
    $clear_text = '';
    ksort($query);
    foreach ($query as $key => $value) {
        if (substr($key, 0, 2) === "x_") {
            $clear_text .= $key . $value;
        }
    }
    $hash = hash_hmac( "sha256", $clear_text, $api_key);
    return str_replace('-', '', $hash);
}

/**
 * validates and associative array that contains a hmac signature against an api key
 * @param $query array
 * @param $api_key string
 * @return bool
 */
function oxipay_checksign($query, $api_key)
{
    $actualSignature = $query['x_signature'];
    unset($query['x_signature']);
    $expectedSignature = oxipay_sign($query, $api_key);
    return $actualSignature == $expectedSignature;
}

function generate_processing_form($query) {
    $url = htmlentities($query["gateway_url"]);

    echo "<form id='oxipayload' method='post' action='$url'>";

    foreach ($query as $item => $value) {
        $i = htmlentities($item);
        $v = htmlentities($value);

        if (substr($item, 0, 2) === "x_") {
            echo "<input id='$i' name='$i' value='$v' type='hidden'/>";
        }
    }

    echo "</form>";
    echo "<script>document.getElementById('oxipayload').submit();</script>";
}
?>