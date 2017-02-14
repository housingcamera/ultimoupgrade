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

class Appmerce_EselectPlus_Model_Cc extends Mage_Payment_Model_Method_Cc
{
    protected $_isGateway = true;
    protected $_canOrder = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = false;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    // Do NOT store Cc data in Magento
    protected $_canSaveCc = false;

    // Restrictions
    protected $_allowCurrencyCode = array();

    const CVD_PRESENT = 1;
    const CVD_NOT_PRESENT = 9;

    const MPG_TYPE_AUTH = 'preauth';
    const MPG_TYPE_CAPTURE = 'completion';
    const MPG_TYPE_REFUND = 'refund';
    const MPG_TYPE_VOID = 'purchasecorrection';
    const MPG_CRYPT_TYPE = 7;
    const MPG_API_TEST_TOKEN = 'yesguy';

    /**
     * Return Eselectplus config instance
     *
     * @return Appmerce_Eselectplus_Model_Config
     */
    public function __construct()
    {
        $this->_config = Mage::getSingleton('eselectplus/config');
        return $this;
    }

    /**
     * Return eselectplus configuration instance
     *
     * @return Appmerce_Eselectplus_Model_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Validate if payment is possible
     *  - check allowed currency codes
     *
     * @return bool
     */
    public function validate()
    {
        // Do not use parent server method validation (Credit Card numbers)
        // parent::validate();
        $currency_code = $this->getCurrencyCode();
        if (!empty($this->_allowCurrencyCode) && !in_array($currency_code, $this->_allowCurrencyCode)) {
            $errorMessage = Mage::helper('eselectplus')->__('Selected currency (%s) is not compatible with this payment method.', $currency_code);
            Mage::throwException($errorMessage);
        }
        return $this;
    }

    /**
     * Authorize a payment for future capture
     *
     * @var Variant_Object $payment
     * @var Float $amount
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('eselectplus')->__('Invalid amount for authorization.'));
        }
        $payment->setAmount($amount);
        $order = $payment->getOrder();

        $this->_authorize($payment, $order);

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Capture payment
     *
     * @var Variant_Object $payment
     * @var Float $amount
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('eselectplus')->__('Invalid amount for capture.'));
        }
        $payment->setAmount($amount);
        $order = $payment->getOrder();

        $transactionId = $payment->getTransactionId();
        if (!$transactionId) {
            $this->_capture($payment, $order);
        }
        else {
            $this->_capturePreauth($payment, $order);
        }

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Refund a payment
     *
     * @var Variant_Object $payment
     * @var Float $amount
     */
    public function refund(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException(Mage::helper('eselectplus')->__('Invalid amount for refund.'));
        }
        $payment->setAmount($amount);
        $order = $payment->getOrder();

        $this->_refund($payment, $order);

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Void the payment through gateway
     *
     * @param  Mage_Payment_Model_Info $payment
     * @return Mage_Paygate_Model_Authorizenet
     */
    public function void(Varien_Object $payment)
    {
        $order = $payment->getOrder();

        $this->_void($payment, $order);

        $payment->setSkipTransactionCreation(true);
        return $this;
    }

    /**
     * Void process
     */
    public function _authorize($payment, $order)
    {
        $storeId = $order->getStoreId();

        // Expiration date mm/yy
        $month = $payment->getCcExpMonth();
        $mm = (string)($month < 10 ? '0' . $month : $month);
        $yy = (string)substr($payment->getCcExpYear(), 2, 2);

        $txnArray = array(
            'type' => self::MPG_TYPE_AUTH,
            'order_id' => $order->getIncrementId() . '-' . $storeId,
            'cust_id' => substr($order->getBillingAddress()->getLastname(), 0, 30),
            'amount' => number_format($payment->getAmount(), 2, '.', ''),
            'pan' => $payment->getCcNumber(),
            'expdate' => $mm . $yy,
            'crypt_type' => self::MPG_CRYPT_TYPE,
            'dynamic_descriptor' => '',
        );

        // Debug out
        if ($this->getConfigData('debug_flag')) {
            $data = print_r($txnArray, true);
            Mage::getModel('eselectplus/api_debug')->setDir('out')->setUrl('checkout/onepage')->setData('data', $data)->save();
        }

        // Flush all but card type and last4
        $this->_clearAssignedData($payment);

        $mpgTxn = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Transaction($txnArray);
        $mpgRequest = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Request($mpgTxn);
        $mpgHttpPost = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Httpspost($this->getConfigData('store_id', $storeId), $this->getConfigData('api_token', $storeId), $mpgRequest);
        $response = $mpgHttpPost->getMpgResponse();

        $gatewayTransactionId = (string)$response->getTxnNumber();
        $note = Mage::helper('eselectplus')->__('Moneris eSELECTplus Direct Post');

        $responseCode = (int)$response->getResponseCode();
        if ($responseCode && $responseCode > 0 && $responseCode != 'null') {
            switch ($responseCode) {
                case !is_null($responseCode) && $responseCode < 50 :
                    $this->_addTransaction($payment, $gatewayTransactionId, Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, array('is_transaction_closed' => 0), array($this->_realTransactionIdKey => $gatewayTransactionId), $note);
                    break;

                default :
                    Mage::throwException(Mage::helper('eselectplus')->__('Preauth request failed. %s', $response->getMessage()));
            }
        }
        else {
            Mage::throwException(Mage::helper('eselectplus')->__('Preauth request failed. %s', $response->responseData['Message']));
        }

        // !important setLastTransId for CreditMemo
        $payment->setLastTransId($gatewayTransactionId);
    }

