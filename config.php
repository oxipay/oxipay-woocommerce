<?php

//define ('OXIPAY_PLATFORM_NAME', 'woocommerce');
//define ('OXIPAY_DISPLAYNAME', 'Oxipay');

class Config {
    const countries = array (
        'AU' => array (
            'name'				=> 'Australia',
            'currency_code' 	=> 'AUD',
            'currency_symbol'	=> '$',
            'base_url'			=> 'https://oxipay.com.au',
        ),
        'NZ' => array (
            'name'				=> 'New Zealand',
            'currency_code'		=> 'NZD',
            'currency_symbol' 	=> '$',
            'base_url'			=> 'https://oxipay.co.nz'
        )
    );

    const platform_name = 'woocommerce';
    const display_name = 'Oxipay';
}
