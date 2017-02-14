<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */ 
class Amasty_Label_Block_Adminhtml_Catalog_Product_Edit_Tabs extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _beforeToHtml()
    {
        if ($this->getProduct()->getTypeId()) {
            $name = Mage::helper('amlabel')->__('Product Labels');
            $this->addTab('general', array(
                'label'     => $name,
                'content'   => $this->getLayout()->createBlock('amlabel/adminhtml_catalog_product_edit_labels')
                    ->setTitle($name)->toHtml(),
            ));
        }
        
        return parent::_beforeToHtml();
    }
}