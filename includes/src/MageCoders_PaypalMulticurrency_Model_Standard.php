<?php
/**
 * @author   MageCoders
 * @package    MageCoders_PaypalMulticurrency
 */
require_once Mage::getModuleDir('', 'Mage_Paypal').DS.'Model'.DS.'Standard.php';

class MageCoders_PaypalMulticurrency_Model_Standard extends Mage_Paypal_Model_Standard
{   
    public function getStandardCheckoutFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $api = Mage::getModel('paypal/api_standard')->setConfigObject($this->getConfig());
		
		$currency = $this->getPaypalSupportedCurrency($order);
		
		Mage::unregister('order_currency'); Mage::unregister('paypal_currency');
		
		// convert currency to paypal supported
		if(!$currency){
			$currency = $this->switchCurrency();
		}
		
		if($currency!=$order->getOrderCurrencyCode()){
			Mage::register('order_currency',$order->getOrderCurrencyCode());
			Mage::register('paypal_currency',$currency);
		}
		
        $api->setOrderId($orderIncrementId)->setCurrencyCode($currency)
										   ->setOrder($order)
										   ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
										   ->setReturnUrl(Mage::getUrl('paypal/standard/success'))
										   ->setCancelUrl(Mage::getUrl('paypal/standard/cancel'));

        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
		
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif (!empty($address) && $address->validate()) {
            $api->setAddress($address);
        }

        // add cart totals and line items
        $api->setPaypalCart(Mage::getModel('paypal/cart', array($order)))
            ->setIsLineItemsEnabled($this->_config->lineItemsEnabled)
        ;
        $api->setCartSummary($this->_getAggregatedCartSummary());
		
        $result = $api->getStandardCheckoutRequest();
        return $result;
    }
	
	private function _getAggregatedCartSummary()
    {
        if ($this->_config->lineItemsSummary) {
            return $this->_config->lineItemsSummary;
        }
        return Mage::app()->getStore($this->getStore())->getFrontendName();
    }
	
	
	public function canUseForCurrency($currencyCode)
    {
		return true;  // by default allow all currencies
    }
	
	public function getPaypalSupportedCurrency($order){
		
		$ord_currency = $order->getOrderCurrencyCode();
		$base_currency = $order->getBaseCurrencyCode();
		
		if($this->getConfig()->isCurrencyCodeSupported($ord_currency)){
			return $ord_currency;
		}elseif($this->getConfig()->isCurrencyCodeSupported($base_currency)){
			return $base_currency;
		}else{
			return false;
		}
		
	}
	
	protected function switchCurrency(){
		
		$baseCurrencyCode = Mage::app()->getBaseCurrencyCode(); 
		
		$allowedCurrencies = Mage::getModel('directory/currency')
						->getConfigAllowCurrencies();   
						
		if($this->getConfig()->isCurrencyCodeSupported($baseCurrencyCode)){
			return $baseCurrencyCode;
		}						
		$currencies = array_keys($allowedCurrencies);
		if(in_array('USD',$currencies)){
			$currency = 'USD';
		}else{
			foreach($currencies as $cr){
				if($this->getConfig()->isCurrencyCodeSupported($cr)){
					$currency = $cr;
					break;
				}
			}
		}
		return $currency;
		
	}
	

   
}
