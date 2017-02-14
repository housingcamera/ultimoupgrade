<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
class Amasty_Shiprestriction_Model_Rule_Condition_Customer extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $hlp = Mage::helper('customer');
        
        $customerAttributes = Mage::getResourceSingleton('customer/customer')
            ->loadAllAttributes()
            ->getAttributesByCode();
        $attributes = array();
        
        foreach ($customerAttributes as $attribute) {
        if (!($attribute->getFrontendLabel()) || !($attribute->getAttributeCode())) {
                continue;
            }

            $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
        } 
        asort($attributes);
        $this->setAttributeOption($attributes);
        return $this;
    }
    
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        $customerAttribute = Mage::getResourceSingleton('customer/customer') -> getAttribute($this->getAttribute()); 

         switch ($customerAttribute->getFrontendInput()) {
            
            case 'boolean':
                return 'select';
            case 'text':
                return 'string';
             case 'datetime':
                 return 'date';
            default :
                return $customerAttribute->getFrontendInput();
        }
        
    }
    
    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getInputType()) {
            case 'date':
                $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                break;
        }
        return $element;
    }

    public function getExplicitApply()
    {
        return ($this->getInputType() == 'date');
    }     

    public function getValueElementType()
    {
        $customerAttribute = Mage::getResourceSingleton('customer/customer') -> getAttribute($this->getAttribute());    
  
        switch ($customerAttribute->getFrontendInput()) {
            case 'boolean':
                return 'select';
            default :
                return $customerAttribute->getFrontendInput();
        }
    }

    public function getValueSelectOptions()
    {
        $selectOptions = array();
            $attributeObject = Mage::getResourceSingleton('customer/customer') -> getAttribute($this->getAttribute());
           
           if (is_object($attributeObject) && $attributeObject->usesSource() ) {
                if ($attributeObject->getFrontendInput() == 'multiselect') {
                    $addEmptyOption = false;
                } else {
                   $addEmptyOption = true;
                }
                $selectOptions = $attributeObject->getSource()->getAllOptions($addEmptyOption);
           }                
        
        $key = 'value_select_options';
        
        if (!$this->hasData($key)) {        
            $this->setData($key, $selectOptions);
        }

        return $this->getData($key);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $customer = $object;
        if (!$customer instanceof Mage_Customer_Model_Customer) {
            $customer = $object->getQuote()->getCustomer();
            $attr = $this->getAttribute();

           if ($attr != 'entity_id' && !$customer->getData($attr)){
                $address = $object->getQuote()->getBillingAddress();
                $customer->setData($attr, $address->getData($attr));
            }
        }
        return parent::validate($customer);
    }
}
