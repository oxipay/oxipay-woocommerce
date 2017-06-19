<?php
/*
 * Plugin Name: Oxipay Payment Gateway
 * Plugin URI: https://www.oxipay.com.au
 * Description: Easy to setup installment payment plans from <a href="https://oxipay.com.au">Oxipay</a>.
 * Version: oxipay_plugin_version_placeholder
 * Author: FlexiGroup
 * @package WordPress
 * @author FlexiGroup
 * @since 0.4.8
 */

if ( !defined('ABSPATH')) exit; // Exit if accessed directly

// this checks that the woocommerce plugin is alive and well.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active( 'woocommerce/woocommerce.php')) return;

require_once( 'crypto.php' );
require_once( 'oxipay-config.php' );

add_action('plugins_loaded', 'woocommerce_oxipay_init', 0);

/**
 * Hook for WC plugin subsystem to initialise the Oxipay plugin
 */
function woocommerce_oxipay_init() {
    require_once('WC_Oxipay_Gateway.php');	
}

function add_oxipay_payment_gateway($methods) {
	$methods[] = 'WC_Oxipay_Gateway';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_oxipay_payment_gateway');
