<?php

/**
 * StitchLabs_ChannelIntegration extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category       StitchLabs
 * @package        StitchLabs_ChannelIntegration
 * @copyright      Copyright (c) 2014
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
class StitchLabs_ChannelIntegration_Model_Order_Api
    extends Mage_Sales_Model_Order_Api {

    /**
     * Retrieve list of orders. Filtration could be applied
     *
     * @param null|object|array $filters
     * @return array
     */
    public function items($filters = null)
    {
        $orders = [];

        //TODO: add full name logic
        $billingAliasName  = 'billing_o_a';
        $shippingAliasName = 'shipping_o_a';

        /** @var $orderCollection Mage_Sales_Model_Mysql4_Order_Collection */
        $orderCollection        = Mage::getModel("sales/order")->getCollection();
        $billingFirstnameField  = "$billingAliasName.firstname";
        $billingLastnameField   = "$billingAliasName.lastname";
        $shippingFirstnameField = "$shippingAliasName.firstname";
        $shippingLastnameField  = "$shippingAliasName.lastname";
        $orderCollection->addAttributeToSelect('*')
            ->addAddressFields()
            ->addExpressionFieldToSelect('billing_firstname', "{{billing_firstname}}",
                ['billing_firstname' => $billingFirstnameField])
            ->addExpressionFieldToSelect('billing_lastname', "{{billing_lastname}}",
                ['billing_lastname' => $billingLastnameField])
            ->addExpressionFieldToSelect('shipping_firstname', "{{shipping_firstname}}",
                ['shipping_firstname' => $shippingFirstnameField])
            ->addExpressionFieldToSelect('shipping_lastname', "{{shipping_lastname}}",
                ['shipping_lastname' => $shippingLastnameField])
            ->addExpressionFieldToSelect('billing_name', "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})",
                ['billing_firstname' => $billingFirstnameField, 'billing_lastname' => $billingLastnameField])
            ->addExpressionFieldToSelect('shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})',
                ['shipping_firstname' => $shippingFirstnameField, 'shipping_lastname' => $shippingLastnameField]
            );

        /** @var $apiHelper Mage_Api_Helper_Data */
        $apiHelper = Mage::helper('api');
        $filters   = $apiHelper->parseFilters($filters, $this->_attributesMap['order']);
        try
        {
            $page      = 1;
            $page_size = 50;

            foreach ($filters as $field => $value)
            {
                if ($field == 'page')
                {
                    $page = $value;
                }
                elseif ($field == 'size')
                {
                    $page_size = $value;
                }
                else
                {
                    $orderCollection->addFieldToFilter($field, $value);
                }
            }

            $orderCollection->setPage($page, $page_size);
        }
        catch (Mage_Core_Exception $e)
        {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        foreach ($orderCollection as $order)
        {
            if ($order->getGiftMessageId() > 0)
            {
                $order->setGiftMessage(
                    Mage::getSingleton('giftmessage/message')->load($order->getGiftMessageId())->getMessage()
                );
            }

            $result = $this->_getAttributes($order, 'order');

            $result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
            $result['billing_address']  = $this->_getAttributes($order->getBillingAddress(), 'order_address');
            $result['items']            = [];

            foreach ($order->getAllItems() as $item)
            {
                if ($item->getGiftMessageId() > 0)
                {
                    $item->setGiftMessage(
                        Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
                    );
                }

                $result['items'][] = $this->_getAttributes($item, 'order_item');
            }

            /**
             * Add invoices
             */
            $result['invoices']  = [];
            $invoice_collection = $order->getInvoiceCollection();

            foreach ($invoice_collection as $invoice)
            {
                $temp_invoice = $this->_getAttributes($invoice, 'invoice');

                $temp_invoice['invoice_id'] = $invoice->getId();

                $temp_invoice['items'] = [];
                foreach ($invoice->getAllItems() as $item)
                {
                    $temp_invoice['items'][] = $this->_getAttributes($item, 'invoice_item');
                }

                $temp_invoice['comments'] = [];
                foreach ($invoice->getCommentsCollection() as $comment)
                {
                    $temp_invoice['comments'][] = $this->_getAttributes($comment, 'invoice_comment');
                }

                $result['invoices'][] = $temp_invoice;
            }

            /**
             * Add payment
             */
            $result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');

            /**
             * Add shipments
             */
            $result['shipments'] = [];
            $shipment_collection = $order->getShipmentsCollection();

            foreach ($shipment_collection as $shipment)
            {
                $temp_shipment = $this->_getAttributes($shipment, 'shipment');

                $temp_shipment['shipment_id'] = $shipment->getId();

                $temp_shipment['items'] = [];
                foreach ($shipment->getAllItems() as $item)
                {
                    $temp_shipment['items'][] = $this->_getAttributes($item, 'shipment_item');
                }

                $temp_shipment['tracks'] = [];
                foreach ($shipment->getAllTracks() as $track)
                {
                    $temp_shipment['tracks'][] = $this->_getAttributes($track, 'shipment_track');
                }

                $temp_shipment['comments'] = [];
                foreach ($shipment->getCommentsCollection() as $comment)
                {
                    $temp_shipment['comments'][] = $this->_getAttributes($comment, 'shipment_comment');
                }

                $result['shipments'][] = $temp_shipment;
            }

            /**
             * Add credit memos
             */
            $result['creditmemos']  = [];
            $creditmemos_collection = $order->getCreditmemosCollection();

            foreach ($creditmemos_collection as $creditmemo)
            {
                $temp_creditmemo = $this->_getAttributes($creditmemo, 'creditmemo');

                $temp_creditmemo['creditmemo_id'] = $creditmemo->getId();

                $temp_creditmemo['items'] = [];
                foreach ($creditmemo->getAllItems() as $item)
                {
                    $temp_creditmemo['items'][] = $this->_getAttributes($item, 'creditmemo_item');
                }


                /**
                 * Add order comments
                 */
                $temp_creditmemo['comments'] = [];

                foreach ($creditmemo->getCommentsCollection() as $comment)
                {
                    $temp_creditmemo['comments'][] = $this->_getAttributes($comment, 'creditmemo_comment');
                }


                $result['creditmemos'][] = $temp_creditmemo;
            }

            $result['status_history'] = [];

            foreach ($order->getAllStatusHistory() as $history)
            {
                $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
            }

            $orders[] = $result;
        }

        return $orders;
    }
}
