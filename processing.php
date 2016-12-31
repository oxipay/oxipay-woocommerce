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

parse_str($_SERVER['QUERY_STRING'], $query);

function generate_processing_form($query) {
    $url = htmlentities($query["gateway_url"]);

    echo "<form id='oxipayload' method='post' action='$url'>";

    foreach ($query as $i => $v) {
        $item = htmlentities($i);
        $value = htmlentities($v);

        if (substr($item, 0, 2) === "x_") {
            echo "<input id='$item' name='$item' value='$value' type='hidden'/>";
        }
    }

    echo "</form>";
    echo "<script>document.getElementById('oxipayload').submit();</script>";
}

generate_processing_form($query);

?>

</body>

</html>