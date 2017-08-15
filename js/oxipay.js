/**
 * This is used to switch on the modal dialog for Oxipay transactions
 */
(function($) {
    'use strict';
    var oxipay_settings;

    $( document ).ready(function() {

        if (typeof wc_checkout_params != 'undefined' && wc_checkout_params.checkout_url) {
            var checkoutUrl = wc_checkout_params.checkout_url;
            loadSettings(checkoutUrl);
        }

        $('form.checkout.woocommerce-checkout').on('checkout_place_order_oxipay', function(e) {
            $.ajax({
                url     : wc_checkout_params.checkout_url,
                type    : 'POST',
                data    : $(this).serialize(),
                success : function( data ) {
                    try {
                        if (data && data.redirect) {
                            showModal(data.redirect);
                        } else {
                            throw 'Invalid response';
                        }
                    } catch (err) {
                        if (true === data.reload) {
                            window.location.reload();
                            return;
                        }
                        // Trigger update in case we need a fresh nonce
                        if ( true === data.refresh ) {
                            $( document.body ).trigger( 'update_checkout' );
                        }

                        // Add new errors
                        if ( data.messages ) {
                            submit_error( data.messages );
                        } else {
                            submit_error( '<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>' );
                        }
                    }
                },
                error:  function( jqXHR, textStatus, errorThrown ) {
                    submit_error( '<div class="woocommerce-error">' + errorThrown + '</div>' );
                }
            });
            return false;
        });
    });

    /**
     * This is more or less a direct copy of the woocommerce implementation
     * since WC do not export their wc_checkout_form and I can't see a way to re-use
     * their error handling.
     *
     */
    function submit_error( error_message ) {
        var wc_checkout_form = $('form.checkout.woocommerce-checkout');

        $( '.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message' ).remove();
        wc_checkout_form.prepend( '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>' );
        wc_checkout_form.removeClass( 'processing' ).unblock();
        wc_checkout_form.find( '.input-text, select, input:checkbox' ).blur();
        $( 'html, body' ).animate({
                    scrollTop: ( $( 'form.checkout' ).offset().top - 100 )
        }, 1000 );
        $( document.body ).trigger( 'checkout_error' );
    }


    function loadSettings(checkoutUrl) {
        $.ajax({
            url     : checkoutUrl + "&oxi_settings=true",
            type    : 'GET',
            success : function( data ) {
                oxipay_settings = data;
            },
            error   : function( xhr, err ) {
                // we have failed to load the settings for some reason.
            }
        });
    };

    function extractKeys(redirectUrl) {
        var keyArr = redirectUrl.split('&');
        var keys = {};
        for (var i = 0; i < keyArr.length; i++) {
            var split = keyArr[i].split('=');
            keys[split[0].trim()] = decodeURIComponent((split[1]).trim());
        }
        return keys;
    };

    function showModal(urlString) {

        var modal = false;

        var form        = $('form.checkout.woocommerce-checkout');
        var keyStartPos = urlString.indexOf('?')+1
        var values      = extractKeys(urlString.substring(keyStartPos));
        modal           = oxipay_settings.use_modal;

        var gateway = urlString.substring(0,urlString.indexOf('&'));
        // we already include the platform as part of the gateway URL so remove it
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