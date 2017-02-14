<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Xlanding_Block_Adminhtml_Page extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_page';
        $this->_blockGroup = 'amlanding';
        $this->_headerText = Mage::helper('amlanding')->__('Landing Pages');
        $this->_addButtonLabel = Mage::helper('amlanding')->__('Add Landing Page');
        parent::__construct();
    }
}