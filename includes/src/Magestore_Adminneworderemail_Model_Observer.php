<?php

class Magestore_Adminneworderemail_Model_Observer
{
	const XML_PATH_ADMIN_EMAIL_TEMPLATE = 'sales_email/order/admin_email_template';
	
	public function onepageCheckoutSuccess($observer){
		// $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$orderId = $observer->getData('order')->getId();
                $this->sendAdminOrderNotification($orderId);
	}
	
	public function sendAdminOrderNotification($orderId) {
		$translate = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		
		$mailTemplate = Mage::getModel('core/email_template');		
		$template = Mage::getStoreConfig(self::XML_PATH_ADMIN_EMAIL_TEMPLATE, $this->getStoreId());
		$adminEmail = '';
		$adminName = 'Mozaik Admin';
		$_order = Mage::getModel('sales/order')->load($orderId);
		
		$paymentBlock = Mage::helper('payment')->getInfoBlock($_order->getPayment())->setIsSecureMode(true);
		$paymentBlock->getMethod()->setStore($_order->getStore()->getId());
		
		$adminEmailString = Mage::getStoreConfig('sales_email/order/admin_email_notify');
		$adminEmailArray = explode(',', $adminEmailString);
		$sender = array('name' => $_order->getCustomerFirstname() ." " .  $_order->getCustomerLastname(), 'email' => $_order->getCustomerEmail()); //

		foreach ($adminEmailArray as $adminEmail){
                       

			/* $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                                    ->sendTransactional($template,
                                                    $sender,
                                                    $adminName,
                                                    $adminEmail,
                                                    array(
                                                        'order'	=> $_order,
                                                        'payment_html'	=> $paymentBlock->toHtml(),
                                                    )
				); */
                        $mailer = Mage::getModel('core/email_template_mailer');
                        $emailInfo = Mage::getModel('core/email_info');
                        $emailInfo->addTo($adminEmail,$adminName);
                        $mailer->addEmailInfo($emailInfo);
                        $mailer->setSender($sender);
                        $mailer->setStoreId($this->getStoreId());
                        $mailer->setTemplateId($template);
                        $mailer->setTemplateParams(array(
                                'order'	=> $_order,
                                'payment_html'	=> $paymentBlock->toHtml(),
                            )
                        );
                        $mailer->send();
                }
		$translate->setTranslateInline(true);
	}
	
	public function getStoreId(){
		return Mage::app()->getStore()->getId();
	}
}