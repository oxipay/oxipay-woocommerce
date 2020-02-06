<?php

/**
 * Class WC_HummpaymentTestMain
 */
class WC_HummpaymentTestMain extends WP_UnitTestCase
{
    /**
     * @var object
     */
    public $payment_gateway;

    /**
     * @var string
     */
    public $WC_Session;

    /**
     * setup
     */

    public function setup()
    {
        parent::setup();
        $this->payment_gateway = new WC_Oxipay_Gateway();
        $this->WC_Session = WC()->session;

        self::set_session();
    }

    /**
     * set_session
     */

    public function set_session()
    {

        $serialise = 'a:28:{s:21:"removed_cart_contents";s:6:"a:0:{}";s:22:"shipping_for_package_0";s:901:"a:2:{s:12:"package_hash";s:40:"wc_ship_28c7e48366e1e7f01133e88912c17675";s:5:"rates";a:3:{s:11:"flat_rate:1";O:16:"WC_Shipping_Rate":6:{s:2:"id";s:11:"flat_rate:1";s:5:"label";s:9:"Flat Rate";s:4:"cost";s:5:"10.00";s:5:"taxes";a:1:{i:1;d:1;}s:9:"method_id";s:9:"flat_rate";s:27:"WC_Shipping_Ratemeta_data";a:1:{s:5:"Items";s:20:"DVD Player &times; 1";}}s:15:"free_shipping:2";O:16:"WC_Shipping_Rate":6:{s:2:"id";s:15:"free_shipping:2";s:5:"label";s:13:"Free Shipping";s:4:"cost";s:4:"0.00";s:5:"taxes";a:0:{}s:9:"method_id";s:13:"free_shipping";s:27:"WC_Shipping_Ratemeta_data";a:1:{s:5:"Items";s:20:"DVD Player &times; 1";}}s:14:"local_pickup:3";O:16:"WC_Shipping_Rate":6:{s:2:"id";s:14:"local_pickup:3";s:5:"label";s:12:"Local Pickup";s:4:"cost";s:4:"0.00";s:5:"taxes";a:0:{}s:9:"method_id";s:12:"local_pickup";s:27:"WC_Shipping_Ratemeta_data";a:1:{s:5:"Items";s:20:"DVD Player &times; 1";}}}}";s:23:"chosen_shipping_methods";s:32:"a:1:{i:0;s:14:"local_pickup:3";}";s:22:"shipping_method_counts";s:14:"a:1:{i:0;i:3;}";s:19:"humm_billing_details";s:462:"a:11:{s:22:"humm_billing_first_name";s:3:"humm";s:21:"humm_billing_last_name";s:4:"Test";s:19:"humm_billing_company";s:8:"hummmoney";s:17:"humm_billing_email";s:26:"wayson.lin@hummmoney.com.au";s:17:"humm_billing_phone";s:10:"0416022804";s:19:"humm_billing_country";s:2:"AU";s:21:"humm_billing_address_1";s:12:"50 Bridge st";s:21:"humm_billing_address_2";s:0:"";s:16:"humm_billing_city";s:6:"Sydney";s:17:"humm_billing_state";s:2:"SA";s:20:"humm_billing_postcode";s:4:"2000";}";s:20:"humm_shipping_details";s:473:"a:11:{s:23:"humm_shipping_first_name";s:3:"humm";s:22:"humm_shipping_last_name";s:4:"Test";s:20:"humm_shipping_company";s:8:"hummmoney";s:18:"humm_shipping_email";s:26:"wayson.lin@hummmoney.com.au";s:18:"humm_shipping_phone";s:10:"0416022804";s:20:"humm_shipping_country";s:2:"AU";s:22:"humm_shipping_address_1";s:12:"50 Bridge st";s:22:"humm_shipping_address_2";s:0:"";s:17:"humm_shipping_city";s:6:"Sydney";s:18:"humm_shipping_state";s:2:"SA";s:21:"humm_shipping_postcode";s:4:"2000";}";s:21:"chosen_payment_method";s:8:"hummmoney";s:8:"customer";s:429:"a:14:{s:8:"postcode";s:4:"2000";s:4:"city";s:6:"Sydney";s:9:"address_1";s:12:"50 Bridge st";s:9:"address_2";s:0:"";s:5:"state";s:2:"SA";s:7:"country";s:2:"AU";s:17:"shipping_postcode";s:4:"2000";s:13:"shipping_city";s:6:"Sydney";s:18:"shipping_address_1";s:12:"50 Bridge st";s:18:"shipping_address_2";s:0:"";s:14:"shipping_state";s:2:"SA";s:16:"shipping_country";s:2:"AU";s:13:"is_vat_exempt";b:0;s:19:"calculated_shipping";b:1;}";s:21:"_hummmoney_checkout_id";s:25:"co_IwvE8adHGKg9YMURefHDD0";s:7:"user_id";i:1;s:10:"wc_notices";N;s:4:"cart";s:630:"a:1:{s:32:"c9f0f895fb98ab9159f51fd0297e236d";a:9:{s:10:"product_id";i:0;s:12:"variation_id";i:0;s:9:"variation";a:0:{}s:8:"quantity";i:1;s:10:"line_total";d:3.59090000000000042490455598453991115093231201171875;s:8:"line_tax";d:0.3590999999999999747757328805164434015750885009765625;s:13:"line_subtotal";d:3.59090000000000042490455598453991115093231201171875;s:17:"line_subtotal_tax";d:0.3590999999999999747757328805164434015750885009765625;s:13:"line_tax_data";a:2:{s:5:"total";a:1:{i:1;d:0.3590999999999999747757328805164434015750885009765625;}s:8:"subtotal";a:1:{i:1;d:0.3590999999999999747757328805164434015750885009765625;}}}}";s:15:"applied_coupons";s:6:"a:0:{}";s:23:"coupon_discount_amounts";s:6:"a:0:{}";s:27:"coupon_discount_tax_amounts";s:6:"a:0:{}";s:19:"cart_contents_total";d:3.59090000000000042490455598453991115093231201171875;s:5:"total";d:3.95000000000000017763568394002504646778106689453125;s:8:"subtotal";d:3.95000000000000017763568394002504646778106689453125;s:15:"subtotal_ex_tax";d:3.59090000000000042490455598453991115093231201171875;s:9:"tax_total";d:0.3590999999999999747757328805164434015750885009765625;s:5:"taxes";s:67:"a:1:{i:1;d:0.3590999999999999747757328805164434015750885009765625;}";s:14:"shipping_taxes";s:6:"a:0:{}";s:13:"discount_cart";i:0;s:17:"discount_cart_tax";i:0;s:14:"shipping_total";d:0;s:18:"shipping_tax_total";i:0;s:9:"fee_total";i:0;s:4:"fees";s:6:"a:0:{}";}';

        $array = unserialize($serialise);
        foreach ($array as $key => $value) {
            $data = @unserialize($value);

            if ($data !== false) {
                $this->WC_Session->set($key, unserialize($value));
            } else {
                $this->WC_Session->set($key, $value);
            }

        }
    }

}