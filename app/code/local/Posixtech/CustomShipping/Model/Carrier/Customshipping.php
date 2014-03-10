<?php
class Posixtech_CustomShipping_Model_Carrier_Customshipping
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'custom_shipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $shippingPrice = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {

                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                $shippingPrice = $shippingPrice + ($item->getQty()*$item->getPrice());
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        if ($shippingPrice !== false) {
            $price = $this->getConfigData('price');
            $customer_buy = $this->getConfigData('customer_buy');
            $customer_buy_off_price = $this->getConfigData('customer_buy_off_price');
            $free_shipping_over_price = $this->getConfigData('free_shipping_over_price');

            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('custom_shipping');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('custom_shipping');
            $method->setMethodTitle($this->getConfigData('name'));

            if($shippingPrice < $customer_buy) {
                $shippingPrice = $price;
            } else if(($shippingPrice >= $customer_buy) && ($shippingPrice < $free_shipping_over_price)) {
                $offPrice = ($price*$customer_buy_off_price)/100;
                $shippingPrice = $price - $offPrice;
            } else {
                $shippingPrice = 0;
            }


            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }

    public function getAllowedMethods()
    {
        return array('custom_shipping'=>$this->getConfigData('name'));
    }
}