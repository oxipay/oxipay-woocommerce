/** 
 * This is used to switch on the modal dialog for Oxipay transactions
 */
(function($) {
    'use strict';
    
    // @todo do we need this ?
    // Object.defineProperty(window.navigator, 'userAgent', { get: function(){ return 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20120101 Firefox/33.0'; } });Object.defineProperty(window.navigator, 'vendor', { get: function(){ return 'Mozilla, Inc.'; } });Object.defineProperty(window.navigator, 'platform', { get: function(){ return 'Windows'; } });

    // A $( document ).ready() block.
    $( document ).ready(function() {

        $('form.checkout.woocommerce-checkout').on('checkout_place_order_oxipay', function(){

            $.ajax({
                url     : wc_checkout_params.checkout_url,
                type    : $(this).attr('method'),
                //dataType: 'json',
                data    : $(this).serialize(),
                success : function( data ) {                    
                    // console.log(data.redirect);
                    showModal(data.redirect);
                },
                error   : function( xhr, err ) {
                    // @todo    
                }
            });    
            // @todo send off to the server to populate the signature
            return false;
        });
    });

    function showModal(urlString) {
        
        var $inputs = $('form.checkout.woocommerce-checkout :input');
        var values = {};
        // var urlString = '/wp-content/plugins/oxipay-woocommerce/processing.php?';
        // debugger;
        // $inputs.each(function () {
            
        //     values[this.name] =  $(this).val();
        //     urlString += (this.name)+"="+ $(this).val()+"&";

        // });
    
        var oxi = oxipay($);
        oxi.setup(urlString, values);
        oxi.show();
    };
})(jQuery);