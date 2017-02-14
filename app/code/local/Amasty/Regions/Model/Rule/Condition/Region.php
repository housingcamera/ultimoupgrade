<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Model_Rule_Condition_Region extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'amasty_region_id' => Mage::helper('amregions')->__('Shipping Area'),
        );

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
        return 'select';
    }

    public function getValueElementType()
    {
        return 'select';
    }

    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $options = Mage::getModel('amregions/region')->getCollection()->toOptionArray();
            $this->setData('value_select_options', $options);
        }
        return $this->getData('value_select_options');
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $address = $object;
        if($object instanceof Mage_Shipping_Model_Rate_Request) {
            $validatedValue = array($object->getDestCountryId());
        } else {
            if (!$address instanceof Mage_Sales_Model_Quote_Address) {
                if(!method_exists($object, 'getQuote')) {
                    return false;
                }
                if ($object->getQuote()->isVirtual()) {
                    return false;
                } else {
                    $address = $object->getQuote()->getShippingAddress();
                }
            }

            $validatedValue = array($address->getCountryId());
        }


        return $this->validateAttribute($validatedValue);
    }

    public function getValueParsed()
    {
        if (!$this->hasValueParsed()) {
            $value = $this->getData('value');
            $value = Mage::getModel('amregions/region')->load($value)->getCountries();
            $this->setValueParsed($value);
        }
        return $this->getData('value_parsed');
    }
    public function isArrayOperatorType()
    {
        return true;
    }
}