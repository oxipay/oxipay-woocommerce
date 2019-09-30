<?php

defined( 'ABSPATH' ) || exit;

class Oxipay_Config {
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME_BEFORE = 'Oxipay';
    const DISPLAY_NAME_AFTER = 'humm';
    const PLUGIN_FILE_NAME = 'oxipay';
    const LAUNCH_TIME_URL = 'https://s3-ap-southeast-2.amazonaws.com/humm-variables/launch-time.txt';
    const BUTTON_COLOR = array( "Oxipay" => "E68821", "humm" => "FF6C00" );

    public $countries = array(
        self::COUNTRY_AUSTRALIA   => array(
            'name'            => 'Australia',
            'currency_code'   => 'AUD',
            'currency_symbol' => '$',
            'tld'             => '.com.au',
            'max_purchase'    => 2100,
            'min_purchase'    => 20,
        ),
        self::COUNTRY_NEW_ZEALAND => array(
            'name'            => 'New Zealand',
            'currency_code'   => 'NZD',
            'currency_symbol' => '$',
            'tld'             => '.co.nz',
            'max_purchase'    => 1500,
            'min_purchase'    => 20,
        )
    );

    const URLS = [
        'AU' => [
            'sandboxURL'             => 'https://integration-cart.shophumm.com.au/Checkout?platform=Default',
            'liveURL'                => 'https://cart.shophumm.com.au/Checkout?platform=Default',
            'sandbox_refund_address' => 'https://integration-buyerapi.shophumm.com.au/api/ExternalRefund/v1/processrefund',
            'live_refund_address'    => 'https://buyerapi.shophumm.com.au/api/ExternalRefund/v1/processrefund',
        ],
        'NZ' => [
            'sandboxURL'             => 'https://securesandbox.oxipay.co.nz/Checkout?platform=Default',
            'liveURL'                => 'https://secure.oxipay.co.nz/Checkout?platform=Default',
            'sandbox_refund_address' => 'https://portalssandbox.oxipay.co.nz/api/ExternalRefund/processrefund',
            'live_refund_address'    => 'https://portals.oxipay.co.nz/api/ExternalRefund/processrefund',
        ]
    ];

    public function getButtonColor() {
        return self::BUTTON_COLOR[ $this->getDisplayName() ];
    }

    public function getDisplayName() {
        $name    = self::DISPLAY_NAME_BEFORE;
        $country = get_option( 'woocommerce_oxipay_settings' )['country'];
        if ( ! $country ) {
            $wc_country = get_option( 'woocommerce_default_country' );
            if ( $wc_country ) {
                $country = substr( $wc_country, 0, 2 );
            }
        }
        if ( $country == 'AU' ) {
            $name = self::DISPLAY_NAME_AFTER;
        }

        return $name;
    }

    public function getUrlAddress( $countryCode ) {
        if ( $countryCode == 'AU' ) {
            return self::URLS['AU'];
        } else {
            return self::URLS['NZ'];
        }
    }

    public function getPlatformName() {
        return self::PLATFORM_NAME;
    }

    public function getPluginFileName() {
        return self::PLUGIN_FILE_NAME;
    }

    public function getPluginVersion() {
        return get_plugin_data( plugin_dir_path( __FILE__ ) . Oxipay_Config::PLUGIN_FILE_NAME . '.php', false, false )['Version'];
    }
}