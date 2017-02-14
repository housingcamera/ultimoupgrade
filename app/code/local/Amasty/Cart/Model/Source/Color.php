<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2013 Amasty (http://www.amasty.com)
* @package Amasty_Cart
*/
class Amasty_Cart_Model_Source_Color extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
                'value' => '0',
                'label' => Mage::helper('amcart')->__('Default')
        );
        $options[] = array(
                'value' => '1',
                'label' => Mage::helper('amcart')->__('Blue')
        );
        $options[] = array(
                'value' => '2',
                'label' => Mage::helper('amcart')->__('Red')
        );
        $options[] = array(
                'value' => '3',
                'label' => Mage::helper('amcart')->__('Green')
        );
        return $options;
    }
}