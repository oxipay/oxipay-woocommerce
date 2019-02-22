<?php
if(!class_exists('WC_Flexi_Gateway')) {
	require_once( 'WC_Flexi_Gateway.php' );
}

class WC_Oxipay_Gateway extends WC_Flexi_Gateway {

        //todo: localise these string constants
        const PLUGIN_NO_GATEWAY_LOG_MSG = 'Transaction attempted with no gateway URL set. Please check oxipay plugin configuration, and provide a gateway URL.';
        const PLUGIN_MISCONFIGURATION_CLIENT_MSG = 'There is an issue with the site configuration, which has been logged. We apologize for any inconvenience. Please try again later. ';
        const PLUGIN_NO_API_KEY_LOG_MSG = 'Transaction attempted with no API key set. Please check oxipay plugin configuration, and provide an API Key';
        const PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG = 'Transaction attempted with no Merchant ID key. Please check oxipay plugin configuration, and provide an Merchant ID.';
        const PLUGIN_NO_REGION_LOG_MSG = 'Transaction attempted with no Oxipay region set. Please check oxipay plugin configuration, and provide an Oxipay region.';

        public $shop_details;

        function __construct() {
            $config = new Oxipay_Config();
            parent::__construct($config);

            $this->method_description = __( 'Easy to setup installment payment plans from ' . $config->getDisplayName() );
            $this->title              = __( $config->getDisplayName() , 'woocommerce' );
            $this->icon               = plugin_dir_url( __FILE__ ) . 'images/'.$config->getDisplayName() . '.png';
            $this->shop_details       = __($config->getDisplayName() . ' Payment', 'woocommerce' );
            $this->order_button_text  = __( 'Proceed to ' . $config->getDisplayName(), 'woocommerce' );

            $country_domain = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'co.nz' : 'com.au';
            $payments_script = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'payments' : 'payments-weekly';
            $checkout_total = (WC()->cart)? WC()->cart->get_totals()['total'] : "0";
            $this->description = __( '<div id="checkout_method_oxipay"></div><script id="oxipay-checkout-price-widget-script" src="https://widgets.oxipay.'.$country_domain.'/content/scripts/'.$payments_script.'.js?used_in=checkout&productPrice='.$checkout_total.'&element=%23checkout_method_oxipay"></script>', 'woocommerce' );
        }



        /**
         * Load JavaScript for the checkout page
         */
        function flexi_enqueue_script() {
            
            wp_register_script('oxipay_gateway', plugins_url( '/js/oxipay.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_register_script('oxipay_modal', plugins_url( '/js/oxipay_modal.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_localize_script('oxipay_modal', 'php_vars', ['plugin_url' => plugins_url("", __FILE__)]);
            wp_register_script('iframeResizer', plugins_url( '/js/resizer/iframeResizer.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_enqueue_script('oxipay_gateway');
            wp_enqueue_script('oxipay_modal');
            wp_enqueue_script('iframeResizer');
        }


        /**
         * Load javascript for Wordpress admin
         */
        function admin_scripts(){
            wp_register_script( 'oxipay_admin', plugins_url( '/js/admin.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
            wp_enqueue_script( 'oxipay_admin' );
        }

        function add_price_widget(){
            // do we really need a global here?
            global $product;
            if(isset($this->settings['price_widget']) && $this->settings['price_widget']=='yes'){
                $country_domain = 'com.au';
                $widget_type = 'payments-weekly';
                if(isset($this->settings['country']) && $this->settings['country']=='NZ'){
                    $country_domain = 'co.nz';
                    $widget_type = 'payments';
                }
                
                $maximum = $this->getMaxPrice();
                $price = wc_get_price_to_display($product);
                if($maximum == 0 || $price <= $maximum) {
                    echo '<div id="oxipay-price-info-anchor"></div><script id="oxipay-price-info" src="https://widgets.oxipay.'.$country_domain.'/content/scripts/'.$widget_type.'.js?productPrice='.$price.'&element=%23oxipay-price-info-anchor"></script>';
                }
            }
        }

	function add_top_banner_widget() {
		if ( isset( $this->settings['top_banner_widget'] ) && $this->settings['top_banner_widget'] == 'yes' ) {
			if ( ( isset( $this->settings['top_banner_widget_homepage_only'] ) && $this->settings['top_banner_widget_homepage_only'] == 'yes' ) && ! is_front_page() ) {
				return;
			} else {
				$country_domain = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'co.nz' : 'com.au';
				if ( $country_domain == "com.au" ) {
					echo '<script id="oxipay-top-banner-script" src="https://widgets.oxipay.' . $country_domain . '/content/scripts/top-banner.js?element=header"></script>';
				}
			}
		}
    }
}