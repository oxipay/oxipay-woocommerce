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
include_once( 'config.php' );

$baseUrl = get_option('oxipay_gateway_url'); //todo: this doesn't work
$url = $baseUrl . OXIPAY_CHECKOUT_URL;
parse_str($_SERVER['QUERY_STRING'], $query);

echo "<form id='oxipay_payload' method='post' action='$url'>";

foreach ($query as $item => $value) {
    echo "<input id='$item' name='$item' value='$value' type='hidden'/>";
}

echo "</form>";

?>

<script>
    document.getElementById('oxipay_payload').submit();
</script>
</body>

</html>