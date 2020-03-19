<?php
defined('ABSPATH') || exit;

if (!class_exists('WC_Flexi_Gateway_Oxipay')) {
    require_once('WC_Flexi_Gateway_Oxipay.php');
}

/**
 * Class WC_Oxipay_Gateway
 * @author roger.bi@flexogroup.com
 * @copyright flexigroup
 */
class WC_Oxipay_Gateway extends WC_Flexi_Gateway_Oxipay
{
    /**
     * @constant
     */
    const PLUGIN_NO_GATEWAY_LOG_MSG = 'Transaction attempted with no gateway URL set. Please check oxipay plugin configuration, and provide a gateway URL.';
    const PLUGIN_MISCONFIGURATION_CLIENT_MSG = 'There is an issue with the site configuration, which has been logged. We apologize for any inconvenience. Please try again later. ';
    const PLUGIN_NO_API_KEY_LOG_MSG = 'Transaction attempted with no API key set. Please check oxipay plugin configuration, and provide an API Key';
    const PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG = 'Transaction attempted with no Merchant ID key. Please check oxipay plugin configuration, and provide an Merchant ID.';
    const PLUGIN_NO_REGION_LOG_MSG = 'Transaction attempted with no Oxipay region set. Please check oxipay plugin configuration, and provide an Oxipay region.';
    public static $littleBigFlag = 0;
    public static $big_small_flag = array(
        "big" => '&BigThings',
        "little" => '&LittleThings'
    );
    public $shop_details;

    /**
     * WC_Oxipay_Gateway constructor
     */
    function __construct()
    {
        $config = new Oxipay_Config();
        parent::__construct($config);

        $this->method_description = __('Easy to setup installment payment plans from ' . $config->getDisplayName());
        $this->title = __($config->getDisplayName(), 'woocommerce');
        $this->icon = plugin_dir_url(__FILE__) . '../assets/images/' . $config->getDisplayName() . '.png';
        $this->shop_details = __($config->getDisplayName() . ' Payment', 'woocommerce');
        $this->order_button_text = __('Proceed to ' . $config->getDisplayName(), 'woocommerce');
        $this->description = "<br>";

        add_action('admin_notices', array($this, 'admin_notice_rename_to_humm'));
    }

    /**
     * admin_notice_rename_to_humn
     */

    function admin_notice_rename_to_humm()
    {
        $show_times = get_option('humm_admin_notice_update_show_times');
        if (!$show_times) {
            update_option('humm_admin_notice_update_show_times', 1);
        }
        if ($show_times < 3 && $this->settings['country'] == 'AU') {
            update_option('humm_admin_notice_update_show_times', $show_times + 1);
            printf('<div class="notice notice-info is-dismissible"><p><strong>humm</strong> <img alt="humm logo" src="https://widgets.shophumm.com.au/content/images/logo-orange.svg" height="16px" /> is the new Oxipay!</p></div>');
        }
    }

    /**
     * flexi_enqueue_script
     */
    function flexi_enqueue_script()
    {
        wp_register_script('oxipay_gateway', plugins_url('/assets/js/oxipay.js', __FILE__), array('jquery'), '0.4.5');
        wp_register_script('oxipay_modal', plugins_url('/assets/js/oxipay_modal.js', __FILE__), array('jquery'), '0.4.5');
        wp_localize_script('oxipay_modal', 'php_vars', ['plugin_url' => plugins_url("", __FILE__)]);
        wp_register_script('iframeResizer', plugins_url('/assets/js/resizer/iframeResizer.js', __FILE__), array('jquery'), '0.4.5');
        wp_enqueue_script('oxipay_gateway');
        wp_enqueue_script('oxipay_modal');
        wp_enqueue_script('iframeResizer');
    }

