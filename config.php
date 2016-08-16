<?php
DEFINE ('ENVIRONMENT', 'DEVELOPMENT');
DEFINE ('PLATFORM_NAME', 'WooCommerce');
DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
DEFINE ('CWD', basename( __DIR__ ));

$config = array(
	'XPAY_DISPLAYNAME' => 'OxiPay',
	'WAIT_URL' => 'waiting.php'
);

switch (ENVIRONMENT) {

	case 'DEVELOPMENT':
		$config['XPAY_URL'] = 'http://localhost:60343/Checkout?platform=WooCommerce';
		$config['TEST'] = true;
		return $config;
		break;
	case 'PRODUCTION'
		$config['XPAY_URL'] = 'http://xpozsecure.certegyezipay.com.au/Checkout?platform=WooCommerce';
		$config['TEST'] = false;
		return $config;
		break;
	default:
		break;
}
