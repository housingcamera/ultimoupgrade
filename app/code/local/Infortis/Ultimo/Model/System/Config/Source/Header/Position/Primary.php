<?php

class Infortis_Ultimo_Model_System_Config_Source_Header_Position_Primary
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'primLeftCol',			'label' => Mage::helper('ultimo')->__('Primary, Left Column')),
			array('value' => 'primCentralCol',		'label' => Mage::helper('ultimo')->__('Primary, Central Column')),
			array('value' => 'primRightCol',		'label' => Mage::helper('ultimo')->__('Primary, Right Column')),
        );
    }
}