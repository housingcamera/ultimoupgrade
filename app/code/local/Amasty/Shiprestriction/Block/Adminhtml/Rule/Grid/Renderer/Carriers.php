<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */ 
class Amasty_Shiprestriction_Block_Adminhtml_Rule_Grid_Renderer_Carriers extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        /* @var $hlp Amasty_Shiprestriction_Helper_Data */
        $hlp = Mage::helper('amshiprestriction'); 
        
        $carriers = $row->getData('carriers');
        if (!$carriers) {
            return $hlp->__('Allows All');
        }
        $carriers = explode(',', $carriers);
        
        $html = '';
        foreach($hlp->getAllCarriers() as $row)
        {
            if (in_array($row['value'], $carriers)){
                $html .= $row['label'] . "<br />";
            }
        }
        return $html;
    }
}