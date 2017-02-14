<?php
/**
 * Magento
 @author  MageCoders
 @Email   support@magecoders.com
*/ 
require_once 'Mage/Paypal/Model/Standard.php';

class MageCoders_PaypalMulticurrency_Model_Standard extends Mage_Paypal_Model_Standard
{
    public function getStandardCheckoutFormFields()
    {

		if(!Mage::helper('paypalmulticurrency')->isActive()){
			return parent::getStandardCheckoutFormFields();
		}

	
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $api = Mage::getModel('paypal/api_standard')->setConfigObject($this->getConfig());
        $api->setOrderId($orderIncrementId)->setCurrencyCode($order->getOrderCurrencyCode())
										   ->setOrder($order)
										   ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
										   ->setReturnUrl(Mage::getUrl('paypal/standard/success'))
										   ->setCancelUrl(Mage::getUrl('paypal/standard/cancel'));

        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif ($address->validate()) {
            $api->setAddress($address);
        }

        // add cart totals and line items
        $api->setPaypalCart(Mage::getModel('paypal/cart', array($order)))
            ->setIsLineItemsEnabled($this->_config->lineItemsEnabled)
        ;
        $api->setCartSummary($this->_getAggregatedCartSummary());

        $result = $api->getStandardCheckoutRequest();
		
		//echo "<pre>"; print_r($result); exit;
		
        return $result;
    }
	private function _getAggregatedCartSummary()
    {
		if(!Mage::helper('paypalmulticurrency')->isActive()){
			return parent::_getAggregatedCartSummary();
		}
	
        if ($this->_config->lineItemsSummary) {
            return $this->_config->lineItemsSummary;
        }
        return Mage::app()->getStore($this->getStore())->getFrontendName();
    }

   
}
