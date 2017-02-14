<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
class Amasty_Shiprestriction_Model_Shipping_Shipping extends Mage_Shipping_Model_Shipping
{
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    { 	
        parent::collectRates($request);
        
        $result   = $this->getResult();
        Mage::dispatchEvent('am_restrict_rates', array(
            'request' => $request, 
            'result'  => $result,
        ));
                 
        return $this;
    }
}