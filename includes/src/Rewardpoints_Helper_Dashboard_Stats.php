<?php

class Rewardpoints_Helper_Dashboard_Stats extends Mage_Adminhtml_Helper_Dashboard_Abstract
{

    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = Mage::getResourceSingleton('rewardpoints/stats_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        
        if (Mage::getStoreConfig('rewardpoints/default/store_scope')){
            if ($this->getParam('store')) {
                //$this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
                $this->_collection->addFieldToFilter('find_in_set(?, store_id)', $this->getParam('store'));
            } else if ($this->getParam('website')){
                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
                //$this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
                foreach ($storeIds as $storeId)
                    $this->_collection->addFieldToFilter('find_in_set(?, store_id)', $storeId);
            } else if ($this->getParam('group')){
                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
                //$this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
                foreach ($storeIds as $storeId)
                    $this->_collection->addFieldToFilter('find_in_set(?, store_id)', $storeId);
            } else {
                /*$this->_collection->addFieldToFilter('store_id',
                    array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
                );*/
                $this->_collection->addFieldToFilter('find_in_set(?, store_id)', Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId());
            }
        }

        $this->_collection->load();
    }

}
