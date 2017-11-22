<?php
class Ezipay_Config {
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME  = 'Certegy Ezipay';
    const PLUGIN_FILE_NAME = 'ezipay';

    public $countries = array(
        self::COUNTRY_AUSTRALIA => array (
            'name'				=> 'Australia',
            'currency_code' 	=> 'AUD',
            'currency_symbol'	=> '$',
            'tld'			    => '.com.au',
            'sandboxURL'        => 'https://securesandbox.certegyezipay.com.au/Checkout?platform=WooCommerce',
            'liveURL'           => 'https://secure.certegyezipay.com.au/Checkout?platform=WooCommerce',
        ),
        self::COUNTRY_NEW_ZEALAND => array (
            'name'				=> 'New Zealand',
            'currency_code'		=> 'NZD',
            'currency_symbol' 	=> '$',
            'tld'		  	    => '.co.nz',
            'sandboxURL'        => 'https://securesandbox.certegyezipay.co.nz/Checkout?platform=WooCommerce',
            'liveURL'           => 'https://secure.certegyezipay.co.nz/Checkout?platform=WooCommerce',
        )        
    );

    public function getDisplayName() {
        return self::DISPLAY_NAME;
    } 
    
    public function getPlatformName() {
        return self::PLATFORM_NAME;
    }

    public function getPluginFileName() {
    	return self::PLUGIN_FILE_NAME;
    }
	public function getPluginVersion() {
		return get_plugin_data( plugin_dir_path(__FILE__) . Ezipay_Config::PLUGIN_FILE_NAME.'.php', false, false)['Version'];
	}
}
