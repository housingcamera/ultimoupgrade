<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Model_Config_Width extends Mage_Core_Model_Config_Data
{
    
    public function _beforeSave()
    {
    
        $result = $this->validate();
        
        if ($result !== true) {
            
            Mage::throwException(implode("\n", $result));
            
        }
        
        return parent::_beforeSave();
        
    }
    
    public function validate()
    {
        
        $errors = array();
        $helper = Mage::helper('apptrian_subcategories');
        $value  = $this->getValue();
        
        // Empty is allowed
        if ($value === '') {
            return true;
        }
        
        if (!Zend_Validate::is($value, 'Digits')) {
            $errors[] = $helper->__('Image Width must be an integer.');
        }
        
        if (!Zend_Validate::is($value, 'GreaterThan', array(1))) {
            $errors[] = $helper->__('Image Width must be greater than zero.');
        }
        
        if (empty($errors)) {
            return true;
        }
        
        return $errors;
        
    }
    
}