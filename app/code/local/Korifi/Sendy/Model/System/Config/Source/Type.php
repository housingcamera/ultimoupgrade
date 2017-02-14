<?php

class Korifi_Sendy_Model_System_Config_Source_Type  {

    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('All')),
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('1 day ago')),
            array('value' => 2, 'label'=>Mage::helper('adminhtml')->__('2 days ago')),
            array('value' => 3, 'label'=>Mage::helper('adminhtml')->__('3 days ago'))
        );
    }

}