<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Label_Grid_Renderer_Image_Product extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $url = Mage::getBaseUrl('media') . 'amlabel/';
        
        if ($row->getProdImg()) {
            $html = '<img style="max-width:100px" src="' . $url . '/' . $row->getProdImg() . '" />';
        } else {
            $html = Mage::helper('amlabel')->__('No Image');
        }
        
        return $html;
    }
}