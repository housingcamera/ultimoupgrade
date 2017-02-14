<?php

class WP_AjaxShippingPriceCalculator_Block_Result extends WP_AjaxShippingPriceCalculator_Block_Abstract
{
    public function getResult()
    {
        return $this->getCalculator()->getResult();
    }

    public function getCarrierName($code)
    {
        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code);
        if ($carrier) {
            return $carrier->getConfigData('title');
        }
        return null;
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->formatPrice(
            $this->helper('tax')->getShippingPrice(
                $price,
                $flag,
                $this->getCalculator()->getQuote()->getShippingAddress()
           )
        );
    }

    public function formatPrice($price)
    {
        return $this->getCalculator()->getQuote()->getStore()->convertPrice($price, true);
    }
}
