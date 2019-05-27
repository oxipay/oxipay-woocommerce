/* globals jQuery */
/* globals countryUrls */
(function ($) {
    'use strict';

    $(function () {
        var regionSelect = $("select#woocommerce_oxipay_country");
        var au_settings = $("#woocommerce_oxipay_au_settings").next();
        var nz_settings = $("#woocommerce_oxipay_nz_settings").next();

        function refresh() {
            var selectedRegion = regionSelect.val();
            if (selectedRegion == "AU") {
                au_settings.show();
                nz_settings.hide();
            }
            if (selectedRegion == "NZ") {
                nz_settings.show();
                au_settings.hide();
            }
        }

        regionSelect.change(refresh);
        refresh();

    });
})(jQuery);