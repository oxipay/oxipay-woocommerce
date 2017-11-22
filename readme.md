# Certegy EziPay's WooCommerce Plugin [![Gitter](https://badges.gitter.im/ezipay/ezipay-woocommerce.svg)](https://gitter.im/ezipay/ezipay-woocommerce?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge) [![Build status](https://ci.appveyor.com/api/projects/status/jgrgyfkq3147nh8l?svg=true)](https://ci.appveyor.com/project/ezipay/ezipay-woocommerce)

## Pre-requisites
1. We've tested this on WordPress 4.6 - 4.8, but in theory is compatible with any WP instance that supports the latest version of the WooCommerce platform
2. A working WooCommerce plugin installation

## Other assumptions
We've tested this using the WooCommerce storefront theme, which is a free theme and an excellent starting point for those starting from scratch.

You have received a Merchant ID and API key for use from Certegy EziPay's support team. You will also have received a payment gateway URL and a testing 'Sandbox' URL 

## Install
4. From the WooCommerce settings panel, choose the Checkout tab, and click the Certegy EziPay link
5. Configure your merchant ID and api key accordingly
6. When testing, ensure the test mode is enabled and that the gateway & sandbox URLs are set.
7. Ensure all the other configuration settings are configured to your stores requirements.

1. Log into the WordPress admin area, then click on **Plugins** on the sidebar.
2. Click on **Add New** on the top left.
3. In the top left, type Certegy EziPay in the **Search plugins...** search box then hit Enter.
4. From the search results, click on **Install Now** next to **Certegy EziPay Payment Gateway for WooCommerce**.
5. Still on the same page and once installation is successful, you should see the **Activate button**. Click on it.
6. This will re-direct you to **Plugins** page, confirm that Certegy EziPay is installed and activated.

## Configure
1. From the side-bar, click on WooCommerce then on Settings.
2. From the top bar, click on Checkout then on Certegy EziPay.
3. **Certegy EziPay Region**: set the country of your store.
4. **Merchant** ID was provided to you as part of your welcome pack.
5. **API Key** should be provided to you by Platform Integration Team. It is used to verify that orders are from your store.