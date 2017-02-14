<?php

class Stamped_Core_Model_Order_Observer
{
	public function __construct()
	{
		
	}
	
	public function add_review_request($observer)
	{
		try {

			$event = $observer->getEvent();
			$order = $event->getOrder();
			$store_id = $order->getStoreId();
			$orderStatuses = Mage::getStoreConfig('stamped/stamped_settings_group/order_status_trigger', $order->getStore());
			if ($orderStatuses == null) {
				$orderStatuses = array('complete');
			} else {
				$orderStatuses = array_map('strtolower', explode(',', $orderStatuses));
			}

            if (!Stamped_Core_ApiClient::isConfigured($store_id))
            {
                return $this;
            }
			
			if (!in_array($order->getStatus(), $orderStatuses)) {
				return $this;
			}
			
			$data = array();
			if (!$order->getCustomerIsGuest()) {
				$data["user_reference"] = $order->getCustomerId();
			}
			
						// Get the id of the orders shipping address
						$shippingId = $order->getShippingAddress()->getId();

						// Get shipping address data using the id
						$address = Mage::getModel('sales/order_address')->load($shippingId);

                        $data = array();
                        if (!$order->getCustomerIsGuest()) {
                            $data["userReference"] = $order->getCustomerEmail();
                        }

                        $data["customerId"] = $order->getCustomerId();
                        $data["email"] = $order->getCustomerEmail();
                        $data["firstName"] = $order->getCustomerFirstname();
                        $data["lastName"] = $order->getCustomerLastname();
                        $data["location"] = $address->getCountry();
                        $data['orderNumber'] = $order->getIncrementId();
                        $data['orderId'] = $order->getIncrementId();
                        $data['orderCurrencyISO'] = $order->getOrderCurrency()->getCode();
                        $data["orderTotalPrice"] = $order->getGrandTotal();
                        $data["orderSource"] = 'magento';
                        $data["orderDate"] = $order->getCreatedAtDate()->toString('yyyy-MM-dd HH:mm:ss');
                        $data['itemsList'] = Stamped_Core_ApiClient::getOrderProductsData($order);
			$data['platform'] = 'magento';

			Stamped_Core_ApiClient::createReviewRequest($data, $store_id);

			return $this;	

		} catch(Exception $e) {
			Mage::log('Failed to send mail after purchase. Error: '.$e);
		}
	}
}