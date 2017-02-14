<?php
/**
 * @author   MageCoders
 * @package    MageCoders_PaypalMulticurrency
 */
class MageCoders_PaypalMulticurrency_Model_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment{

 /**
     * Decide whether authorization transaction may close (if the amount to capture will cover entire order)
     * @param float $amountToCapture
     * @return bool
     */
	protected $_helper; 
	
	protected function _isCaptureFinal($amountToCapture)
    {
		$defaultBase  = false;

		$this->_helper = Mage::helper('paypalmulticurrency');
        $amountToCapture = $this->_formatAmount($amountToCapture, true);
        
		$baseCurrency = $this->getOrder()->getBaseCurrencyCode(); 
		if(!$this->_helper->isCurrencySupported($baseCurrency)){
			$baseCurrency = 'USD';
			$defaultBase = true;
		}
		
		$orderCurrency = $this->getOrder()->getOrderCurrencyCode();
		$orderGrandTotal = $this->_formatAmount($this->getOrder()->getGrandTotal(), true);
	
		if(!$this->_helper->isCurrencySupported($orderCurrency)){
			$orderGrandTotal = $this->_helper->convertCurrency($orderGrandTotal,$baseCurrency,$orderCurrency);
		}
		
		if($defaultBase){
			$orderGrandTotal = number_format($orderGrandTotal,2,'.','');
		}
		$captureAmount = $this->_formatAmount($this->getBaseAmountPaid(), true) + $amountToCapture;
		
		$this->_helper->debug('capture:'.$captureAmount.' --- OrderTotal:'.$orderGrandTotal);
		
     	if ($orderGrandTotal == $this->_formatAmount($this->getBaseAmountPaid(), true) + $amountToCapture) {
	        if (false !== $this->getShouldCloseParentTransaction()) {
                $this->setShouldCloseParentTransaction(true);
            }
            return true;
        }
        return false;
    }	

}