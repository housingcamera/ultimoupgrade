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

class Appmerce_EselectPlus_Controller_Common extends Mage_Core_Controller_Front_Action
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
     * Return order process instance
     *
     * @return Appmerce_EselectPlus_Model_Process
     */
    public function getProcess()
    {
        return Mage::getSingleton('eselectplus/process');
    }

    /**
     * Return order instance by LastOrderId
     *
     * @return  Mage_Sales_Model_Order object
     */
    protected function getLastRealOrder()
    {
        $order = Mage::getModel('sales/order');
        $order->load($this->getCheckout()->getLastRealOrderId(), 'increment_id');
        return $order;
    }

    /**
     * Debug IN
     */
    public function saveDebugIn($in)
    {
        if ($this->getApi()->getConfigData('debug_flag')) {
            $url = $this->getRequest()->getPathInfo();
            $data = print_r($in, true);
            Mage::getModel('eselectplus/api_debug')->setDir('in')->setUrl($url)->setData('data', $data)->save();
        }
    }

    /**
     * Save checkout session
     */
    public function saveCheckoutSession()
    {
        $this->getCheckout()->seteSELECTplusQuoteId($this->getCheckout()->getLastSuccessQuoteId());
        $this->getCheckout()->seteSELECTplusOrderId($this->getCheckout()->getLastOrderId());
    }

}
