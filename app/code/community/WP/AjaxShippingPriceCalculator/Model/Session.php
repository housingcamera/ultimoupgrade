<?php

class WP_AjaxShippingPriceCalculator_Model_Session extends Mage_Core_Model_Session_Abstract
{
    const MODULE_NAMESPACE = 'ajaxshippingpricecalculator';

    public function __construct()
    {
        $this->init(self::MODULE_NAMESPACE);
    }
}