    /**
     * Load javascript for Wordpress admin
     */
    function admin_scripts()
    {
        wp_enqueue_style('oxipay_css', plugins_url('../assets/css/oxipay.css', __FILE__));
        wp_register_script('oxipay_admin', plugins_url('../assets/js/admin.js', __FILE__), array('jquery'), '0.4.5');
        wp_enqueue_script('oxipay_admin');

    }

    /**
     * add_price_widget
     */
    function add_price_widget()
    {
        echo $this->get_widget_script();
    }

    /**
     * @return string
     */

    function get_widget_script()
    {
        global $product;
        $threshold = array("little" => "&LittleThings", "big" => "&BigThings");
        $thresholdPrice = $this->getThreshold();
        if (is_product()) {
            $displayPrice = wc_get_price_to_display($product);
        }
        $script ='';
        if ($this->settings['country'] == 'NZ') {
            if ($this->settings['enabled'] == 'yes' && isset($this->settings['price_widget']) && $this->settings['price_widget'] == 'yes') {
                if (floatval($displayPrice) <= floatval($thresholdPrice)) {
                    $ec_pattern = sprintf("%s%s%s%s", '<script src= "https://widgets.shophumm.co.nz/content/scripts/price-info.js?productPrice=', $displayPrice, '&LittleThings&little=F5&element=%23Humm-price-info-anchor', '"></script>');
                    $ec_pattern = $ec_pattern . sprintf("%s%s%s%s", '<script src= "https://widgets.shophumm.co.nz/content/scripts/price-info.js?productPrice=', $displayPrice, '&LittleThings&little=w10&element=%23Humm-price-info-anchor', '"></script>');
                }
                else {
                    $ec_pattern = sprintf("%s%s%s%s", '<script src= "https://widgets.shophumm.co.nz/content/scripts/price-info.js?productPrice=', $displayPrice, '&big=M6&element=%23Humm-price-info-anchor', '"></script>');
                }
                return $ec_pattern;
            }
        }
        else if ($this->settings['country'] == 'AU'){
          if ($this->settings['enabled'] == 'yes' && isset($this->settings['price_widget']) && $this->settings['price_widget'] == 'yes') {
            $country_domain = 'shophumm.com.au';
            $maximum = $this->getMaxPrice();
            $name = 'Humm';
            $advanced = isset($this->settings['price_widget_advanced']) && $this->settings['price_widget_advanced'] === 'yes';
            $script = '<script ';
            if ($maximum > 0)
                $script .= 'data-max="' . $maximum . '" ';
            $script .= 'src="https://widgets.' . $country_domain . '/content/scripts/';
            $script .= $name === 'Humm' ? 'price-info' : 'payments';
            $script .= '.js?';
            if ($advanced && isset($this->settings['price_widget_dynamic_enabled']) && $this->settings['price_widget_dynamic_enabled'] === 'yes') {
                if (isset($this->settings['price_widget_price_selector'])) {
                    $selector = $this->settings['price_widget_price_selector'];
                } else {
                    $selector = '.price .woocommerce-Price-amount.amount';
                }
                $script .= 'price-selector=' . urlencode($selector);
            } else {
                $script .= 'productPrice=' . $displayPrice;
            }

            $bigThing = '';

            if (floatval($displayPrice) <= floatval($thresholdPrice)) {
                $script .= $threshold['little'];
            } else {
                self::$littleBigFlag = true;
                $script = '<div id="BigThing"></div>' . $script;
                $script .= $threshold['big'];
            }
            $script .= '&element=';
            if ($advanced && isset($this->settings['price_widget_element_selector']) && $this->settings['price_widget_element_selector'] !== '') {
                $script .= urlencode($this->settings['price_widget_element_selector']);
            } else {
                if ($bigThing)
                    $script .= '%23' . 'BigThing';
                else
                    $script .= '%23' . $name . '-price-info-anchor';
            }

            if ($name === 'Humm') {
                $merchant_type = "&" . $this->settings['merchant_type'];
                if ($merchant_type !== '&both')
                    $script .= $merchant_type;
            }

            $script .= '"></script>';

        }
            if (floatval($displayPrice) <= floatval($thresholdPrice))
               return $script;
           }
    }

