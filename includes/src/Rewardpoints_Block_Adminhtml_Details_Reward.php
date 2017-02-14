<?php

class Rewardpoints_Block_Adminhtml_Details_Reward extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getOrder()
    {
        return Mage::registry('sales_order');
    }

    public function getPointsUsed()
    {
        return (int)$this->getOrder()->getRewardpointsQuantity();
    }
    
    public function getPointsOnOrder() {
        return (int)$this->getOrder()->getRewardpointsGathered();
    }
    
    public function canShow() {
        return Mage::getStoreConfig('rewardpoints/order_invoice/show_on_order_admin');
    }
}
