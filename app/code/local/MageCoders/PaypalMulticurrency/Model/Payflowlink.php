<?php
class MageCoders_PaypalMulticurrency_Model_Payflowlink extends Mage_Paypal_Model_Payflowlink{
	
 /**
     * Build request for getting token
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Varien_Object
     */
    protected function _buildTokenRequest(Mage_Sales_Model_Order_Payment $payment)
    {
        $request = $this->_buildBasicRequest($payment);
        $request->setCreatesecuretoken('Y')
            ->setSecuretokenid($this->_generateSecureTokenId())
            ->setTrxtype($this->_getTrxTokenType())
            ->setAmt($this->_formatStr('%.2F', $payment->getOrder()->getTotalDue()))
            ->setCurrency($payment->getOrder()->getOrderCurrencyCode())
            ->setInvnum($payment->getOrder()->getIncrementId())
            ->setCustref($payment->getOrder()->getIncrementId())
            ->setPonum($payment->getOrder()->getId());
        //This is PaPal issue with taxes and shipping
            //->setSubtotal($this->_formatStr('%.2F', $payment->getOrder()->getBaseSubtotal()))
            //->setTaxamt($this->_formatStr('%.2F', $payment->getOrder()->getBaseTaxAmount()))
            //->setFreightamt($this->_formatStr('%.2F', $payment->getOrder()->getBaseShippingAmount()));


        $order = $payment->getOrder();
        if (empty($order)) {
            return $request;
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
                ->setEmail($order->getCustomerEmail());
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
        //pass store Id to request
        $request->setUser1($order->getStoreId())
            ->setUser2($this->_getSecureSilentPostHash($payment));

        return $request;
    }
	

}