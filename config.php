<?php
define ('OXIPAY_PLATFORM_NAME', 'woocommerce');
define ('OXIPAY_CWD', basename( __DIR__ ));
define ("OXIPAY_DISPLAYNAME", "Oxipay");
define ("OXIPAY_WAIT_URL", "processing.php");
define ('OXIPAY_TEST', true);
define ('OXIPAY_DEFAULT_CURRENCY', 'AUD');
define ('OXIPAY_DEFAULT_COUNTRY', 'AU');
define ('OXIPAY_COUNTRIES', array (
	'AU' => array (
		'name'			=> 'Australia',
		'currency_code' => 'AUD'
	),
	'NZ' => array (
		'name'			=> 'New Zealand',
		'currency_code'	=> 'NZD'
	)
));