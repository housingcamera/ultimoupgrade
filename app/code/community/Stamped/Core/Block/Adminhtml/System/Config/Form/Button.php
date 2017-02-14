<?php
class Stamped_Core_Block_Adminhtml_System_Config_Form_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /*
     * template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('stamped/system/config/button.phtml');
    }
 
    /**
     * return html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }
}
?>