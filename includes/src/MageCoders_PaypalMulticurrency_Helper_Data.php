<?php
/**
 * @author   MageCoders
 * @package    MageCoders_PaypalMulticurrency 
 */ 
class MageCoders_PaypalMulticurrency_Helper_Data extends Mage_Core_Helper_Abstract{
 
	public function convertCurrency($price,$from = null,$to = null){
		if(!$from){
			$from = Mage::registry('paypal_currency');
		}
		if(empty($from)){ return $price; }
		if(empty($to)){
			$to =  Mage::registry('order_currency');
		}
		// Retrieve currency rate (only base=>allowed)
		$rate = Mage::getResourceModel('directory/currency')->getRate($from,$to);
		$newPrice = (float)($price/$rate);
		
		//$this->debug('old:'.$price.' '.$from.' --- New:'.$newPrice.' '.$to);
		
		return $newPrice; 
	}
	
	public function isCurrencySupported($currency){
		return Mage::getSingleton('paypal/config')->isCurrencyCodeSupported($currency);
	}

	public function debug($message){
		Mage::log($message,null,'pmc.log');
	}
}