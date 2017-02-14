<?php
include "Mage/Newsletter/controllers/SubscriberController.php";
class Korifi_Sendy_HookController extends Mage_Newsletter_SubscriberController {

    public function newAction() {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session   = Mage::getSingleton('core/session');
            $email     = (string)$this->getRequest()->getPost('email');
            $apiKey = trim(Mage::getStoreConfig('sendy/setting/api_key'));
            $listID = trim(Mage::getStoreConfig('sendy/setting/list_id'));
        
            if($apiKey && $listID) {
                try {
					$sendy = new Korifi_Sendy_Helper_SysApi($listID);
                } catch(Exception $e) {
                    $session->addException($e, $this->__('There was a problem with the subscription'));
                    $this->_redirectReferer();
                }

                $customerHelper = Mage::helper('customer');
                if($customerHelper->isLoggedIn()) {
                    $customer = $customerHelper->getCustomer();
                    $name = $customer->getFirstname() . " " . $customer->getLastname();
                    try {
						$results = $sendy->subscribe(array(
												'name'=> $name,
												'email' => $email
												));										
                    } catch(Exception $e) {
                        $session->addException($e, $this->__('There was a problem with the subscription'));
                        $this->_redirectReferer();
                    }
                } else {
                    // otherwise if nobody's logged in, ignore the custom
                    // attributes and just set the name to '(Guest)'
                    try {
						$results = $sendy->subscribe(array(
												'name'=> "(Guest)",
												'email' => $email
												));												
                    } catch (Exception $e) {
                        $session->addException($e, $this->__('There was a problem with the subscription'));
                        $this->_redirectReferer();
                    }
                }
            } else {
            }
        }

        parent::newAction();
    }

    /**
     * Unsubscribe newsletter
     */
    public function unsubscribeAction()
    {
        $id    = (int) $this->getRequest()->getParam('id');
        $code  = (string) $this->getRequest()->getParam('code');

        if ($id && $code) {
            $session = Mage::getSingleton('core/session');
            try {
                Mage::getModel('newsletter/subscriber')->load($id)
                    ->setCheckCode($code)
                    ->unsubscribe();
                $session->addSuccess($this->__('You have been unsubscribed.'));

				$apiKey = trim(Mage::getStoreConfig('sendy/setting/api_key'));
				$listID = trim(Mage::getStoreConfig('sendy/setting/list_id'));		
				
				try {
					$sendy = new Korifi_Sendy_Helper_SysApi($listID); 
					//echo $apiKey.'**'.$listID; die('adu');
				} catch(Exception $e) {
					$session->addException($e, $this->__('There was a problem with the subscription'));
					$this->_redirectReferer();
				}
				$data = Mage::getModel('newsletter/subscriber')->load($id);
				if($email=$data->getSubscriberEmail()){					
					try {
						$results = $sendy->unsubscribe(array(
												'email' => $email
												));					
					} catch (Exception $e) {
						print"<pre>"; print_r($e->getMessage());
					}					
				}
            }
            catch (Mage_Core_Exception $e) {
                $session->addException($e, $e->getMessage());
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the un-subscription.'));
            }
        }
        $this->_redirectReferer();
		parent::unsubscribeAction();
    }	
}

?>
