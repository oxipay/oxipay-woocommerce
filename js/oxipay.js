/** 
 * This is used to switch on the modal dialog for Oxipay transactions
 */
(function($) {
    'use strict';
    var oxipay_settings;
    // @todo do we need this ?
    // Object.defineProperty(window.navigator, 'userAgent', { get: function(){ return 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20120101 Firefox/33.0'; } });Object.defineProperty(window.navigator, 'vendor', { get: function(){ return 'Mozilla, Inc.'; } });Object.defineProperty(window.navigator, 'platform', { get: function(){ return 'Windows'; } });

    // A $( document ).ready() block.
    $( document ).ready(function() {

        if (typeof wc_checkout_params != 'undefined' && wc_checkout_params.checkout_url) {
            var checkoutUrl = wc_checkout_params.checkout_url;
            // console.log(checkoutUrl);
            // console.log(window.location);
            loadSettings(checkoutUrl);
        }
        

        $('form.checkout.woocommerce-checkout').on('checkout_place_order_oxipay', function() {
            // debugger;

            $.ajax({
                url     : wc_checkout_params.checkout_url,
                type    : $(this).attr('method'),
                //dataType: 'json',
                data    : $(this).serialize(),
                success : function( data ) { 
                    // debugger;                   
                    showModal(data.redirect);
                }
            });    
            // @todo send off to the server to populate the signature
            return false;
        });
    });
    
    function loadSettings(checkoutUrl) {
        // debugger;
        var url = checkoutUrl + "&oxi_settings=true";
        $.ajax({
            url     : url,
            type    : 'GET',
            // dataType: 'json',
            // data    : ,
            success : function( data ) {                
                //debugger;
                oxipay_settings = data;
                // console.log(oxipay_settings);
            },
            error   : function( xhr, err ) {
                // @todo    
            }
        });    
    };

    function extractKeys(redirectUrl) {
        var keyArr = redirectUrl.split('&');
        var keys = {};
        for (var i = 0; i < keyArr.length; i++) {
            var split = keyArr[i].split('=');
            keys[split[0].trim()] = split[1].trim();
        }
        return keys;
    };

   

    function showModal(urlString) {
        debugger;
        var modal       = oxipay_settings.use_modal;
        var form        = $('form.checkout.woocommerce-checkout');
        var keyStartPos = urlString.indexOf('?')+1    
        var values      = extractKeys(urlString.substring(keyStartPos));
        
        var encodedFields = [
            'x_url_callback',
            'x_url_complete',
            'gateway_url',
            'x_url_cancel',
            'x_customer_email'
        ];

        $.each(encodedFields, function(index, key){
            
            if (values[key]) {
                values[key] = decodeURIComponent(values[key]);
            }
        });
    
        var gateway = urlString.substring(0,urlString.indexOf('&'));
        delete values.platform;

        if (modal && modal != 'no' && modal != false) {
            var oxi = oxipay($);
            oxi.setup(gateway, values);
            oxi.show();
        } else {
            post(gateway, values);
        }
        
    };

    function post(path, params) {
                

        // The rest of this code assumes you are not using a library.
        // It can be made less wordy if you use one.
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", path);
        form.setAttribute('id', 'oxipay-submission')

        for(var key in params) {
            if(params.hasOwnProperty(key)) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", key);
                hiddenField.setAttribute("value", params[key]);

                form.appendChild(hiddenField);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }
})(jQuery);