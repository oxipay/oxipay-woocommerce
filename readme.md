# Oxipay's WooCommerce payment method plugin

## Pre-requisites
1. We've tested this on Wordpress 4.6, but in theory is compatible with any WP instance that supports the latest version of the WooCommerce platform
2. A working WooCommerce plugin installation

## Other assumptions
We've tested this using the WooCommerce storefront theme, which is a free theme and an excellent starting point for those starting from scratch.

You have recieved a Merchant ID and API key for use from Oxipay's support team. You will also have recieved a payment gateway URL and a testing 'Sandbox' URL 

## Install
1. Drop/clone this repo into [wordpress-root]/wp-content/plugins/oxipay
2. Open wordpress dashboard -> plugins
3. Activate the Oxipay plugin
4. From the WooCommerce settings panel, choose the Checkout tab, and click the Oxipay link
5. Configure your merchant ID and api key accordingly
6. When testing, ensure the test mode is enabled and that the gateway & sandbox URLs are set.
7. Ensure all the other configuration settings are configured to your stores requirements.
