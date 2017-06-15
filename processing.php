<html>
<head>
    <title>Redirecting to Oxipay</title>
    <link rel="stylesheet" href="css/oxipay.css">
    <meta name="viewport" content="width=device-width">
    <script src="js/lib/spin.min.js"></script>    
</head>
<body>

    <div id="wrapper">
        <div class="card">
            <div class="card-block card-heading"> <h4>Redirecting</h4> </div>
            <p>Please wait while we redirect you to Oxipay</p>
            <div id="spinner"></div>
        </div>
    </div>

<script >
        var opts = {
            lines: 50 // The number of lines to draw
            , length: -5 // The length of each line
            , width: 14 // The line thickness
            , radius: 20 // The radius of the inner circle
            , scale: 1 // Scales overall size of the spinner
            , corners: 1 // Corner roundness (0..1)
            , color: '#e68821' // #rgb or #rrggbb or array of colors
            , opacity: 0 // Opacity of the lines
            , rotate: 0 // The rotation offset
            , direction: 1 // 1: clockwise, -1: counterclockwise
            , speed: 1 // Rounds per second
            , trail: 50 // Afterglow percentage
            , fps: 30 // Frames per second when using setTimeout() as a fallback for CSS
            , zIndex: 2e9 // The z-index (defaults to 2000000000)
            , className: 'spinner' // The CSS class to assign to the spinner
            , top: '50%' // Top position relative to parent
            , left: '50%' // Left position relative to parent
            , shadow: false // Whether to render a shadow
            , hwaccel: true // Whether to use hardware acceleration
            , position: 'absolute' // Element positioning
        };

        var target = document.getElementById('spinner');
        var spinner = new Spinner(opts).spin(target);
</script>    

<?php
include_once( 'oxipay-config.php' );

parse_str($_SERVER['QUERY_STRING'], $query);

function oxipay_generate_processing_form($query) {
    $url = htmlspecialchars( $query["gateway_url"], ENT_QUOTES );

    echo "<form id='oxipayload' method='post' action='$url'>";

    foreach ($query as $i => $v) {
        $item = htmlspecialchars( $i, ENT_QUOTES );
        $value = htmlspecialchars( $v, ENT_QUOTES );

        if (substr($item, 0, 2) === "x_") {
            echo "<input id='$item' name='$item' value='$value' type='hidden'/>";
        }
    }

    echo "</form>";
    echo "<script>document.getElementById('oxipayload').submit();</script>";
}

oxipay_generate_processing_form($query);

?>

</body>

</html>