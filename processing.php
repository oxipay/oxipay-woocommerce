<!-- MIT Licensing -->

<html>
    <head>
        <title>Processing Payment</title>
    </head>
    <body>
        <h2>Please wait, we are processing your purchase...</h2>
   
        <div id="spinner"></div>

        <?php
            include_once( 'config.php' );

            $full_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = 'http://localhost:60343/Checkout?platform=WooCommerce'; //todo: from config

            $parts = parse_url($full_url, PHP_URL_QUERY);
            parse_str($parts, $params);

            $jparams = json_encode($params);
            echo $jparams;

            echo "<form id='oxipay_payload' method='post' action='$url'>";
            echo "<input id='payload' name='payload' value='$jparams' type='hidden'/>";
            echo "</form>";
        ?>
        <!-- TODO: INCORPORATE PROCESSING.HTML -->
        <script>
            document.getElementById('oxipay_payload').submit();
        </script>
    </body>

</html>