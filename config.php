<?php
DEFINE ('ENVIRONMENT', 'DEVELOPMENT');
DEFINE ('PLATFORM_NAME', 'WooCommerce');
DEFINE ('CWD', basename( __DIR__ ));
DEFINE ('WEBROOT', "http://localhost:60343/"); //todo: retrieve API URL from woocommerce configuration
DEFINE('ABSPATH', dirname(__FILE__).'/');

$config = array(
	"OXIPAY_DISPLAYNAME" => "Oxipay",
	"WAIT_URL" => "processing.php"
);

//todo: retrieve API URL from woocommerce configuration
switch (ENVIRONMENT) {

	case 'DEVELOPMENT':
		$config['OXIPAY_URL'] = 'http://localhost:60343/Checkout?platform=WooCommerce';
		$config['TEST'] = true;
		return $config;
    case 'PRODUCTION':
		$config['OXIPAY_URL'] = '';
		$config['TEST'] = false;
		return $config;
	default:
		break;
}

?>