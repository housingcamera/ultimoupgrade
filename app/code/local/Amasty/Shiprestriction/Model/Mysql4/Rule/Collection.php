<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * @author Amasty
 */ 
class Amasty_Shiprestriction_Model_Mysql4_Rule_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('amshiprestriction/rule');
    }
    
    public function addAddressFilter($address)
    {
        $this->addFieldToFilter('is_active', 1);
        
        $storeId = $address->getQuote()->getStoreId();
        $storeId = intVal($storeId);
        $this->getSelect()->where('stores="" OR stores LIKE "%,'.$storeId.',%"');
        
        $groupId = 0;
        if ($address->getQuote()->getCustomerId()){
            $groupId = $address->getQuote()->getCustomer()->getGroupId();    
        }
        $groupId = intVal($groupId);
        $this->getSelect()->where('cust_groups="" OR cust_groups LIKE "%,'.$groupId.',%"');
        $this->getSelect()->where('days="" OR days LIKE "%,'.Mage::getModel('core/date')->date('N').',%"');

        $timeStamp = Mage::getModel('core/date')->date('H') * 100 + Mage::getModel('core/date')->date('i') + 1;

        $this->getSelect()->where('time_from="" OR time_from="0" OR time_to="" OR time_to="0" OR
        (time_from < '.$timeStamp.' AND time_to > '.$timeStamp.') OR
        (time_from < '.$timeStamp. ' AND time_to < time_from) OR
        (time_to > '.$timeStamp. ' AND time_to < time_from)');
        return $this;
    }    
}