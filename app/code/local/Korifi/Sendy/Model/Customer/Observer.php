<?php
class Korifi_Sendy_Model_Customer_Observer
{
    public function check_subscription_status($observer)
    {   
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
		$apiKey = trim(Mage::getStoreConfig('sendy/setting/api_key'));
		$listID = trim(Mage::getStoreConfig('sendy/setting/list_id'));

		$store = Mage::app()->getStore();
		if ($store->getCode() == 'admin') {
			if($customer->getWebsiteId() && $customer->getStoreId()){
				$options = array(
					'stores' => $customer->getStoreId(), 
					'websites' => $customer->getWebsiteId(), 
					'default' => 0
				);
				$resource = Mage::getSingleton('core/resource');
				$db 			= $resource->getConnection('core_read');
				$config	= array();
				
				foreach($options as $scope => $scopeId) {
					$select = $db->select()
						->from($resource->getTableName('core/config_data'), array('path', 'value'))
						->where('path LIKE ?', 'sendy%')
						->where('scope=?', $scope)
						->where('scope_id=?', $scopeId);
					if ($results = $db->fetchAll($select)) {
						foreach($results as $result) {
							if (!isset($config[$result['path']])) {
								$config[$result['path']] = $result['value'];
							}
						}
					}
				}
				$listID = $config['sendy/setting/list_id'];	
			}
		}
		else {
		}
		$name = $customer->getFirstname() . " " . $customer->getLastname();
        $newEmail = $customer->getEmail();
        $subscribed = $customer->getIsSubscribed();
        $oldEmail = Mage::getModel('customer/customer')->load($customer->getId())->getEmail();
        if($subscribed === NULL)
        {
            $subscribed = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer)->isSubscribed();
        }
        if($apiKey and $listID)
        {
            try {
				$sendy = new Korifi_Sendy_Helper_SysApi($listID); 
            } catch(Exception $e) {
                return;
            }

            if($subscribed)
            {
                /* If the customer:
                   
                   1) Already exists (i.e. has an old email address)
                   2) Has changed their email address
                    
                   unsubscribe their old address. */
                if ($oldEmail and $newEmail != $oldEmail)
                {
                    try {
						$results = $sendy->unsubscribe(array(
												'email' => $oldEmail
												));			
                    } catch(Exception $e) {
                        return;
                    }
                }
                try {
					$results = $sendy->subscribe(array(
											'name'=> $name,
											'email' => $newEmail
											));		
                } catch(Exception $e) {
                    return;
                }
            }
            else
            {
				if(empty($oldEmail)){
					try {
						$results = $sendy->subscribe(array(
												'name'=> $name,
												'email' => $newEmail
												));	
					} catch(Exception $e) {
						return;
					}
				}else{
					try {
						$results = $sendy->unsubscribe(array(
												'email' => $oldEmail
												));	
					} catch(Exception $e) {
						return;
					}
				}
            }
        }
    }

    public function customer_deleted($observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
		$apiKey = trim(Mage::getStoreConfig('sendy/setting/api_key'));
		$listID = trim(Mage::getStoreConfig('sendy/setting/list_id'));
        $email = $customer->getEmail();
		$store = Mage::app()->getStore();
		if ($store->getCode() == 'admin') {
			if($customer->getWebsiteId() && $customer->getStoreId()){
				$options = array(
					'stores' => $customer->getStoreId(), 
					'websites' => $customer->getWebsiteId(), 
					'default' => 0
				);
				$resource = Mage::getSingleton('core/resource');
				$db 			= $resource->getConnection('core_read');
				$config	= array();
				
				foreach($options as $scope => $scopeId) {
					$select = $db->select()
						->from($resource->getTableName('core/config_data'), array('path', 'value'))
						->where('path LIKE ?', 'sendy%')
						->where('scope=?', $scope)
						->where('scope_id=?', $scopeId);
					if ($results = $db->fetchAll($select)) {
						foreach($results as $result) {
							if (!isset($config[$result['path']])) {
								$config[$result['path']] = $result['value'];
							}
						}
					}
				}
				$listID = $config['sendy/setting/list_id'];	
			}
		}
		else {
		}
        if($apiKey and $listID)
        {
            try {
				$sendy = new Korifi_Sendy_Helper_SysApi($listID);
				
				$results = $sendy->unsubscribe(array(
										'email' => $email
										));
            } catch(Exception $e) {
                return;
            }
        }
    }
}
