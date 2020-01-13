/* globals jQuery */
/**
 * @copyright Flexigroup
 */
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
        var priceWidgetAdvanced = $("input#woocommerce_oxipay_price_widget_advanced");
        var dynamicPriceWidgetSetting = $("input#woocommerce_oxipay_price_widget_dynamic_enabled");

        var priceWidgetAdvancedBlock = priceWidgetAdvanced.parent().parent().parent().parent();
        var priceWidgetElementSelectorBlock = $("input#woocommerce_oxipay_price_widget_element_selector").parent().parent().parent();
        var dynamicPriceWidgetSettingBlock = dynamicPriceWidgetSetting.parent().parent().parent().parent();
        var priceWidgetCssSelectorBlock = $("input#woocommerce_oxipay_price_widget_price_selector").parent().parent().parent();

        function refresh() {
            var priceWidgetEnabled = priceWidgetSetting.is(":checked");
            var priceWidgetAdvancedEnabled = priceWidgetAdvanced.is(":checked");
            var dynamicPriceWidgetEnabled = dynamicPriceWidgetSetting.is(":checked");

            if (priceWidgetEnabled) {
                priceWidgetAdvancedBlock.show();
                if (priceWidgetAdvancedEnabled) {
                    priceWidgetElementSelectorBlock.show();
                    dynamicPriceWidgetSettingBlock.show();

                    if (dynamicPriceWidgetEnabled) {
                        priceWidgetCssSelectorBlock.show();
                    } else {
                        priceWidgetCssSelectorBlock.hide();
                    }
                } else {
                    priceWidgetElementSelectorBlock.hide();
                    dynamicPriceWidgetSettingBlock.hide();
                    priceWidgetCssSelectorBlock.hide();
                }
            } else {
                priceWidgetAdvancedBlock.hide();
                priceWidgetElementSelectorBlock.hide();
                dynamicPriceWidgetSettingBlock.hide();
                priceWidgetCssSelectorBlock.hide();
            }
        }

        priceWidgetSetting.change(refresh);
        priceWidgetAdvanced.change(refresh);
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