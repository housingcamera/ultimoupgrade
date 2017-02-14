<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Express Module
 */

class MageCoders_PaypalMulticurrency_Model_Express extends Mage_Paypal_Model_Express{
	
 	/**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Paypal_Model_Express
     */
    public function capture(Varien_Object $payment, $amount)
    {
		if(!Mage::helper('paypalmulticurrency')->isActive()){
			return parent::capture($payment,$amount);
		}
		
		$version = Mage::getVersion();
        if(version_compare($version,'1.5.1.0')==0){
			return parent::capture($payment,$amount);
		}
		
		
		if(!$payment){
			return parent::capture($payment,$amount);
		}
	
        $authorizationTransaction = $payment->getAuthorizationTransaction();
        $authorizationPeriod = abs(intval($this->getConfigData('authorization_honor_period')));
        $maxAuthorizationNumber = abs(intval($this->getConfigData('child_authorization_number')));
        $order = $payment->getOrder();
        $isAuthorizationCreated = false;

        if ($payment->getAdditionalInformation($this->_isOrderPaymentActionKey)) {
            $voided = false;
            if (!$authorizationTransaction->getIsClosed()
                && $this->_isTransactionExpired($authorizationTransaction, $authorizationPeriod)
            ) {
                //Save payment state and configure payment object for voiding
                $isCaptureFinal = $payment->getShouldCloseParentTransaction();
                $captureTrxId = $payment->getTransactionId();
                $payment->setShouldCloseParentTransaction(false);
                $payment->setParentTransactionId($authorizationTransaction->getTxnId());
                $payment->unsTransactionId();
                $payment->setVoidOnlyAuthorization(true);
                $payment->void(new Varien_Object());

                //Revert payment state after voiding
                $payment->unsAuthorizationTransaction();
                $payment->unsTransactionId();
                $payment->setShouldCloseParentTransaction($isCaptureFinal);
                $voided = true;
            }

            if ($authorizationTransaction->getIsClosed() || $voided) {
                if ($payment->getAdditionalInformation($this->_authorizationCountKey) > $maxAuthorizationNumber - 1) {
                    Mage::throwException(Mage::helper('paypal')->__('The maximum number of child authorizations is reached.'));
                }
                $api = $this->_callDoAuthorize(
                    $amount,
                    $payment,
                    $authorizationTransaction->getParentTxnId()
                );

                //Adding authorization transaction
                $this->_pro->importPaymentInfo($api, $payment);
                $payment->setTransactionId($api->getTransactionId());
                $payment->setParentTransactionId($authorizationTransaction->getParentTxnId());
                $payment->setIsTransactionClosed(false);

               // $formatedPrice = $order->getBaseCurrency()->formatTxt($amount);
			    $formatedPrice = $order->getOrderCurrency()->formatTxt($amount);

                if ($payment->getIsTransactionPending()) {
                    $message = Mage::helper('paypal')->__('Authorizing amount of %s is pending approval on gateway.', $formatedPrice);
                } else {
                    $message = Mage::helper('paypal')->__('Authorized amount of %s.', $formatedPrice);
                }

                $transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null,
                    true, $message
                );

                $payment->setParentTransactionId($api->getTransactionId());
                $isAuthorizationCreated = true;
            }
            //close order transaction if needed
            if ($payment->getShouldCloseParentTransaction()) {
                $orderTransaction = $payment->lookupTransaction(
                    false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER
                );

                if ($orderTransaction) {
                    $orderTransaction->setIsClosed(true);
                    $order->addRelatedObject($orderTransaction);
                }
            }
        }

        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }

        if ($isAuthorizationCreated && isset($transaction)) {
            $transaction->setIsClosed(true);
        }

        return $this;
    }
	
	
	protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
		
		if(!Mage::helper('paypalmulticurrency')->isActive()){
			return parent::_placeOrder($payment,$amount);
		}
	 
        $order = $payment->getOrder();

        // prepare api call
        $token = $payment->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_TOKEN);
        $api = $this->_pro->getApi()
            ->setToken($token)
            ->setPayerId($payment->
                getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
            ->setAmount($amount)
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setNotifyUrl(Mage::getUrl('paypal/ipn/'))
            ->setInvNum($order->getIncrementId())
            ->setCurrencyCode($order->getOrderCurrencyCode())
            ->setPaypalCart(Mage::getModel('paypal/cart', array($order)))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled);
			
			
		
		$version = Mage::getVersion();
        if(version_compare($version,'1.6.0.0')>0){
		
			if ($order->getIsVirtual()) {
				$api->setAddress($order->getBillingAddress())->setSuppressShipping(true);
			} else {
				$api->setAddress($order->getShippingAddress());
				$api->setBillingAddress($order->getBillingAddress());
			}
		
		}

        // call api and get details from it
        $api->callDoExpressCheckoutPayment();

        $this->_importToPayment($api, $payment);
        return $this;
    }
	

}
