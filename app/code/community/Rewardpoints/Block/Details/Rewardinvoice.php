<?php

class Rewardpoints_Block_Details_Rewardinvoice extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getInvoice()
    {
        return Mage::registry('current_invoice');
    }

    public function getPointsUsed()
    {
        return (int)$this->getInvoice()->getRewardpointsQuantity();
    }
    
    public function getPointsOnOrder() {
        return (int)$this->getInvoice()->getRewardpointsGathered();
    }
    
    public function canShow() {
        return Mage::getStoreConfig('rewardpoints/order_invoice/show_on_invoice_client');
    }
}
