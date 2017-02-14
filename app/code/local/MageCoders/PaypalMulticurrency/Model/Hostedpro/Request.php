<?php
/**
 * Magento
 @author  MageCoders
 @Email   support@magecoders.com
*/ 
require_once 'Mage/Paypal/Model/Hostedpro/Request.php';

class MageCoders_PaypalMulticurrency_Model_Hostedpro_Request extends Mage_Paypal_Model_Hostedpro_Request
{
 
 	protected function _getOrderData(Mage_Sales_Model_Order $order)
    {
		if(!Mage::helper('paypalmulticurrency')->isActive()){
			return parent::_getOrderData($order);
		}
	
		
		  $request = array(
            'subtotal'      => $this->_formatPrice(
                $this->_formatPrice($order->getPayment()->getAmountAuthorized()) -
                $this->_formatPrice($order->getTaxAmount()) -
                $this->_formatPrice($order->getShippingAmount())
            ),
            'tax'           => $this->_formatPrice($order->getTaxAmount()),
            'shipping'      => $this->_formatPrice($order->getShippingAmount()),
            'invoice'       => $order->getIncrementId(),
            'address_override' => 'false',
            'currency_code'    => $order->getOrderCurrencyCode(),
            'buyer_email'      => $order->getCustomerEmail()
        );
		
		

        // append to request billing address data
        if ($billingAddress = $order->getBillingAddress()) {
            $request = array_merge($request, $this->_getBillingAddress($billingAddress));
        }

        // append to request shipping address data
        if ($shippingAddress = $order->getShippingAddress()) {
            $request = array_merge($request, $this->_getShippingAddress($shippingAddress));
        }
		

        return $request;
    }
}
