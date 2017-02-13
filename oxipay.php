<?php

/*
 * Plugin Name: Oxipay Payment Gateway
 * Plugin URI: https://www.oxipay.com.au
 * Description: Easy to setup installment payment plans from <a href="https://oxipay.com.au">Oxipay</a>.
 * Version: 0.4.8
 * Author: FlexiGroup
 * @package WordPress
 * @author FlexiGroup
 * @since 0.4.8
 */

// this checks that the woocommerce plugin is alive and well.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

require_once( 'crypto.php' );
require_once( 'config.php' );

add_action('plugins_loaded', 'woocommerce_oxipay_init', 0);

/**
 * Hook for WC plugin subsystem to initialise the Oxipay plugin
 */
function woocommerce_oxipay_init() {
	class WC_Oxipay_Gateway extends WC_Payment_Gateway {
		//todo: localise these string constants
		const PLUGIN_NO_GATEWAY_LOG_MSG = 'Transaction attempted with no gateway URL set. Please check oxipay plugin configuration, and provide a gateway URL.';
		const PLUGIN_MISCONFIGURATION_CLIENT_MSG = 'There is an issue with the site configuration, which has been logged. We apologize for any inconvenience. Please try again later. ';
		const PLUGIN_NO_API_KEY_LOG_MSG = 'Transaction attempted with no API key set. Please check oxipay plugin configuration, and provide an API Key';
		const PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG = 'Transaction attempted with no Merchant ID key. Please check oxipay plugin configuration, and provide an Merchant ID.';
		const PLUGIN_NO_SANDBOX_GATEWAY_LOG_MSG = 'Test Transaction attempted with no sandbox gateway URL set. Please check oxipay plugin configuration, and provide a sandbox gateway URL.';
		const PLUGIN_NO_REGION_LOG_MSG = 'Transaction attemped with no Oxipay region set. Please check oxipay plugin configuration, and provide an Oxipay region.';
		//Allow single run upgrade processes. If an upgrade task is needed, increment this and add your logic to the upgrade() function
		const DB_UPGRADE_VERSION = 1;

		function __construct() {
			$this->id = 'oxipay';
			$this->has_fields = false;
			$this->order_button_text = __( 'Proceed to ' . Config::DISPLAY_NAME, 'woocommerce' );
			$this->method_title      = __( Config::DISPLAY_NAME, 'woocommerce' );
			$this->method_descripton = __( 'Easy to setup installment payment plans from ' . Config::DISPLAY_NAME );

			$this->init_form_fields();
			$this->init_settings();
			if( is_admin() ){
				$this->init_upgrade_process();
			}

			$this->title         = $this->get_option( 'title' );
			$this->description   = $this->get_option( 'description' );
			$this->icon          = plugin_dir_url( __FILE__ ) .  'images/oxipay.png';

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'woocommerce_api_wc_oxipay_gateway', array( $this, 'oxipay_callback') );
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_thankyou_order_id', array( $this,'payment_finalisation' ) );
			add_filter( 'the_title', array( $this,'order_received_title' ), 11 );
		}

		/**
		 * Load javascript for Wordpress admin
		 */
		function admin_scripts(){
			wp_register_script( 'oxipay_admin', plugins_url( '/js/admin.js', __FILE__ ), array( 'jquery' ), '0.4.5' );
			wp_enqueue_script( 'oxipay_admin' );
		}

		/**
		 * WC override to display the administration property page
		 */
		function init_form_fields() {
			//Build options for the country select field from the config
			$countryOptions = array('' => __( 'Please select...', 'woocommerce' ));
			foreach(Config::$countries as $countryCode => $country){
				$countryOptions[$countryCode] = __( $country['name'], 'woocommerce' );
			}

			$this->form_fields = array(
				'enabled' 			=> array(
					'title' 		=> __( 'Enabled', 'woocommerce' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable the ' . Config::DISPLAY_NAME . ' Payment Gateway', 'woocommerce' ),
					'default' 		=> 'yes',
					'description'	=> 'Disable oxipay services, your customers will not be able to use our easy installment plans.',
					'desc_tip'		=> true
				),
				'display_details' 	=> array(
					'title' 		=> __( Config::DISPLAY_NAME . ' Display Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the ' . Config::DISPLAY_NAME . ' display details for your site. These details will be displayed during the WooCommerce checkout process.', 'woocommerce' ),
					'default' 		=> __( Config::DISPLAY_NAME . ' Payment', 'woocommerce' ),
				),
				'title' 			=> array(
					'title' 		=> __( 'Title', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( Config::DISPLAY_NAME , 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' 		=> array(
					'title' 		=> __( 'Description', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
					'default' 		=> __( 'Breathe easy with ' . Config::DISPLAY_NAME . ', an interest-free installment payment plan.', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'shop_details' 		=> array(
					'title' 		=> __( Config::DISPLAY_NAME . ' Shop Details', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the ' . Config::DISPLAY_NAME . ' shop details for your site. These details will be displayed during the oxipay checkout process.', 'woocommerce' ),
					'default' 		=> __( Config::DISPLAY_NAME . ' Payment', 'woocommerce' ),
				),
				'shop_name' 		=> array(
					'title' 		=> __( 'Shop Name', 'woocommerce' ),
					'type' 			=> 'text',
					'description' 	=> __( 'The name of the shop that will be displayed in ' . Config::DISPLAY_NAME, 'woocommerce' ),
					'default' 		=> __( '', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'country'=> array(
					'title'			=> __( 'Oxipay Region', 'woocommerce' ),
					'type'			=> 'select',
					'description'	=> 'Select the closest region in which this store communicates with Oxipay. This will ensure your customers receive the best possible experience.',
					'options'		=> $countryOptions,
					'custom_attributes' => array('required' => 'required'),
				),
				'gateway_details' 	=> array(
					'title' 		=> __( Config::DISPLAY_NAME . ' Gateway Settings', 'woocommerce' ),
					'type' 			=> 'title',
					'description' 	=> __( 'Enter the gateway settings that were supplied to you by ' . Config::DISPLAY_NAME . '.', 'woocommerce' ),
					'default' 		=> __( Config::DISPLAY_NAME . ' Payment', 'woocommerce' ),
				),
				'oxipay_gateway_url'=> array(
					'id'			=> 'oxipay_gateway_url',
					'title' 		=> __( Config::DISPLAY_NAME . ' Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> $this->getDefaultGatewayUrl(),
					'description'	=> 'This is the base URL of the Oxipay payment services. Do not change this unless directed to by Oxipay staff.',
					'desc_tip'		=> true,
					'custom_attributes' => array('required' => 'required'),
				),
				'oxipay_sandbox_gateway_url'=> array(
					'id'			=> 'oxipay_sandbox_gateway_url',
					'title' 		=> __( Config::DISPLAY_NAME . ' Sandbox Gateway URL', 'woocommerce' ),
					'type' 			=> 'text',
					'default' 		=> $this->getDefaultSandboxGatewayUrl(),
					'description'	=> 'This is the base URL of the Oxipay sandbox services. If this test mode is enabled, and this is set - the sandbox will be used. If this is not set, with test mode enabled, the sandbox will not be used, but a test flag will still be sent.',
					'desc_tip'		=> true,
					'custom_attributes' => array('required' => 'required'),
				),
                'oxipay_merchant_id'=>array(
                	'id'		    => 'oxipay_merchant_id',
                    'title'     	=> __( 'Merchant ID', 'woocommerce' ),
					'type' 	    	=> 'text',
                    'default'   	=> '',
					'description'	=> 'Oxipay will have supplied you with your Oxipay Merchant ID. Contact us if you cannot find it.',
					'desc_tip'		=> true,
					'custom_attributes' => array('required' => 'required'),
                ),
                'oxipay_api_key'    => array(
                    'id'        	=> 'oxipay_api_key',
                    'title'     	=> __( 'API Key', 'woocommerce' ),
					'type' 	    	=> 'text',
                    'default'   	=> '',
					'description'	=> 'Oxipay will have supplied you with your Oxipay API key. <a href="'.$this->getSupportUrl().'">Contact us</a> if you cannot find it.',
					'desc_tip'		=> true,
					'custom_attributes' => array('required' => 'required'),
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
		 * Check to see if we need to run upgrades.
		 */
		function init_upgrade_process() {
			//get the current upgrade version. This will default to 0 before version 0.4.5 of the plugin
			$currentDbVersion = isset( $this->settings['db_version'] ) ? $this->settings['db_version'] : 0;
			//see if the current upgrade version is lower than the latest version
			if ( $currentDbVersion < self::DB_UPGRADE_VERSION ) {
				//run the upgrade process
				if($this->upgrade( $currentDbVersion )){
					//update the stored upgrade version if the upgrade process was successful
					$this->updateSetting('db_version', self::DB_UPGRADE_VERSION);
				}
			}
		}

		/**
		 * Run one off upgrade routines. A DB stored version numberis compared to the class constant to
		 * tell if processes need to run.
		 * Increment the class constant and add the task here when an upgrade task is needed.
		 * Users coming from especially old versions may have multiple version upgrade tasks to process.
		 *
		 * @param int $currentDbVersion
		 * @return bool
		 */
		private function upgrade( $currentDbVersion ) {
			//add processes per version here.
			if ( $current_db_version < 1 ) {
				//detect and save a country if one is not set- the country field was a recent field addition
				if($this->is_null_or_empty($this->settings['country']) && ! $this->is_null_or_empty($this->settings['oxipay_gateway_url'])){
					//look at the gateway url and set the country from the tld used
					foreach(Config::$countries as $countryCode => $country){
						if( stripos($this->settings['oxipay_gateway_url'], $country['tld'] ) !== false){
							$this->updateSetting('country', $countryCode);
						}
					}
				}

				//gateway URLs have changed in this version- update the URLs
				$this->setGatewayUrlsToDefault();
			}

			/*
			//for future upgrades, self::DB_UPGRADE_VERSION should be set to 2 and logic put here
			if ( $current_db_version < 2 ){
				//update a setting, create a DB table etc.
			}
			*/

			return true;
		}

		/**
		 * Update a plugin setting stored in the database
		 */
		private function updateSetting($key, $value) {
			$this->settings[$key] = $value;

			update_option( $this->get_option_key(), $this->settings );
		}

		private function setGatewayUrlsToDefault(){
			$defaultGatewayUrl = $this->getDefaultGatewayUrl();
			$defaultSandboxUrl = $this->getDefaultSandboxGatewayUrl();

			if( strlen( $this->settings['oxipay_gateway_url'] ) == 0 || $this->settings['oxipay_gateway_url'] != $defaultGatewayUrl ){
				$this->updateSetting( 'oxipay_gateway_url', $defaultGatewayUrl );
			}

			if( strlen( $this->settings['oxipay_sandbox_gateway_url'] ) == 0 || $this->settings['oxipay_sandbox_gateway_url'] != $defaultSandboxUrl ){
				$this->updateSetting( 'oxipay_sandbox_gateway_url', $defaultSandboxUrl );
			}
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
			$order->update_status('pending');
			$gatewayUrl = $this->getGatewayUrl();

			$isValid = true;
			$isValid = $isValid && $this->verifyConfiguration($order);
			$isValid = $isValid && $this->checkCustomerLocation($order);
			$isValid = $isValid && $this->checkOrderAmount($order);
			$isValid = $isValid && !is_null($gatewayUrl) && $gatewayUrl != '';

			if(!$isValid) return;

			$order->update_status('processing', __('Awaiting Oxipay payment processing to complete.', 'woocommerce'));

            $transaction_details = array (
                'x_reference'     				=> $order_id,
                'x_account_id'    				=> $this->settings['oxipay_merchant_id'],
                'x_amount' 	    				=> $order->order_total,
                'x_currency' 	    			=> $this->getCurrencyCode(),
                'x_url_callback'  				=> plugins_url("callback.php", __FILE__),
                'x_url_complete'  				=> $this->get_return_url( $order ),
                'x_url_cancel'           		=> $woocommerce->cart->get_cart_url(),
                'x_test'          				=> $this->settings['test_mode'],
                'x_shop_country'          		=> $this->getCountryCode(),
                'x_shop_name'          			=> $this->settings['shop_name'],
				//customer detail
                'x_customer_first_name' 		=> $order->billing_first_name,
                'x_customer_last_name' 			=> $order->billing_last_name,
                'x_customer_email'      		=> $order->billing_email,
                'x_customer_phone'				=> $order->billing_phone,
                //billing detail
                'x_customer_billing_country'	=> $order->billing_country,
                'x_customer_billing_city' 	    => $order->billing_city,
                'x_customer_billing_address1' 	=> $order->billing_address_1,
                'x_customer_billing_address2' 	=> $order->billing_address_2,
                'x_customer_billing_state' 	    => $order->billing_state,
                'x_customer_billing_zip' 		=> $order->billing_postcode,
                //shipping detail
                'x_customer_shipping_country'	=> $order->billing_country,
 				'x_customer_shipping_city' 	    => $order->postal_city,
                'x_customer_shipping_address1'  => $order->postal_address_1,
                'x_customer_shipping_address2'  => $order->postal_address_2,
                'x_customer_shipping_state' 	=> $order->postal_state,
                'x_customer_shipping_zip' 		=> $order->postal_postcode,
                'gateway_url' 					=> $gatewayUrl
            );

          	$signature = oxipay_sign($transaction_details, $this->settings['oxipay_api_key']);
			$transaction_details['x_signature'] = $signature;

            $order->update_status('on-hold', __('Awaiting '.Config::DISPLAY_NAME.' payment', 'woothemes'));
            $qs = http_build_query($transaction_details);

            return array(
                    'result' 	=>  'success',
                    'redirect'	=>  plugins_url("processing.php?$qs", __FILE__ )
            );
		}

		/**
		 * @param $order
		 * @return bool
		 */
		private function verifyConfiguration($order)
		{
			$apiKey = $this->settings[ 'oxipay_api_key' ];
			$merchantId = $this->settings[ 'oxipay_merchant_id' ];
			$testMode = strtolower($this->settings['test_mode']) == 'yes';
			$sandboxUrl = $this->settings['oxipay_sandbox_gateway_url'];
			$gatewayUrl = $this->settings['oxipay_gateway_url'];
			$region = $this->settings['country'];

			$hasSandboxUrl = !$this->is_null_or_empty($sandboxUrl);
			$hasGatewayUrl = !$this->is_null_or_empty($gatewayUrl);

			$isValid = true;
			$clientMsg = self::PLUGIN_MISCONFIGURATION_CLIENT_MSG;
			$logMsg = '';

			if($this->is_null_or_empty($region)) {
				$logMsg = self::PLUGIN_NO_REGION_LOG_MSG;
				$isValid = false;
			}

			if($this->is_null_or_empty($apiKey)) {
				$logMsg = self::PLUGIN_NO_API_KEY_LOG_MSG;
				$isValid = false;
			}

			if($this->is_null_or_empty($merchantId)) {
				$logMsg = self::PLUGIN_NO_MERCHANT_ID_SET_LOG_MSG;
				$isValid = false;
			}

			if($testMode && !$hasSandboxUrl) {
				$logMsg = self::PLUGIN_NO_SANDBOX_GATEWAY_LOG_MSG;
				$isValid = false;
			}

			if(!$testMode && !$hasGatewayUrl) {
				$logMsg = self::PLUGIN_NO_API_KEY_LOG_MSG;
				$isValid = false;
			}

			if(!$isValid) {
				$order->cancel_order($logMsg);
				$this->logValidationError($clientMsg);
			}

			return $isValid;
		}

		/**
		 * enforces test mode logic to return the correct gateway URL
		 */
		private function getGatewayUrl() {
			$testMode = strtolower($this->settings['test_mode']) == 'yes';
			$sandboxUrl = $this->settings['oxipay_sandbox_gateway_url'];
			$gatewayUrl = $this->settings['oxipay_gateway_url'];

			if($testMode)
				return $sandboxUrl;
			else
				return $gatewayUrl;
		}

		/**
		 * Renders plugin configuration markup
		 */
		function admin_options() { ?>
			<h2><?php _e(Config::DISPLAY_NAME,'woocommerce'); ?></h2>
			<p><?php _e($this->method_description, 'woocommerce' ); ?></p>
			<p>For help setting this plugin up please contact our support team.</p>
			<table class="form-table">
			<?php $this->generate_settings_html(); ?>
			</table>
			<?php

			$countryUrls = array();
			foreach(Config::$countries as $countryCode => $country){
				$countryUrls[$countryCode] = array('gateway' => $this->getDefaultGatewayUrl($countryCode),
												   'sandbox' => $this->getDefaultSandboxGatewayUrl($countryCode) );
			}
			if( count( $countryUrls ) > 0 ) {
				?>
				<script>
					var countryUrls = <?php echo json_encode( $countryUrls ); ?>;
				</script>
				<?php
			}
		}

		/**
		 * This is a filter setup to receive the results from the Oxipay services to show the required
		 * outcome for the order based on the 'x_result' property
		 * @param $order_id
		 * @return mixed
		 */
		function payment_finalisation($order_id)
		{
			$order = wc_get_order($order_id);
			$cart = WC()->session->get('cart', null);
			$full_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$parts = parse_url($full_url, PHP_URL_QUERY);
			parse_str($parts, $params);


			if (oxipay_checksign($params, $this->settings['oxipay_api_key'])) {

				// Get the status of the order from XPay and handle accordingly
				switch ($params['x_result']) {

					case "completed":
						$order->add_order_note(__('Payment approved using ' . Config::DISPLAY_NAME . '. Reference #'. $params['x_gateway_reference'], 'woocommerce'));
						$order->payment_complete($params['x_reference']);
						if (!is_null($cart)) {
							$cart->empty_cart();
						}
						break;

					case "failed":
						$order->add_order_note(__('Payment declined using ' . Config::DISPLAY_NAME . '. Reference #'. $params['x_gateway_reference'], 'woocommerce'));
						$order->update_status('failed');
						break;

					case "pending":
						$order->add_order_note(__('Payment pending using ' . Config::DISPLAY_NAME . '. Reference #'. $params['x_gateway_reference'], 'woocommerce'));
						$order->update_status('on-hold', 'Error may have occurred with ' . Config::DISPLAY_NAME . '. Reference #'. $params['x_gateway_reference']);
						break;
				}

				return $order_id;
			}
			else
			{
				$order->add_order_note(__(Config::DISPLAY_NAME . ' payment response failed signature validation. Please check your Merchant Number and API key or contact Oxipay for assistance.', 0, 'woocommerce'));
				$order->add_order_note(__('Payment declined using ' . Config::DISPLAY_NAME . '. Your Order ID is ' . $order->id, 'woocommerce'));
				$order->update_status('failed');
			}
		}

		/**
		 * This is a filter setup to override the title on the order received page
		 * in the case where the payment has failed
		 * @param $title
		 * @return string
		 */
		function order_received_title( $title ) {
			global $wp_query;

			//copying woocommerce logic from wc_page_endpoint_title() in wc-page-functions.php
			if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && in_the_loop() && is_page() && is_wc_endpoint_url() ) {
				//make sure we are on the Order Received page and have the payment result available
				$endpoint = WC()->query->get_current_endpoint();
				if( $endpoint == 'order-received' && ! empty( $_GET['x_result'] ) ){
					//look at the x_result query var. Ideally we'd load the order and look at the status, but this has not been updated when this filter runs
					if( $_GET['x_result'] == 'failed' ){
						$title = 'Payment Failed';
					}
				}
				//copying woocommerce code- the filter only needs to run once
				remove_filter( 'the_title', array( $this, 'order_received_title' ), 11 );
			}

			return $title;
		}


		// USAGE:  http://myurl.com/?wc-api=WC_Oxipay_Gateway
		function oxipay_callback()
		{
			//todo: provide asynchronous callback implementation for increased resilience during network disruption
			throw new Exception("Not implemented");
		}

		/**
		 * Ensure the customer is being billed from and is shipping to, Australia.
		 * @param $order
		 * @return bool
		 */
		private function checkCustomerLocation($order)
		{
			// The following get shipping and billing countries, and filters null or empty values
			// Then we check to see if there is just a single unique value that is equal to AU, otherwise we 
			// display an error message.

            $countries = array($order->billing_country, $order->shipping_country);
            $set_addresses = array_filter($countries);
			$countryCode = $this->getCountryCode();
			$countryName = $this->getCountryName();
			$valid_addresses = (count(array_unique($set_addresses)) === 1 && end($set_addresses) === $countryCode);

            if (!$valid_addresses) {
                $errorMessage = "&nbsp;Orders from outside " . $countryName . " are not supported by " . Config::DISPLAY_NAME .". Please select a different payment option.";
                $order->cancel_order($errorMessage);
                $this->logValidationError($errorMessage);
                return false;
            }
            return true;
		}

		/**
		 * Ensure the order amount is >= $20
		 * @param $order
		 * @return true
		 */
		private function checkOrderAmount($order)
		{
			if($order->order_total < 20) {
				$errorMessage = "&nbsp;Orders under " . $this->getCurrencyCode() . $this->getCurrencySymbol() . "20 are not supported by " . Config::DISPLAY_NAME . ". Please select a different payment option.";
				$order->cancel_order($errorMessage);
				$this->logValidationError($errorMessage);
				return false;
			}
			return true;
		}

		private function logValidationError($message) {
			wc_add_notice(__('Payment error: ', 'woothemes') . $message, 'error');
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
		private function getCountryName() {
			return Config::$countries[$this->getCountryCode()]['name'];
		}

		/**
		 * @return string
		 */
		private function getCurrencyCode() {
			return Config::$countries[$this->getCountryCode()]['currency_code'];
		}

		/**
		 * @return string
		 */
		private function getCurrencySymbol() {
			return Config::$countries[$this->getCountryCode()]['currency_symbol'];
		}

		/**
		 * @return string
		 */
		private function getBaseUrl() {
			$tld = Config::$countries[$this->getCountryCode()]['tld'];
			$displayName = strtolower(Config::DISPLAY_NAME);
			if($this->is_null_or_empty($tld)) {
				$tld = ".com.au";
			}

			return "https://{$displayName}{$tld}";
		}

		/**
		 * @return string
		 */
		private function getSupportUrl() {
			$baseUrl = $this->getBaseUrl();

			return "$baseUrl/contact";
		}

		/**
		 * Return the default gateway URL for the given country code.
		 * If no country code is provided, use the currently set country.
		 * Default to Australia if no country or an invalid country is set.
		 * @param $str
		 * @return string
		 */
		private function getDefaultGatewayUrl($countryCode = false){
			if(!$countryCode){
				$countryCode = $this->getCountryCode();
			}
			if($this->is_null_or_empty(Config::$countries[$countryCode])) {
				$countryCode = Config::COUNTRY_AUSTRALIA;
			}
			$tld = Config::$countries[$countryCode]['tld'];
			if($this->is_null_or_empty($tld)) {
				$tld = ".com.au";
			}
			$displayName = strtolower(Config::DISPLAY_NAME);

			return "https://secure.{$displayName}{$tld}/Checkout?platform=WooCommerce";
		}

		/**
		 * Return the default sandbox URL for the given country code.
		 * If no country code is provided, use the currently set country.
		 * Default to Australia if no country or an invalid country is set.
		 * @param $str
		 * @return string
		 */
		private function getDefaultSandboxGatewayUrl($countryCode = false){
			if(!$countryCode){
				$countryCode = $this->getCountryCode();
			}
			if($this->is_null_or_empty(Config::$countries[$countryCode])) {
				$countryCode = Config::COUNTRY_AUSTRALIA;
			}
			$tld = Config::$countries[$countryCode]['tld'];
			if($this->is_null_or_empty($tld)) {
				$tld = ".com.au";
			}
			$displayName = strtolower(Config::DISPLAY_NAME);

			return "https://sandboxsecure.{$displayName}{$tld}/Checkout?platform=WooCommerce";
		}

		/**
		 * @param $str
		 * @return bool
		 */
		private function is_null_or_empty($str) {
			return is_null($str) || $str == '';
		}


	}
}

function add_oxipay_payment_gateway($methods) {
	$methods[] = 'WC_Oxipay_Gateway';
	return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_oxipay_payment_gateway');
