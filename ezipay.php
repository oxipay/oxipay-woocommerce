<?php
/*
 * Plugin Name: Certegy EziPay Payment Gateway
 * Plugin URI: https://www.certegyezipay.com.au
 * Description: Easy to setup installment payment plans from <a href="https://certegyezipay.com.au">Certegy EziPay</a>.
 * Version: plugin_version_placeholder
 * Author: FlexiGroup
 * Author URI: https://www.certegyezipay.com.au
 * @package WordPress
 * @author FlexiGroup
 * @since 0.4.8
 */

if ( !defined('ABSPATH')) exit; // Exit if accessed directly

// this checks that the woocommerce plugin is alive and well.
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(!is_plugin_active( 'woocommerce/woocommerce.php')) return;

require_once( 'ezipay-config.php' );

add_action('plugins_loaded', 'woocommerce_ezipay_init', 0);

add_action('parse_request', 'get_ezipay_settings' );
/**
 * Hook for WC plugin subsystem to initialise the Certegy ezipay plugin
 */
function woocommerce_ezipay_init() {
    require_once( 'WC_CertegyEzipay_Gateway.php' );
}

function add_ezipay_payment_gateway($methods) {
	$methods[] = 'WC_CertegyEzipay_Gateway';
	return $methods;
}

function add_ezipay_query_vars_filter( $vars ){
    $vars[] = "ezi_settings";
    return $vars;
}
/**
* Look for an ajax request that wants settings
*/
function get_ezipay_settings($query) {

    $gateways = WC_Payment_Gateways::instance();
    if (!$gateways) {
        // ezipay not installed properly
        return;
    }

    $list = $gateways->payment_gateways();
    if (!$list || !isset($list['ezipay'])) {
        // abort
    }

    $ezipay = $list['ezipay'];

    if (isset($query->query_vars['ezi_settings'])) {
        $settings = $ezipay->get_settings();
        wp_send_json($settings);
    }
}

function ezipay_settings_link($links){
	$settings_link = array('<a href="'.admin_url('admin.php?page=wc-settings&tab=checkout&section=ezipay').'">Settings</a>');
	return array_merge($settings_link, $links);
}

add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'ezipay_settings_link' );
add_filter('woocommerce_payment_gateways', 'add_ezipay_payment_gateway' );
add_filter( 'query_vars', 'add_ezipay_query_vars_filter' );