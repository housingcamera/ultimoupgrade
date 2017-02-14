<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Label_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Label_Helper_Data */
        $hlp   = Mage::helper('amlabel');
        $model = Mage::registry('amlabel_label');
        
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('General')));
        
        $fldInfo->addField('name', 'text', array(
            'label'     => $hlp->__('Name'),
            'name'      => 'name',
        ));
        $fldInfo->addField('open_tab_input', 'hidden', array(
            'label'     => $hlp->__(''),
            'name'      => 'open_tab_input',
        ));

        $fldInfo->addField('is_active', 'select', array(
            'label'     => $hlp->__('Status'),
            'name'      => 'is_active',
            'values'    => array(
                0 => $hlp->__('Inactive'),
                1 => $hlp->__('Active'),
            ),
        ));
        
        $fldInfo->addField('pos', 'text', array(
            'label'     => $hlp->__('Priority'),
            'name'      => 'pos',
            'note'      => $hlp->__('Use 0 to show label first, and 99 to show it last'),
        ));         
      
        $fldInfo->addField('is_single', 'select', array(
            'label'     => $hlp->__('Hide if label with higher priority is already applied'),
            'name'      => 'is_single',
            'values'    => array(
                0 => $hlp->__('No'), 
                1 => $hlp->__('Yes'), 
             ),
        ));
          
        $fldInfo->addField('use_for_parent', 'select', array(
            'label'     => $hlp->__('Use for Parent'),
            'title'     => $hlp->__('Use for Parent'),
            'name'      => 'use_for_parent',
            'note'      => $hlp->__('Display child`s label for parent (configurable and grouped products only)'),
            'options'   => array(           
                '0' => $hlp->__('No'),
                '1' => $hlp->__('Yes'),
            ),
        ));
        
        $fldInfo->addField('stores', 'multiselect', array(
            'label'     => $hlp->__('Show In'),
            'name'      => 'stores[]',
            'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(), 
        ));               

        //set form values
        $form->setValues($model->getData()); 
        
        return parent::_prepareForm();
    }
}
