<?php

/*
 * Plugin Name: XPay Payment Gateway
 * Plugin URI: http://www.certegyezipay.com
 * Description: Easy to setup intstallment payment plans from Certegy.
 * Version: 0.9.0
 * Author: FlexiGroup
 * @package WordPress
 * @author FlexiGroup
 * @since 0.9.0
 */

// this checks that the woocommerce plugin is alive and well.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action('plugins_loaded', 'woocommerce_xpay_init', 0);

function woocommerce_xpay_init() {


  DEFINE ('PLUGIN_DIR', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) . '/' );
  DEFINE ('XPAY_URL', 'http://localhost/CCP.XPay.Secure/checkout/Index?platform=WooCommerce');
  DEFINE ('TEST_URL', 'http://www.google.com/');
  DEFINE ('XPAY_DISPLAYNAME', 'XPay');
  DEFINE ('PLATFORM_NAME', 'WooCommerce');

	class XPay_Gateway extends WC_Payment_Gateway {
		function __construct() {
			$this->id 					= 'xpay';
			$this->has_fields 			= false;
			$this->order_button_text 	= __( 'Proceed to ' . XPAY_DISPLAYNAME, 'woocommerce' );

            // Tab Title on the WooCommerce Checkout page
			$this->method_title       	= __( XPAY_DISPLAYNAME, 'woocommerce' );

            // Description displayed underneath heading
			$this->method_descripton	= __( XPAY_DISPLAYNAME . ' is a payment gateway from FlexiGroup. ' .
                                            'The plugin works by sending payment details to ' . XPAY_DISPLAYNAME . ' for processing.', 'woocommerce' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title          = $this->get_option( 'title' );
			$this->description    = $this->get_option( 'description' );

			$this->icon = PLUGIN_DIR . 'images/xpay.png';

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title' 		=> __( 'Enable', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable the ' . XPAY_DISPLAYNAME . ' Payment Gateway', 'woocommerce' ),
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
					'default' 		=> __( XPAY_DISPLAYNAME , 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' 		=> __( 'Description', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( 'Pay using ' . XPAY_DISPLAYNAME . ' with an interest-free installment payment plan', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'gateway_details' => array(
					'title' 		=> __( XPAY_DISPLAYNAME . ' Settings', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the gateway settings to process payments via the ' . XPAY_DISPLAYNAME . ' payment gateway.', 'woocommerce' ),
					'default' 		=> __( XPAY_DISPLAYNAME . ' Payment', 'woocommerce' ),
				),
				'gateway_url' => array(
					'title' 		=> __( XPAY_DISPLAYNAME . ' Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> __( XPAY_URL, 'woocommerce' ),
				),
                'api_key'   =>array(
                    'title'     => __( 'API Key', 'woocommerce' ),
					'type' 	    => 'text',
                    'default'   => ''
                ),
				'business_details' => array(
					'title' 		=> __( 'Merchant Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter your business details to process payments via ' . XPAY_DISPLAYNAME, 'woocommerce' ),
					'default' 		=> __( XPAY_DISPLAYNAME . ' Payment', 'woocommerce' ),
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
         * default XPay payment gateway URL
         */
        function get_gateway_url( $order ) {
            if ($this->settings[test_mode] == "yes") {
                $this->view_transaction_url = TEST_URL;
            } else {
                $this->view_transaction_url = XPAY_URL;
            }
            return parent::get_transaction_url( $order );
        }

        /**
         * Returns the test gateway URL if enabled in the admin panel, otherwise, returns the
         * default XPay payment gateway URL
         */
        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

            $transaction_details = array (
                'order_key'     =>  '', //no idea what this is :) possibly a merchant identifier
                'account_id'    =>  $this->settings[account_id],
                'total' 	    =>  $order->order_total,
                //'currency'      =>  $order->order_currency,
                'url_callback'  =>  '',
                'url_complete'  =>  '',
                //'shop_country'  =>  'AU',
                //'shop_name'     =>  $this->settings[shop_name],
                'test'          =>  $this->test_enabled(),
                'first_name'    =>  $order->billing_first_name,
                'last_name' 	=>  $order->billing_last_name,
                'email'         =>  $order->billing_email,
                'counry'        =>  $order->billing_country,
                'city' 	        =>  $order->billing_city,
                'address_1' 	=>  $order->billing_address_1,
                'address_2' 	=>  $order->billing_address_2,
                'state' 	    =>  $order->billing_state,
                'postcode' 		=>  $order->billing_postcode,
                'platform'		=>	PLATFORM_NAME

            );
            
           

            // 'phone'      =>  $order->billing_phone,
            // 'api_key'    =>  $this->settings[api_key],
            // 'platform'   =>  PLATFORM_NAME

            // Send request and get response from server
            $response = $this->post_and_get_response($transaction_details);

        	if($response[result] == 'success') {
    		 	$order->reduce_order_stock();
            	$woocommerce->cart->empty_cart();	
    			$order->payment_complete();
        	} else {
        		wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' );
				return;
        	}

            return array(
                    'result' 	=> $response['result'],
                    'redirect'	=> $this->get_return_url( $order )
            );
		}

		//refer: http://ad-d-dev02:8080/browse/XPAY-293
        function generate_signature( $query, $api_key ) {
        	//step 1: order by key_name ascending
        	$encoded_query = '';
        	ksort($query);
        	foreach ($query as $key => $value) {
	        	//step 2: concat all keys in form "{key}{value}"
        		$encoded_query .= $key . $value;
        	}
        	//step 3: use HMAC-SHA256 function on step 4 using API key as entropy
            $hash = hash_hmac( "sha256", $encoded_query, $api_key );
            return str_replace('-', '', $hash);
        }

		function post_and_get_response( $request ) {
			global $woocommerce;

			// Genereate URL encoded query string
            $signature = $this->generate_signature($request, $this->settings[api_key]);
            // Send request to server
            $options = array(
                'http' => array(
                    'method'    => 'POST',
                    'content'   => json_encode( $request ),
                    'header'    => "Content-Type: application/json\r\n" . "Accept: application/json\r\n"
                    )
                );

            $url = $this->get_gateway_url();
            //is there a cleaner way to POST in PHP?
            $context = stream_context_create( $options );
            $result = file_get_contents( $url, false, $context );

            // If needed to decode, use below
            // $response = json_decode( $result );



			// Convert the resonse from the server to an array
			$vars = explode( '&', $data['body'] );
			foreach ( $vars as $key => $val ) {
				$var = explode( '=', $val );
				$data[ $var[0] ] = $var[1];
			}

			// Return the array
			return $data;
		}

		function admin_options() { ?>
		 <h2><?php _e(XPAY_DISPLAYNAME,'woocommerce'); ?></h2>
		 <p><?php _e( XPAY_DISPLAYNAME . ' is a payment gateway from FlexiGroup. The plugin works by sending payment details to ' . 
                      XPAY_DISPLAYNAME . ' for processing.', 'woocommerce' ); ?></p>
		 <table class="form-table">
		 <?php $this->generate_settings_html(); ?>
		 </table> <?php
		 }
	}
}

function add_xpay_payment_gateway($methods) {
	$methods[] = 'XPay_Gateway';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_xpay_payment_gateway');

?>