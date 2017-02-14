<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Amazon_Orders_Refund_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    // M2ePro_TRANSLATIONS
    // Amazon Order was not refunded. Reason: %msg%
    // Amazon Order was refunded.

    private $orders = NULL;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Processing_Request $processingRequest
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        foreach ($this->getOrders() as $order) {
            $order->deleteObjectLocks('refund_order', $processingRequest->getHash());
        }
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        foreach ($this->getOrders() as $order) {
            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
            $order->addErrorLog('Amazon Order was not refunded. Reason: %msg%', array('msg' => $message));
        }
    }

    //########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        /** @var $orders Ess_M2ePro_Model_Order[] */
        $orders = $this->getOrders();

        // Check global messages
        // ---------------------------------------
        $globalMessages = $this->messages;
        if (isset($response['messages']['0-id']) && is_array($response['messages']['0-id'])) {
            $globalMessages = array_merge($globalMessages,$response['messages']['0-id']);
        }

        if (count($globalMessages) > 0) {
            foreach ($orders as $order) {
                foreach ($globalMessages as $message) {
                    $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                    $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
                    $order->addErrorLog('Amazon Order was not refunded. Reason: %msg%', array('msg' => $text));
                }
            }

            return;
        }
        // ---------------------------------------

        // Check separate messages
        // ---------------------------------------
        $failedOrdersIds = array();

        foreach ($response['messages'] as $changeId => $messages) {
            $changeId = (int)$changeId;

            if ($changeId <= 0) {
                continue;
            }

            $orderId = $this->getOrderIdByChangeId($changeId);

            if (!is_numeric($orderId)) {
                continue;
            }

            $failedOrdersIds[] = $orderId;

            foreach ($messages as $message) {
                $text = $message[Ess_M2ePro_Model_Connector_Protocol::MESSAGE_TEXT_KEY];

                $orders[$orderId]->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
                $orders[$orderId]->addErrorLog('Amazon Order was not refunded. Reason: %msg%', array('msg' => $text));
            }
        }
        // ---------------------------------------

        // ---------------------------------------
        foreach ($this->params as $changeId => $requestData) {
            $orderId = $this->getOrderIdByChangeId($changeId);

            if (in_array($orderId, $failedOrdersIds)) {
                continue;
            }

            if (!is_numeric($orderId)) {
                continue;
            }

            $orders[$orderId]->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
            $orders[$orderId]->addSuccessLog('Amazon Order was refunded.');
        }
        // ---------------------------------------
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     * @return Ess_M2ePro_Model_Order[]
     */
    private function getOrders()
    {
        if (!is_null($this->orders)) {
            return $this->orders;
        }

        $ordersIds = array();

        foreach ($this->params as $update) {
            if (!isset($update['order_id'])) {
                throw new Ess_M2ePro_Model_Exception_Logic('Order ID is not defined.');
            }

            $ordersIds[] = (int)$update['order_id'];
        }

        $this->orders = Mage::getModel('M2ePro/Order')
            ->getCollection()
            ->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Amazon::NICK)
            ->addFieldToFilter('id', array('in' => $ordersIds))
            ->getItems();

        return $this->orders;
    }

    private function getOrderIdByChangeId($changeId)
    {
        foreach ($this->params as $requestChangeId => $requestData) {
            if ($changeId == $requestChangeId && isset($requestData['order_id'])) {
                return $requestData['order_id'];
            }
        }

        return NULL;
    }

    //########################################
}