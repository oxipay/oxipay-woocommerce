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
$config = include('./config.php');

add_action('plugins_loaded', 'woocommerce_xpay_init', 0);

function woocommerce_xpay_init() {
	class XPay_Gateway extends WC_Payment_Gateway {
		function __construct() {
			$this->id 					= 'xpay';
			$this->has_fields 			= false;
			$this->order_button_text 	= __( 'Proceed to ' . $config['XPAY_DISPLAYNAME'], 'woocommerce' );

            // Tab Title on the WooCommerce Checkout page
			$this->method_title       	= __( $config['XPAY_DISPLAYNAME'], 'woocommerce' );

            // Description displayed underneath heading
			$this->method_descripton	= __( $config['XPAY_DISPLAYNAME'] . ' is a payment gateway from FlexiGroup. ' .
                                            'The plugin works by sending payment details to ' . $config['XPAY_DISPLAYNAME'] . ' for processing.', 'woocommerce' );

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
					'label' 		=> __( 'Enable the ' . $config['XPAY_DISPLAYNAME'] . ' Payment Gateway', 'woocommerce' ),
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
					'default' 		=> __( $config['XPAY_DISPLAYNAME'] , 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' 		=> __( 'Description', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( 'Pay using ' . $config['XPAY_DISPLAYNAME'] . ' with an interest-free installment payment plan', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'gateway_details' => array(
					'title' 		=> __( $config['XPAY_DISPLAYNAME'] . ' Settings', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the gateway settings to process payments via the ' . $config['XPAY_DISPLAYNAME'] . ' payment gateway.', 'woocommerce' ),
					'default' 		=> __( $config['XPAY_DISPLAYNAME'] . ' Payment', 'woocommerce' ),
				),
				'gateway_url' => array(
					'title' 		=> __( $config['XPAY_DISPLAYNAME'] . ' Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> __( $config['XPAY_URL'], 'woocommerce' ),
				),
                'api_key'   =>array(
                    'title'     => __( 'API Key', 'woocommerce' ),
					'type' 	    => 'text',
                    'default'   => ''
                ),
				'business_details' => array(
					'title' 		=> __( 'Merchant Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter your business details to process payments via ' . $config['$config['XPAY_DISPLAYNAME']'], 'woocommerce' ),
					'default' 		=> __( $config['XPAY_DISPLAYNAME'] . ' Payment', 'woocommerce' ),
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
        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

            $transaction_details = array (
                'order_key'     =>  '', //this is a merchant identifier
                'account_id'    =>  $this->settings[account_id],
                'total' 	    =>  $order->order_total,
                'url_callback'  =>  CWD . '/callback2.php', //server->server callback
                'url_complete'  =>  get_return_url( $order ), //server->client callback - TODO: determine if this std. thankyou is OK
                'test'          =>  $config['TEST'],
                'first_name'    =>  $order->billing_first_name,
                'last_name' 	=>  $order->billing_last_name,
                'email'         =>  $order->billing_email,
                'phone_mobile'	=>  $order->billing_phone,
                //AU only at this stage
                //'counry'        =>  $order->billing_country, 
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
                'platform'		=>	PLATFORM_NAME // required for backend            
            );
          	
          	$signature = generate_signature($transaction_details, $this->form_fields['api_key']);
          	$transaction_details['signature'] = $signature;

    //     	if($response[result] == 'success') {
    // 		 	$order->reduce_order_stock();
    //         	$woocommerce->cart->empty_cart();	
    // 			$order->payment_complete();
    //     	} else {
    //     		wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' );
				// return;
    //     	}

            $order->update_status('on-hold', __("Awaiting {$config['XPAY_DISPLAYNAME']} payment", 'woothemes'));

            return array(
                    'result' 	=> 'Success', 
                    'redirect'	=> $config['XPAY_URL']
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
		 <h2><?php _e($config['XPAY_DISPLAYNAME'],'woocommerce'); ?></h2>
		 <p><?php _e( $config['XPAY_DISPLAYNAME'] . ' is a payment gateway from FlexiGroup. The plugin works by sending payment details to ' . 
                      $config['XPAY_DISPLAYNAME'] . ' for processing.', 'woocommerce' ); ?></p>
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