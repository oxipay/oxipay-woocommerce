<?php
class Oxipay_Config {
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME_BEFORE = 'Oxipay';
    const DISPLAY_NAME_AFTER = 'Humm';
    const DISPLAY_NAME  = 'Oxipay';
	const PLUGIN_FILE_NAME = 'oxipay';
	const LAUNCH_TIME_URL = 'https://s3-ap-southeast-2.amazonaws.com/widgets.shophumm.com.au/time.txt';
	const LAUNCH_TIME_DEFAULT = '2019-03-31 13:30:00';

    public $countries = array(
        self::COUNTRY_AUSTRALIA => array (
            'name'				=> 'Australia',
            'currency_code' 	=> 'AUD',
            'currency_symbol'	=> '$',
            'tld'			    => '.com.au',
            'max_purchase'      => 2100,
            'min_purchase'      => 20,
        ),
        self::COUNTRY_NEW_ZEALAND => array (
            'name'				=> 'New Zealand',
            'currency_code'		=> 'NZD',
            'currency_symbol' 	=> '$',
            'tld'		  	    => '.co.nz',
            'max_purchase'      => 1500,
            'min_purchase'      => 20,
        )        
    );

    const URLS = [
    	'AU_Oxipay' => [
		    'sandboxURL'        => 'https://securesandbox.oxipay.com.au/Checkout?platform=WooCommerce',
		    'liveURL'           => 'https://secure.oxipay.com.au/Checkout?platform=WooCommerce',
		    'sandbox_refund_address'    => 'https://portalssandbox.oxipay.com.au/api/ExternalRefund/processrefund',
		    'live_refund_address'    => 'https://portals.oxipay.com.au/api/ExternalRefund/processrefund',
	    ],
	    'AU_Humm' => [
		    'sandboxURL'        => 'https://securesandbox.shophumm.com.au/Checkout?platform=WooCommerce',
		    'liveURL'           => 'https://secure.shophumm.com.au/Checkout?platform=WooCommerce',
		    'sandbox_refund_address'    => 'https://portalssandbox.shophumm.com.au/api/ExternalRefund/processrefund',
		    'live_refund_address'    => 'https://portals.shophumm.com.au/api/ExternalRefund/processrefund',
	    ],
	    'NZ' => [
		    'sandboxURL'        => 'https://securesandbox.oxipay.co.nz/Checkout?platform=WooCommerce',
		    'liveURL'           => 'https://secure.oxipay.co.nz/Checkout?platform=WooCommerce',
		    'sandbox_refund_address'    => 'https://portalssandbox.oxipay.co.nz/api/ExternalRefund/processrefund',
		    'live_refund_address'    => 'https://portals.oxipay.co.nz/api/ExternalRefund/processrefund',
	    ]
    ];

	public function getDisplayName() {
		$name = self::DISPLAY_NAME_BEFORE;
		$country = get_option('woocommerce_oxipay_settings')['country'];
		if(!$country){
			$wc_country = get_option('woocommerce_default_country');
			if($wc_country){
				$country = substr($wc_country, 0, 2);
			}
		}
		$is_after = ( time() - strtotime($this::getLaunchDate()) >= 0 );
		if ( $country == 'AU' &&  $is_after ) {
			$name = self::DISPLAY_NAME_AFTER;
		}
		return $name;
	}

    public function getUrlAddress($countryCode) {
	    $is_after = ( time() - strtotime( $this::getLaunchDate() ) >= 0 );
	    if ( $countryCode == 'AU' ) {
		    return $is_after? self::URLS['AU_Humm'] : self::URLS['AU_Oxipay'];
	    } else  {
		    return self::URLS['NZ'];
	    }
    }

    private function getLaunchDate(){
		$launch_time_string = get_option('oxipay_launch_time');
	    $launch_time_update_time_string = get_option('oxipay_launch_time_updated');
	    if(empty($launch_time_string) || ( time() - $launch_time_update_time_string >= 3600 )) {
			$remote_launch_time_string = wp_remote_get(self::LAUNCH_TIME_URL)['body'];
			if(!empty($remote_launch_time_string)){
				$launch_time_string = $remote_launch_time_string;
				update_option('oxipay_launch_time', $launch_time_string);
				update_option('oxipay_launch_time_updated', time());
			} elseif(empty($launch_time_string) || (empty($launch_time_update_time_string) && $launch_time_string != self::LAUNCH_TIME_DEFAULT)) {
				// this is when $launch_time_string never set (first time run of the plugin), or local const LAUNCH_TIME_DEFAULT changes and and never update from remote.
				// Mainly for development, for changing const LAUNCH_TIME_DEFAULT to take effect.
				$launch_time_string = self::LAUNCH_TIME_DEFAULT;
				update_option('oxipay_launch_time', $launch_time_string);
			}
	    }
	    return $launch_time_string;
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