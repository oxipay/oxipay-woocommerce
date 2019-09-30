/**
 * This is used to switch on the modal dialog for Oxipay transactions
 */

/* global wc_checkout_params */

(function ($) {
    'use strict';
    var oxipay_settings;
    /**
     * @param wc_checkout_params.checkout_url
     * @param wc_checkout_params.i18n_checkout_error
     */
    $(document).ready(function () {
        if (typeof wc_checkout_params != 'undefined' && wc_checkout_params.checkout_url) {
            var checkoutUrl = wc_checkout_params.checkout_url;
            loadSettings(checkoutUrl);
        }

        function submit_post() {
            showLoadingPopup();
            $.ajax({
                url: wc_checkout_params.checkout_url,
                type: 'POST',
                data: $(this).serialize(),
                success: function (data) {
                    if (data && data.redirect) {
                        showModal(data.redirect);
                    } else {
                        $('#oxipay-popup-wrapper').hide();
                        if (true === data.reload) {
                            window.location.reload();
                            return;
                        }
                        // Trigger update in case we need a fresh nonce
                        if (true === data.refresh) {
                            $(document.body).trigger('update_checkout');
                        }
                        // Add new errors
                        if (data.messages) {
                            submit_error(data.messages);
                        } else {
                            submit_error('<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>');
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
                }
            });
            return false;
        }

        $('form.checkout').on('checkout_place_order_oxipay', submit_post);
    });

    function showLoadingPopup() {
        var oxipay_popup_wrapper = $(document.createElement('div'))
            .attr('id', 'oxipay-popup-wrapper')
            .css({
                'position': 'fixed',
                'width': '100%',
                'min-height': '100%',
                'z-index': 999999,
                'left': 0,
                'top': 0,
                'right': 0,
                'bottom': 0,
                'overflow': 'auto',
                'display': 'flex',
                'justify-content': 'center',
                'align-content': 'center',
                'align-items': 'center',
                'background-color': 'rgba(255, 255, 255, 0.4)'
            })
            .appendTo('body')
            .on('click', function (event) {
                closeLoadingPopup(event);
            });

        $(document.createElement('img'))
            .attr('src', php_vars.plugin_url + '/images/spinner.gif')
            .appendTo(oxipay_popup_wrapper);
    }

    function closeLoadingPopup(event) {
        event.preventDefault();
        $('#oxipay-popup-wrapper').hide();
    }

    /**
     * This is more or less a direct copy of the woocommerce implementation
     * since WC do not export their wc_checkout_form and I can't see a way to re-use
     * their error handling.
     *
     */
    function submit_error(error_message) {
        var wc_checkout_form = $('form.checkout.woocommerce-checkout');

        $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
        wc_checkout_form.prepend('<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
        wc_checkout_form.removeClass('processing').unblock();
        wc_checkout_form.find('.input-text, select, input:checkbox').blur();
        $('html, body').animate({
            scrollTop: ($('form.checkout').offset().top - 100)
        }, 1000);
        $(document.body).trigger('checkout_error');
    }


    function loadSettings(checkoutUrl) {
        $.ajax({
            url: checkoutUrl + "&oxi_settings=true",
            type: 'GET',
            success: function (data) {
                oxipay_settings = data;
            },
            error: function (xhr, err) {
                // we have failed to load the settings for some reason.
            }
        });
    }

    function extractKeys(redirectUrl) {
        var keyArr = redirectUrl.split('&');
        var keys = {};
        for (var i = 0; i < keyArr.length; i++) {
            var split = keyArr[i].split('=');
            keys[split[0].trim()] = decodeURIComponent((split[1]).trim());
        }
        return keys;
    }

    function showModal(urlString) {
        var keyStartPos = urlString.indexOf('?') + 1;
        var values = extractKeys(urlString.substring(keyStartPos));
        var encodedFields = ['x_url_callback', 'x_url_complete', 'gateway_url', 'x_url_cancel'];
        encodedFields.forEach(function (item) {
            values[item] = atob(values[item])
        });
        var modal = oxipay_settings.use_modal;

        var gateway = values.gateway_url;

        if (modal && modal !== 'no' && modal !== false) {
            var oxi = oxipay($);
            var modalCSS = php_vars.plugin_url + '/css/oxipay-modal.css';
            oxi.setup(gateway, values, modalCSS);
            oxi.show();

        } else {
            post(gateway, values);
        }
    }

    function post(path, params) {
        // The rest of this code assumes you are not using a library.
        // It can be made less wordy if you use one.
        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", path);
        form.setAttribute('id', 'oxipay-submission');

        for (var key in params) {
            if (params.hasOwnProperty(key)) {
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