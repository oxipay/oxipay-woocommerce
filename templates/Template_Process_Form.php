<html lang="en">
<head>
    <title>Redirecting to Oxipay</title>
    <link rel="stylesheet" href="../assets/css/oxipay.css">
    <meta name="viewport" content="width=device-width">
    <script src="../assets/js/lib/spin.min.js"></script>
</head>
<body>

<div id="wrapper">
    <div class="card">
        <div class="card-block card-heading">
            <h4>Redirecting</h4>
        </div>
        <p>Please wait while we redirect you ...</p>        
        <div id="spinner"></div>
    </div>
</div>

<script type="application/javascript">
    var opts = {
        lines: 50 // The number of lines to draw
        , length: -5 // The length of each line
        , width: 10 // The line thickness
        , radius: 40 // The radius of the inner circle
        , scale: 1 // Scales overall size of the spinner
        , corners: 1 // Corner roundness (0..1)
        , color: '#e68821' // #rgb or #rrggbb or array of colors
        , opacity: 0 // Opacity of the lines
        , rotate: 0 // The rotation offset
        , direction: 1 // 1: clockwise, -1: counterclockwise
        , speed: 1 // Rounds per second
        , trail: 60 // Afterglow percentage
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
parse_str($_SERVER['QUERY_STRING'], $query);

/**
 * @param $query
 */
function oxipay_generate_processing_form($query)
{
    if (!isset($query["gateway_url"])) {
        error_log('gateway_url is not specified');
        return;
    }
    $url = base64_decode($query["gateway_url"]);
    $url = htmlspecialchars($url, ENT_QUOTES);
    ?>
    <form id='oxipayload' name="processForm" method='post' action='<?php echo $url ?>'>;
        <?php
        $encodedFields = array(
            'x_url_callback',
            'x_url_complete',
            'gateway_url',
            'x_url_cancel'
        );
        foreach ($query as $i => $v) {
            $item = htmlspecialchars($i, ENT_QUOTES);
            $value = null;
            if (in_array($item, $encodedFields)) {
                $value = htmlspecialchars(base64_decode($v), ENT_QUOTES);

            } else {
                $value = htmlspecialchars($v, ENT_QUOTES);
            }
            echo sprintf('<input id="%s" name="%s" value="%s" type="hidden" />', $item, $item, $value);
        };
        ?>
    </form>
    <?php
}

oxipay_generate_processing_form($query);
?>
<script type="text/javascript">
    /**
     * formSubmit
     */
    function formSubmit() {
        if ((typeof document.forms.processForm.x_signature == undefined) && (document.forms.processForm.x_signature.value == undefined)) {
            console.log("delay ... for submit");
            setTimeout(function () {
                document.getElementById('oxipayload').submit();
            }, 500);
        }
        else {
            console.log(document.forms.processForm.version_info.value);
            document.getElementById('oxipayload').submit();
        }
    }

    formSubmit();
</script>
</html>