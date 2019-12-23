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


=== Oxipay Payment Gateway for WooCommerce ===
Tags: Oxipay
Requires at least: 4.0
Tested up to: 4.9.5
License: GNU General Public License v3.0

Shop now and pay over time

Oxipay is a smarter way to pay.
Shop online or in-store, and pay for your purchase by four, easy interest free instalments.

== Description ==
Oxipay is a flexible payment solution backed by FlexiGroup, an ASX 200 financial services company with over 14,000 partners, 20,000 distribution points, and $2 billion in assets.

Oxipay is simply the easier way to pay. You can shop online or in-store at any Oxipay retail partner and we’ll spread the cost of your purchase over 4 payments. It’s just that little bit extra breathing space to make buying easier.

https://oxipay.com.au

== Installation ==
= Pre-requisites -
A working WooCommerce plugin (version >=3.0) installation

= Other assumptions -
You have received a Merchant ID and API key for use from Oxipay's support team.

= Installation =
Upload the Oxipay plugin to your blog, Activate it.

Go to WooCommerce -> Settings -> Checkout -> Oxipay


**Oxipay is currently available in Australia and New Zealand only.**

https://oxipay.com.au

== Changelog ==

= 1.3.7 =
*Release Date - 20 April 2018*
* Add 'preselect checkout button' in the checkout page.  
* Disable price-widget when product price is outside of the specified price range.
* Add defer to payments.js tag so that it does not block loading of the page.

= 1.2.1 =
*Release Date – 18 Aug 2017*
* Improve modal design.
* Remove Gateway URL input from settings.
* First version hosted on wordpress.org

= 1.2.0 =
*Release Date – 14 Aug 2017*
* Add minimum/maximum limit option.
* Add modal mode. User can choose between modal and redirect mode.
* Integrate price-info widget.
* Changes to comply with WordPress guidelines.

= 1.1.1 =
*Release Date - 20 Jun 2017*
* Added readme.txt file
