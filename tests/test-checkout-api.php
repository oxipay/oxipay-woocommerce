<?php
/**
 * Class CheckoutApiTest
 */
class CheckoutApiTest extends WC_HummpaymentTestMain
{
    /**
     * @return array
     */
    public function exception_provider()
    {
        return array(
            array(new \Exception())
        );
    }

    /**
     * createOrder
     */
    private function createOrder(){        
        global $woocommerce;

       if ( ! defined( 'WOOCOMMERCE_CART' ) )
        define( 'WOOCOMMERCE_CART', true );
            
        if(!$this->orderD){
            $woocommerce->cart->add_to_cart(53);

            WC()->cart->calculate_totals();

            $order_id = $woocommerce->checkout()->create_order(array());

            $this->order = new WC_Order( $order_id );
        }
    }

}