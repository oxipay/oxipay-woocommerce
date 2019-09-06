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
            if (selectedRegion === "AU") {
                au_settings.show();
                nz_settings.hide();
            }
            if (selectedRegion === "NZ") {
                nz_settings.show();
                au_settings.hide();
            }
        }

        regionSelect.change(refresh);
        refresh();
    });

    $(function () {
        var priceWidgetSetting = $("input#woocommerce_oxipay_price_widget");
        var dynamicPriceWidgetSetting = $("input#woocommerce_oxipay_price_widget_dynamic_enabled");
        var dynamicPriceWidgetSettingBlock = dynamicPriceWidgetSetting.parent().parent().parent().parent();
        var priceWidgetCssSelectorBlock = $("input#woocommerce_oxipay_price_widget_selector").parent().parent().parent();

        function refresh() {
            var priceWidgetEnabled = priceWidgetSetting.is(":checked");
            var dynamicPriceWidgetEnabled = dynamicPriceWidgetSetting.is(":checked");
            if (priceWidgetEnabled) {
                dynamicPriceWidgetSettingBlock.show();
                if (dynamicPriceWidgetEnabled) {
                    priceWidgetCssSelectorBlock.show();
                } else {
                    priceWidgetCssSelectorBlock.hide();
                }
            } else {
                dynamicPriceWidgetSettingBlock.hide();
                priceWidgetCssSelectorBlock.hide();
            }
        }

        priceWidgetSetting.change(refresh);
        dynamicPriceWidgetSetting.change(refresh);
        refresh();
    });

    $(function () {
        var topBannerWidgetSetting = $("input#woocommerce_oxipay_top_banner_widget");
        var topBannerWidgetSettingShowOnFrontpageOnlyBlock = $("input#woocommerce_oxipay_top_banner_widget_homepage_only").parent().parent().parent();

        function refresh() {
            var topBannerWidgetEnabled = topBannerWidgetSetting.is(":checked");
            if (topBannerWidgetEnabled) {
                topBannerWidgetSettingShowOnFrontpageOnlyBlock.show();
            } else {
                topBannerWidgetSettingShowOnFrontpageOnlyBlock.hide();
            }
        }

        topBannerWidgetSetting.change(refresh);
        refresh();
    });
})(jQuery);