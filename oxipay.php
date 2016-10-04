<?php

/*
 * Plugin Name: Oxipay Payment Gateway
 * Plugin URI: https://www.oxipay.com.au
 * Description: Easy to setup installment payment plans from <a href="https://oxipay.com.au">Oxipay</a>.
 * Version: 0.1.0
 * Author: FlexiGroup
 * @package WordPress
 * @author FlexiGroup
 * @since 0.1.0
 */

// this checks that the woocommerce plugin is alive and well.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

require_once( 'config.php' );
require_once( 'callback.php' );
require_once(ABSPATH.'wp-settings.php');

add_action('plugins_loaded', 'woocommerce_oxipay_init', 0);

function woocommerce_oxipay_init() {
	class Oxipay_Gateway extends WC_Payment_Gateway {
		function __construct() {
			$this->id = 'oxipay';
			$this->has_fields = false;
			$this->order_button_text = __( 'Proceed to ' . OXIPAY_DISPLAYNAME, 'woocommerce' );

            // Tab Title on the WooCommerce Checkout page
			$this->method_title = __( OXIPAY_DISPLAYNAME, 'woocommerce' );

            // Description displayed underneath heading
			$this->method_descripton = __( 'Easy to setup installment payment plans from <a href="https://oxipay.com.au">Oxipay</a>' );

			$this->init_form_fields();
			$this->init_settings();

			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );

			//$this->icon = PLUGIN_DIR . 'images/oxipay.png';

			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_thankyou_order_id',array($this,'payment_finalisation'));
		}

		function init_form_fields() {

			$this->form_fields = array(
				'enabled' 			=> array(
					'title' 		=> __( 'Enabled', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable the ' . OXIPAY_DISPLAYNAME . ' Payment Gateway', 'woocommerce' ),
					'default' 		=> 'yes',
					'description'	=> 'Disable oxipay services, your customers will not be able to use our easy installment plans.',
					'desc_tip'		=> true
				),
				'display_details' 	=> array(
					'title' 		=> __( OXIPAY_DISPLAYNAME . ' Display Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the ' . OXIPAY_DISPLAYNAME . ' display details for your site. These details will be displayed during the WooCommerce checkout process.', 'woocommerce' ),
					'default' 		=> __( OXIPAY_DISPLAYNAME . ' Payment', 'woocommerce' ),
				),
				'title' 			=> array(
					'title' 		=> __( 'Title', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( OXIPAY_DISPLAYNAME , 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' 		=> array(
					'title' 		=> __( 'Description', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( 'Breathe easy with ' . OXIPAY_DISPLAYNAME . ', an interest-free installment payment plan.', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'shop_details' 		=> array(
					'title' 		=> __( OXIPAY_DISPLAYNAME . ' Shop Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the ' . OXIPAY_DISPLAYNAME . ' shop details for your site. These details will be displayed during the oxipay checkout process.', 'woocommerce' ),
					'default' 		=> __( OXIPAY_DISPLAYNAME . ' Payment', 'woocommerce' ),
				),
				'shop_name' 		=> array(
					'title' 		=> __( 'Shop Name', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'The name of the shop that will be displayed in ' . OXIPAY_DISPLAYNAME, 'woocommerce' ),
					'default' 		=> __( '', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'gateway_details' 	=> array(
					'title' 		=> __( OXIPAY_DISPLAYNAME . ' Gateway Settings', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the gateway settings that were supplied to you by ' . OXIPAY_DISPLAYNAME . '.', 'woocommerce' ),
					'default' 		=> __( OXIPAY_DISPLAYNAME . ' Payment', 'woocommerce' ),
				),
				'oxipay_gateway_url'=> array(
					'id'			=> 'oxipay_gateway_url',
					'title' 		=> __( OXIPAY_DISPLAYNAME . ' Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> __( '', 'woocommerce' ),
					'description'	=> 'This is the base URL of the Oxipay payment services. Do not change this unless directed to by Oxipay staff.',
					'desc_tip'		=> true
				),
				'oxipay_sandbox_gateway_url'=> array(
					'id'			=> 'oxipay_sandbox_gateway_url',
					'title' 		=> __( OXIPAY_DISPLAYNAME . ' Sandbox Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> __( 'https://xpozsecure.certegyezipay.com.au/Checkout?platform=WooCommerce', 'woocommerce' ),
					'description'	=> 'This is the base URL of the Oxipay sandbox services. If this test mode is enabled, and this is set - the sandbox will be used. If this is not set, with test mode enabled, the sandbox will not be used, but a test flag will still be sent.',
					'desc_tip'		=> true
				),
                'oxipay_merchant_id'=>array(
                	'id'		    => 'oxipay_merchant_id',
                    'title'     	=> __( 'Merchant ID', 'woocommerce' ),
					'type' 	    	=> 'text',
                    'default'   	=> '',
					'description'	=> 'Oxipay will have supplied you with your Oxipay Merchant ID. <a href="https://oxipay.com.au/support">Contact us</a> if you cannot find it.',
					'desc_tip'		=> true
                ),
                'oxipay_api_key'    => array(
                    'id'        	=> 'oxipay_api_key',
                    'title'     	=> __( 'API Key', 'woocommerce' ),
					'type' 	    	=> 'text',
                    'default'   	=> '',
					'description'	=> 'Oxipay will have supplied you with your Oxipay API key. <a href="https://oxipay.com.au/support">Contact us</a> if you cannot find it.',
					'desc_tip'		=> true
                ),
                'test_mode' 		=> array(
					'title' 		=> __( 'Test Mode', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable Test Mode', 'woocommerce' ),
					'default' 		=> 'no',
					'description'	=> 'WARNING: Setting this will not process any money on our services, so do not use this setting in a production environment.',
					'desc_tip'		=> true
				)
			);
		}

		/**
		 * Returns the test gateway URL if enabled in the admin panel, otherwise, returns the
		 * default Oxipay payment gateway URL
		 * @param int $order_id
		 * @return array
		 */
        function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );

			$order->update_status('processing', __('Awaiting Oxipay payment processing to complete.', 'woocommerce'));
			$gatewayUrl = $this->getGatewayUrl();
            $transaction_details = array (
                'x_reference'     				=> $order_id,
                'x_account_id'    				=> $this->settings['oxipay_merchant_id'],
                'x_amount' 	    				=> $order->order_total,
                'x_currency' 	    			=> CURRENCY,
                'x_url_callback'  				=> plugins_url("callback.php", __FILE__),
                'x_url_complete'  				=> $this->get_return_url( $order ),
                'x_url_cancel'           		=> $woocommerce->cart->get_cart_url(),
                'x_test'          				=> $this->settings['test_mode'],
                'x_shop_country'          		=> AUSTRALIA,
                'x_shop_name'          			=> $this->settings['shop_name'],
				//customer detail
                'x_customer_first_name' 		=> $order->billing_first_name,
                'x_customer_last_name' 			=> $order->billing_last_name,
                'x_customer_email'      		=> $order->billing_email,
                'x_customer_phone'				=> $order->billing_phone,
                //billing detail
                'x_customer_billing_country'	=> AUSTRALIA,
                'x_customer_billing_city' 	    => $order->billing_city,
                'x_customer_billing_address1' 	=> $order->billing_address_1,
                'x_customer_billing_address2' 	=> $order->billing_address_2,
                'x_customer_billing_state' 	    => $order->billing_state,
                'x_customer_billing_zip' 		=> $order->billing_postcode,
                //shipping detail
                'x_customer_shipping_country'	=> AUSTRALIA,
 				'x_customer_shipping_city' 	    => $order->postal_city,
                'x_customer_shipping_address1'  => $order->postal_address_1,
                'x_customer_shipping_address2'  => $order->postal_address_2,
                'x_customer_shipping_state' 	=> $order->postal_state,
                'x_customer_shipping_zip' 		=> $order->postal_postcode,
                //pass the gateway URL through to the processing page.
                //TODO: the processing.php page should probably resolve this differently.
                'gateway_url' 					=> $gatewayUrl
            );

			ksort($transaction_details);
          	$signature = $this->generate_signature($transaction_details, $this->settings['oxipay_api_key']);
			$transaction_details['x_signature'] = $signature;

            $order->update_status('on-hold', __('Awaiting '.OXIPAY_DISPLAYNAME.' payment', 'woothemes'));
            $qs = http_build_query($transaction_details);

            return array(
                    'result' 	=>  'success',
                    'redirect'	=>  plugins_url("processing.php?$qs", __FILE__ )
            );
		}

		/**
		 * enforces test mode logic to return the correct gateway URL
		 */
		private function getGatewayUrl() {
			$testMode = strtolower($this->settings['test_mode']) == 'yes';
			$sandboxUrl = $this->settings['oxipay_sandbox_gateway_url'];
			$gatewayUrl = $this->settings['oxipay_gateway_url'];
			$hasSandboxUrl = !is_null($sandboxUrl) && $sandboxUrl != '';
			if($testMode && $hasSandboxUrl)
				return $sandboxUrl;

			return $gatewayUrl;
		}
		/**
		 * Generates a HMAC based on the merchants api key and the request
		 * @param $query
		 * @param $api_key
		 * @return mixed
		 */
		function generate_signature($query, $api_key ) {
        	$clear_text = '';
        	foreach ($query as $key => $value) {
        		if (substr($key, 0, 2) === "x_") {
        			$clear_text .= $key . $value;
        		}
        	}
            $hash = hash_hmac( "sha256", $clear_text, $api_key);
            return str_replace('-', '', $hash);
        }

		/**
		 * validates and associative array that contains a hmac signature against an api key
		 * @param $query array
		 * @param $api_key string
		 * @return bool
		 */
		public function is_valid_signature($query, $api_key) {
			$actualSignature = $query['x_signature'];
			unset($query['x_signature']);
			$expectedSignature = $this->generate_signature($query, $api_key);
			return $actualSignature == $expectedSignature;
		}

		/**
		 * Renders plugin configuration markup
		 */
		function admin_options() { ?>
			<h2><?php _e(OXIPAY_DISPLAYNAME,'woocommerce'); ?></h2>
			<p><?php _e($this->method_description, 'woocommerce' ); ?></p>
			<p>For help setting this plugin up please contact our support team via <a href="https://oxipay.com.au/support">Oxipay support</a></p>
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