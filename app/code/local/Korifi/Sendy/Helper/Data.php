<?php
class Korifi_Sendy_Helper_Data extends Mage_Core_Helper_Abstract {
	const XML_PATH_SENDY_ACTIVE = 'sendy/setting/active';
	public function checkStatus(){
        if (! Mage::getStoreConfig(self::XML_PATH_SENDY_ACTIVE)) {
            return ;
        }	
	}
}