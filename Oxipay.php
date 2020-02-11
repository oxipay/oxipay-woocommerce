<?php
/*
 * Plugin Name: Humm / Oxipay Payment Gateway
 * Plugin URI: https://www.shophumm.com.au
 * Description: In Australia - <a href="https://www.shophumm.com.au"><strong>humm</strong></a>, In New Zealand - <a href="https://www.oxipay.com.au">Oxipay</a>.
 * Version:           2.0.0
 * Author:            flexigroup
 * Author URI:
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Github URI:
 * WC requires at least:
 * WC tested up to:
 * @version  2.0.0
 * @package  flexigroup
 * @author   flexigroup
 * package WordPress
 * author FlexiGroup
 * since 0.4.8
 */

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

// this checks that the woocommerce plugin is alive and well.
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    return;
}
define('WC_HUMM_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
require_once('includes/Oxipay_config.php');

add_action('plugins_loaded', 'woocommerce_oxipay_init', 0);

add_action('parse_request', 'get_oxipay_settings');
/**
 * Hook for WC plugin subsystem to initialise the Oxipay plugin
 */
function woocommerce_oxipay_init()
{
    require_once('includes/WC_Oxipay_Gateway.php');
}

/**
 * @param $methods
 * @return array
 */
function add_oxipay_payment_gateway($methods)
{
    $methods[] = 'WC_Oxipay_Gateway';

    return $methods;
}

/**
 * @param $vars
 * @return array
 */
function add_oxipay_query_vars_filter($vars)
{
    $vars[] = "oxi_settings";

    return $vars;
}

/**
 * Look for an ajax request that wants settings
 *
 * @param @query
 *
 * @return null
 */
function get_oxipay_settings($query)
{

    $gateways = WC_Payment_Gateways::instance();
    if (!$gateways) {
        return;
    }

    $list = $gateways->payment_gateways();
    if (!$list || !isset($list['oxipay'])) {
        // abort
        return;
    }

    /** @var WC_Oxipay_Gateway $oxipay */
    $oxipay = $list['oxipay'];

    if (isset($query->query_vars['oxi_settings'])) {
        $settings = $oxipay->get_settings();
        wp_send_json($settings);
    }
    return;
}

/**
 * @param $links
 * @return array
 */
function oxipay_settings_link($links)
{
    $settings_link = array('<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=oxipay') . '">Settings</a>');

    return array_merge($settings_link, $links);
}
define ( 'WC_HUMM_PATH', plugin_dir_path ( __FILE__ ) );
define ( 'WC_HUMM_PLUGIN_NAME', plugin_basename ( __FILE__ ) );
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'oxipay_settings_link');
add_filter('woocommerce_payment_gateways', 'add_oxipay_payment_gateway');
add_filter('query_vars', 'add_oxipay_query_vars_filter');
