<?php
class Oxipay_Config {
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME_BEFORE = 'Oxipay';
    const DISPLAY_NAME_AFTER = 'Humm';
    const DISPLAY_NAME  = 'Oxipay';
    const PLUGIN_FILE_NAME = 'oxipay';

    public $countries = array(
        self::COUNTRY_AUSTRALIA => array (
            'name'				=> 'Australia',
            'currency_code' 	=> 'AUD',
            'currency_symbol'	=> '$',
            'tld'			    => '.com.au',
            'sandboxURL'        => 'https://securesandbox.oxipay.com.au/Checkout?platform=WooCommerce',
            'liveURL'           => 'https://secure.oxipay.com.au/Checkout?platform=WooCommerce',
	        'sandbox_refund_address'    => 'https://portalssandbox.oxipay.com.au/api/ExternalRefund/processrefund',
            'live_refund_address'    => 'https://portals.oxipay.com.au/api/ExternalRefund/processrefund',
            'max_purchase'      => 2100,
            'min_purchase'      => 20,
        ),
        self::COUNTRY_NEW_ZEALAND => array (
            'name'				=> 'New Zealand',
            'currency_code'		=> 'NZD',
            'currency_symbol' 	=> '$',
            'tld'		  	    => '.co.nz',
            'sandboxURL'        => 'https://securesandbox.oxipay.co.nz/Checkout?platform=WooCommerce',
            'liveURL'           => 'https://secure.oxipay.co.nz/Checkout?platform=WooCommerce',
            'sandbox_refund_address'    => 'https://portalssandbox.oxipay.co.nz/api/ExternalRefund/processrefund',
	        'live_refund_address'    => 'https://portals.oxipay.co.nz/api/ExternalRefund/processrefund',
            'max_purchase'      => 1500,
            'min_purchase'      => 20,
        )        
    );

    public function getDisplayName(  ) {
        $name = self::DISPLAY_NAME_BEFORE;
        $country = get_option('woocommerce_oxipay_settings')['country'];
        $wc_country = get_option('woocommerce_default_country');
		if($wc_country){
			$wc_country = substr($wc_country, 0, 2);
		}
		if(!$country){
			$country = $wc_country;
		}

        $is_after = ( time() - strtotime("2019-04-01 00:00:00.0") >= 0 );
        if ( $country == 'AU' &&  $is_after ) {
        	$name = self::DISPLAY_NAME_AFTER;
        }
        return $name;
    }
    
    public function getPlatformName() {
        return self::PLATFORM_NAME;
    }

	public function getPluginFileName() {
		return self::PLUGIN_FILE_NAME;
	}
	public function getPluginVersion() {
		return get_plugin_data( plugin_dir_path(__FILE__) . Oxipay_Config::PLUGIN_FILE_NAME.'.php', false, false)['Version'];
	}
}