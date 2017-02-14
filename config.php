<?php

//define ('OXIPAY_PLATFORM_NAME', 'woocommerce');
//define ('OXIPAY_DISPLAYNAME', 'Oxipay');

class Config {
    const COUNTRY_AUSTRALIA = 'AU';
    const COUNTRY_NEW_ZEALAND = 'NZ';

    static $countries = array (
        self::COUNTRY_AUSTRALIA => array (
            'name'				=> 'Australia',
            'currency_code' 	=> 'AUD',
            'currency_symbol'	=> '$',
            'tld'			=> '.com.au',
        ),
        self::COUNTRY_NEW_ZEALAND => array (
            'name'				=> 'New Zealand',
            'currency_code'		=> 'NZD',
            'currency_symbol' 	=> '$',
            'tld'			=> '.co.nz'
        )
    );

    const PLATFORM_NAME = 'woocommerce';
    const DISPLAY_NAME = 'Oxipay';
}
