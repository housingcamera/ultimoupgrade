<?php
class MageCoders_PaypalMulticurrency_Model_Payflowpro  extends Mage_Paypal_Model_Payflowpro{

	protected function _buildPlaceRequest(Varien_Object $payment, $amount)
    {
	
		if(!Mage::helper('paypalmulticurrency')->isActive()){
			return parent::_buildPlaceRequest($payment, $amount);
		}
	
        $request = $this->_buildBasicRequest($payment);
        $request->setAmt(round($amount,2));
        $request->setAcct($payment->getCcNumber());
        $request->setExpdate(sprintf('%02d',$payment->getCcExpMonth()) . substr($payment->getCcExpYear(),-2,2));
        $request->setCvv2($payment->getCcCid());

        if ($this->getIsCentinelValidationEnabled()){
            $params = array();
            $params = $this->getCentinelValidator()->exportCmpiData($params);
            $request = Varien_Object_Mapper::accumulateByMap($params, $request, $this->_centinelFieldMap);
        }

        $order = $payment->getOrder();
        if (!empty($order)) {
            $orderIncrementId = $order->getIncrementId();

          //  $request->setCurrency($order->getBaseCurrencyCode())
			  $request->setCurrency($order->getOrderCurrencyCode())		  
                ->setInvnum($orderIncrementId)
                ->setPonum($order->getId())
                ->setComment1($orderIncrementId);

            $customerId = $order->getCustomerId();
            if ($customerId) {
                $request->setCustref($customerId);
            }

            $billing = $order->getBillingAddress();
            if (!empty($billing)) {
                $request->setFirstname($billing->getFirstname())
                    ->setLastname($billing->getLastname())
                    ->setStreet(implode(' ', $billing->getStreet()))
                    ->setCity($billing->getCity())
                    ->setState($billing->getRegionCode())
                    ->setZip($billing->getPostcode())
                    ->setCountry($billing->getCountry())
                    ->setEmail($payment->getOrder()->getCustomerEmail());
            }
            $shipping = $order->getShippingAddress();
            if (!empty($shipping)) {
                $this->_applyCountryWorkarounds($shipping);
                $request->setShiptofirstname($shipping->getFirstname())
                    ->setShiptolastname($shipping->getLastname())
                    ->setShiptostreet(implode(' ', $shipping->getStreet()))
                    ->setShiptocity($shipping->getCity())
                    ->setShiptostate($shipping->getRegionCode())
                    ->setShiptozip($shipping->getPostcode())
                    ->setShiptocountry($shipping->getCountry());
            }
        }
        return $request;
    }

}