    /**
     * Capture preauth process
     */
    public function _capturePreauth($payment, $order)
    {
        $storeId = $order->getStoreId();

        // Expiration date mm/yy
        $month = $payment->getCcExpMonth();
        $mm = (string)($month < 10 ? '0' . $month : $month);
        $yy = (string)substr($payment->getCcExpYear(), 2, 2);

        $txnArray = array(
            'type' => self::MPG_TYPE_CAPTURE,
            'txn_number' => $payment->getLastTransId(),
            'order_id' => $order->getIncrementId() . '-' . $storeId,
            'comp_amount' => number_format($payment->getAmount(), 2, '.', ''),
            'crypt_type' => self::MPG_CRYPT_TYPE,
            'dynamic_descriptor' => '',
        );

        // Debug out
        if ($this->getConfigData('debug_flag')) {
            $data = print_r($txnArray, true);
            Mage::getModel('eselectplus/api_debug')->setDir('out')->setUrl('checkout/onepage')->setData('data', $data)->save();
        }

        // Flush all but card type and last4
        $this->_clearAssignedData($payment);

        $response = $this->getMpg($txnArray, $storeId);
        $gatewayTransactionId = (string)$response->getTxnNumber();
        $note = Mage::helper('eselectplus')->__('Moneris eSELECTplus Direct Post');

        $responseCode = (int)$response->getResponseCode();
        if ($responseCode && $responseCode > 0 && $responseCode != 'null') {
            switch ($responseCode) {
                case !empty($responseCode) && $responseCode < 50 :
                    $this->_addTransaction($payment, $gatewayTransactionId, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, array('is_transaction_closed' => 0), array($this->_realTransactionIdKey => $gatewayTransactionId), $note);
                    break;

                default :
                    Mage::throwException(Mage::helper('eselectplus')->__('Capture request failed. %s', $response->getMessage()));
            }
        }
        else {
            Mage::throwException(Mage::helper('eselectplus')->__('Capture request failed. %s', $response->responseData['Message']));
        }

        // !important setLastTransId for CreditMemo
        $payment->setLastTransId($gatewayTransactionId);
    }

    /**
     * Capture process
     */
    public function _capture($payment, $order)
    {
        $url = $this->getGatewayUrl('moneris');
        $query = $this->getFormFields($order);
        $request = $this->curlPost($url, $query);

        // Debug out
        if ($this->getConfigData('debug_flag')) {
            $data = print_r($request, true);
            Mage::getModel('eselectplus/api_debug')->setDir('out')->setUrl('checkout/onepage')->setData('data', $data)->save();
        }

        // Process response
        if ($request) {
            print '<pre>';
            print_r($request);
            exit;
            $attributes = $this->parseQuery($request);

            $gatewayTransactionId = (string) isset($attributes['txn_num']) ? $attributes['txn_num'] : $attributes['bank_transaction_id'];
            $gatewayStatus = (int)$attributes['result'];
            $gatewayError = (int)$attributes['response_code'];
            $gatewayErrorMessage = (string)$attributes['message'];

            // Flush all but card type and last4
            $this->_clearAssignedData($payment);

            $note = $this->buildNote($attributes);
            switch ($gatewayStatus) {
                case 1 :
                    $this->_addTransaction($payment, $gatewayTransactionId, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, array('is_transaction_closed' => 0), array($this->_realTransactionIdKey => $gatewayTransactionId), $note);
                    break;

                default :
                    Mage::throwException(Mage::helper('eselectplus')->__('Payment refused. %s (%s)', $gatewayErrorMessage, $gatewayError));
            }
        }
        else {
            Mage::throwException(Mage::helper('eselectplus')->__('Payment request failed. Please contact the merchant.'));
        }

        // !important setLastTransId for CreditMemo
        $payment->setLastTransId($gatewayTransactionId);
    }

