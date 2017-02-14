<?php
class Zero1_Customshipprice_Model_Carrier_Customshipprice
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'customshipprice';
    protected $_isFixed = false;

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
    	if(!Mage::app()->getStore()->isAdmin()) {
    		return false;		// Only allow this to be used from the admin system
    	}
    	
        if (!$this->getConfigFlag('active')) {
            return false;
        }
        
        $result = Mage::getModel('shipping/rate_result');

        $shippingPrice = Mage::getSingleton('core/session')->getCustomshippriceAmount();
        $baseShippingPrice = Mage::getSingleton('core/session')->getCustomshippriceBaseAmount();
        $description = Mage::getSingleton('core/session')->getCustomshippriceDescription();
        
        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('customshipprice');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('customshipprice');
            $method->setMethodTitle((strlen($description) > 0) ? $description : $this->getConfigData('name'));

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }
        
        return $result;
    }

    public function getAllowedMethods()
    {
        return array('customshipprice'=>$this->getConfigData('name'));
    }

}
