<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Flexi_Gateway_Oxipay' ) ) {
    require_once( 'WC_Flexi_Gateway_Oxipay.php' );
}

class WC_Oxipay_Gateway extends WC_Flexi_Gateway_Oxipay {

    //todo: localise these string constants
    const PLUGIN_NO_GATEWAY_LOG_MSG = 'Transaction attempted with no gateway URL set. Please check oxipay plugin configuration, and provide a gateway URL.';
    const PLUGIN_MISCONFIGURATION_CLIENT_MSG = 'There is an issue with the site configuration, which has been logged. We apologize for any inconvenience. Please try again later. ';
    const PLUGIN_NO_API_KEY_LOG_MSG = 'Transaction attempted with no API key set. Please check oxipay plugin configuration, and provide an API Key';
    const PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG = 'Transaction attempted with no Merchant ID key. Please check oxipay plugin configuration, and provide an Merchant ID.';
    const PLUGIN_NO_REGION_LOG_MSG = 'Transaction attempted with no Oxipay region set. Please check oxipay plugin configuration, and provide an Oxipay region.';

    public $shop_details;

    function __construct() {
        $config = new Oxipay_Config();
        parent::__construct( $config );

        $this->method_description = __( 'Easy to setup installment payment plans from ' . $config->getDisplayName() );
        $this->title              = __( $config->getDisplayName(), 'woocommerce' );
        $this->icon               = plugin_dir_url( __FILE__ ) . 'images/' . $config->getDisplayName() . '.png';
        $this->shop_details       = __( $config->getDisplayName() . ' Payment', 'woocommerce' );
        $this->order_button_text  = __( 'Proceed to ' . $config->getDisplayName(), 'woocommerce' );
        $this->description        = "<br>";

        add_action( 'admin_notices', array( $this, 'admin_notice_rename_to_humm' ) );
    }

    function admin_notice_rename_to_humm() {
        $show_times = get_option( 'humm_admin_notice_update_show_times' );
        if ( ! $show_times ) {
            update_option( 'humm_admin_notice_update_show_times', 1 );
        }
        if ( $show_times < 3 && $this->settings['country'] == 'AU' ) {
            update_option( 'humm_admin_notice_update_show_times', $show_times + 1 );
            printf( '<div class="notice notice-info is-dismissible"><p><strong>humm</strong> <img alt="humm logo" src="https://widgets.shophumm.com.au/content/images/logo-orange.svg" height="16px" /> is the new Oxipay!</p></div>' );
        }
    }

    /**
     * Load JavaScript for the checkout page
     */
    function flexi_enqueue_script() {

        wp_register_script( 'oxipay_gateway', plugins_url( '/js/oxipay.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
        wp_register_script( 'oxipay_modal', plugins_url( '/js/oxipay_modal.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
        wp_localize_script( 'oxipay_modal', 'php_vars', [ 'plugin_url' => plugins_url( "", __FILE__ ) ] );
        wp_register_script( 'iframeResizer', plugins_url( '/js/resizer/iframeResizer.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
        wp_enqueue_script( 'oxipay_gateway' );
        wp_enqueue_script( 'oxipay_modal' );
        wp_enqueue_script( 'iframeResizer' );
    }

    /**
     * Load javascript for Wordpress admin
     */
    function admin_scripts() {
        wp_register_script( 'oxipay_admin', plugins_url( '/js/admin.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
        wp_enqueue_script( 'oxipay_admin' );
    }

    function add_price_widget() {
        // do we really need a global here?
        global $product;
        if ( $this->settings['enabled'] == 'yes' && isset( $this->settings['price_widget'] ) && $this->settings['price_widget'] == 'yes' ) {
            $country_domain = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'oxipay.co.nz' : 'shophumm.com.au';
            $maximum = $this->getMaxPrice();
            $name = $this->currentConfig->getDisplayName();

            $advanced = isset( $this->settings['price_widget_advanced'] ) && $this->settings['price_widget_advanced'] === 'yes';

            // data-max
            $script = '<script ';
            if ($maximum > 0)
                $script .= 'data-max="' . $maximum . '" ';

            // Script URL
            $script .= 'src="https://widgets.' . $country_domain . '/content/scripts/';
            $script .= $name === 'humm' ? 'price-info' : 'payments';
            $script .= '.js?';

            //  Widget type - Dynamic or Static
            if ( $advanced && isset( $this->settings['price_widget_dynamic_enabled'] ) && $this->settings['price_widget_dynamic_enabled'] === 'yes') {
                if ( isset( $this->settings['price_widget_price_selector'] )) {
                    $selector = $this->settings['price_widget_price_selector'];
                } else {
                    $selector = '.price .woocommerce-Price-amount.amount';
                }

                $script .= 'price-selector=' . urlencode($selector);
            } else {
                $script .= 'productPrice=' . wc_get_price_to_display( $product );
            }

            // Widget location in page
            $script .= '&element=';
            if ( $advanced && isset( $this->settings['price_widget_element_selector'] ) && $this->settings['price_widget_element_selector'] !== '') {
                $script .= urlencode($this->settings['price_widget_element_selector']);
            } else {
                $script .= '%23' . $name . '-price-info-anchor';
            }

            // Merchant type
            if ( $name === 'humm' ) {
                $merchant_type = "&" . $this->settings['merchant_type'];
                if ( $merchant_type !== '&both' )
                    $script .= $merchant_type;
            }

            $script .= '"></script>';
            echo $script;
        }
    }

    function add_price_widget_anchor() {
        echo '<div id="' . $this->currentConfig->getDisplayName() . '-price-info-anchor"></div>';
    }

    function add_top_banner_widget() {
        if ( isset( $this->settings['top_banner_widget'] ) && $this->settings['top_banner_widget'] == 'yes' ) {
            if ( ( isset( $this->settings['top_banner_widget_homepage_only'] ) && $this->settings['top_banner_widget_homepage_only'] == 'yes' ) && ! is_front_page() ) {
                return;
            } else {
                $country_domain = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'co.nz' : 'com.au';
                if ( $country_domain == "com.au" && $this->settings['enabled'] == 'yes' ) {
                    if ( $this->currentConfig->getDisplayName() == 'humm' ) {
                        echo '<script id="humm-top-banner-script" src="https://widgets.shophumm.' . $country_domain . '/content/scripts/top-banner.js?element=header"></script>';
                    } else {
                        echo '<script id="oxipay-top-banner-script" src="https://widgets.oxipay.' . $country_domain . '/content/scripts/top-banner.js?element=header"></script>';
                    }
                }
            }
        }
    }

    public function get_settings() {
        // these are safe values to export via javascript
        $whitelist = [
            'enabled'         => null,
            'display_details' => null,
            'title'           => null,
            'description'     => null,
            'shop_details'    => null,
            'shop_name'       => null,
            'country'         => null,
            'use_modal'       => null
        ];
        foreach ( $whitelist as $k => $v ) {
            if ( isset( $this->settings[ $k ] ) ) {
                $whitelist[ $k ] = $this->settings[ $k ];
            }
        }

        // if humm, always set 'use_modal' to 'no'
        if ( $whitelist['use_modal'] == 'yes' ) {
            if ( $this->currentConfig->getDisplayName() == 'humm' ) {
                $whitelist['use_modal'] = 'no';
            }
        }

        return $whitelist;
    }
}