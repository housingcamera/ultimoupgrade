<?php

class WP_AjaxShippingPriceCalculator_Block_Form extends WP_AjaxShippingPriceCalculator_Block_Abstract
{
    public function isFieldVisible($fieldName)
    {
        if (method_exists($this->getConfig(), 'use' . uc_words($fieldName, ''))) {
            return $this->getConfig()->{'use' . uc_words($fieldName, '')}();
        }
        return true;
    }

    public function getFieldValue($fieldName)
    {
        $values = $this->getSession()->getFormValues();
        if (isset($values[$fieldName])) return $values[$fieldName];
        return null;
    }

    public function isFieldRequired($fieldName)
    {
        $methodMap = array(
            'region'    => 'isStateProvinceRequired', // --- is region required?
            'city'      => 'isCityRequired', // --- is city required?
            'postcode'  => 'isZipCodeRequired' // --- is postal code required?
        );

        if (!isset($methodMap[$fieldName])) return false;

        $method = $methodMap[$fieldName];

        foreach ($this->getCarriers() as $carrier)
        {
            if ($carrier->$method()) return true;
        }

        return false;
    }

    public function getCalculatorUrl()
    {
        return $this->getUrl('wp_ajaxshippingpricecalculator/index/index', array('_current' => true));
    }

    public function getCarriers()
    {
        if ($this->_carriers === null)
        $this->_carriers = Mage::getModel('shipping/config')->getActiveCarriers();
        return $this->_carriers;
    }

    public function useShoppingCart()
    {
        if ($this->getSession()->getFormValues() === null || !$this->isFieldVisible('cart'))
            return $this->getConfig()->useCartDefault();
        return $this->getFieldValue('cart');
    }
}
