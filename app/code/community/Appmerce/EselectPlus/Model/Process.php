<?php
/**
 * Appmerce - Applications for Ecommerce
 * http://www.appmerce.com
 *
 * @extension   eSELECTplus eSELECTplus Canada payment suite
 * @type        Payment method
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Magento
 * @package     Appmerce_EselectPlus
 * @copyright   Copyright (c) 2011-2014 Appmerce (http://www.appmerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Appmerce_EselectPlus_Model_Process extends Varien_Object
{
    /**
     * Return checkout session
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return payment API model
     *
     * @return Appmerce_EselectPlus_Model_Api
     */
    protected function getApi()
    {
        return Mage::getSingleton('eselectplus/api');
    }

    /**
     * Success process
     * [multi-method] [no-service]
     *
     * Update succesful (paid) orders, send order email, create invoice
     * and send invoice email. Restore quote and clear cart.
     *
     * @param $order object Mage_Sales_Model_Order
     * @param $note string Backend order history note
     * @param $transactionId string Transaction ID
     * @param $responseCode integer Response code
     * @param $frontend boolean
     */
    public function success(Mage_Sales_Model_Order $order, $note, $transactionId, $responseCode = 1, $frontend = false)
    {
        $this->check($order);
        if ($order->getId() && $responseCode != $order->getPayment()->getAppmerceResponseCode()) {
            $order->getPayment()->setAppmerceResponseCode($responseCode);
            $order->getPayment()->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);

            // Multi-method API
            $paymentMethodCode = $order->getPayment()->getMethod();

            // Send order email
            if (!$order->getEmailSent() && $this->getApi()->getConfig()->getPaymentConfigData($paymentMethodCode, 'order_email')) {
                $order->sendNewOrderEmail()->setEmailSent(true);
            }

            // Set processing status
            $processingOrderStatus = $this->getApi()->getConfig()->getProcessingStatus($paymentMethodCode);
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $processingOrderStatus, $note, $notified = false);
            $order->save();

            // Create invoice
            if ($this->getApi()->getConfig()->getPaymentConfigData($paymentMethodCode, 'invoice_create')) {
                $this->invoice($order);
            }
        }

        if ($frontend) {
            $this->restore();
            $this->clear();
        }
    }

    /**
     * Create automatic invoice
     * [multi-method] [no-service]
     *
     * @param $order Mage_Sales_Model_Order
     */
    public function invoice($order)
    {
        $this->check($order);
        if (!$order->hasInvoices() && $order->canInvoice()) {
            $invoice = $order->prepareInvoice();
            if ($invoice->getTotalQty() > 0) {
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->setTransactionId($order->getPayment()->getTransactionId());
                $invoice->register();
                $transaction = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
                $transaction->save();
                $invoice->addComment(Mage::helper('eselectplus')->__('Automatic invoice.'), false);

                // Multi-method API
                $paymentMethodCode = $order->getPayment()->getMethod();

                // Send invoice email
                if (!$invoice->getEmailSent() && $this->getApi()->getConfig()->getPaymentConfigData($paymentMethodCode, 'invoice_email')) {
                    $invoice->sendEmail()->setEmailSent(true);
                }
                $invoice->save();
            }
        }
    }

    /**
     * Pending process [multi-method] [no-service]
     *
     * Update orders with explicit payment pending status. Restore quote.
     *
     * @param $order object Mage_Sales_Model_Order
     * @param $note string Backend order history note
     * @param $transactionId string Transaction ID
     * @param $redirect boolean
     */
    public function pending(Mage_Sales_Model_Order $order, $note, $transactionId, $responseCode = 1, $frontend = false)
    {
        $this->check($order);
        if ($order->getId() && $responseCode != $order->getPayment()->getAppmerceResponseCode()) {
            # don't set response code, allow for another status update
            $order->getPayment()->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);

            // Multi-method API
            $paymentMethodCode = $order->getPayment()->getMethod();

            // Set pending_payment state
            $pendingOrderStatus = $this->getApi()->getConfig()->getPendingStatus($paymentMethodCode);
            $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, $pendingOrderStatus, $note, $notified = false);
            $order->save();
        }

        if ($frontend) {
            $this->restore();
            $this->clear();
        }
    }

    /**
     * Cancel process
     *
     * Update failed, cancelled, declined, rejected etc. orders. Cancel
     * the order and show user message. Restore quote.
     *
     * @param $order object Mage_Sales_Model_Order
     * @param $note string Backend order history note
     * @param $transactionId string Transaction ID
     * @param $responseCode integer Response code
     * @param $frontend boolean
     */
    public function cancel(Mage_Sales_Model_Order $order, $note, $transactionId, $responseCode = 1, $frontend = false)
    {
        $this->check($order);
        if ($order->getId() && $responseCode != $order->getPayment()->getAppmerceResponseCode()) {
            $order->getPayment()->setAppmerceResponseCode($responseCode);
            $order->getPayment()->setTransactionId($transactionId);
            $order->getPayment()->setLastTransId($transactionId);

            // Cancel order
            $order->addStatusToHistory($order->getStatus(), $note, $notified = true);
            $order->cancel()->save();
        }

        if ($frontend) {
            $this->repeat();
        }
    }

    /**
     * Check order state
     *
     * If the order state (not status) is already one of:
     * canceled, closed, holded or completed,
     * then we do not update the order status anymore.
     *
     * @param $order object Mage_Sales_Model_Order
     */
    public function check(Mage_Sales_Model_Order $order)
    {
        if ($order->getId()) {
            $state = $order->getState();
            switch ($state) {
                case Mage_Sales_Model_Order::STATE_HOLDED :
                case Mage_Sales_Model_Order::STATE_CANCELED :
                case Mage_Sales_Model_Order::STATE_CLOSED :
                case Mage_Sales_Model_Order::STATE_COMPLETE :
                    exit();
                    break;

                default :
            }
        }
    }

    /**
     * Done processing
     *
     * Restore checkout session and clear cart for success page.
     */
    public function done()
    {
        $this->restore();
        $this->clear();
    }

    /**
     * Restore process
     *
     * Restore checkout session and show payment failed message.
     */
    public function repeat()
    {
        $this->restore();
        $message = Mage::helper('eselectplus')->__('Payment failed. Please try again.');
        $this->getCheckout()->addError($message);
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        $this->getCheckout()->getQuote()->setIsActive(false)->save();
    }

    /**
     * Restore checkout session
     */
    public function restore()
    {
        $this->getCheckout()->setQuoteId($this->getCheckout()->geteSELECTplusQuoteId(true));
        $this->getCheckout()->setLastOrderId($this->getCheckout()->geteSELECTplusOrderId(true));
    }

}
