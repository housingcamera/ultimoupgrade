<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CEC_ExtendedGrid_Block_Adminhtml_Catalog_Product_Renderer_Link extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
 
    public function render(Varien_Object $row)
    {
        $value =  Mage::getBaseUrl() . $row->getData($this->getColumn()->getIndex());
        return '<a href="' . $value . '">View</a>';

    }
}