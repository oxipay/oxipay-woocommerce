<?php

defined( 'ABSPATH' ) || exit;

abstract class WC_Flexi_Gateway_Oxipay extends WC_Payment_Gateway {
    //current version of the plugin- used to run upgrade tasks on update
    public $plugin_current_version;

    public $logger = null;
    private $logContext;

    protected $currentConfig = null;
    protected $pluginDisplayName = null;
    protected $pluginFileName = null;
    protected $flexi_payment_preselected = false;

    function __construct( $config ) {

        $this->currentConfig     = $config;
        $this->pluginDisplayName = $config->getDisplayName();
        $this->pluginFileName    = strtolower( $config->getPluginFileName() );

        // where available we can use logging to assist with debugging
        if ( function_exists( 'wc_get_logger' ) ) {
            $this->logger     = wc_get_logger();
            $this->logContext = array( 'source' => $this->pluginDisplayName );
        }

        $this->id           = $this->pluginFileName;
        $this->has_fields   = false;
        $this->method_title = __( $this->pluginDisplayName, 'woocommerce' );

        $this->plugin_current_version = $config->getPluginVersion();

        $this->init_form_fields();
        $this->init_settings();
        if ( is_admin() ) {
            $this->init_upgrade_process();
        }

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        // when we are on the checkout page we want to provide
        // the checkout process through a modal dialog box
        if ( ! is_admin() ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'flexi_enqueue_script' ) );
        }

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
            $this,
            'process_admin_options'
        ) );
        add_action( 'wp_footer', array( $this, 'add_top_banner_widget' ) );
        add_action( 'woocommerce_single_product_summary', array( $this, 'add_price_widget' ) );
        add_filter( 'woocommerce_thankyou_order_id', array( $this, 'payment_finalisation' ) );
        add_filter( 'the_title', array( $this, 'order_received_title' ), 11 );
        add_action( 'woocommerce_before_checkout_form', array( $this, 'display_min_max_notice' ) );
        add_action( 'woocommerce_before_cart', array( $this, 'display_min_max_notice' ) );
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'display_min_max_filter' ) );
        add_filter( 'woocommerce_available_payment_gateways', array( $this, 'preselect_flexi' ) );
        add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'thankyou_page_message' ) );

        $preselect_button_order = $this->settings["preselect_button_order"] ? $this->settings["preselect_button_order"] : '20';
        add_action( 'woocommerce_proceed_to_checkout', array(
            $this,
            "flexi_checkout_button"
        ), $preselect_button_order );
    }

    abstract public function add_price_widget();

    function flexi_checkout_button() {
        if ( $this->settings["preselect_button_enabled"] == "yes" && $this->settings['enabled'] == 'yes' ) {
            echo '<div><a href="' . esc_url( wc_get_checkout_url() ) . '?' . $this->pluginDisplayName . '_preselected=true" class="checkout-button button" style="font-size: 1.2em; padding-top: 0.4em; padding-bottom: 0.4em; background-color: #' . $this->currentConfig->getButtonColor() . '; color: #FFF;">Check out with ' . $this->pluginDisplayName . '</a></div>';
        }
    }

    function display_min_max_notice() {
        $minimum = $this->getMinPrice();
        $maximum = $this->getMaxPrice();

        if ( $minimum != 0 && WC()->cart->total < $minimum ) {
            if ( is_checkout() ) {
                wc_print_notice(
                    sprintf( "You must have an order with a minimum of %s to use %s. Your current order total is %s.",
                        wc_price( $minimum ),
                        $this->pluginDisplayName,
                        wc_price( WC()->cart->total )
                    ), 'notice'
                );
            }
        } elseif ( $maximum != 0 && WC()->cart->total > $maximum ) {
            if ( is_checkout() ) {
                wc_print_notice(
                    sprintf( "You must have an order with a maximum of %s to use %s. Your current order total is %s.",
                        wc_price( $maximum ),
                        $this->pluginDisplayName,
                        wc_price( WC()->cart->total )
                    ), 'notice'
                );
            }
        }
    }

    protected function getMinPrice() {
        $field = sprintf( '%s_minimum', $this->pluginFileName );

        return isset( $this->settings[ $field ] ) ? $this->settings[ $field ] : 0;
    }

    protected function getMaxPrice() {
        $field = sprintf( '%s_maximum', $this->pluginFileName );

        return isset( $this->settings[ $field ] ) ? $this->settings[ $field ] : 0;
    }

    function display_min_max_filter( $available_gateways ) {
        $minimum = $this->getMinPrice();
        $maximum = $this->getMaxPrice();
        if ( ( $minimum != 0 && WC()->cart->total < $minimum ) || ( $maximum != 0 && WC()->cart->total > $maximum ) ) {
            if ( isset( $available_gateways[ $this->pluginFileName ] ) ) {
                unset( $available_gateways[ $this->pluginFileName ] );
            }
        }

        return $available_gateways;
    }

    function preselect_flexi( $available_gateways ) {
        if ( isset( $_GET[ $this->pluginDisplayName . "_preselected" ] ) ) {
            $this->flexi_payment_preselected = $_GET[ $this->pluginDisplayName . "_preselected" ];
        }

        if ( ! empty( $available_gateways ) ) {
            if ( $this->flexi_payment_preselected == "true" ) {
                foreach ( $available_gateways as $gateway ) {
                    if ( strtolower( $gateway->id ) == $this->pluginFileName ) {
                        WC()->session->set( 'chosen_payment_method', $gateway->id );
                    }
                }
            }
        }

        return $available_gateways;
    }

    /**
     * Log a message using the 2.7 logging infrastructure
     *
     * @param string $message Message log
     * @param string $level WC_Log_Levels
     */
    public function log( $message, $level = WC_Log_Levels::DEBUG ) {
        if ( $this->logger != null && $this->settings["enable_logging"] ) {
            $this->logger->log( $level, $message, $this->logContext );
        }
    }

    /**
     * Load javascript for Wordpress admin
     */
    abstract protected function admin_scripts();

    /**
     * Load JavaScript for the checkout page
     */
    abstract protected function flexi_enqueue_script();

    /**
     * WC override to display the administration property page
     */
    function init_form_fields() {
        //Build options for the country select field from the config
        $countryOptions = array( '' => __( 'Please select...', 'woocommerce' ) );

        foreach ( $this->currentConfig->countries as $countryCode => $country ) {
            $countryOptions[ $countryCode ] = __( $country['name'], 'woocommerce' );
        }

        $merchantTypes = array(
            'both'         => __( 'both (default)', 'woocommerce' ),
            'BigThings'    => 'BigThings only',
            'LittleThings' => 'LittleThings only',
        );

        $this->form_fields = array(
            'enabled'                             => array(
                'title'       => __( 'Enabled', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable the ' . $this->pluginDisplayName . ' Payment Gateway', 'woocommerce' ),
                'default'     => 'yes',
                'description' => 'Disable ' . $this->pluginDisplayName . ' services, your customers will not be able to use our easy installment plans.',
                'desc_tip'    => true
            ),
            'country'                             => array(
                'title'             => __( $this->pluginDisplayName . ' Region', 'woocommerce' ),
                'type'              => 'select',
                'class'             => 'wc-enhanced-select',
                'description'       => 'Select the option that matches your retailer agreement.',
                'options'           => $countryOptions,
                'desc_tip'          => true,
                'custom_attributes' => array( 'required' => 'required' ),
            ),
            'use_test'                            => array(
                'title'       => __( 'Test Mode', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Use Test Mode', 'woocommerce' ),
                'default'     => 'no',
                'description' => __( 'While test mode is enabled, transactions will be simulated and cards will not be charged', 'woocommerce' )
            ),
            "{$this->pluginFileName}_merchant_id" => array(
                'id'                => $this->pluginFileName . '_merchant_id',
                'title'             => __( 'Merchant ID', 'woocommerce' ),
                'type'              => 'text',
                'default'           => '',
                'description'       => $this->pluginDisplayName . ' will have supplied you with your ' . $this->pluginDisplayName . ' Merchant ID. Contact us if you cannot find it.',
                'desc_tip'          => true,
                'custom_attributes' => array( 'required' => 'required' ),
            ),
            $this->pluginFileName . '_api_key'    => array(
                'id'                => $this->pluginFileName . '_api_key',
                'title'             => __( 'API Key', 'woocommerce' ),
                'type'              => 'text',
                'default'           => '',
                'description'       => $this->pluginDisplayName . ' will have supplied you with your ' . $this->pluginDisplayName . ' API key. Contact us if you cannot find it.',
                'desc_tip'          => true,
                'custom_attributes' => array( 'required' => 'required' ),
            ),
            'enable_logging'                      => array(
                'title'       => __( 'Enable Logging', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable logging', 'woocommerce' ),
                'default'     => 'yes',
                'description' => __( 'The ' . $this->pluginDisplayName . ' logs are available at the <a href="' . admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">WooCommerce status page</a>', 'woocommerce' )
            ),
            'display_settings'                    => array(
                'title' => __( 'Display Settings', 'woocommerce' ),
                'type'  => 'title',
            ),
            'price_widget'                        => array(
                'title'       => __( 'Price Widget', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable the ' . $this->pluginDisplayName . ' Price Widget', 'woocommerce' ),
                'default'     => 'yes',
                'description' => 'Display a price widget in each product page.',
            ),
            'price_widget_dynamic_enabled'            => array(
                'title'       => __( 'Dynamic Price Widget', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Enable the humm Dynamic Price Widget' ),
                'default'     => 'no',
                'description' => 'Price widget will automatically update breakdown if the product price changes. Uses the CSS selector below to track changes. Leave disabled if unsure.',
            ),
            'price_widget_selector' => array(
                'id'                => $this->pluginFileName . '_api_key',
                'title'             => __( 'Price Widget CSS Selector', 'woocommerce' ),
                'type'              => 'text',
                'default'           => '.price .woocommerce-Price-amount.amount',
                'description'       => 'CSS selector for the element containing the product price',
                'desc_tip'          => true,
            ),
            'preselect_button_enabled'            => array(
                'title'       => __( 'Pre-select Checkout Button', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Add a "Checkout with ' . $this->pluginDisplayName . '" button in Cart page', 'woocommerce' ),
                'default'     => 'yes',
                'description' => __( 'Add a "Checkout with ' . $this->pluginDisplayName . '" button in Cart page that takes customer to Checkout page and have ' . $this->pluginDisplayName . ' pre-selected', 'woocommerce' ),
            ),
            'preselect_button_order'              => array(
                'title'       => __( 'Pre-select Button Order', 'woocommerce' ),
                'type'        => 'text',
                'label'       => __( 'Pre-select Button Order', 'woocommerce' ),
                'default'     => '20',
                'description' => __( 'Position the "checkout with ' . $this->pluginDisplayName . ' button" in Cart page if there are multiple checkout buttons. Default is 20. Smaller number moves the button ahead and larger number moves it lower in the list of checkout buttons.', 'woocommerce' ),
                'desc_tip'    => true
            ),
            'au_settings'                         => array(
                'title' => __( '', 'woocommerce' ),
                'type'  => 'title',
            ),
            'merchant_type'                       => array(
                'title'             => __( 'Humm Merchant Type', 'woocommerce' ),
                'type'              => 'select',
                'class'             => 'wc-enhanced-select',
                'description'       => 'Select the option that matches your retailer agreement.',
                'options'           => $merchantTypes,
                'desc_tip'          => true,
                'custom_attributes' => array( 'required' => 'required' ),
            ),
            'top_banner_widget'                   => array(
                'title'           => __( 'Humm Top Banner Widget', 'woocommerce' ),
                'label'           => __( 'Enable the ' . $this->pluginDisplayName . ' Top Banner Widget', 'woocommerce' ),
                'default'         => 'no',
                'type'            => 'checkbox',
                'checkboxgroup'   => 'start',
                'show_if_checked' => 'option',
                'description'     => 'Display a top banner.',
            ),
            'top_banner_widget_homepage_only'     => array(
                'label'           => __( 'Top Banner Widget Shows on FrontPage Only', 'woocommerce' ),
                'default'         => 'yes',
                'type'            => 'checkbox',
                'checkboxgroup'   => 'end',
                'show_if_checked' => 'yes',
                'description'     => 'When the top banner enabled, it shows in homepage only (if checked), or shows in every page (if unchecked)',
                'autoload'        => false,
            ),
            'nz_settings'                         => array(
                'title' => __( '', 'woocommerce' ),
                'type'  => 'title',
            ),
            'use_modal'                           => array(
                'title'       => __( 'Modal Checkout', 'woocommerce' ),
                'type'        => 'checkbox',
                'label'       => __( 'Modal Checkout', 'woocommerce' ),
                'default'     => 'no',
                'description' => __( 'The customer will be forwarded to checkout in a modal dialog', 'woocommerce' )
            ),
            'shop_settings'                       => array(
                'title' => __( 'Shop Settings', 'woocommerce' ),
                'type'  => 'title',
            ),
            'shop_name'                           => array(
                'title'       => __( 'Shop Name', 'woocommerce' ),
                'type'        => 'text',
                'description' => __( 'The name of the shop that will be displayed in ' . $this->pluginDisplayName, 'woocommerce' ),
                'default'     => __( '', 'woocommerce' ),
                'desc_tip'    => true,
            ),
            $this->pluginFileName . '_minimum'    => array(
                'id'          => $this->pluginFileName . '_minimum',
                'title'       => __( 'Minimum Order Total', 'woocommerce' ),
                'type'        => 'text',
                'default'     => '0',
                'description' => 'Minimum order total to use ' . $this->pluginDisplayName . '. Empty for unlimited',
                'desc_tip'    => true,
            ),
            $this->pluginFileName . '_maximum'    => array(
                'id'          => $this->pluginFileName . '_maximum',
                'title'       => __( 'Maximum Order Total', 'woocommerce' ),
                'type'        => 'text',
                'default'     => '0',
                'description' => 'Maximum order total to use ' . $this->pluginDisplayName . '. Empty for unlimited',
                'desc_tip'    => true,
            )
        );
    }

    /**
     * Check to see if we need to run upgrades.
     */
    function init_upgrade_process() {
        //get the current upgrade version. This will default to 0 before version 0.4.5 of the plugin
        $currentDbVersion = isset( $this->settings['db_plugin_version'] ) ? $this->settings['db_plugin_version'] : 0;
        //see if the current upgrade version is lower than the latest version
        if ( version_compare( $currentDbVersion, $this->plugin_current_version ) < 0 ) {
            //run the upgrade process
            if ( $this->upgrade( $currentDbVersion ) ) {
                //update the stored upgrade version if the upgrade process was successful
                $this->updateSetting( 'db_plugin_version', $this->plugin_current_version );
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
    private function upgrade( $currentDbVersion ) {
        if ( version_compare( $currentDbVersion, '1.2.0' ) < 0 ) {
            if ( ! isset( $this->settings['use_modal'] ) ) {
                // default to the redirect for existing merchants
                // so we don't break the existing behaviour
                $this->settings['use_modal'] = false;
                $this->updateSetting( 'use_modal', $this->settings['use_modal'] );
            }
            $minField = sprintf( '%s_minimum', $this->pluginFileName );
            $maxField = sprintf( '%s_maximum', $this->pluginFileName );
            if ( ! isset( $this->settings[ $minField ] ) ) {
                $this->updateSetting( 'use_modal', $this->settings[ $minField ] );
            }
            if ( ! isset( $this->settings[ $maxField ] ) ) {
                $this->updateSetting( 'use_modal', $this->settings[ $maxField ] );
            }
        } elseif ( version_compare( $currentDbVersion, '1.3.5' ) < 0 ) {
            if ( ! isset( $this->settings['preselect_button_enabled'] ) ) {
                // default to the disable the pre-select checkout button for existing merchants
                // so we don't break the existing behaviour
                $this->settings['preselect_button_enabled'] = "no";
                $this->updateSetting( 'preselect_button_enabled', $this->settings['preselect_button_enabled'] );
            }
            if ( ! isset( $this->settings['preselect_button_order'] ) ) {
                // set default to 20 for pre-select button sequence
                $this->settings['preselect_button_order'] = "20";
                $this->updateSetting( 'preselect_button_order', $this->settings['preselect_button_order'] );
            }
        } elseif ( version_compare( $currentDbVersion, '1.3.14' ) < 0 ) {
            if ( ! isset( $this->settings['top_banner_widget'] ) ) {
                // default to the disable the pre-select checkout button for existing merchants
                // so we don't break the existing behaviour
                $this->settings['top_banner_widget'] = "no";
                $this->updateSetting( 'top_banner_widget', $this->settings['top_banner_widget'] );
            }
            if ( ! isset( $this->settings['top_banner_widget_homepage_only'] ) ) {
                // set default to 20 for pre-select button sequence
                $this->settings['top_banner_widget_homepage_only'] = "20";
                $this->updateSetting( 'top_banner_widget_homepage_only', $this->settings['top_banner_widget_homepage_only'] );
            }
        } elseif ( version_compare( $currentDbVersion, '1.6.0' ) < 0 ) {
            if ( ! isset( $this->settings['merchant_type'] ) ) {
                // default to both
                $this->settings['merchant_type'] = "both";
                $this->updateSetting( 'merchant_type', $this->settings['merchant_type'] );
            }
        } elseif ( version_compare( $currentDbVersion, '1.7.4' ) < 0 ) {
            if ( ! isset( $this->settings['enable_logging'] ) ) {
                // default to yes
                $this->settings['enable_logging'] = "yes";
                $this->updateSetting( 'enable_logging', $this->settings['enable_logging'] );
            }
        }

        return true;
    }

    /**
     * Update a plugin setting stored in the database
     */
    private function updateSetting( $key, $value ) {
        $this->settings[ $key ] = $value;
        update_option( $this->get_option_key(), $this->settings );
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

        return $whitelist;
    }

    public function payment_fields() {
        $country_domain = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'co.nz' : 'com.au';
        $checkout_total = ( WC()->cart ) ? WC()->cart->get_totals()['total'] : "0";

        if ( $this->currentConfig->getDisplayName() == 'humm' ) {
            $widget_type   = 'price-info';
            $merchant_type = "&" . $this->settings['merchant_type'];
            if ( $merchant_type == '&both' ) {
                $merchant_type = '';
            }
            $this->description = __( '<div id="checkout_method_humm_anchor"></div><script src="https://widgets.shophumm.' . $country_domain . '/content/scripts/' . $widget_type . '.js?used_in=checkout&productPrice=' . $checkout_total . '&element=%23checkout_method_humm_anchor' . $merchant_type . '"></script>', 'WooCommerce' );
        } else {
            $widget_type       = ( isset( $this->settings['country'] ) && $this->settings['country'] == 'NZ' ) ? 'payments' : 'payments-weekly';
            $this->description = __( '<div id="checkout_method_oxipay_anchor"></div><script src="https://widgets.oxipay.' . $country_domain . '/content/scripts/' . $widget_type . '.js?used_in=checkout&productPrice=' . $checkout_total . '&element=%23checkout_method_oxipay_anchor"></script>', 'woocommerce' );
        }

        echo $this->description;
    }

    /**
     * Generates the payment gateway request parameters and signature and redirects to the
     * payment gateway through the invisible processing.php form
     *
     * @param int $order_id
     *
     * @return array
     */
    function process_payment( $order_id ) {
        $order      = new WC_Order( $order_id );
        $gatewayUrl = $this->getGatewayUrl();

        $isValid = true;
        $isValid = $isValid && $this->verifyConfiguration( $order );
        $isValid = $isValid && $this->checkCustomerLocation( $order );
        $isValid = $isValid && $this->checkOrderAmount( $order );
        $isValid = $isValid && ! is_null( $gatewayUrl ) && $gatewayUrl != '';

        if ( ! $isValid ) {
            return array();
        }

        $callbackURL = $this->get_return_url( $order );

        $transaction_details = array(
            'x_reference'                  => $order_id,
            'x_account_id'                 => $this->settings[ $this->pluginFileName . '_merchant_id' ],
            'x_amount'                     => $order->get_total(),
            'x_currency'                   => $this->getCurrencyCode(),
            'x_url_callback'               => $callbackURL,
            'x_url_complete'               => $callbackURL,
            'x_url_cancel'                 => $order->get_cancel_order_url_raw(),
            'x_test'                       => 'false',
            'x_shop_country'               => $this->getCountryCode(),
            'x_shop_name'                  => $this->settings['shop_name'],
            //customer detail
            'x_customer_first_name'        => $order->get_billing_first_name(),
            'x_customer_last_name'         => $order->get_billing_last_name(),
            'x_customer_email'             => $order->get_billing_email(),
            'x_customer_phone'             => $order->get_billing_phone(),
            //billing detail
            'x_customer_billing_country'   => $order->get_billing_country(),
            'x_customer_billing_city'      => $order->get_billing_city(),
            'x_customer_billing_address1'  => $order->get_billing_address_1(),
            'x_customer_billing_address2'  => $order->get_billing_address_2(),
            'x_customer_billing_state'     => $order->get_billing_state(),
            'x_customer_billing_zip'       => $order->get_billing_postcode(),
            //shipping detail
            'x_customer_shipping_country'  => $order->get_billing_country(),
            'x_customer_shipping_city'     => $order->get_shipping_city(),
            'x_customer_shipping_address1' => $order->get_shipping_address_1(),
            'x_customer_shipping_address2' => $order->get_shipping_address_2(),
            'x_customer_shipping_state'    => $order->get_shipping_state(),
            'x_customer_shipping_zip'      => $order->get_shipping_postcode(),
            'version_info'                 => 'humm_' . $this->currentConfig->getPluginVersion() . '_on_wc' . substr( WC()->version, 0, 3 ),
            'gateway_url'                  => $gatewayUrl
        );

        $signature                          = $this->flexi_sign( $transaction_details, $this->settings[ $this->pluginFileName . '_api_key' ] );
        $transaction_details['x_signature'] = $signature;

        $encodedFields = array(
            'x_url_callback',
            'x_url_complete',
            'gateway_url',
            'x_url_cancel'
        );

        foreach ( $encodedFields as $i ) {
            $transaction_details[ $i ] = base64_encode( $transaction_details[ $i ] );
        }

        // use RFC 3986 so that we can decode it correctly in js
        $qs = http_build_query( $transaction_details, null, '&', PHP_QUERY_RFC3986 );

        return array(
            'result'   => 'success',
            'redirect' => plugins_url( "processing.php?$qs", __FILE__ )
        );
    }

    /**
     * @param $order
     *
     * @return bool
     */
    private function verifyConfiguration( $order ) {

        $apiKey     = $this->settings[ $this->pluginFileName . '_api_key' ];
        $merchantId = $this->settings[ $this->pluginFileName . '_merchant_id' ];
        $region     = $this->settings['country'];

        $isValid   = true;
        $clientMsg = static::PLUGIN_MISCONFIGURATION_CLIENT_MSG;
        $logMsg    = '';

        if ( $this->is_null_or_empty( $region ) ) {
            $logMsg  = static::PLUGIN_NO_REGION_LOG_MSG;
            $isValid = false;
        }

        if ( $this->is_null_or_empty( $apiKey ) ) {
            $logMsg  = static::PLUGIN_NO_API_KEY_LOG_MSG;
            $isValid = false;
        }

        if ( $this->is_null_or_empty( $merchantId ) ) {
            $logMsg  = static::PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG;
            $isValid = false;
        }

        if ( ! $isValid ) {
            $order->cancel_order( $logMsg );
            $this->logValidationError( $clientMsg );
        }

        return $isValid;
    }

    /**
     * returns the gateway URL
     *
     * @param $countryCode
     *
     * @return string
     */
    private function getGatewayUrl( $countryCode = '' ) {
        //if no countryCode passed in
        if ( $this->is_null_or_empty( $countryCode ) ) {
            if ( isset( $this->settings['country'] ) ) {
                $countryCode = $this->settings['country'];
            } else {
                $countryCode = 'AU';
            }
        }

        $environment = ( $this->isTesting() == 'no' ) ? "live" : "sandbox";
        $url         = $this->currentConfig->getUrlAddress( $countryCode )[ $environment . 'URL' ];

        return $url;
    }

    /**
     * Renders plugin configuration markup
     */
    function admin_options() { ?>
        <h2><?php _e( $this->pluginDisplayName, 'woocommerce' ); ?> Payment Gateway</h2>

        <p>For help setting this plugin up please contact our integration team.</p>

        <table class="form-table">
            <?php $this->generate_settings_html(); ?>
        </table>
        <p>Plugin Version: <?php echo $this->plugin_current_version; ?></p>
        <?php

        $countryUrls = array();
        foreach ( $this->currentConfig->countries as $countryCode => $country ) {
            $countryUrls[ $countryCode ] = array( 'gateway' => $this->getGatewayUrl( $countryCode ) );
        }
        if ( count( $countryUrls ) > 0 ) {
            ?>
            <script>
                var countryUrls = <?php echo json_encode( $countryUrls ); ?>;
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
    function payment_finalisation( $order_id ) {
        $order = wc_get_order( $order_id );
        $cart  = WC()->cart;

        $isJSON = ( $_SERVER['REQUEST_METHOD'] === "POST" && isset( $_SERVER['CONTENT_TYPE'] ) &&
                    ( strpos( $_SERVER['CONTENT_TYPE'], 'application/json' ) !== false ) );

        $isAsyncCallback = $isJSON ? "true" : "false";
        // This addresses the callback.
        if ( $isJSON ) {
            $params = json_decode( file_get_contents( 'php://input' ), true );
        } else {
            $scheme = 'http';
            if ( ! empty( $_SERVER['HTTPS'] ) ) {
                $scheme = 'https';
            }

            $full_url = sprintf(
                '%s://%s%s',
                $scheme,
                $_SERVER['HTTP_HOST'],
                $_SERVER['REQUEST_URI']
            );
            $parts    = parse_url( $full_url, PHP_URL_QUERY );
            parse_str( $parts, $params );
        }

        // we need order information in order to complete the order
        if ( empty( $order ) ) {
            $this->log( sprintf( 'unable to get order information for orderId: %s, (isAsyncCallback=%s)', $order_id, $isAsyncCallback ) );

            return $order_id;
        }

        // make sure we have an flexi order
        // OIR-3
        if ( $order->get_data()['payment_method'] !== $this->pluginFileName ) {
            // we don't care about it because it's not an flexi order
            // log in debug level
            $this->log( sprintf( 'No action required. orderId: %s is not a %s order, (isAsyncCallback=%s)', $order_id, $this->pluginDisplayName, $isAsyncCallback ) );

            return $order_id;
        }

        $api_key    = $this->settings[ $this->pluginFileName . '_api_key' ];
        $sig_exists = isset( $params['x_signature'] );
        $sig_match  = false;
        if ( $sig_exists ) {
            $expected_sig = $this->flexi_sign( $params, $api_key );
            $sig_match    = $expected_sig === $params['x_signature'];
        }

        if ( $sig_exists && $sig_match ) {
            $this->log( sprintf( 'Finalising orderId: %s, (isAsyncCallback=%s)', $order_id, $isAsyncCallback ) );
            // Get the status of the order and handle accordingly
            $flexi_result_note = '';
            switch ( $params['x_result'] ) {
                case "completed":
                    $flexi_result_note = __( 'Payment approved using ' . $this->pluginDisplayName . '. Gateway_Reference #' . $params['x_gateway_reference'], 'woocommerce' );
                    $order->add_order_note( $flexi_result_note );
                    $order->update_meta_data( "flexi_purchase_number", $params["x_gateway_reference"] );
                    $order->payment_complete( $params['x_reference'] );

                    if ( ! is_null( $cart ) && ! empty( $cart ) ) {
                        $cart->empty_cart();
                    }
                    $msg = 'complete';
                    break;
                case "failed":
                    $flexi_result_note = __( 'Payment declined using ' . $this->pluginDisplayName . '. Gateway Reference #' . $params['x_gateway_reference'], 'woocommerce' );
                    $order->add_order_note( $flexi_result_note );
                    $order->update_status( 'failed' );
                    $msg = 'failed';
                    WC()->session->set( 'flexi_result', 'failed' );
                    break;
                case "error":
                    $flexi_result_note = __( 'Payment error using ' . $this->pluginDisplayName . '. Gateway Reference #' . $params['x_gateway_reference'], 'woocommerce' );
                    $order->add_order_note( $flexi_result_note );
                    $order->update_status( 'on-hold', 'Error may have occurred with ' . $this->pluginDisplayName . '. Gateway Reference #' . $params['x_gateway_reference'] );
                    $msg = 'error';
                    WC()->session->set( 'flexi_result', 'error' );
                    break;
            }
            WC()->session->set( 'flexi_result_note', $flexi_result_note );
        } else {
            $order->add_order_note( __( $this->pluginDisplayName . ' payment response failed signature validation. Please check your Merchant Number and API key or contact ' . $this->pluginDisplayName . ' for assistance.' .
                                        '</br></br>isJSON: ' . $isJSON .
                                        '</br>Payload: ' . print_r( $params, true ) .
                                        '</br>Expected Signature: ' . $expected_sig, 0, 'woocommerce' ) );
            $msg = "signature error";
            WC()->session->set( 'flexi_result_note', $this->pluginDisplayName . ' signature error' );
        }

        if ( $isJSON ) {
            $return = array(
                'message' => $msg,
                'id'      => $order_id
            );
            wp_send_json( $return );
        }

        return $order_id;
    }

    function thankyou_page_message( $original_message ) {
        if ( ! empty( WC()->session->get( 'flexi_result_note' ) ) ) {
            return WC()->session->get( 'flexi_result_note' );
        }
    }

    /**
     * This is a filter setup to override the title on the order received page
     * in the case where the payment has failed
     *
     * @param $title
     *
     * @return string
     */
    function order_received_title( $title ) {
        global $wp_query;

        //copying woocommerce logic from wc_page_endpoint_title() in wc-page-functions.php
        if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_wc_endpoint_url() ) {
            //make sure we are on the Order Received page and have the payment result available
            $endpoint = WC()->query->get_current_endpoint();
            if ( $endpoint == 'order-received' && ! empty( $_GET['x_result'] ) ) {
                //look at the x_result query var. Ideally we'd load the order and look at the status, but this has not been updated when this filter runs
                if ( $_GET['x_result'] == 'failed' ) {
                    $title = 'Payment Failed';
                }
            }
            //copying woocommerce code- the filter only needs to run once
            remove_filter( 'the_title', array( $this, 'order_received_title' ), 11 );
        }

        return $title;
    }

    /**
     * Ensure the customer is being billed from and is shipping to, Australia.
     *
     * @param $order
     *
     * @return bool
     */
    private function checkCustomerLocation( $order ) {
        // The following get shipping and billing countries, and filters null or empty values
        // Then we check to see if there is just a single unique value that is equal to AU, otherwise we
        // display an error message.

        $countries     = array( $order->get_billing_country(), $order->get_shipping_country() );
        $set_addresses = array_filter( $countries );
        $countryCode   = $this->getCountryCode();
        $countryName   = $this->getCountryName();

//            valid address is either:
//                1. only have billing country or only ship country, or both have same country, and that country is the supported country in flexi setting;
//                2. have no country at all in both billing and shipping address
        $valid_addresses = ( ( count( array_unique( $set_addresses ) ) === 1 && end( $set_addresses ) === $countryCode ) || count( $set_addresses ) === 0 );

        if ( ! $valid_addresses ) {
            $errorMessage = "&nbsp;Orders from outside " . $countryName . " are not supported by " . $this->pluginDisplayName . ". Please select a different payment option.";
            $order->cancel_order( $errorMessage );
            $this->logValidationError( $errorMessage );

            return false;
        }

        return true;
    }

    /**
     * Ensure the order amount is >= $20
     * Also ensure order is <= max_purchase
     *
     * @param $order
     *
     * @return true
     */
    private function checkOrderAmount( $order ) {
        if ( $this->currentConfig->getDisplayName() == 'humm' ) {
            return true;
        }
        $total = $order->get_total();
        $min   = $this->getMinPurchase();
        if ( $total < $min ) {
            $errorMessage = "&nbsp;Orders under " . $this->getCurrencyCode() . $this->getCurrencySymbol() . $min . " are not supported by " . $this->pluginDisplayName . ". Please select a different payment option.";
            $order->cancel_order( $errorMessage );
            $this->logValidationError( $errorMessage );

            return false;
        }

        $max = $this->getMaxPurchase();
        if ( $total > $max ) {
            $errorMessage = "&nbsp;Orders over " . $this->getCurrencyCode() . $this->getCurrencySymbol() . $max . " are not supported by " . $this->pluginDisplayName . ". Please select a different payment option.";
            $order->cancel_order( $errorMessage );
            $this->logValidationError( $errorMessage );

            return false;
        }

        return true;
    }

    private function logValidationError( $message ) {
        wc_add_notice( __( 'Payment error: ', 'woothemes' ) . $message, 'error' );
    }

    /**
     * @return string
     */
    public function isTesting() {
        return isset( $this->settings['use_test'] ) ? $this->settings['use_test'] : 'no';
    }

    /**
     * @return string
     */
    private function getCountryCode() {
        return $this->settings['country'];
    }

    /**
     * @return string
     */
    private function getCountryName() {
        return $this->currentConfig->countries[ $this->getCountryCode() ]['name'];
    }

    /**
     * @return string
     */
    private function getCurrencyCode() {
        return $this->currentConfig->countries[ $this->getCountryCode() ]['currency_code'];
    }

    /**
     * @return string
     */
    private function getCurrencySymbol() {
        return $this->currentConfig->countries[ $this->getCountryCode() ]['currency_symbol'];
    }

    /**
     * @return int
     */
    private function getMaxPurchase() {
        return $this->currentConfig->countries[ $this->getCountryCode() ]['max_purchase'];
    }

    private function getMinPurchase() {
        return $this->currentConfig->countries[ $this->getCountryCode() ]['min_purchase'];
    }

    /**
     * @param $str
     *
     * @return bool
     */
    private function is_null_or_empty( $str ) {
        return is_null( $str ) || $str == '';
    }

    /**
     * Created by PhpStorm.
     * User: trowri
     * Date: 2/10/2016
     * Time: 3:24 PM
     */

    /**
     * Generates a HMAC based on the merchants api key and the request
     *
     * @param $query
     * @param $api_key
     *
     * @return mixed
     */
    function flexi_sign( $query, $api_key ) {
        $clear_text = '';
        ksort( $query );
        foreach ( $query as $key => $value ) {
            if ( substr( $key, 0, 2 ) === "x_" && $key !== "x_signature" ) {
                $clear_text .= $key . $value;
            }
        }
        $hash = hash_hmac( "sha256", $clear_text, $api_key );

        return str_replace( '-', '', $hash );
    }

    function supports( $feature ) {
        if ( in_array( $feature, array( "products", "refunds" ) ) ) {
            return true;
        }
    }

    function can_refund_order( $order ) {
        return ( $order->get_status() == "processing" || $order->get_status() == "on-hold" || $order->get_status() == "completed" );
    }

    function process_refund( $order_id, $amount = null, $reason = '' ) {
        $reason = $reason ? $reason : "not provided";

        $order       = wc_get_order( $order_id );
        $purchase_id = get_post_meta( $order_id )["flexi_purchase_number"][0];
        if ( ! $purchase_id ) {
            throw new Exception( __( 'Oxipay Purchase ID not found. Can not proceed with online refund', 'woocommerce' ) );
        }

        if ( isset( $this->settings['country'] ) ) {
            $countryCode = $this->settings['country'];
        } else {
            $countryCode = 'AU';
        }

        $environment    = ( $this->isTesting() == 'no' ) ? "live" : "sandbox";
        $refund_address = $this->currentConfig->getUrlAddress( $countryCode )[ $environment . '_refund_address' ];

        $refund_details              = array(
            "x_merchant_number" => $this->settings[ $this->pluginFileName . '_merchant_id' ],
            "x_purchase_number" => $purchase_id,
            "x_amount"          => $amount,
            "x_reason"          => $reason
        );
        $refund_signature            = $this->flexi_sign( $refund_details, $this->settings[ $this->pluginFileName . '_api_key' ] );
        $refund_details['signature'] = $refund_signature;

        $response = wp_remote_post( $refund_address, array(
            'method'      => 'POST',
            'data_format' => 'body',
            'body'        => json_encode( $refund_details ),
            'timeout'     => 3600,
            'user-agent'  => 'Woocommerce ' . WC_VERSION,
            'httpversion' => '1.1',
            'headers'     => array( 'Content-Type' => 'application/json; charset=utf-8' )
        ) );
        if ( is_wp_error( $response ) ) {
            throw new Exception( __( 'There was a problem connecting to the payment gateway.', 'woocommerce' ) );
        }
        if ( empty( $response['response'] ) ) {
            throw new Exception( __( 'Empty response.', 'woocommerce' ) );
        }

        $refund_result  = $response['response'];
        $refund_message = '';
        if ( $response['body'] ) {
            $refund_message = json_decode( $response['body'], true )['Message'];
        }

        if ( isset( $refund_result['code'] ) && $refund_result['code'] == '204' ) {
            $order->add_order_note( sprintf( __( 'Refunding of $%s for order #%s through %s succeeded', 'woocommerce' ), $amount, $order->get_order_number(), $this->pluginDisplayName ) );

            return true;
        } elseif ( isset( $refund_result['code'] ) && $refund_result['code'] == '400' ) {
            $order->add_order_note( sprintf( __( 'Refunding of $%s for order #%s through %s failed. Error Code: %s', 'woocommerce' ), $amount, $order->get_order_number(), $this->pluginDisplayName, $refund_message ) );
        } elseif ( isset( $refund_result['code'] ) && $refund_result['code'] == '401' ) {
            $order->add_order_note( sprintf( __( 'Refunding of $%s for order #%s through %s failed Signature Check', 'woocommerce' ) ) );
        } else {
            $order->add_order_note( sprintf( __( 'Refunding of $%s for order #%s through %s failed with unknown error', 'woocommerce' ) ) );
        }

        return false;
    }
}
