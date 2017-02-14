<?php
class MageCoders_PaypalMulticurrency_Helper_Data extends Mage_Core_Helper_Data{
	
	
	public function getConfig($key){
		if($key==''){ return; } 
		
		if(Mage::app()->getStore()->getId()==Mage_Core_Model_App::ADMIN_STORE_ID){

			$params = Mage::app()->getRequest()->getParams();

			if(empty($params['store']) && isset($params['website'])){
				return Mage::app()->getWebsite($params['website'])
								->getConfig('paypalmulticurrency/settings/'.$key); 	
			}else{
				return Mage::app()->getStore($params['store'])
						->getConfig('paypalmulticurrency/settings/'.$key);
			}
	
		}else{
			return Mage::getStoreConfig('paypalmulticurrency/settings/'.$key);
		}

	}
	
	
	public function isActive(){
		
		return $this->getConfig('active');
	}
	
	
	
}