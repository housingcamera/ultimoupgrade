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

class Appmerce_EselectPlus_Model_Api extends Mage_Payment_Model_Method_Abstract
{
    // Magento features
    protected $_isGateway = false;
    protected $_canOrder = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = true;
    protected $_canFetchTransactionInfo = false;
    protected $_canReviewPayment = false;
    protected $_canCreateBillingAgreement = false;
    protected $_canManageRecurringProfiles = false;

    // Restrictions
    protected $_allowCurrencyCode = array();

    public function __construct()
    {
        $this->_config = Mage::getSingleton('eselectplus/config');
        return $this;
    }

    /**
     * Return configuration instance
     *
     * @return Appmerce_EselectPlus_Model_Config
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
        parent::validate();
        $currency_code = $this->getCurrencyCode();
        if (!empty($this->_allowCurrencyCode) && !in_array($currency_code, $this->_allowCurrencyCode)) {
            $errorMessage = Mage::helper('eselectplus')->__('Selected currency (%s) is not compatible with this payment method.', $currency_code);
            Mage::throwException($errorMessage);
        }
        return $this;
    }

    /**
     * Get redirect URL after placing order
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getConfig()->getHostedUrl('placement');
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

        // Moneris requires to set a valid REFERER URL
        curl_setopt($ch, CURLOPT_REFERER, $this->getConfig()->getHostedUrl('placement', Mage::app()->getStore()->getStoreId()));

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