    /**
     * Refund process
     */
    public function _refund($payment, $order)
    {
        $storeId = $order->getStoreId();
        $txnArray = array(
            'type' => self::MPG_TYPE_REFUND,
            'txn_number' => $payment->getLastTransId(),
            'order_id' => $order->getIncrementId() . '-' . $storeId,
            'amount' => number_format($payment->getAmount(), 2, '.', ''),
            'crypt_type' => self::MPG_CRYPT_TYPE,
        );

        $response = $this->getMpg($txnArray, $storeId);
        $responseCode = (int)$response->getResponseCode();
        if ($responseCode && $responseCode > 0 && $responseCode != 'null') {
            switch ($responseCode) {
                case !empty($responseCode) && $responseCode < 50 :
                    // Success, intentionally left empty
                    break;

                default :
                    Mage::throwException(Mage::helper('eselectplus')->__('Refund request failed. %s', $response->getMessage()));
            }
        }
        else {
            Mage::throwException(Mage::helper('eselectplus')->__('Refund request failed. %s', $response->responseData['Message']));
        }
    }

    /**
     * Void process
     */
    public function _void($payment, $order)
    {
        $storeId = $order->getStoreId();
        $txnArray = array(
            'type' => self::MPG_TYPE_VOID,
            'txn_number' => $payment->getLastTransId(),
            'order_id' => $order->getIncrementId() . '-' . $storeId,
            'amount' => number_format($payment->getAmount(), 2, '.', ''),
            'crypt_type' => self::MPG_CRYPT_TYPE,
        );

        $response = $this->getMpg($txnArray, $storeId);
        $responseCode = (int)$response->getResponseCode();
        if ($responseCode && $responseCode > 0 && $responseCode != 'null') {
            switch ($responseCode) {
                case !empty($responseCode) && $responseCode < 50 :
                    // Success, intentionally left empty
                    break;

                default :
                    Mage::throwException(Mage::helper('eselectplus')->__('Void request failed. %s', $response->getMessage()));
            }
        }
        else {
            Mage::throwException(Mage::helper('eselectplus')->__('Void request failed. %s', $response->responseData['Message']));
        }
    }

    /**
     * Build transaction note
     */
    public function buildNote($attributes)
    {
        $note = Mage::helper('eselectplus')->__('Moneris eSELECTplus Direct Post:');
        $note .= '<br />' . Mage::helper('eselectplus')->__('Card Holder Name: %s', $attributes['cardholder']);
        $cardTypes = $this->getConfig()->getCardTypes();
        if (isset($cardTypes[$attributes['card']])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('Card Type: %s', $cardTypes[$attributes['card']]);
        }
        $note .= '<br />' . Mage::helper('eselectplus')->__('Card Number: %s', $attributes['f4l4']);

        // CVD / AVS
        if (isset($attributes['avs_response_code'])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('AVS Response Code: %s', $attributes['avs_response_code']);
        }
        if (isset($attributes['cvd_response_code'])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('CVD Respones Code: %s', $attributes['cvd_response_code']);
        }

        // INTERAC Online Payments
        if (isset($attributes['ISSNAME'])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('INTERAC Card Issuer: %s', $attributes['ISSNAME']);
            $note .= '<br />' . Mage::helper('eselectplus')->__('INTERAC Invoice Nr.: %s', $attributes['INVOICE']);
            $note .= '<br />' . Mage::helper('eselectplus')->__('INTERAC Confirmation Nr.: %s', $attributes['ISSCONF']);
        }

        return $note;
    }

    /**
     * Parse key=valye<br>key=value query string
     *
     * @return array
     */
    public function parseQuery($request)
    {
        $parsed_str = explode("<br>", $request);

        $response = array();
        foreach ($parsed_str as $fragment) {
            if (!empty($fragment)) {
                $explode = explode('=', $fragment);
                $response[trim($explode[0])] = trim($explode[1]);
            }
        }
        return $response;
    }

    /**
     * Get gateway Url
     */
    public function getGatewayUrl($type)
    {
        $gateways = $this->getConfig()->getGateways();
        $mode = $this->getConfigData('test_flag') ? 'test' : 'live';
        return $gateways[$type][$mode];
    }

    /**
     * Decide currency code type
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return Mage::app()->getStore()->getCurrencyCode();
    }

    /**
     * Decide grand total
     *
     * @param $order Mage_Sales_Model_Order
     */
    public function getGrandTotal($order)
    {
        return number_format($order->getGrandTotal(), 2, '.', '');
    }

