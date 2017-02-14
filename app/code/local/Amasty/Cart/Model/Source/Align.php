<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2013 Amasty (http://www.amasty.com)
* @package Amasty_Cart
*/
class Amasty_Cart_Model_Source_Align extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
                'value' => '0',
                'label' => Mage::helper('amcart')->__('Center')
        );
        $options[] = array(
                'value' => '1',
                'label' => Mage::helper('amcart')->__('Top')
        );
        $options[] = array(
                'value' => '2',
                'label' => Mage::helper('amcart')->__('Top Left')
        );
        $options[] = array(
                'value' => '3',
                'label' => Mage::helper('amcart')->__('Top Right')
        ); 
        $options[] = array(
                'value' => '4',
                'label' => Mage::helper('amcart')->__('Left')
        ); 
        
        $options[] = array(
                'value' => '5',
                'label' => Mage::helper('amcart')->__('Right')
        );
        return $options;
    }
}