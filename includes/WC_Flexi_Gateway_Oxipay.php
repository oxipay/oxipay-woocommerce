<?php

defined('ABSPATH') || exit;

/**
 * Class WC_Flexi_Gateway_Oxipay
 * @author roger.bi@flexigroup.com.au
 * @copyright Flexigroup
 */
abstract class WC_Flexi_Gateway_Oxipay extends WC_Payment_Gateway
{
    public static $init_val = 1;
    /**
     * @var mixed
     */
    public $plugin_current_version;
    /**
     * @var WC_Logger|null
     */
    public $logger = null;
    /**
     * @var Oxipay_Config|null
     */
    protected $currentConfig = null;
    /**
     * @var string|null
     */
    protected $pluginDisplayName = null;
    /**
     * @var string|null
     */
    protected $pluginFileName = null;
    /**
     * @var bool
     */
    protected $flexi_payment_preselected = false;
    /**
     * @var array
     */
    private $logContext;

    /**
     * WC_Flexi_Gateway_Oxipay constructor.
     * @param $config
     */
    function __construct($config)
    {
        self::$init_val++;
        $this->currentConfig = $config;
        $this->pluginDisplayName = $config->getDisplayName();
        $this->pluginFileName = strtolower($config->getPluginFileName());

        if (function_exists('wc_get_logger')) {
            $this->logger = wc_get_logger();
            $this->logContext = array('source' => $this->pluginDisplayName);
        }

        $this->id = $this->pluginFileName;
        $this->has_fields = false;
        $this->method_title = __($this->pluginDisplayName, 'woocommerce');
        $this->plugin_current_version = $config->getPluginVersion();

        $this->init_form_fields();
        $this->init_settings();
        if (is_admin()) {
            $this->init_upgrade_process();
        }
        if (is_admin() && ($this->settings['enabled'] == 'yes')) {
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        }

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'flexi_enqueue_script'));
        }
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
            $this,
            'process_admin_options'
        ));
        add_action('storefront_content_top', array($this, 'add_top_banner_widget'));
        add_action('woocommerce_after_single_product', array($this, 'add_price_widget'));
        add_action('woocommerce_proceed_to_checkout', array($this, 'add_price_widget_cart'));
        add_action('woocommerce_single_product_summary', array($this, 'add_price_widget_anchor'));
        add_filter('woocommerce_thankyou_order_id', array($this, 'payment_finalisation'));
        add_filter('the_title', array($this, 'order_received_title'), 11);
        add_action('woocommerce_before_checkout_form', array($this, 'display_min_max_notice'));
        add_action('woocommerce_before_cart', array($this, 'display_min_max_notice'));
        add_filter('woocommerce_available_payment_gateways', array($this, 'display_min_max_filter'));
        add_filter('woocommerce_available_payment_gateways', array($this, 'preselect_flexi'));
        add_filter('woocommerce_thankyou_order_received_text', array($this, 'thankyou_page_message'));
        if ($this->settings['enabled'] == 'yes') {
            add_filter('manage_edit-shop_order_columns', array($this, 'humm_order_payment_note_column'));
            add_action('manage_shop_order_posts_custom_column', array($this, 'humm_order_payment_note_column_content'));
        }
        $preselect_button_order = $this->settings["preselect_button_order"] ? $this->settings["preselect_button_order"] : '20';
        add_action('woocommerce_proceed_to_checkout', array(
            $this,
            "flexi_checkout_button"
        ), $preselect_button_order);
    }

    /**
     * WC override to display the administration property page
     */
    function init_form_fields()
    {

        $countryOptions = array('' => __('Please select...', 'woocommerce'));

        foreach ($this->currentConfig->countries as $countryCode => $country) {
            $countryOptions[$countryCode] = __($country['name'], 'woocommerce');
        }

        $merchantTypes = array(
            'both' => __('both (default)', 'woocommerce'),
            'BigThings' => 'BigThings only',
            'LittleThings' => 'LittleThings only',
        );

        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enabled', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable the ' . $this->pluginDisplayName . ' Payment Gateway', 'woocommerce'),
                'default' => 'yes',
                'description' => 'Disable ' . $this->pluginDisplayName . ' services, your customers will not be able to use our easy installment plans.',
                'desc_tip' => true
            ),
            'General Settings' => array(
                'title' => __('General Settings', 'woocommerce'),
                'type' => 'title',
                'css' => WC_HUMM_ASSETS . 'css/oxipay-config.css',
                'class' => 'humm-general',
            ),
            'country' => array(
                'title' => __($this->pluginDisplayName . ' Region', 'woocommerce'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => 'Select the option that matches your retailer agreement.',
                'options' => $countryOptions,
                'desc_tip' => true,
                'custom_attributes' => array('required' => 'required'),
            ),
            'use_test' => array(
                'title' => __('Test Mode', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Use Test Mode', 'woocommerce'),
                'default' => 'no',
                'description' => __('While test mode is enabled, transactions will be simulated and cards will not be charged', 'woocommerce')
            ),
            "{$this->pluginFileName}_merchant_id" => array(
                'id' => $this->pluginFileName . '_merchant_id',
                'title' => __('Merchant ID', 'woocommerce'),
                'type' => 'text',
                'default' => '',
                'description' => $this->pluginDisplayName . ' will have supplied you with your ' . $this->pluginDisplayName . ' Merchant ID. Contact us if you cannot find it.',
                'desc_tip' => true,
                'custom_attributes' => array('required' => 'required'),
            ),
            $this->pluginFileName . '_api_key' => array(
                'id' => $this->pluginFileName . '_api_key',
                'title' => __('API Key', 'woocommerce'),
                'type' => 'text',
                'default' => '',
                'description' => $this->pluginDisplayName . ' will have supplied you with your ' . $this->pluginDisplayName . ' API key. Contact us if you cannot find it.',
                'desc_tip' => true,
                'custom_attributes' => array('required' => 'required'),
            ),
            'enable_logging' => array(
                'title' => __('Enable Logging', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable logging', 'woocommerce'),
                'default' => 'yes',
                'description' => __('The ' . $this->pluginDisplayName . ' logs are available at the <a href="' . admin_url('admin.php?page=wc-status&tab=logs') . '">WooCommerce status page</a>', 'woocommerce')
            ),
            'display_settings' => array(
                'title' => __('Banners and Widgets', 'woocommerce'),
                'type' => 'title',
            ),
            'price_widget' => array(
                'title' => __('Price Widget', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable the ' . $this->pluginDisplayName . ' Price Widget', 'woocommerce'),
                'default' => 'yes',
                'description' => 'Display a price widget in each product page.',
            ),
            'price_widget_advanced' => array(
                'title' => __('Price Widget Advanced Settings', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable advanced options for the Price Widget', 'woocommerce'),
                'default' => 'no',
                'description' => '<strong>Leave disabled if unsure.</strong>',
            ),
            'price_widget_element_selector' => array(
                'type' => 'text',
                'default' => '',
                'description' => 'CSS selector for the element to insert the price widget after.<br>Leave empty for default location.',
            ),
            'price_widget_dynamic_enabled' => array(
                'type' => 'checkbox',
                'label' => __('Use Dynamic Version of the Price Widget'),
                'default' => 'no',
                'description' => 'Price widget will automatically update the breakdown if the product price changes. <br>Leave this disabled if unsure. <br><strong>Uses the CSS selector below to track changes.</strong>',
            ),
            'price_widget_price_selector' => array(
                'label' => __('Price Widget CSS Selector', 'woocommerce'),
                'type' => 'text',
                'default' => '.price .woocommerce-Price-amount.amount',
                'description' => 'CSS selector for the element containing the product price',
                'desc_tip' => true,
            ),
            'preselect_button_enabled' => array(
                'title' => __('Pre-select Checkout Button', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Add a "Checkout with ' . $this->pluginDisplayName . '" button in Cart page', 'woocommerce'),
                'default' => 'yes',
                'description' => __('Add a "Checkout with ' . $this->pluginDisplayName . '" button in Cart page that takes customer to Checkout page and have ' . $this->pluginDisplayName . ' pre-selected', 'woocommerce'),
            ),
            'preselect_button_order' => array(
                'title' => __('Pre-select Button Order', 'woocommerce'),
                'type' => 'text',
                'label' => __('Pre-select Button Order', 'woocommerce'),
                'default' => '20',
                'description' => __('Position the "checkout with ' . $this->pluginDisplayName . ' button" in Cart page if there are multiple checkout buttons. Default is 20. Smaller number moves the button ahead and larger number moves it lower in the list of checkout buttons.', 'woocommerce'),
                'desc_tip' => true
            ),
            'top_banner_widget' => array(
                'title' => __('Humm Top Banner Widget', 'woocommerce'),
                'label' => __('Enable the ' . $this->pluginDisplayName . ' Top Banner Widget', 'woocommerce'),
                'default' => 'no',
                'type' => 'checkbox',
                'checkboxgroup' => 'start',
                'show_if_checked' => 'option',
                'description' => 'Display a top banner.',
            ),
            'top_banner_widget_homepage_only' => array(
                'label' => __('Top Banner Widget Shows on FrontPage Only', 'woocommerce'),
                'default' => 'yes',
                'type' => 'checkbox',
                'checkboxgroup' => 'end',
                'show_if_checked' => 'yes',
                'description' => 'When the top banner enabled, it shows in homepage only (if checked), or shows in every page (if unchecked)',
                'autoload' => false,
            ),
            'au_settings' => array(
                'title' => __('', 'woocommerce'),
                'type' => 'title',
            ),
            'merchant_type' => array(
                'title' => __('Humm Merchant Type', 'woocommerce'),
                'type' => 'select',
                'class' => 'wc-enhanced-select',
                'description' => 'Select the option that matches your retailer agreement.',
                'options' => $merchantTypes,
                'desc_tip' => true,
                'custom_attributes' => array('required' => 'required'),
            ),
            'nz_settings' => array(
                'title' => __('', 'woocommerce'),
                'type' => 'title',
            ),
            'use_modal' => array(
                'title' => __('Modal Checkout', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Modal Checkout', 'woocommerce'),
                'default' => 'no',
                'description' => __('The customer will be forwarded to checkout in a modal dialog', 'woocommerce')
            ),
            'shop_settings' => array(
                'title' => __('Shop Settings', 'woocommerce'),
                'type' => 'title',
            ),
            'shop_name' => array(
                'title' => __('Shop Name', 'woocommerce'),
                'type' => 'text',
                'description' => __('The name of the shop that will be displayed in ' . $this->pluginDisplayName, 'woocommerce'),
                'default' => __('', 'woocommerce'),
                'desc_tip' => true,
            ),
            'force_humm' => array(
                'title' => __('Force humm', 'woocommerce'),
                'type' => 'checkbox',
                'label' => __('Force display and checkout with <strong>humm</strong>, not waiting for automatic switch over (NZ only)', 'woocommerce'),
                'default' => 'yes',
                'description' => __('You will switch to <strong>humm</strong> if this is set to \'yes\'. Otherwise you will be automatically switched over on the official <strong>humm</strong> launch date', 'woocommerce'),
                'desc_tip' => true
            ),
            $this->pluginFileName . '_minimum' => array(
                'id' => $this->pluginFileName . '_minimum',
                'title' => __('Minimum Order Total', 'woocommerce'),
                'type' => 'text',
                'default' => '20',
                'description' => 'Minimum order total to use ' . $this->pluginDisplayName . '. Empty for unlimited',
                'desc_tip' => true,
            ),
            $this->pluginFileName . '_maximum' => array(
                'id' => $this->pluginFileName . '_maximum',
                'title' => __('Maximum Order Total', 'woocommerce'),
                'type' => 'text',
                'default' => '0',
                'description' => 'Maximum order total to use ' . $this->pluginDisplayName . '. Empty for unlimited',
                'desc_tip' => true,
            ),
            $this->pluginFileName . '_thresholdAmount' => array(
                'id' => $this->pluginFileName . '_thresholdAmount',
                'title' => __('Little Things Threshold Amount', 'woocommerce'),
                'type' => 'text',
                'default' => '2000',
                'description' => 'Little Things Threshold Amount to use ' . $this->pluginDisplayName . '. Empty for unlimited',
                'desc_tip' => true,
            ),
        );
    }

    /**
     * Check to see if we need to run upgrades.
     */
    function init_upgrade_process()
    {
        //get the current upgrade version. This will default to 0 before version 0.4.5 of the plugin
        $currentDbVersion = isset($this->settings['db_plugin_version']) ? $this->settings['db_plugin_version'] : 0;
        //see if the current upgrade version is lower than the latest version
        if (version_compare($currentDbVersion, $this->plugin_current_version) < 0) {
            //run the upgrade process
            if ($this->upgrade($currentDbVersion)) {
                //update the stored upgrade version if the upgrade process was successful
                $this->updateSetting('db_plugin_version', $this->plugin_current_version);
            }
        }
    }

    /**
     * Run one off upgrade routines. A DB stored version number is compared to the class constant to
     * tell if processes need to run.
     * Update the class constant each time the version number changes. Add tasks here to handle
     * upgrade tasks when needed.
     * Users coming from especially old versions may have multiple version upgrade tasks to process.
     *
     * @param int $currentDbVersion
     *
     * @return bool
     */
    private function upgrade($currentDbVersion)
    {
        if (is_admin() && ($this->settings['enabled'] == 'yes')) {
            add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        }
        if (version_compare($currentDbVersion, '1.2.0') < 0) {
            if (!isset($this->settings['use_modal'])) {
                // default to the redirect for existing merchants
                // so we don't break the existing behaviour
                $this->settings['use_modal'] = false;
                $this->updateSetting('use_modal', $this->settings['use_modal']);
            }
            $minField = sprintf('%s_minimum', $this->pluginFileName);
            $maxField = sprintf('%s_maximum', $this->pluginFileName);
            $thresholdField = sprintf('%s_thresholdAmount', $this->pluginFileName);
            if (!isset($this->settings[$minField])) {
                $this->updateSetting('use_modal', $this->settings[$minField]);
            }
            if (!isset($this->settings[$maxField])) {
                $this->updateSetting('use_modal', $this->settings[$maxField]);
            }
            if (!isset($this->settings[$thresholdField])) {
                $this->updateSetting('use_modal', $this->settings[$thresholdField]);
            }
        } elseif (version_compare($currentDbVersion, '1.3.5') < 0) {
            if (!isset($this->settings['preselect_button_enabled'])) {
                // default to the disable the pre-select checkout button for existing merchants
                // so we don't break the existing behaviour
                $this->settings['preselect_button_enabled'] = "no";
                $this->updateSetting('preselect_button_enabled', $this->settings['preselect_button_enabled']);
            }
            if (!isset($this->settings['preselect_button_order'])) {
                // set default to 20 for pre-select button sequence
                $this->settings['preselect_button_order'] = "20";
                $this->updateSetting('preselect_button_order', $this->settings['preselect_button_order']);
            }
        } elseif (version_compare($currentDbVersion, '1.3.14') < 0) {
            if (!isset($this->settings['top_banner_widget'])) {
                // default to the disable the pre-select checkout button for existing merchants
                // so we don't break the existing behaviour
                $this->settings['top_banner_widget'] = "no";
                $this->updateSetting('top_banner_widget', $this->settings['top_banner_widget']);
            }
            if (!isset($this->settings['top_banner_widget_homepage_only'])) {
                // set default to 20 for pre-select button sequence
                $this->settings['top_banner_widget_homepage_only'] = "20";
                $this->updateSetting('top_banner_widget_homepage_only', $this->settings['top_banner_widget_homepage_only']);
            }
        } elseif (version_compare($currentDbVersion, '1.6.0') < 0) {
            if (!isset($this->settings['merchant_type'])) {
                // default to both
                $this->settings['merchant_type'] = "both";
                $this->updateSetting('merchant_type', $this->settings['merchant_type']);
            }
        } elseif (version_compare($currentDbVersion, '1.7.4') < 0) {
            if (!isset($this->settings['enable_logging'])) {
                // default to yes
                $this->settings['enable_logging'] = "yes";
                $this->updateSetting('enable_logging', $this->settings['enable_logging']);
            }
        }

        return true;
    }

    /**
     * Update a plugin setting stored in the database
     */
    private function updateSetting($key, $value)
    {
        $this->settings[$key] = $value;
        update_option($this->get_option_key(), $this->settings);
    }

    abstract public function add_top_banner_widget();

    abstract public function add_price_widget();

    abstract public function add_price_widget_cart();

    abstract public function add_price_widget_anchor();

    /**
     * flexi_checkout_button
     */

    function flexi_checkout_button()
    {

        $minimum = $this->getMinPrice();
        $maximum = $this->getMaxPrice();
        if (($minimum != 0 && WC()->cart->total > $minimum) && ($maximum != 0 && WC()->cart->total < $maximum)) {
            if ($this->settings["preselect_button_enabled"] == "yes" && $this->settings['enabled'] == 'yes') {
                echo '<div><a href="' . esc_url(wc_get_checkout_url()) . '?' . $this->pluginDisplayName . '_preselected=true" class="checkout-button button" style="font-size: 1.2em; padding-top: 0.4em; padding-bottom: 0.4em; background-color: #' .
                    $this->currentConfig->getButtonColor() . '; color: #FFF;">Check out with ' . $this->pluginDisplayName . '</a></div>';
            }
        }
    }

    /**
     * @param $columns
     * @return mixed
     */

    function humm_order_payment_note_column($columns)
    {
        $columns['Payment_Info'] = 'Payment_Info';
        return $columns;
    }

    /**
     * @param $column
     */
    function humm_order_payment_note_column_content($column)
    {
        global $post;
        ?>
        <style>
            mark.humm-status {
                display: -webkit-inline-flex;
                display: inline-flex;
                text-align: center;
                font-size: 14px;
                line-height: 2.5em;
                border-radius: 4px;
                border-bottom: 1px solid rgba(0, 0, 0, .08);
                margin: 0.5em 0.5em;
                cursor: inherit !important;
                max-width: 100%;
                background: #e68821;
                color: white;
            }

            mark.payment-status {
                display: -webkit-inline-flex;
                display: inline-flex;
                line-height: 2.5em;
                color: #777;
                background: #e5e5e5;
                border-radius: 4px;
                border-bottom: 1px solid rgba(0, 0, 0, .05);
                margin: -.25em 0;
                cursor: inherit !important;
                max-width: 100%;
            }
        </style>
        <?php
        if ('Payment_Info' === $column) {
            $order = wc_get_order($post->ID);
            $orderNote = $this->get_humm_order_notes($order->get_id());

            if ($order->get_data()['payment_method'] == $this->pluginFileName) {
                $showNote = ' <mark class="humm-status"><span>' . (isset($orderNote[0]) ? $orderNote[0] : ' ') . '</span></mark>';
                echo $showNote;
            } else {
                $showNote = ' <mark class="payment-status"><span>' . $order->get_data()['payment_method'] . '</span></mark>';
                echo $showNote;
            }
        }
    }

    /**
     * @param $orderId
     * @return array
     */
    function get_humm_order_notes($orderId)
    {
        global $wpdb;
        $tablePerfixed = $wpdb->prefix . 'comments';
        $results = $wpdb->get_results("
        SELECT *
        FROM $tablePerfixed
        WHERE  `comment_post_ID` = $orderId
        AND  `comment_type` LIKE  'order_note'
    ");

        $orderNote = [];
        foreach ($results as $note) {
            $orderNote[] = sprintf("%s <br/>", $note->comment_content);
        }
        return $orderNote;
    }

    /**
     * display_min_max_notice
     */

    function display_min_max_notice()
    {
        $minimum = $this->getMinPrice();
        $maximum = $this->getMaxPrice();

        if ($minimum != 0 && WC()->cart->total < $minimum) {
            if (is_checkout()) {
                wc_print_notice(
                    sprintf("You must have an order with a minimum of %s to use %s. Your current order total is %s.",
                        wc_price($minimum),
                        $this->pluginDisplayName,
                        wc_price(WC()->cart->total)
                    ), 'notice'
                );
            }
        } elseif ($maximum != 0 && WC()->cart->total > $maximum) {
            if (is_checkout()) {
                wc_print_notice(
                    sprintf("You must have an order with a maximum of %s to use %s. Your current order total is %s.",
                        wc_price($maximum),
                        $this->pluginDisplayName,
                        wc_price(WC()->cart->total)
                    ), 'notice'
                );
            }
        }
    }

    /**
     * @return int
     */

    protected function getMinPrice()
    {
        $field = sprintf('%s_minimum', $this->pluginFileName);
        return isset($this->settings[$field]) ? $this->settings[$field] : 0;
    }

    /**
     * @return int
     */
    protected function getMaxPrice()
    {
        $field = sprintf('%s_maximum', $this->pluginFileName);

        return isset($this->settings[$field]) && (intval($this->settings[$field]) <> 0) ? $this->settings[$field] : 1000000;
    }

    /**
     * @param $available_gateways
     * @return mixed
     */

    function display_min_max_filter($available_gateways)
    {
        $minimum = $this->getMinPrice();
        $maximum = $this->getMaxPrice();
        if (($minimum != 0 && WC()->cart->total < $minimum) || ($maximum != 0 && WC()->cart->total > $maximum)) {
            if (isset($available_gateways[$this->pluginFileName])) {
                unset($available_gateways[$this->pluginFileName]);
            }
        }
        return $available_gateways;
    }

    /**
     * @param $available_gateways
     * @return mixed
     */

    function preselect_flexi($available_gateways)
    {
        if (isset($_GET[$this->pluginDisplayName . "_preselected"])) {
            $this->flexi_payment_preselected = $_GET[$this->pluginDisplayName . "_preselected"];
        }

        if (!empty($available_gateways)) {
            if ($this->flexi_payment_preselected == "true") {
                foreach ($available_gateways as $gateway) {
                    if (strtolower($gateway->id) == $this->pluginFileName) {
                        WC()->session->set('chosen_payment_method', $gateway->id);
                    }
                }
            }
        }

        return $available_gateways;
    }

    /**
     * @return array
     */

    public function get_settings()
    {
        // these are safe values to export via javascript
        $whitelist = [
            'enabled' => null,
            'display_details' => null,
            'title' => null,
            'description' => null,
            'shop_details' => null,
            'shop_name' => null,
            'country' => null,
            'use_modal' => null
        ];
        foreach ($whitelist as $k => $v) {
            if (isset($this->settings[$k])) {
                $whitelist[$k] = $this->settings[$k];
            }
        }

        return $whitelist;
    }

    /**
     * payment_fields
     */
    public function payment_fields()
    {
        /*
        $country_domain = (isset($this->settings['country']) && $this->settings['country'] == 'NZ') ? 'co.nz' : 'com.au';
        $checkout_total = (WC()->cart) ? WC()->cart->get_totals()['total'] : "0";
        if (($this->currentConfig->getDisplayName() == 'Humm')|| ( $this->currentConfig->getDisplayName() == 'Oxipay' )){
            $widget_type = 'price-info';
            $merchant_type = "&" . $this->settings['merchant_type'];
            if ($merchant_type == '&both') {
                $merchant_type = '';
            }
            $this->description = __('<div id="checkout_method_humm_anchor"></div><script src="https://widgets.shophumm.' . $country_domain . '/content/scripts/' . $widget_type . '.js?used_in=checkout&productPrice=' . $checkout_total . '&element=%23checkout_method_humm_anchor' . $merchant_type . '"></script>', 'WooCommerce');
        }
        else {
            $widget_type = (isset($this->settings['country']) && $this->settings['country'] == 'NZ') ? 'payments' : 'payments-weekly';
            $this->description = __('<div id="checkout_method_oxipay_anchor"></div><script src="https://widgets.oxipay.' . $country_domain . '/content/scripts/' . $widget_type . '.js?used_in=checkout&productPrice=' . $checkout_total . '&element=%23checkout_method_oxipay_anchor"></script>', 'woocommerce');
        }
        echo $this->description;
        */
    }

    /**
     * Generates the payment gateway request parameters and signature and redirects to the
     * payment gateway through the invisible processing.php form
     *
     * @param int $order_id
     *
     * @return array
     */
    function process_payment($order_id)
    {
        $order = new WC_Order($order_id);
        $gatewayUrl = $this->getGatewayUrl();
        $isValid = true;
        $isValid = $isValid && $this->verifyConfiguration($order);
        $isValid = $isValid && $this->checkCustomerLocation($order);
        $isValid = $isValid && $this->checkOrderAmount($order);
        $isValid = $isValid && !is_null($gatewayUrl) && $gatewayUrl != '';

        if (!$isValid) {
            return array();
        }

        $callbackURL = $this->get_return_url($order);

        $transaction_details = array(
            'x_reference' => $order_id,
            'x_account_id' => $this->settings[$this->pluginFileName . '_merchant_id'],
            'x_amount' => $order->get_total(),
            'x_currency' => $this->getCurrencyCode(),
            'x_url_callback' => $callbackURL,
            'x_url_complete' => $callbackURL,
            'x_url_cancel' => $order->get_checkout_payment_url(),
            'x_test' => 'false',
            'x_shop_country' => $this->getCountryCode(),
            'x_shop_name' => $this->settings['shop_name'],
            //customer detail
            'x_customer_first_name' => $order->get_billing_first_name(),
            'x_customer_last_name' => $order->get_billing_last_name(),
            'x_customer_email' => $order->get_billing_email(),
            'x_customer_phone' => $order->get_billing_phone(),
            //billing detail
            'x_customer_billing_country' => $order->get_billing_country(),
            'x_customer_billing_city' => $order->get_billing_city(),
            'x_customer_billing_address1' => $order->get_billing_address_1(),
            'x_customer_billing_address2' => $order->get_billing_address_2(),
            'x_customer_billing_state' => $order->get_billing_state(),
            'x_customer_billing_zip' => $order->get_billing_postcode(),
            //shipping detail
            'x_customer_shipping_country' => $order->get_billing_country(),
            'x_customer_shipping_city' => $order->get_shipping_city(),
            'x_customer_shipping_address1' => $order->get_shipping_address_1(),
            'x_customer_shipping_address2' => $order->get_shipping_address_2(),
            'x_customer_shipping_state' => $order->get_shipping_state(),
            'x_customer_shipping_zip' => $order->get_shipping_postcode(),
            'version_info' => 'humm_' . $this->currentConfig->getPluginVersion() . '_on_wc' . substr(WC()->version, 0, 3),
            'gateway_url' => $gatewayUrl
        );

        $signature = $this->flexi_sign($transaction_details, $this->settings[$this->pluginFileName . '_api_key']);
        $transaction_details['x_signature'] = $signature;
        $this->log(json_encode($transaction_details));
        $encodedFields = array(
            'x_url_callback',
            'x_url_complete',
            'gateway_url',
            'x_url_cancel'
        );


        foreach ($encodedFields as $i) {
            $transaction_details[$i] = base64_encode($transaction_details[$i]);
        }
        // use RFC 3986 so that we can decode it correctly in js
        $qs = http_build_query($transaction_details, null, '&', PHP_QUERY_RFC3986);
        return array(
            'result' => 'success',
            'redirect' => plugins_url("../templates/Template_Process_Form.php?$qs", __FILE__)
        );
    }

    /**
     * returns the gateway URL
     *
     * @param $countryCode
     *
     * @return string
     */
    private function getGatewayUrl($countryCode = '')
    {
        //if no countryCode passed in
        if ($this->is_null_or_empty($countryCode)) {
            if (isset($this->settings['country'])) {
                $countryCode = $this->settings['country'];
            } else {
                $countryCode = 'AU';
            }
        }
        $environment = ($this->isTesting() == 'no') ? "live" : "sandbox";
        $url = $this->currentConfig->getUrlAddress($countryCode)[$environment . 'URL'];
        return $url;
    }

    /**
     * @param $str
     *
     * @return bool
     */
    private function is_null_or_empty($str)
    {
        return is_null($str) || $str == '';
    }

    /**
     * @return string
     */
    public function isTesting()
    {
        return isset($this->settings['use_test']) ? $this->settings['use_test'] : 'no';
    }

    /**
     * @param WC_Order $order
     *
     * @return bool
     */
    private function verifyConfiguration($order)
    {

        $apiKey = $this->settings[$this->pluginFileName . '_api_key'];
        $merchantId = $this->settings[$this->pluginFileName . '_merchant_id'];
        $region = $this->settings['country'];

        $isValid = true;
        $clientMsg = static::PLUGIN_MISCONFIGURATION_CLIENT_MSG;
        $logMsg = '';

        if ($this->is_null_or_empty($region)) {
            $logMsg = static::PLUGIN_NO_REGION_LOG_MSG;
            $isValid = false;
        }

        if ($this->is_null_or_empty($apiKey)) {
            $logMsg = static::PLUGIN_NO_API_KEY_LOG_MSG;
            $isValid = false;
        }

        if ($this->is_null_or_empty($merchantId)) {
            $logMsg = static::PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG;
            $isValid = false;
        }

        if (!$isValid) {
            $order->cancel_order($logMsg);
            $this->logValidationError($clientMsg);
        }

        return $isValid;
    }

    /**
     * @param $message
     */

    private function logValidationError($message)
    {
        wc_add_notice(__('Payment error: ', 'woothemes') . $message, 'error');
    }

    /**
     * Ensure the customer is being billed from and is shipping to, Australia.
     *
     * @param WC_Order $order
     *
     * @return bool
     */
    private function checkCustomerLocation($order)
    {

        $countries = array($order->get_billing_country(), $order->get_shipping_country());
        $set_addresses = array_filter($countries);
        $countryCode = $this->getCountryCode();
        $countryName = $this->getCountryName();

        return true;
    }

    /**
     * @return string
     */
    private function getCountryCode()
    {
        return $this->settings['country'];
    }

    /**
     * @return string
     */
    private function getCountryName()
    {
        return $this->currentConfig->countries[$this->getCountryCode()]['name'];
    }

    /**
     * Ensure the order amount is >= $20
     * Also ensure order is <= max_purchase
     *
     * @param WC_Order $order
     *
     * @return true
     */
    private function checkOrderAmount($order)
    {
        if ($this->currentConfig->getDisplayName() == 'humm') {
            return true;
        }
        $total = $order->get_total();
        $min = $this->getMinPurchase();
        if ($total < $min) {
            $errorMessage = "&nbsp;Orders under " . $this->getCurrencyCode() . $this->getCurrencySymbol() . $min . " are not supported by " . $this->pluginDisplayName . ". Please select a different payment option.";
            $order->cancel_order($errorMessage);
            $this->logValidationError($errorMessage);

            return false;
        }

        $max = $this->getMaxPurchase();
        if ($total > $max) {
            $errorMessage = "&nbsp;Orders over " . $this->getCurrencyCode() . $this->getCurrencySymbol() . $max . " are not supported by " . $this->pluginDisplayName . ". Please select a different payment option!";
            $order->cancel_order($errorMessage);
            $this->logValidationError($errorMessage);

            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */

    private function getMinPurchase()
    {
//      return $this->currentConfig->countries[$this->getCountryCode()]['min_purchase'];
        return $this->getMinPrice();
    }

    /**
     * @return string
     */
    private function getCurrencyCode()
    {
        return $this->currentConfig->countries[$this->getCountryCode()]['currency_code'];
    }

    /**
     * @return string
     */
    private function getCurrencySymbol()
    {
        return $this->currentConfig->countries[$this->getCountryCode()]['currency_symbol'];
    }

    /**
     * @return int
     */
    private function getMaxPurchase()
    {
//      return $this->currentConfig->countries[$this->getCountryCode()]['max_purchase'];
        return $this->getMaxPrice();
    }

    /**
     * @param $query
     * @param $api_key
     * @return mixed
     */
    function flexi_sign($query, $api_key)
    {
        $clear_text = '';
        ksort($query);
        foreach ($query as $key => $value) {
            if (substr($key, 0, 2) === "x_" && $key !== "x_signature") {
                $clear_text .= $key . $value;
            }
        }
        $hash = hash_hmac("sha256", $clear_text, $api_key);

        return str_replace('-', '', $hash);
    }

    /**
     * Log a message using the 2.7 logging infrastructure
     *
     * @param string $message Message log
     * @param string $level WC_Log_Levels
     */
    public function log($message, $level = WC_Log_Levels::DEBUG)
    {
        if ($this->logger != null && $this->settings["enable_logging"]) {
            $this->logger->log($level, $message, $this->logContext);
        }
    }

    /**
     * Renders plugin configuration markup
     */
    function admin_options()
    {
        include plugin_dir_path(dirname(__FILE__)) . 'includes/view/backend/admin_options.php';
        $countryUrls = array();
        foreach ($this->currentConfig->countries as $countryCode => $country) {
            $countryUrls[$countryCode] = array('gateway' => $this->getGatewayUrl($countryCode));
        }
        if (count($countryUrls) > 0) {
            ?>
            <script>
                var countryUrls = <?php echo json_encode($countryUrls); ?>;
            </script>
            <?php
        }
    }

    /**
     * This is a filter setup to receive the results from the flexi services to show the required
     * outcome for the order based on the 'x_result' property
     *
     * @param $order_id
     *
     * @return mixed
     */
    function payment_finalisation($order_id)
    {
        $order = wc_get_order($order_id);
        $cart = WC()->cart;
        $msg = "";
        $isAsyncCallback = $_SERVER['REQUEST_METHOD'] === "POST" ? true : false;
        if ($order->get_data()['payment_method'] !== $this->pluginFileName) {
            // we don't care about it because it's not an flexi order
            // log in debug level
            WC()->session->set('flexi_result_note', '');
            $this->log(sprintf('No action required. orderId: %s is not a %s order, (isAsyncCallback=%s)', $order_id, $this->pluginDisplayName, $isAsyncCallback));
            return $order_id;
        }

        if ($isAsyncCallback) {
            $params = $_POST;
        } else {
            $scheme = 'http';
            if (!empty($_SERVER['HTTPS'])) {
                $scheme = 'https';
            }

            $full_url = sprintf(
                '%s://%s%s',
                $scheme,
                $_SERVER['HTTP_HOST'],
                $_SERVER['REQUEST_URI']
            );
            $parts = parse_url($full_url, PHP_URL_QUERY);
            parse_str($parts, $params);
        }

        // we need order information in order to complete the order
        if (empty($order)) {
            $this->log(sprintf('unable to get order information for orderId: %s, (isAsyncCallback=%s)', $order_id, $isAsyncCallback));

            return $order_id;
        }

        $api_key = $this->settings[$this->pluginFileName . '_api_key'];
        $sig_exists = isset($params['x_signature']);
        $sig_match = false;
        if ($sig_exists) {
            $expected_sig = $this->flexi_sign($params, $api_key);
            $sig_match = $expected_sig === $params['x_signature'];
        }

        if ($sig_exists && $sig_match) {
            $this->log(sprintf('Finalising orderId: %s, (isAsyncCallback=%s)', $order_id, $isAsyncCallback));
            if (!empty($params)) {
                $this->log(json_encode($params));
            }
            $flexi_result_note = '';
            switch ($params['x_result']) {
                case "completed":
                    $flexi_result_note = __('Payment approved using ' . $this->pluginDisplayName . '. Gateway_Reference #' . $params['x_gateway_reference'], 'woocommerce');
                    $order->add_order_note($flexi_result_note);
                    $order->update_meta_data("flexi_purchase_number", $params["x_gateway_reference"]);
                    $order->payment_complete($params['x_reference']);

                    if (!is_null($cart) && !empty($cart)) {
                        $cart->empty_cart();
                    }
                    $msg = 'complete';
                    break;
                case "failed":
                    $flexi_result_note = __('Payment declined using ' . $this->pluginDisplayName . '. Gateway Reference #' . $params['x_gateway_reference'], 'woocommerce');
                    $order->add_order_note($flexi_result_note);
                    $order->update_status('failed');
                    $msg = 'failed';
                    WC()->session->set('flexi_result', 'failed');
                    break;
                case "error":
                    $flexi_result_note = __('Payment error using ' . $this->pluginDisplayName . '. Gateway Reference #' . $params['x_gateway_reference'], 'woocommerce');
                    $order->add_order_note($flexi_result_note);
                    $order->update_status('on-hold', 'Error may have occurred with ' . $this->pluginDisplayName . '. Gateway Reference #' . $params['x_gateway_reference']);
                    $msg = 'error';
                    WC()->session->set('flexi_result', 'error');
                    break;
            }
            WC()->session->set('flexi_result_note', $flexi_result_note);
        } else {
            $order->add_order_note(__($this->pluginDisplayName . ' payment response failed signature validation. Please check your Merchant Number and API key or contact ' . $this->pluginDisplayName . ' for assistance.' .
                '</br></br>isJSON: ' . $isAsyncCallback .
                '</br>Payload: ' . print_r($params, true) .
                '</br>Expected Signature: ' . $expected_sig, 0, 'woocommerce'));
            $msg = "signature error";
            WC()->session->set('flexi_result_note', $this->pluginDisplayName . ' signature error');
        }

        if ($isAsyncCallback) {
            $return = array(
                'message' => $msg,
                'id' => $order_id
            );
            wp_send_json($return);
        }

        return $order_id;
    }

    /**
     * @param $original_message
     * @return array|string
     */

    function thankyou_page_message($original_message)
    {
        if (WC()->session->get('chosen_payment_method') == $this->pluginFileName) {
            if (!empty(WC()->session->get('flexi_result_note'))) {
                return WC()->session->get('flexi_result_note');
            }
        }
        return $original_message;
    }

    /**
     * This is a filter setup to override the title on the order received page
     * in the case where the payment has failed
     *
     * @param $title
     *
     * @return string
     */
    function order_received_title($title)
    {
        global $wp_query;

        if (!is_null($wp_query) && !is_admin() && is_main_query() && in_the_loop() && is_page() && is_wc_endpoint_url()) {
            $endpoint = WC()->query->get_current_endpoint();
            if ($endpoint == 'order-received' && !empty($_GET['x_result'])) {
                //look at the x_result query var. Ideally we'd load the order and look at the status, but this has not been updated when this filter runs
                if ($_GET['x_result'] == 'failed') {
                    $title = 'Payment Failed';
                }
            }
            remove_filter('the_title', array($this, 'order_received_title'), 11);
        }

        return $title;
    }

    /**
     * @param string $feature
     * @return bool
     */

    function supports($feature)
    {
        return in_array($feature, array("products", "refunds")) ? true : false;
    }

    /**
     * Can the order be refunded?
     * @param WC_Order $order
     * @return    bool
     */
    function can_refund_order($order)
    {
        return ($order->get_status() == "processing" || $order->get_status() == "on-hold" || $order->get_status() == "completed");
    }

    /**
     * @param int $order_id
     * @param null $amount
     * @param string $reason
     * @return bool
     */

    function process_refund($order_id, $amount = null, $reason = '')
    {
        $reason = $reason ? $reason : "not provided";

        $order = wc_get_order($order_id);
        $purchase_id = get_post_meta($order_id)["flexi_purchase_number"][0];
        if (!$purchase_id) {
            $this->log(__('Oxipay Purchase ID not found. Can not proceed with online refund', 'woocommerce'));
            return false;
        }

        if (isset($this->settings['country'])) {
            $countryCode = $this->settings['country'];
        } else {
            $countryCode = 'AU';
        }

        $environment = ($this->isTesting() == 'no') ? "live" : "sandbox";
        $refund_address = $this->currentConfig->getUrlAddress($countryCode)[$environment . '_refund_address'];

        $refund_details = array(
            "x_merchant_number" => $this->settings[$this->pluginFileName . '_merchant_id'],
            "x_purchase_number" => $purchase_id,
            "x_amount" => $amount,
            "x_reason" => $reason
        );
        $refund_signature = $this->flexi_sign($refund_details, $this->settings[$this->pluginFileName . '_api_key']);
        $refund_details['signature'] = $refund_signature;

        $response = wp_remote_post($refund_address, array(
            'method' => 'POST',
            'data_format' => 'body',
            'body' => json_encode($refund_details),
            'timeout' => 3600,
            'user-agent' => 'Woocommerce ' . WC_VERSION,
            'httpversion' => '1.1',
            'headers' => array('Content-Type' => 'application/json; charset=utf-8')
        ));
        if (is_wp_error($response)) {
            $this->log(__('There was a problem connecting to the refund gateway.', 'woocommerce'));
            return false;
        }
        if (empty($response['response'])) {
            $this->log(__('Empty response.', 'woocommerce'));
            return false;
        }

        $refund_result = $response['response'];
        $refund_message = '';
        if ($response['body']) {
            $refund_message = json_decode($response['body'], true)['Message'];
        }

        if (isset($refund_result['code']) && $refund_result['code'] == '204') {
            $order->add_order_note(sprintf(__('Refunding of $%s for order #%s through %s succeeded', 'woocommerce'), $amount, $order->get_order_number(), $this->pluginDisplayName));

            return true;
        } elseif (isset($refund_result['code']) && $refund_result['code'] == '400') {
            $order->add_order_note(sprintf(__('Refunding of $%s for order #%s through %s failed. Error Code: %s', 'woocommerce'), $amount, $order->get_order_number(), $this->pluginDisplayName, $refund_message));
        } elseif (isset($refund_result['code']) && $refund_result['code'] == '401') {
            $order->add_order_note(sprintf(__('Refunding of $%s for order #%s through %s failed Signature Check', 'woocommerce')));
        } else {
            $order->add_order_note(sprintf(__('Refunding of $%s for order #%s through %s failed with unknown error', 'woocommerce')));
        }

        return false;
    }

    /**
     * @param $template_name
     * @param $args
     * @return string
     */
    function wc_humm_get_template($template_name, $args)
    {
        return wc_get_template_html($template_name, $args, $this->template_path(), $this->plugin_path() . 'templates/');
    }

    /**
     * @return string
     */
    function template_path()
    {
        return trailingslashit('oxipay');
    }

    /**
     * @return string
     */
    function plugin_path()
    {
        return WC_HUMM_PATH;
    }

    /**
     * @return int
     */
    function getThreshold()
    {
        $thresholdAmount = sprintf('%s_thresholdAmount', $this->pluginFileName);
        return isset($this->settings[$thresholdAmount]) ? $this->settings[$thresholdAmount] : 0;
    }

    /**
     * Load javascript for Wordpress admin
     */
    abstract public function admin_scripts();

    /**
     * Load JavaScript for the checkout page
     */
    abstract public function flexi_enqueue_script();
}