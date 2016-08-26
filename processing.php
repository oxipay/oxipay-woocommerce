<!-- MIT Licensing -->

<html>
    <head>
        <title>Processing Payment</title>
    </head>
    <body>
        <h2>Please wait, we are processing your purchase...</h2>
   
        <div id="spinner"></div>

        <!-- PHP code to post to payment gateway -->
        <?php
            include_once( 'config.php' );

            $full_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url = 'http://localhost:60343/Checkout?platform=WooCommerce';

            $parts = parse_url($full_url, PHP_URL_QUERY);
            parse_str($parts, $params);

            $jparams = json_encode($params);
            echo $jparams;

            echo "<form id='xpay_payload' method='post' action='$url'>";
            echo "<input id='payload' name='payload' value='$jparams' type='hidden'/>";
            echo "</form>";
        ?>

        <!-- spinner animation -->
        <!--<script type="text/javascript" src="spin.min.js"></script>

        <script>
            var opts = {
                lines: 13 // The number of lines to draw
                , length: 28 // The length of each line
                , width: 14 // The line thickness
                , radius: 42 // The radius of the inner circle
                , scale: 1 // Scales overall size of the spinner
                , corners: 1 // Corner roundness (0..1)
                , color: '#000' // #rgb or #rrggbb or array of colors
                , opacity: 0.25 // Opacity of the lines
                , rotate: 0 // The rotation offset
                , direction: 1 // 1: clockwise, -1: counterclockwise
                , speed: 1 // Rounds per second
                , trail: 60 // Afterglow percentage
                , fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
                , zIndex: 2e9 // The z-index (defaults to 2000000000)
                , className: 'spinner' // The CSS class to assign to the spinner
                , top: '50%' // Top position relative to parent
                , left: '50%' // Left position relative to parent
                , shadow: true // Whether to render a shadow
                , hwaccel: true // Whether to use hardware acceleration
                , position: 'absolute' // Element positioning
            }
            var target = document.getElementById('spinner')
            var spinner = new Spinner(opts).spin(target);
        </script>-->
        <script>
            document.getElementById('xpay_payload').submit();
        </script>
    </body>

</html>