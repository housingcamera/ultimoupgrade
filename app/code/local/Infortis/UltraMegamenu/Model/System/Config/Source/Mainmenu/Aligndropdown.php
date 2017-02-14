<?php

class Infortis_UltraMegamenu_Model_System_Config_Source_Mainmenu_AlignDropdown
{
    public function toOptionArray()
    {
		return array(
			array('value' => 'window',				'label' => Mage::helper('ultramegamenu')->__('Viewport')),
			array('value' => 'menuBar',				'label' => Mage::helper('ultramegamenu')->__('Menu bar')),
			array('value' => 'headPrimInner',		'label' => Mage::helper('ultramegamenu')->__('Primary header, inner container')),
        );
    }
}