    /**
     * Decide discount
     *
     * @return float
     */
    public function getDiscountTotal($order)
    {
        return number_format($order->getDiscountAmount(), 2, '.', '');
    }

    /**
     * Decide shipping
     *
     * @return float
     */
    public function getShippingTotal($order)
    {
        return number_format($order->getShippingInclTax(), 2, '.', '');
    }

    /**
     * Decide order item price
     *
     * @return float
     */
    public function getItemPrice($orderItem)
    {
        return number_format($orderItem->getPriceInclTax(), 2, '.', '');
    }

    /**
     * Generates array of fields for rehosted form
     *
     * @return array
     */
    public function getFormFields($order)
    {
        $storeId = $order->getStoreId();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress || !is_object($shippingAddress)) {
            $shippingAddress = $billingAddress;
        }

        $formFields = array();
        $formFields['ps_store_id'] = $this->getConfigData('ps_store_id', $storeId);
        $formFields['hpp_key'] = $this->getConfigData('hpp_key', $storeId);
        $formFields['charge_total'] = $this->getGrandTotal($order);
        $formFields['cust_id'] = substr($billingAddress->getLastname(), 0, 30);

        // Transfer Card Data
        $formFields['cc_num'] = substr($order->getPayment()->getCcNumber(), 0, 30);

        // Expiration date mm/yy
        $month = $order->getPayment()->getCcExpMonth();
        $mm = (string)($month < 10 ? '0' . $month : $month);
        $yy = (string)substr($order->getPayment()->getCcExpYear(), 2, 2);
        $formFields['expMonth'] = $mm;
        $formFields['expYear'] = $yy;

        // eFraud Support
        // Split billing address street / number (Magento stores both in 1 field)
        preg_match('/([^\d]+)\s?(.+)/i', str_replace("\n", ' ', $billingAddress->getStreet(-1)), $street);
        $formFields['avs_street_number'] = isset($street[2]) ? $street[2] : '';
        $formFields['avs_street_name'] = isset($street[1]) ? $street[1] : '';
        $formFields['avs_zipcode'] = substr($billingAddress->getPostcode(), 0, 10);
        $formFields['cvd_value'] = $order->getPayment()->getCcCid();
        $ccid = $order->getPayment()->getCcCid();
        $formFields['cvd_indicator'] = !empty($ccid) ? self::CVD_PRESENT : self::CVD_NOT_PRESENT;

        // Add Item Details Full List
        // Sum must match total amount incl tax! Think about shipping,
        // discount,...
        if ($this->getConfigData('line_item_details', $storeId)) {
            $i = 0;
            foreach ($order->getAllItems() as $orderItem) {
                if ($orderItem->getParentItemId()) {
                    continue;
                }
                ++$i;
                $formFields['id' . $i] = substr($orderItem->getItemId(), 0, 10);
                $formFields['description' . $i] = substr($orderItem->getName(), 0, 15);
                $formFields['quantity' . $i] = substr(intval($orderItem->getQtyOrdered() + 0.5), 0, 4);
                $formFields['price' . $i] = $this->getItemPrice($orderItem);
                $formFields['subtotal' . $i] = round($orderItem->getQtyOrdered() * $this->getItemPrice($orderItem), 2);
            }

            // Add shipping cost
            $shipping = $this->getShippingTotal($order);
            if ($shipping != 0) {
                ++$i;
                $formFields['id' . $i] = 'SHIPPING';
                $formFields['description' . $i] = 'SHIPPING-AMOUNT';
                $formFields['quantity' . $i] = 1;
                $formFields['price' . $i] = number_format($shipping, 2, '.', '');
                $formFields['subtotal' . $i] = number_format($shipping, 2, '.', '');
            }

            // Add discount amount
            $discount = $this->getDiscountTotal($order);
            if ($discount != 0) {
                ++$i;
                $formFields['id' . $i] = 'DISCOUNT';
                $formFields['description' . $i] = 'DISCOUNT-AMOUNT';
                $formFields['quantity' . $i] = 1;
                $formFields['price' . $i] = number_format($discount, 2, '.', '');
                $formFields['subtotal' . $i] = number_format($discount, 2, '.', '');
            }
        }

        $locale = Mage::app()->getLocale()->getLocaleCode();
        $formFields['lang'] = strpos($locale, 'fr_') !== false ? 'fr-ca' : 'en-ca';
        $formFields['order_id'] = substr($order->getIncrementId() . '-' . $storeId, 0, 50);
        $formFields['email'] = substr($billingAddress->getEmail(), 0, 50);

        // Actual order number as rvar
        $formFields['rvar_increment_id'] = $order->getIncrementId();

