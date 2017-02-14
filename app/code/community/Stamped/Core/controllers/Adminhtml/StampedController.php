<?php


class stamped_core_Adminhtml_StampedController extends Mage_Adminhtml_Controller_Action
{
    public function importHistoryOrdersAction() {
        try {
            $current_store;
            $page = 0;
            $now = time();
            $last = $now - (60*60*24*180); // 180 days ago
            $from = date("Y-m-d", $last);

            $store_code = Mage::app()->getRequest()->getParam('store');

            foreach (Mage::app()->getStores() as $store) {
                if ($store->getCode() == $store_code) {
                    global $current_store;
                    $current_store = $store;
                    break;
                }
            }

            $store_id = $current_store->getId();

            if (Stamped_Core_ApiClient::isConfigured($current_store) == false)
            {
                Mage::app()->getResponse()->setBody('Please ensure you have configured the API Public Key and Private Key in Settings.');
                return;   
            }

            $salesOrder=Mage::getModel("sales/order");
            $orderStatuses = Mage::getStoreConfig('stamped/stamped_settings_group/order_status_trigger', $current_store);
            if ($orderStatuses == null) {
                $orderStatuses = array('complete');
            } else {
                $orderStatuses = array_map('strtolower', (explode(',', $orderStatuses)));
            }
			
            $salesCollection = $salesOrder->getCollection()
                    ->addFieldToFilter('status', $orderStatuses)
                    ->addFieldToFilter('store_id', $store_id)
                    ->addAttributeToFilter('created_at', array('gteq' =>$from))
                    ->addAttributeToSort('created_at', 'DESC')
                    ->setPageSize(200);

            $pages = $salesCollection->getLastPageNumber();
			
			Mage::log(($pages));

            do {
                try {
                    $page++;
                    $salesCollection->setCurPage($page)->load();
                 
                    $orders = array();

                    foreach($salesCollection as $order)
                    {
                        $order_data = array();

						//Mage::log(('start loop '. $order->getIncrementId()));
						// Get the id of the orders shipping address
						$shippingAddress = $order->getShippingAddress();

						// Get shipping address data using the id
						if(!empty($shippingAddress)) {
							$address = Mage::getModel('sales/order_address')->load($shippingAddress->getId());
							
							if (!empty($address)){
								$order_data["location"] = $address->getCountry();
							}
						}

                        //if (!$order->getCustomerIsGuest()) {
                        //    $order_data["userReference"] = $order->getCustomerEmail();
                        //}

                        $order_data["customerId"] = $order->getCustomerId();
                        $order_data["email"] = $order->getCustomerEmail();
                        $order_data["firstName"] = $order->getCustomerFirstname();
                        $order_data["lastName"] = $order->getCustomerLastname();
                        $order_data['orderNumber'] = $order->getIncrementId();
                        $order_data['orderId'] = $order->getIncrementId();
                        $order_data['orderCurrencyISO'] = $order->getOrderCurrency()->getCode();
                        $order_data["orderTotalPrice"] = $order->getGrandTotal();
                        $order_data["orderSource"] = 'magento';
                        $order_data["orderDate"] = $order->getCreatedAtDate()->toString('yyyy-MM-dd HH:mm:ss');
                        $order_data['itemsList'] = Stamped_Core_ApiClient::getOrderProductsData($order);
                        $order_data['apiUrl'] = Stamped_Core_ApiClient::getApiUrlAuth($current_store)."/survey/reviews/bulk";

                        $orders[] = $order_data;
						
						//Mage::log(('end loop '. $order->getIncrementId()));
                    }
					
					//Mage::log(('number of orders '.count($orders)));

                    if (count($orders) > 0) 
                    {
						//Mage::log(('importing '.count($orders)));
						$result = Stamped_Core_ApiClient::createReviewRequestBulk($orders, $current_store);
						
						//Mage::log(($result));
                    }
                } catch (Exception $e) {
                    Mage::log('Failed to export past orders. Error: '.$e);
					Mage::app()->getResponse()->setBody($e->getMessage());

					return;
                }

                $salesCollection->clear();

            } while ($page <= (3000 / 200) && $page < $pages);

        } catch(Exception $e) {
            Mage::log('Failed to import history orders. Error: '.$e);
        }

        Mage::app()->getResponse()->setBody(1);
    } 
}