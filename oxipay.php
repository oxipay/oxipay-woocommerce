<?php

/*
 * Plugin Name: Oxipay Payment Gateway
 * Plugin URI: https://www.oxipay.com.au
 * Description: Easy to setup intstallment payment plans from Oxipay.
 * Version: 0.1.0
 * Author: FlexiGroup
 * @package WordPress
 * @author FlexiGroup
 * @since 0.1.0
 */

// this checks that the woocommerce plugin is alive and well.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

include_once( 'config.php' );
include_once( 'callback.php' );

require_once(ABSPATH.'wp-settings.php');

add_action('plugins_loaded', 'woocommerce_oxipay_init', 0);

function woocommerce_oxipay_init() {
	class Oxipay_Gateway extends WC_Payment_Gateway {
		function __construct() {
			$this->id 					= 'oxipay';
			$this->has_fields 			= false;
			$this->order_button_text 	= __( 'Proceed to ' . $config['OXIPAY_DISPLAYNAME'], 'woocommerce' );

            // Tab Title on the WooCommerce Checkout page
			$this->method_title       	= __( $config['OXIPAY_DISPLAYNAME'], 'woocommerce' );

            // Description displayed underneath heading
			$this->method_descripton	= __( $config['OXIPAY_DISPLAYNAME'] . ' is a payment gateway from FlexiGroup. ' .
                                            'The plugin works by sending payment details to ' . $config['OXIPAY_DISPLAYNAME'] . ' for processing.', 'woocommerce' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title          = $this->get_option( 'title' );
			$this->description    = $this->get_option( 'description' );

			//$this->icon = PLUGIN_DIR . 'images/oxipay.png';

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_thankyou_order_id',array($this,'payment_finalisation'));
		}

		function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title' 		=> __( 'Enable', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable the ' . $config['OXIPAY_DISPLAYNAME'] . ' Payment Gateway', 'woocommerce' ),
					'default' 		=> 'yes'
				),
                'test_mode' => array(
					'title' 		=> __( 'Test Mode', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable Test Mode', 'woocommerce' ),
					'default' 		=> 'no'
				),
				'title' => array(
					'title' 		=> __( 'Title', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( $config['OXIPAY_DISPLAYNAME'] , 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' 		=> __( 'Description', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( 'Pay using ' . $config['OXIPAY_DISPLAYNAME'] . ' with an interest-free installment payment plan', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'gateway_details' => array(
					'title' 		=> __( $config['OXIPAY_DISPLAYNAME'] . ' Settings', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the gateway settings to process payments via the ' . $config['OXIPAY_DISPLAYNAME'] . ' payment gateway.', 'woocommerce' ),
					'default' 		=> __( $config['OXIPAY_DISPLAYNAME'] . ' Payment', 'woocommerce' ),
				),
				'gateway_url' => array(
					'title' 		=> __( $config['OXIPAY_DISPLAYNAME'] . ' Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> __( $config['OXIPAY_URL'], 'woocommerce' ),
				),
                'api_key'   =>array(
                    'id'        => 'merchant_api_key',
                    'title'     => __( 'API Key', 'woocommerce' ),
					'type' 	    => 'text',
                    'default'   => ''
                ),
				'business_details' => array(
					'title' 		=> __( 'Merchant Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter your business details to process payments via ' . $config['OXIPAY_DISPLAYNAME'], 'woocommerce' ),
					'default' 		=> __( $config['OXIPAY_DISPLAYNAME'] . ' Payment', 'woocommerce' ),
				),
                'account_id'   =>array(
                    'title'     => __( 'Merchant ID', 'woocommerce' ),
					'type' 	    => 'text',
                    'default'   => ''
                ),
                'shop_name'   =>array(
                    'title'     => __( 'Business Name', 'woocommerce' ),
					'type' 	    => 'text',
                    'default'   => ''
                )
			);
		}

        /**
         * Returns the test gateway URL if enabled in the admin panel, otherwise, returns the
         * default Oxipay payment gateway URL
         */
        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

            $transaction_details = array (
                //'order_key'     		=>  $this->settings[account_id],        //this is a merchant identifier
                //'account_id'    		=>  $this->settings[account_id],
                'order_key'    		    =>  $order_id,
                'account_id'    		=>  30199999,
                'total' 	    		=>  $order->order_total,
                'url_callback'  		=>  "http://$_SERVER[HTTP_HOST]" .
                                            '/wordpress/wp-content/plugins/oxipay-wordpress/' .
                                            '/callback.php',                    //server->server callback
                'url_complete'  		=>  $this->get_return_url( $order ),    //server->client callback - TODO: determine if this std. thankyou is OK
                'url_cancel'            =>  $woocommerce->cart->get_cart_url(), // redirect back to the shopping cart
                'currency'              =>  get_woocommerce_currency(),
                'shop_name'             =>  $this->settings[shop_name],
                'test'          		=>  $this->settings[test_mode],
                'first_name'    		=>  $order->billing_first_name,
                'last_name' 			=>  $order->billing_last_name,
                'email'         		=>  $order->billing_email,
                'phone_mobile'			=>  $order->billing_phone,
                //billing detail
                'billing_city' 	        =>  $order->billing_city,
                'billing_address_1' 	=>  $order->billing_address_1,
                'billing_address_2' 	=>  $order->billing_address_2,
                'billing_state' 	    =>  $order->billing_state,
                'billing_postcode' 		=>  $order->billing_postcode,
                //shipping detail
 				'shipping_city' 	    =>  $order->postal_city,
                'shipping_address_1' 	=>  $order->postal_address_1,
                'shipping_address_2' 	=>  $order->postal_address_2,
                'shipping_state' 	    =>  $order->postal_state,
                'shipping_postcode' 	=>  $order->postal_postcode,
                'platform'				=>	PLATFORM_NAME // required for backend
            );

          	$signature = $this->generate_signature($transaction_details, $this->settings['api_key']);
            $this->echo_to_console($signature);
            $this->echo_to_console($this->settings[account_id]);
            $this->echo_to_console($this->settings[account_id]);

            $order->update_status('on-hold', __("Awaiting {$config['OXIPAY_DISPLAYNAME']} payment", 'woothemes'));
            $qs = http_build_query($transaction_details) . '&x_signature=' . $signature;
            return array(
                    'result' 	=>  'success',
                    'redirect'	=>  plugins_url("processing.php?$qs", __FILE__ )
            );
		}

        function generate_signature( $query, $api_key ) {
        	//step 1: order by key_name ascending
        	$clear_text = '';
        	ksort($query);
        	foreach ($query as $key => $value) {
	        	//step 2: concat all keys in form "{key}{value}"
        		$clear_text .= $key . $value;
        	}

            //fwrite(STDOUT, $clear_text);

            //step 3: use HMAC-SHA256 function on step 4 using API key as entropy
            //WooCommerce v3 requires &. Refer: http://stackoverflow.com/questions/31976059/woocommerce-api-v3-authentication-issue
            $secret = $api_key . '&';
            $hash = base64_encode( hash_hmac( "sha256", $clear_text, $secret, true ));
            return str_replace('+', '', $hash);
        }

        // Function to output echo statements to the browser console.
        function echo_to_console( $data ) {
            echo $data;
            echo "\r\n";    // End of line on Windows
        }

		function admin_options() { ?>
			<h2><?php _e($config['OXIPAY_DISPLAYNAME'],'woocommerce'); ?></h2>
			<p><?php _e( $config['OXIPAY_DISPLAYNAME'] . ' is a payment gateway from FlexiGroup. The plugin works by sending payment details to ' . 
			          $config['OXIPAY_DISPLAYNAME'] . ' for processing.', 'woocommerce' ); ?></p>
			<table class="form-table">
			<?php $this->generate_settings_html(); ?>
			</table> <?php
		}
	}
}

function add_oxipay_payment_gateway($methods) {
	$methods[] = 'Oxipay_Gateway';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_oxipay_payment_gateway');

?>