/* globals jQuery */
/* globals countryUrls */
(function($) {
    'use strict';
    
    $(function() {
        var $countrySelect = $('#woocommerce_ezipay_country'),
            $gatewayUrl = $('#woocommerce_ezipay_ezipay_gateway_url'),
            $sandboxUrl = $('#woocommerce_ezipay_ezipay_sandbox_gateway_url');

        //if we are on the settings page
        if($countrySelect.length > 0 && $gatewayUrl.length > 0 && $sandboxUrl.length > 0 && typeof(countryUrls) == 'object'){
            //update the gateway and sandbox URLs when changing the region field
            $countrySelect.change(function (){
                var selectedCountry = $(this).val(),
                    currentGatewayUrl = $gatewayUrl.val(),
                    currentSandboxUrl = $sandboxUrl.val(),
                    countryDefaultUrls = countryUrls[selectedCountry];

                if(typeof(countryDefaultUrls) != 'undefined'){
                    if(countryDefaultUrls.gateway != currentGatewayUrl){
                        $gatewayUrl.val(countryDefaultUrls.gateway);
                    }
                    
                    if(countryDefaultUrls.sandbox != currentSandboxUrl){
                        $sandboxUrl.val(countryDefaultUrls.sandbox);
                    }
                }
            });
        }
    });
})(jQuery);