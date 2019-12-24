=== Humm / Oxipay Payment Gateway for WooCommerce ===
Tags: humm, Oxipay
Stable tag: trunk
Requires at least: 4.0
Requires PHP: 5.6
Tested up to: 5.2.2
License: GNU General Public License v3.0

New Zealand - Oxipay
Australia - **humm**

== Description ==
New Zealand – Oxipay 

Oxipay lets you spread the cost of your purchases over 4 fortnightly payments – interest free forever.  You can shop online or instore at any Oxipay retail partner. 

https://oxipay.co.nz 

 

Australia – **humm** 

Humm is the Buy Now Pay Later service that’s perfect for both the ‘Little things’ and ‘Big things’ in life.  Customers can buy everything from $1 to $30,000 depending on where they shop.  All purchases with **humm** are interest free forever.  

Humm is integrated at point of sale, instore and online.  

For ‘Little things’ you can be approved for up to $2,000.  You can choose to pay weekly or fortnightly over 2.5 or 5 months.  Each repayment frees your balance to spend again. 

For ‘Big things’ **humm** can pre-approve up to $10,000 or you can apply instore for up to $30,000 depending on the retailer.  You can choose to repay in 6, 12, 24….all the way up to 60 months. 

== Installation ==
= Pre-requisites =
A working WooCommerce plugin (version >=3.0) installation

= Other assumptions =
You have received a Merchant ID and API key for use from the **humm** / Oxipay support team.

= Installation =
Upload the plugin to your blog, Activate it.

Go to WooCommerce -> Settings -> Payments -> **humm** / Oxipay

**Humm is currently only available in Australia.**
**Oxipay is currently only available in New Zealand.**

**Humm** Australia:
https://shophumm.com.au

Oxipay New Zealand:
https://oxipay.co.nz

== Changelog ==

= 1.7.6 =
*Release Date 30 Jul 2019*
Fixed an issue that may halt the redirect-go-checkout page.

= 1.7.5 =
*Release Date 19 Jul 2019*
1. Checkout widget now inserts correctly
2. Improved logging

= 1.7.3 =
*Release Date 28 Jun 2019*
Enable checking out from "order-pay" page, instead of sending a GET request to payment gateway and failing.

= 1.7.2 =
*Release Date 28 Jun 2019*
Reduced size of the **humm** logo so it won't show too large in some themes.

= 1.7.1 =
*Release Date 28 May 2019*
re-organise the settings page

= 1.6.5 =
*Release Date 15 Apr 2019*
Update **humm** refund gateway URL.

= 1.6.3 =
*Release Date 26 Mar 2019*
Initial **humm** release.

= 1.5.0 =
*Release Date - 23 Jan 2019*
Online refunding now available.

= 1.4.3 =
*Release Date - 21 Dec 2018*
Improved logging for signature mismatch error.

= 1.4.1 =
*Release Date - 11 Oct 2018*
Disable checkout when order total is above purchase limit.

= 1.4.0 =
*Release Date - 11 Sep 2018*
Integrate the top-banner widget and checkout-page price widget.

= 1.3.13 =
*Release Date - 20 Aug 2018*
Select the checkout submit form by "form.checkout" instead of "form.checkout.woocommerce-checkout"

= 1.3.12 =
*Release Date - 6 Aug 2018*
Australian merchants now have the 8 week widget.

= 1.3.11 =
*Release Date - 31 July 2018*
Hotfix to remove unwanted error message.

= 1.3.10 =
*Release Date - 26 July 2018*
Removed "defer" from payments.js tag for compatibility with IE11

= 1.3.9 =
*Release Date - 20 July 2018*
Fixed an issue with empty_cart()

= 1.3.8 =
*Release Date - 7 May 2018*
* Show correct payment result, or error message if signature error, upon callback/browser-redirect.
* Fixed an issue that changes status to "failed" upon signature error.

= 1.3.7 =
*Release Date - 20 April 2018*
* Add 'preselect checkout button' in the checkout page.  
* Disable price-widget when product price is outside of the specified price range.
* Add defer to payments.js tag so that it does not block loading of the page.

= 1.3.4 =
*Release Date - 12 Dec 2017*
* Allow transactions that do not have a country field at all.

= 1.3.3 =
*Release Date - 11 Dec 2017*

1. Fixed issue that stock not reduced after success checkout.
2. Correctly verify signature.
3. Improve drop-down box appearance in settings.
4. Updated descrition in checkout page;
5. Integrated crypto code to main plugin file;
6. WC_Flexi_Gateway.php only required once;
7. readme file updated

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
