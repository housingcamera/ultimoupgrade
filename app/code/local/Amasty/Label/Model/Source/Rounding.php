<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Model_Source_Rounding {

    public function toOptionArray() 
    {
        return array(
            array(
                'value' => 'floor',
                'label' => Mage::helper('amlabel')->__('The next lowest integer value')
            ),
            array(
                'value' => 'round',
                'label' => Mage::helper('amlabel')->__('By rules of mathematical rounding')
            ),
            array(
                'value' => 'ceil',
                'label' => Mage::helper('amlabel')->__('The next highest integer value')
            ),
        );
    }
}