        // Transfer billing details
        if ($this->getConfigData('billing_details', $storeId)) {
            $address = $billingAddress->getStreet();
            $formFields['bill_first_name'] = substr($billingAddress->getFirstname(), 0, 30);
            $formFields['bill_last_name'] = substr($billingAddress->getLastname(), 0, 30);
            $formFields['bill_company_name'] = substr($billingAddress->getCompany(), 0, 30);
            $formFields['bill_address_one'] = substr(implode(' ', $address), 0, 30);
            $formFields['bill_city'] = substr($billingAddress->getCity(), 0, 30);
            $formFields['bill_state_or_province'] = substr($billingAddress->getRegion(), 0, 30);
            $formFields['bill_postal_code'] = substr($billingAddress->getPostcode(), 0, 30);
            $formFields['bill_country'] = substr($billingAddress->getCountry(), 0, 30);
            $formFields['bill_phone'] = substr($billingAddress->getTelephone(), 0, 30);
            $formFields['bill_fax'] = substr($billingAddress->getFax(), 0, 30);
        }

        // Transfer shipping details
        if ($this->getConfigData('shipping_details', $storeId)) {
            $address = $shippingAddress->getStreet();
            $formFields['ship_first_name'] = substr($shippingAddress->getFirstname(), 0, 30);
            $formFields['ship_last_name'] = substr($shippingAddress->getLastname(), 0, 30);
            $formFields['ship_company_name'] = substr($shippingAddress->getCompany(), 0, 30);
            $formFields['ship_address_one'] = substr(implode(' ', $address), 0, 30);
            $formFields['ship_city'] = substr($shippingAddress->getCity(), 0, 30);
            $formFields['ship_state_or_province'] = substr($shippingAddress->getRegion(), 0, 30);
            $formFields['ship_postal_code'] = substr($shippingAddress->getPostcode(), 0, 30);
            $formFields['ship_country'] = substr($shippingAddress->getCountry(), 0, 30);
            $formFields['ship_phone'] = substr($shippingAddress->getTelephone(), 0, 30);
            $formFields['ship_fax'] = substr($shippingAddress->getFax(), 0, 30);
        }

        // Sanitize double quotes for hidden form fields
        foreach ($formFields as $key => $value) {
            if (!is_array($value)) {
                $formFields[$key] = str_replace('"', '', $value);
            }
        }

        return $formFields;
    }

    /**
     * Get Mpg classes response
     */
    public function getMpg($txnArray, $storeId)
    {
        $mpgTxn = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Transaction($txnArray);
        $mpgRequest = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Request($mpgTxn);
        $mpgHttpPost = new Appmerce_EselectPlus_Model_Api_Moneris_Mpg_Httpspost($this->getConfigData('store_id', $storeId), $this->getConfigData('api_token', $storeId), $mpgRequest);
        return $mpgHttpPost->getMpgResponse();
    }

    /**
     * Add payment transaction
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string $transactionId
     * @param string $transactionType
     * @param array $transactionDetails
     * @param array $transactionAdditionalInfo
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addTransaction(Mage_Sales_Model_Order_Payment $payment, $transactionId, $transactionType, array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false)
    {
        $message = $message . '<br />';
        $payment->setTransactionId($transactionId);
        $payment->resetTransactionAdditionalInfo();
        foreach ($transactionDetails as $key => $value) {
            $payment->setData($key, $value);
        }
        foreach ($transactionAdditionalInfo as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
        }
        $transaction = $payment->addTransaction($transactionType, null, false, $message);
        foreach ($transactionDetails as $key => $value) {
            $payment->unsetData($key);
        }
        $payment->unsLastTransId();

        /**
         * It for self using
         */
        $transaction->setMessage($message);

        return $transaction;
    }

    /**
     * Reset assigned data in payment info model
     *
     * @param Mage_Payment_Model_Info
     * @return Mage_Paygate_Model_Authorizenet
     */
    private function _clearAssignedData($payment)
    {
        $payment->setCcOwner(null)->setCcNumber(null)->setCcCid(null)->setCcExpMonth(null)->setCcExpYear(null)->setCcSsIssue(null)->setCcSsStartMonth(null)->setCcSsStartYear(null);
        return $this;
    }

    /**
     * Post with CURL and return response
     *
     * @param $postUrl The URL with ?key=value
     * @param $postData string Message
     * @return reponse XML Object
     */
    public function curlPost($url, $post = array(), $get = FALSE, $return = FALSE, $auth = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $get);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($auth) {
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $auth['user'] . ":" . $auth['pass']);
        }

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post, '', '&'));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
