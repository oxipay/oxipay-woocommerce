<?php
/**
 * PHPUnit bootstrap file
 *
 * @package flexigroup.com.au
 */

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin()
{
    require '/tmp/wordpress/wp-content/plugins/woocommerce/woocommerce.php';
    require dirname(dirname(__FILE__)) . '/oxipay.php';
}

tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require 'wc-zipmoney-test-main.php';