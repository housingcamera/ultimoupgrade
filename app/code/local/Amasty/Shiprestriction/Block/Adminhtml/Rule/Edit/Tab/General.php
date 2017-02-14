<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
class Amasty_Shiprestriction_Block_Adminhtml_Rule_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /* @var $hlp Amasty_Shiprestriction_Helper_Data */
        $hlp = Mage::helper('amshiprestriction');

        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'required'  => true,
            'name'      => 'name',
        ));
        $fldInfo->addField('is_active', 'select', array(
            'label'     => Mage::helper('salesrule')->__('Status'),
            'name'      => 'is_active',
            'options'    => $hlp->getStatuses(),
        ));

        $fldInfo->addField('methods', 'textarea', array(
            'label'     => $hlp->__('Restrict Shipping Methods'),
            'name'      => 'methods',
            'note'      => $hlp->__('One method name per line, e.g Next Day Air'),
        ));

        $fldInfo->addField('carriers', 'multiselect', array(
            'label'     => $hlp->__('Restrict ALL METHODS from Carriers'),
            'name'      => 'carriers[]',
            'values'    => $hlp->getAllCarriers(),
            'note'      => $hlp->__('Select if you want to restrict ALL methods from the given carrirers'),
        ));

        $fldInfo->addField('message', 'text', array(
            'label'     => $hlp->__('Error Message'),
            'name'      => 'message',
        ));

        //set form values
        $form->setValues(Mage::registry('amshiprestriction_rule')->getData());

        return parent::_prepareForm();
    }
}