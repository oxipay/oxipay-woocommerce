<?php

/**@copyright Flexigroup
 * Class Oxipay_Config
 */
class Oxipay_Config
{
    /**
     * const
     */
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME = 'Oxipay';
    const PLUGIN_FILE_NAME = 'oxipay';

    /**
     * @var array
     */
    public $countries = array(
        self::COUNTRY_AUSTRALIA => array(
            'name' => 'Australia',
            'currency_code' => 'AUD',
            'currency_symbol' => '$',
            'tld' => '.com.au',
//          'sandboxURL'        => 'https://securesandbox.oxipay.com.au/Checkout?platform=WooCommerce',
            'sandboxURL' => 'https://integration-cart.shophumm.com.au/Checkout?platform=WooCommerce',
            'liveURL' => 'https://secure.oxipay.com.au/Checkout?platform=WooCommerce',
            'sandbox_refund_address' => 'https://integration-cart.shophumm.com.au/api/ExternalRefund/processrefund',
            'live_refund_address' => 'https://portals.oxipay.com.au/api/ExternalRefund/processrefund',
            'max_purchase' => 2100,
            'min_purchase' => 20,
        ),
        self::COUNTRY_NEW_ZEALAND => array(
            'name' => 'New Zealand',
            'currency_code' => 'NZD',
            'currency_symbol' => '$',
            'tld' => '.co.nz',
            'sandboxURL' => 'https://securesandbox.oxipay.co.nz/Checkout?platform=WooCommerce',
            'liveURL' => 'https://secure.oxipay.co.nz/Checkout?platform=WooCommerce',
            'sandbox_refund_address' => 'https://portalssandbox.oxipay.co.nz/api/ExternalRefund/processrefund',
            'live_refund_address' => 'https://portals.oxipay.co.nz/api/ExternalRefund/processrefund',
            'max_purchase' => 1500,
            'min_purchase' => 20,
        )
    );

    /**
     * @return string
     */

    public function getDisplayName()
    {
        return self::DISPLAY_NAME;
    }

    /**
     * @return string
     */
    public function getPlatformName()
    {
        return self::PLATFORM_NAME;
    }

    /**
     * @return string
     */
    public function getPluginFileName()
    {
        return self::PLUGIN_FILE_NAME;
    }

    /**
     * @return mixed
     */

    public function getPluginVersion()
    {
        return get_plugin_data(plugin_dir_path(__FILE__) . Oxipay_Config::PLUGIN_FILE_NAME . '.php', false, false)['Version'];
    }
}
