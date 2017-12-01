# Oxipay's WooCommerce Plugin [![Gitter](https://badges.gitter.im/oxipay/oxipay-woocommerce.svg)](https://gitter.im/oxipay/oxipay-woocommerce?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge) [![Build status](https://ci.appveyor.com/api/projects/status/jgrgyfkq3147nh8l?svg=true)](https://ci.appveyor.com/project/oxipay/oxipay-woocommerce)

## Pre-requisites
1. We've tested this on WordPress 4.6 - 4.9.1, but in theory is compatible with any WP instance that supports the latest version of the WooCommerce platform
2. A working WooCommerce plugin installation

## Other assumptions
We've tested this using the WooCommerce storefront theme, which is a free theme and an excellent starting point for those starting from scratch.

You have received a Merchant ID and API key for use from Oxipay's support team. You will also have received a payment gateway URL and a testing 'Sandbox' URL 

## Install
1. Log into the WordPress admin area, then click on **Plugins** on the sidebar.
2. Click on **Add New** on the top left.
3. In the top left, type Oxipay in the **Search plugins...** search box then hit Enter.
4. From the search results, click on **Install Now** next to **Oxipay Payment Gateway for WooCommerce**.
5. Still on the same page and once installation is successful, you should see the **Activate button**. Click on it.
6. This will re-direct you to **Plugins** page, confirm that Oxipay is installed and activated.

## Configure
1. From the side-bar, click on WooCommerce then on Settings.
2. From the top bar, click on Checkout then on Oxipay.
3. **Oxipay Region**: set the country of your store.
4. **Merchant ID** was provided to you as part of your welcome pack.
5. **API Key** should be provided to you by Platform Integration Team. It is used to verify that orders are from your store.