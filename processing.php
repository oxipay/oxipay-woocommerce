<html>
<head>
    <title>Processing Payment</title>
    <link rel="stylesheet" href="css/oxipay.css">
    <meta name="viewport" content="width=device-width">
</head>
<body>
<div id="top-bar">
    <img id="logo" src="images/oxipay.svg">
</div>
<div class="card">
    <div class="card-block card-heading"> <h4> Processing</h4> </div>
    <p>Please wait while we process your request</p>
    <div id="spinner"></div>
</div>

<!-- spinner animation -->
<script src="js/lib/spin.min.js"></script>
<script src="js/spinner.js"></script>

<?php
include_once( 'oxipay-config.php' );

parse_str($_SERVER['QUERY_STRING'], $query);

function oxipay_generate_processing_form($query) {
    if (!isset($query["gateway_url"])) {
        error_log('gateway_url is not specified');
        return;
    } 
    $url = base64_decode( $query["gateway_url"]); 
    $url = htmlspecialchars($url, ENT_QUOTES );

    echo "<form id='oxipayload' method='post' action='$url'>";

    $encodedFields = array(
       'x_url_callback',
       'x_url_complete',
       'gateway_url',
       'x_url_cancel'
    );

    foreach ($query as $i => $v) {
        $item  = htmlspecialchars($i, ENT_QUOTES );
        $value = null;
        if (in_array($item, $encodedFields)) {
            $value = htmlspecialchars(base64_decode($v), ENT_QUOTES);
        } else {
            $value = htmlspecialchars($v, ENT_QUOTES);
        }

        if (substr($item, 0, 2) === "x_") {
            echo sprintf('<input id="%s" name="%s" value="%s" type="hidden" />', $item, $item, $value);
        }
    }

    echo "</form>";
    echo "<script>document.getElementById('oxipayload').submit();</script>";
}

oxipay_generate_processing_form($query);

?>

</body>
</html>