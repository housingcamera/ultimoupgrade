<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Block_Adminhtml_Region_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);


        $fieldset = $form->addFieldset('general', array(
            'htmlId'	=> 'general_information',
            'legend'	=> $this->__('Information'),
        ));

        $fieldset->addField('region_title', 'text',
            array(
                'label' => $this->__('Shipping Area Title'),
                'name' => 'region_title',
                'required' => true,
            )
        );

        $fieldsetCountries = $form->addFieldset('countries', array(
            'htmlId'	=> 'countries',
            'legend'	=> $this->__('Countries'),
        ));

        $checkedCountries = Mage::registry('amregions_region')->getCountries();

        foreach(Mage::helper('amregions')->getCountries() as $id=>$countryData)
        {
            $field = $fieldsetCountries->addField('country_'.$countryData['value'], 'checkbox',array(
                'after_element_html' => "<label for='country_{$countryData['value']}'>".$countryData['label'].'</label>',
                'name' => 'country['.$countryData['value'].']',
            ))->setIsChecked(in_array($countryData['value'], $checkedCountries));
        }

        $values = Mage::registry('amregions_region')->getData();

        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if($data && is_array($data)) {
            $values = array_merge($values, $data);
        }
        $form->setValues($values);


        $this->setForm($form);

        return parent::_prepareForm();
    }
}