    /**
     * add_price_widget_cart
     */
    function add_price_widget_cart()
    {
        global $woocommerce;
        $ec_identity = 'little';
        if ($this->settings['enabled'] == 'yes' && isset($this->settings['price_widget']) && $this->settings['price_widget'] == 'yes') {
            $threshold_price = $this->getThreshold();
            $cart_total = $woocommerce->cart->total;
            if (is_cart()) {
                if ($this->settings['country'] == 'AU') {
                    $ec_pattern = sprintf("%s%s%s%s", '<script src =" https://widgets.shophumm.com.au/content/scripts/price-info.js?productPrice=', $cart_total, 'pattern', '"></script>');
                    if (floatval($cart_total) >= floatval($threshold_price))
                        $ec_identity = 'big';
                    echo str_replace('pattern', self::$big_small_flag[$ec_identity], $ec_pattern);
                }
                else if ( $this->settings['country'] == 'NZ') {
                    if (floatval($cart_total) <= floatval($threshold_price)) {
                        $ec_pattern = sprintf("%s%s%s%s", '<script src= "https://widgets.shophumm.co.nz/content/scripts/price-info.js?productPrice=', $cart_total, '&LittleThings&little=F5', '"></script>');
                        $ec_pattern = $ec_pattern . sprintf("%s%s%s%s", '<script src= "https://widgets.shophumm.co.nz/content/scripts/price-info.js?productPrice=', $cart_total, '&LittleThings&little=w10', '"></script>');
                    }
                    else {
                        $ec_pattern = sprintf("%s%s%s%s", '<script src= "https://widgets.shophumm.co.nz/content/scripts/price-info.js?productPrice=', $cart_total, '&big=M6', '"></script>');
                    }
                    echo $ec_pattern;
                }
            }
        }
    }

    /**
     * add_price_widget_anchor()
     */
    function add_price_widget_anchor()
    {
        global $product;
        echo '<div id="Humm-price-info-anchor"></div>';
        if ($this->settings['country'] == 'AU') {
            if ($this->settings['enabled'] == 'yes' && isset($this->settings['price_widget']) && $this->settings['price_widget'] == 'yes') {
                $thresholdPrice = $this->getThreshold();
                if (is_product()) {
                    $displayPrice = wc_get_price_to_display($product);
                }
                if (floatval($displayPrice) >= floatval($thresholdPrice)) {
                    $ec = '<div id="testBig"></div>' . '<script src =' . '" https://widgets.shophumm.com.au/content/scripts/price-info.js?productPrice=' . "<?php echo $displayPrice ?>" . '&BigThings&element=%23testBig' . '"></script>';
                    echo $ec;
                }
            }
        }
    }

    /**
     * add_top_banner_widget
     */

    function add_top_banner_widget()
    {
        if (isset($this->settings['top_banner_widget']) && $this->settings['top_banner_widget'] == 'yes') {
            $country_domain = $this->settings['country'] == 'AU'? 'com.au':'co.nz';
            if ((isset($this->settings['top_banner_widget_homepage_only']) && $this->settings['top_banner_widget_homepage_only'] == 'yes') && !is_front_page()) {
                return;
            } else {
                echo '<div style="margin-bottom: 20px">';
                echo '<script id="humm-top-banner-script" src="https://widgets.shophumm.' . $country_domain . '/content/scripts/more-info-small-slices.js"></script>';
                echo '</div>';
            }
        }
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

        // if humm, always set 'use_modal' to 'no'
        if ($whitelist['use_modal'] == 'yes') {
            if ($this->currentConfig->getDisplayName() == 'Humm') {
                $whitelist['use_modal'] = 'no';
            }
        }

        return $whitelist;
    }
}
