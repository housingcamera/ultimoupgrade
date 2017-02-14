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

class Appmerce_EselectPlus_HostedController extends Appmerce_EselectPlus_Controller_Common
{
    // Local constants
    const STATUS_APPROVED = 'Valid-Approved';
    const STATUS_DECLINED = 'Valid-Declined';
    const STATUS_INVALID = 'Invalid';
    const STATUS_RECONFIRMED = 'Invalid-ReConfirmed';
    const STATUS_BAD = 'Invalid-Bad_Source';

    /**
     * Return payment API model
     *
     * @return Appmerce_EselectPlus_Model_Api_Hosted
     */
    protected function getApi()
    {
        return Mage::getSingleton('eselectplus/api_hosted');
    }

    /**a
     * Placement action
     */
    public function placementAction()
    {
        $this->saveCheckoutSession();

        if ($this->getApi()->getConfigData('debug_flag')) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($this->getCheckout()->getLastRealOrderId());
            if ($order->getId()) {
                $url = $this->getRequest()->getPathInfo();
                $data = print_r($this->getApi()->getFormFields($order), true);
                Mage::getModel('eselectplus/api_debug')->setDir('out')->setUrl($url)->setData('data', $data)->save();
            }
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Approved action (user)
     */
    public function approvedAction()
    {
        $this->processResponse();
    }

    /**
     * Declined action (user)
     */
    public function declinedAction()
    {
        $this->processResponse();
    }

    /**
     * Verify action (server-to-server)
     */
    public function processResponse()
    {
        $params = $this->getRequest()->getParams();
        $this->saveDebugIn($params);

        $redirectUrl = 'checkout/cart';
        if (isset($params['rvar_increment_id'])) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($params['rvar_increment_id']);
            if ($order->getId()) {
                $verifiedStatus = $this->_validateResponse($order, $params['transactionKey']);
                $note = $this->buildNote($params);

                // INTERAC Online Payments
                if (isset($params['ISSNAME'])) {

                    // Save information for order/invoice receipt (required by INTERAC)
                    $order->getPayment()->setAdditionalInformation('issname', $params['ISSNAME']);
                    $order->getPayment()->setAdditionalInformation('invoice', $params['INVOICE']);
                    $order->getPayment()->setAdditionalInformation('issconf', $params['ISSCONF']);
                    $order->save();
                }

                // Process order
                switch ($params['response_code']) {
                    case $params['response_code'] < 50 :
                        switch ($verifiedStatus) {
                            case self::STATUS_APPROVED :
                                $redirectUrl = 'checkout/onepage/success';
                                $this->getProcess()->success($order, $note, $params['bank_transaction_id'], $params['response_code'], true);
                                break;

                            case self::STATUS_DECLINED :
                            case self::STATUS_INVALID :
                            case self::STATUS_RECONFIRMED :
                            case self::STATUS_BAD :
                            default :
                                $this->getProcess()->cancel($order, $note, $params['bank_transaction_id'], $params['response_code'], true);
                        }
                        break;

                    case $params['response_code'] >= 50 :
                    default :
                        $this->getProcess()->cancel($order, $note, $params['bank_transaction_id'], $params['response_code'], true);
                }
            }
        }

        // Redirect
        $this->_redirect($redirectUrl, array('_secure' => true));
    }

    /**
     * Build transaction note
     */
    public function buildNote($params)
    {
        $note = Mage::helper('eselectplus')->__('Moneris eSELECTplus Hosted Payment Page:');
        $note .= '<br />' . Mage::helper('eselectplus')->__('Message: %s', $params['message']);
        if (!empty($params['cardholder'])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('Card Holder Name: %s', $params['cardholder']);
        }
        if (!empty($params['card'])) {
            $cardTypes = $this->getApi()->getConfig()->getCardTypes();
            $note .= '<br />' . Mage::helper('eselectplus')->__('Card Type: %s', $cardTypes[$params['card']]);
        }
        if (!empty($params['f4l4'])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('Card Number: %s', $params['f4l4']);
        }

        // INTERAC Online Payments
        if (isset($params['ISSNAME'])) {
            $note .= '<br />' . Mage::helper('eselectplus')->__('INTERAC Card Issuer: %s', $params['ISSNAME']);
            $note .= '<br />' . Mage::helper('eselectplus')->__('INTERAC Invoice Nr.: %s', $params['INVOICE']);
            $note .= '<br />' . Mage::helper('eselectplus')->__('INTERAC Confirmation Nr.: %s', $params['ISSCONF']);
        }

        return $note;
    }

    /**
     * Validate receipt
     */
    public function _validateResponse($order, $transactionKey)
    {
        $status = false;

        // Only verify if configured
        if ($this->getApi()->getConfigData('verification_flag') == false) {
            return self::STATUS_APPROVED;
        }

        // Does not work in Test Mode
        if ($this->getApi()->getConfigData('test_flag') == true) {
            return self::STATUS_APPROVED;
        }

        // CURL POST
        $url = $this->getApi()->getGatewayUrl('verification');
        $post = $this->getApi()->getVerificationFields($order, $transactionKey);
        $request = $this->getApi()->curlPost($url, $post, FALSE, TRUE);
        $response = new SimpleXMLElement($request);
        return $response->status;
    }

}
