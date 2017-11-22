<?php
if(!class_exists('WC_Flexi_Gateway')) {
	require_once( 'WC_Flexi_Gateway.php' );
}

class WC_CertegyEzipay_Gateway extends WC_Flexi_Gateway {

        //todo: localise these string constants
        const PLUGIN_NO_GATEWAY_LOG_MSG = 'Transaction attempted with no gateway URL set. Please check Certegy EziPay plugin configuration, and provide a gateway URL.';
        const PLUGIN_MISCONFIGURATION_CLIENT_MSG = 'There is an issue with the site configuration, which has been logged. We apologize for any inconvenience. Please try again later. ';
        const PLUGIN_NO_API_KEY_LOG_MSG = 'Transaction attempted with no API key set. Please check Certegy EziPay plugin configuration, and provide an API Key';
        const PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG = 'Transaction attempted with no Merchant ID key. Please check Certegy EziPay plugin configuration, and provide an Merchant ID.';
        const PLUGIN_NO_REGION_LOG_MSG = 'Transaction attempted with no Certegy EziPay region set. Please check Certegy EziPay plugin configuration, and provide an Certegy EziPay region.';

        function __construct() {
            $config = new Ezipay_Config();

            $this->method_description = __( 'Easy to setup installment payment plans from ' . $config::DISPLAY_NAME );
            $this->title              = __( $config::DISPLAY_NAME , 'woocommerce' );
            $this->description        = __( 'No Interest Ever!.</strong><br/> Shop today, pay over time with Certegy Ezi-Pay', 'woocommerce' );
            $this->icon               = plugin_dir_url( __FILE__ ) .  'images/ezipay.png';
            $this->shop_details       = __($config::DISPLAY_NAME . ' Payment', 'woocommerce' );
            $this->order_button_text      = __( 'Proceed to ' . $config::DISPLAY_NAME, 'woocommerce' );

            parent::__construct($config);
        }

        /**
         * Load JavaScript for the checkout page
         */
         function flexi_enqueue_script() {
            
            wp_register_script('ezipay_gateway', plugins_url( '/js/ezipay.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_register_script('ezipay_modal', plugins_url( '/js/ezipay_modal.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_localize_script('ezipay_modal', 'php_vars', ['plugin_url' => plugins_url("", __FILE__)]);
            wp_register_script('iframeResizer', plugins_url( '/js/resizer/iframeResizer.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_enqueue_script('ezipay_gateway');
            wp_enqueue_script('ezipay_modal');
            wp_enqueue_script('iframeResizer');
        }


        /**
         * Load javascript for Wordpress admin
         */
         function admin_scripts(){
            wp_register_script( 'ezipay_admin', plugins_url( '/js/admin.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_enqueue_script( 'ezipay_admin' );
        }

        function add_price_widget(){
            // do we really need a global here?
            global $product;
            if(isset($this->settings['price_widget']) && $this->settings['price_widget']=='yes'){
                $country_domain = 'com.au';
                if(isset($this->settings['country']) && $this->settings['country']=='NZ'){
                    $country_domain = 'co.nz';
                }
                
                echo '<script id="ezipay-price-info" src="https://widgets.certegyezipay.'.$country_domain.'/content/scripts/price-info.js?productPrice='.wc_get_price_to_display($product).'"></script>';
            }
        }
    }