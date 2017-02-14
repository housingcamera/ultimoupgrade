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

class Appmerce_EselectPlus_Model_Config extends Mage_Payment_Model_Config
{
    // Default order statuses
    const DEFAULT_STATUS_NEW = 'pending';
    const DEFAULT_STATUS_PENDING_PAYMENT = 'pending_payment';
    const DEFAULT_STATUS_PROCESSING = 'processing';

    // Other
    const HOSTED_CONTROLLER_PATH = 'eselectplus/hosted/';

    /**
     * Get store configuration
     */
    public function getPaymentConfigData($method, $key, $storeId = null)
    {
        return Mage::getStoreConfig('payment/' . $method . '/' . $key, $storeId);
    }

    /**
     * Return gateways
     */
    public function getGateways()
    {
        return array(
            'moneris' => array(
                'test' => 'https://esqa.moneris.com/HPPDP/index.php',
                'live' => 'https://www3.moneris.com/HPPDP/index.php',
            ),
            'verification' => array(
                'test' => 'https://esqa.moneris.com/HPPDP/verifyTxn.php',
                'live' => 'https://www3.moneris.com/HPPDP/verifyTxn.php'
            ),
        );
    }

    /**
     * Card types
     */
    public function getCardTypes()
    {
        return array(
            'P' => 'INTERAC Online',
            'M' => 'Mastercard',
            'V' => 'Visa',
            'AX' => 'American Express',
            'DC' => 'Diners Card',
            'SE' => 'Sears',
            'NO' => 'Novus / Discover'
        );
    }

    /**
     * Return order description
     *
     * @param Mage_Sales_Model_Order
     * @return string
     */
    public function getOrderDescription($order)
    {
        return Mage::helper('eselectplus')->__('Order %s', $order->getIncrementId());
    }

    /**
     * Functions for default new/pending/processing statuses
     */
    public function getOrderStatus($code)
    {
        $status = $this->getPaymentConfigData($code, 'order_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PENDING;
        }
        return $status;
    }

    public function getPendingStatus($code)
    {
        $status = $this->getPaymentConfigData($code, 'pending_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PENDING_PAYMENT;
        }
        return $status;
    }

    public function getProcessingStatus($code)
    {
        $status = $this->getPaymentConfigData($code, 'processing_status');
        if (empty($status)) {
            $status = self::DEFAULT_STATUS_PROCESSING;
        }
        return $status;
    }

    /**
     * Return URLs
     */
    public function getHostedUrl($key, $storeId = null, $noSid = false)
    {
        return Mage::getUrl(self::HOSTED_CONTROLLER_PATH . $key, array(
            '_store' => $storeId,
            '_secure' => true
        ));
    }

}
