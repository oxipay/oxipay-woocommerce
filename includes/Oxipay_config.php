<?php

defined('ABSPATH') || exit;

/**
 * Class Oxipay_Config
 * @copyright flexigroup
 * @author roger.bi@flexigroup.com.au
 */
class Oxipay_Config
{
    /**
     * @constant
     */
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME_BEFORE = 'Oxipay';
    const DISPLAY_NAME_AFTER = 'Humm';
    const PLUGIN_FILE_NAME = 'Oxipay';
    const LAUNCH_TIME_URL = 'https://s3-ap-southeast-2.amazonaws.com/humm-variables/launch-time.txt';
    const NZ_LAUNCH_TIME_URL = 'https://humm-variables.s3-ap-southeast-2.amazonaws.com/nz-launch-time.txt';
    const NZ_LAUNCH_TIME_DEFAULT = "2030-05-11 14:30:00 UTC";
    const NZ_LAUNCH_TIME_CHECK_ENDS = "2030-11-18 14:30:00 UTC";
    const BUTTON_COLOR = array("Oxipay" => "E68821", "Humm" => "FF6C00");
    const URLS = [
        'AU' => [
            'sandboxURL' => 'https://integration-cart.shophumm.com.au/Checkout?platform=Default',
            'liveURL' => 'https://cart.shophumm.com.au/Checkout?platform=Default',
            'sandbox_refund_address' => 'https://integration-buyerapi.shophumm.com.au/api/ExternalRefund/v1/processrefund',
            'live_refund_address' => 'https://buyerapi.shophumm.com.au/api/ExternalRefund/v1/processrefund',
        ],
        'NZ_Oxipay' => [
            'sandboxURL' => 'https://securesandbox.oxipay.co.nz/Checkout?platform=Default',
            'liveURL' => 'https://secure.oxipay.co.nz/Checkout?platform=Default',
            'sandbox_refund_address' => 'https://portalssandbox.oxipay.co.nz/api/ExternalRefund/processrefund',
            'live_refund_address' => 'https://portals.oxipay.co.nz/api/ExternalRefund/processrefund',
        ],
        'NZ_Humm' => [
            'sandboxURL' => 'https://integration-cart.shophumm.co.nz/Checkout?platform=Default',
            'liveURL' => 'https://cart.shophumm.co.nz/Checkout?platform=Default',
            'sandbox_refund_address' => 'https://integration-buyerapi.shophumm.co.nz/api/ExternalRefund/v1/processrefund',
            'live_refund_address' => 'https://buyerapi.shophumm.co.nz/api/ExternalRefund/v1/processrefund',
        ]
    ];


    /**
     * @var array
     */
    public $countries = array(
        self::COUNTRY_AUSTRALIA => array(
            'name' => 'Australia',
            'currency_code' => 'AUD',
            'currency_symbol' => '$',
            'tld' => '.com.au',
            'max_purchase' => 2100,
            'min_purchase' => 20,
        ),
        self::COUNTRY_NEW_ZEALAND => array(
            'name' => 'New Zealand',
            'currency_code' => 'NZD',
            'currency_symbol' => '$',
            'tld' => '.co.nz',
            'max_purchase' => 1500,
            'min_purchase' => 20,
        )
    );

    /**
     * @return mixed
     */
    public function getButtonColor()
    {
        if ($this->isAfter()) {
            return self::BUTTON_COLOR["Humm"];
        } else
            return self::BUTTON_COLOR[$this->getDisplayName()];
    }

    /**
     * @return string
     */

    public function getDisplayName()
    {
        $name = self::DISPLAY_NAME_BEFORE;
        $country = get_option('woocommerce_oxipay_settings')['country'];
        if (!$country) {
            $wc_country = get_option('woocommerce_default_country');
            if ($wc_country) {
                $country = substr($wc_country, 0, 2);
            }
        }
        if (($country == 'AU') || ($this->isAfter())) {
            $name = self::DISPLAY_NAME_AFTER;
        }


        return $name;
    }

    /**
     *
     */
    public function getLogger()
    {
        if (function_exists('wc_get_logger')) {
            return wc_get_logger();

        }
    }

    /**
     * @param $countryCode
     * @return mixed
     */
    public function getUrlAddress($countryCode)
    {
        if ($countryCode == 'AU') {
            return self::URLS['AU'];
        } else {
            return $this->isAfter() ? self::URLS['NZ_Humm'] : self::URLS['NZ_Oxipay'];
        }
    }

    /**
     * @return mixed|string|void
     */
    private function getLaunchDateString()
    {
        $launch_time_string = get_option('oxipay_nz_launch_time_string');
        $launch_time_update_time = get_option('oxipay_nz_launch_time_updated');
        $this->getLogger()->log('info', $launch_time_string . '|' . $launch_time_update_time);

        if (time() - strtotime(self::NZ_LAUNCH_TIME_CHECK_ENDS) > 0) {
            // if after LAUNCH_TIME_CHECK_ENDS time, and launch_time is still empty, set it to default launch time, and done.
            if (!$launch_time_string) {
                $launch_time_string = self::NZ_LAUNCH_TIME_DEFAULT;
                update_option('oxipay_nz_launch_time_string', $launch_time_string);
            }
            return $launch_time_string;
        }
        $country = get_option('woocommerce_oxipay_settings')['country'];
        if ($country == 'NZ' && (empty($launch_time_string) || empty($launch_time_update_time) || (time() - $launch_time_update_time >= 1440))) {
            $remote_launch_time_string = wp_remote_get(self::NZ_LAUNCH_TIME_URL)['body'];
            $launch_time_string = $remote_launch_time_string > self::NZ_LAUNCH_TIME_DEFAULT ? $remote_launch_time_string:self::NZ_LAUNCH_TIME_DEFAULT;
            $this->getLogger()->log('info', 'remote-launch' . $remote_launch_time_string.$launch_time_string);
                update_option('oxipay_nz_launch_time_string', $launch_time_string);
                update_option('oxipay_nz_launch_time_updated', time());
        }

        return $launch_time_string;
    }

    /**
     * @return bool
     */
    public function isAfter()
    {
        $force_humm = get_option('woocommerce_oxipay_settings')['force_humm'];
        return $force_humm == 'yes' ? true : (time() - strtotime($this->getLaunchDateString()) >= 0);

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
        return get_plugin_data(plugin_dir_path(__FILE__) . '../' . Oxipay_Config::PLUGIN_FILE_NAME . '.php', false, false)['Version'];
    }
}
