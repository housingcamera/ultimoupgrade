<?php

class Rewardpoints_Block_Adminhtml_Details_Rewardinvoice extends Mage_Adminhtml_Block_Widget_Form_Container
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
        return Mage::getStoreConfig('rewardpoints/order_invoice/show_on_invoice_admin');
    }
}
