<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Countdown
 * @version    1.1.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Countdown_Block_Adminhtml_Countdown_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $_fieldset = $form->addFieldset('countdown_form', array('legend' => $this->__('General')));
        $_data = Mage::registry('countdown_data');

        $_fieldset->addField(
            'name', 'text',
            array(
                 'name'     => 'name',
                 'label'    => $this->__('Name'),
                 'title'    => $this->__('Name'),
                 'required' => true
            )
        );

        if ($_data != null && $_data->getData('status') === null) {
            $_data->setData('status', 1);
        }

        $_fieldset->addField(
            'is_enabled', 'select',
            array(
                 'name'   => 'is_enabled',
                 'label'  => $this->__('Status'),
                 'title'  => $this->__('Status'),
                 'values' => Mage::getModel('awcountdown/source_status')->toOptionArray()
            )
        );

        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $_fieldset->addField(
            'date_from', 'date',
            array(
                 'name'     => 'date_from',
                 'label'    => Mage::helper('awcountdown')->__('From:'),
                 'title'    => Mage::helper('awcountdown')->__('From:'),
                 'format'   => $outputFormat,
                 'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                 'time'     => true,
                 'class'    => 'required-entry',
                 'required' => true,
            )
        );

        $_fieldset->addField(
            'date_to', 'date',
            array(
                 'name'     => 'date_to',
                 'label'    => Mage::helper('awcountdown')->__('To:'),
                 'title'    => Mage::helper('awcountdown')->__('To:'),
                 'class'    => 'required-entry',
                 'required' => true,
                 'format'   => $outputFormat,
                 'image'    => $this->getSkinUrl('images/grid-cal.gif'),
                 'time'     => true
            )
        );

        if (Mage::app()->isSingleStoreMode()) {
            $_data->setStoreIds(0);
            $_fieldset->addField('store_ids', 'hidden',
                array(
                    'name' => 'store_ids[]'
                )
            );
        } else {
            $_fieldset->addField('store_ids', 'multiselect',
                array(
                    'name' => 'store_ids[]',
                    'label' => $this->__('Store view'),
                    'title' => $this->__('Store view'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                )
            );
        }

        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value'] == 0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift(
                $customerGroups,
                array('value' => 0, 'label' => Mage::helper('catalogrule')->__('NOT LOGGED IN'))
            );
        }

        $_fieldset->addField(
            'customer_group_ids', 'multiselect',
            array(
                 'name'     => 'customer_group_ids[]',
                 'label'    => Mage::helper('catalogrule')->__('Customer Groups'),
                 'title'    => Mage::helper('catalogrule')->__('Customer Groups'),
                 'required' => true,
                 'values'   => $customerGroups,
            )
        );

        $_fieldset->addField(
            'url', 'text',
            array(
                 'name'  => 'url',
                 'label' => $this->__('After click on timer, customer will be redirected to:'),
                 'title' => $this->__('After click on timer, customer will be redirected to:'),
                 'note'  => $this->__('for timers with non-product page positions')
            )
        );

        $_fieldset->addField(
            'priority', 'text',
            array(
                 'name'     => 'priority',
                 'label'    => $this->__('Priority'),
                 'title'    => $this->__('Priority'),
                 'required' => false
            )
        );
        $form->setValues($_data->getData());
        return parent::_prepareForm();
    }

}