<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Label_Grid_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        if($row->getIsActive()){
            return '<span class="grid-severity-notice">
                        <span>' . Mage::helper('amlabel')->__('Active') . '</span>
                    </span>';
        }
        else{
            return '<span class="grid-severity-critical">
                        <span>' . Mage::helper('amlabel')->__('Inactive') . '</span>
                    </span>';
        }
    }
}
