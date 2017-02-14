<?php
class Korifi_Sendy_AdminhookController extends Mage_Adminhtml_Controller_Action {

    public function massUnsubscribeAction() {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('newsletter')->__('Please select subscriber(s)'));
             $this->_redirect('*/*/index');
        }
        else {
            try {
				$apiKey = trim(Mage::getStoreConfig('sendy/setting/api_key'));
				$listID = trim(Mage::getStoreConfig('sendy/setting/list_id'));

                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
                    $subscriber->unsubscribe();					
					$email = $subscriber->getEmail();

					$store = Mage::app()->getStore();
					if ($store->getCode() == 'admin') {
						if($subscriber->getStoreId()){
							$store = Mage::getModel('core/store')->load($subscriber->getStoreId(), 'store_id');
							if ($store->getId()) {
								$options = array(
									'stores' => $store->getId(), 
									'websites' => $store->getWebsiteId(), 
									'default' => 0
								);
							}
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
					try {
						$sendy = new Korifi_Sendy_Helper_SysApi($listID);
					} catch(Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError(
							Mage::helper('adminhtml')->__('There was a problem with the unsubscription')
						);							
						$this->_redirectReferer();
					}
                    try {
						$results = $sendy->unsubscribe(array(
												'email' => $email
												));					
                    } catch (Exception $e) {
						Mage::getSingleton('adminhtml/session')->addError(
							Mage::helper('adminhtml')->__('There was a problem with the unsubscription')
						);							
						$this->_redirectReferer();					
                    }
                }
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__('Total of %d record(s) were unsubscribed and synched to Sendy', count($subscribersIds))
				);		
				$this->_redirectReferer();	
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
		$this->_redirectReferer();
    }
    public function indexAction()
    {
        $this->_title($this->__('Newsletter'))->_title($this->__('Newsletter Subscribers'));

        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->loadLayout();

        $this->_setActiveMenu('newsletter/subscriber');

        $this->_addBreadcrumb(Mage::helper('newsletter')->__('Newsletter'), Mage::helper('newsletter')->__('Newsletter'));
        $this->_addBreadcrumb(Mage::helper('newsletter')->__('Subscribers'), Mage::helper('newsletter')->__('Subscribers'));

        $this->_addContent(
            $this->getLayout()->createBlock('adminhtml/newsletter_subscriber','subscriber')
        );

        $this->renderLayout();
    }	
	public function migrationsubscriberstosendyAction(){
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('newsletter')->__('Please select subscriber(s)'));
             $this->_redirect('*/*/index');
        }
        else {
            try {
				$apiKey = trim(Mage::getStoreConfig('sendy/setting/api_key'));
				$listID = trim(Mage::getStoreConfig('sendy/setting/list_id'));
                try {
                    $sendy = new Korifi_Sendy_Helper_SysApi($listID);
                } catch(Exception $e) {
                    Mage::log("Korifi_Sendy: Error connecting to Sendy server: ".$e->getMessage());
                    $session->addException($e, $this->__('There was a problem with the subscription'));
                    $this->_redirectReferer();
                }

                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = Mage::getModel('newsletter/subscriber')->load($subscriberId);
					if($subscriber->getCustomerId() != 0){
						$customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
						$name = $customer->getFirstname() . " " . $customer->getLastname();					
					}else{
						$name = "(Guest)";
					}
					$email = $subscriber->getEmail();
                    Mage::log("Korifi_Sendy: Subscribing: $email");
                    try {
						$results = $sendy->subscribe(array(
												'name'=> $name,
												'email' => $email
												));				
                    } catch (Exception $e) {
                        Mage::log("Korifi_Sendy: Error in Sendy API call: ".$e->getMessage());
                    }
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were synched to Sendy', count($subscribersIds))
                );
				$this->_redirectReferer();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
	}
}
?>