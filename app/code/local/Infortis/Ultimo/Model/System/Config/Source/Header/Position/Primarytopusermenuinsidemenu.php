<?php

class Infortis_Ultimo_Model_System_Config_Source_Header_Position_PrimaryTopUserMenuInsideMenu
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'topLeft',				'label' => Mage::helper('ultimo')->__('Top, Left')),
			array('value' => 'topCentral',			'label' => Mage::helper('ultimo')->__('Top, Central')),
			array('value' => 'topRight',			'label' => Mage::helper('ultimo')->__('Top, Right')),
			array('value' => 'primLeftCol',			'label' => Mage::helper('ultimo')->__('Primary, Left Column')),
			array('value' => 'primCentralCol',		'label' => Mage::helper('ultimo')->__('Primary, Central Column')),
			array('value' => 'primRightCol',		'label' => Mage::helper('ultimo')->__('Primary, Right Column')),
			array('value' => 'mainMenu',			'label' => Mage::helper('ultimo')->__('Inside Main Menu')),
			array('value' => 'userMenu',			'label' => Mage::helper('ultimo')->__('Inside User Menu')),
        );
    }
}
