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


class AW_Countdown_Block_Adminhtml_Countdown_Edit_Tab_Recurring extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $_fieldset = $form->addFieldset('countdown_form', array('legend' => $this->__('Recurring')));
        $_fieldset->addField('recurring_enabled', 'select',
            array(
                'name'   => 'recurring_enabled',
                'label'  => $this->__('Status'),
                'title'  => $this->__('Status'),
                'values' => Mage::getModel('awcountdown/source_status')->toOptionArray()
            )
        );
        $_fieldset->addField('recurring_type', 'select',
            array(
                 'name'     => 'recurring_type',
                 'label'    => $this->__('Type'),
                 'title'    => $this->__('Type'),
                 'required' => true,
                 'values'   => Mage::getModel('awcountdown/source_recurring_type')->toOptionArray()
            )
        );
        $_fieldset->addField('recurring_everyday_time_from', 'time',
            array(
                'name'     => 'recurring_everyday_time_from',
                'label'    => $this->__('From:'),
                'title'    => $this->__('From:'),
            )
        );
        $_fieldset->addField('recurring_everyday_time_to', 'time',
            array(
                'name'     => 'recurring_everyday_time_to',
                'label'    => $this->__('To:'),
                'title'    => $this->__('To:'),
            )
        );
        $_fieldset->addField('recurring_everyday_weekdays', 'multiselect',
            array(
                'name'   => 'recurring_everyday_weekdays',
                'label'  => $this->__('Include Weekdays'),
                'values' => Mage::app()->getLocale()->getOptionWeekdays(),
            )
        );
        $_fieldset->addField('recurring_xday_range', 'text',
            array(
                 'name'      => 'recurring_xday_range',
                 'label'     => $this->__('Day Range'),
                 'required'  => true,
                 'maxlength' => 3,
                 'class'     => 'validate-digits validate-greater-than-zero'
            )
        );
        $_fieldset->addField('recurring_xday_time_to', 'time',
            array(
                 'name'     => 'recurring_xday_time_to',
                 'label'    => $this->__('To:'),
                 'title'    => $this->__('To:'),
            )
        );
        $_fieldset->addField('recurring_defined_day', 'text',
            array(
               'name'      => 'recurring_defined_day',
               'label'     => $this->__('Day of Month'),
               'required'  => true,
               'maxlength' => 2,
               'class'     => 'validate-digits validate-greater-than-zero'
            )
        );
        $_fieldset->addField('recurring_defined_time_to', 'time',
            array(
                 'name'     => 'recurring_defined_time_to',
                 'label'    => $this->__('To:'),
                 'title'    => $this->__('To:'),
            )
        );

        $form->setValues(Mage::registry('countdown_data')->getData());
        $_result = parent::_prepareForm();

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('recurring_enabled', 'recurring_enabled')
                ->addFieldMap('recurring_type', 'recurring_type')
                ->addFieldMap('recurring_everyday_time_from', 'recurring_everyday_time_from')
                ->addFieldMap('recurring_everyday_time_to', 'recurring_everyday_time_to')
                ->addFieldMap('recurring_everyday_weekdays', 'recurring_everyday_weekdays')
                ->addFieldMap('recurring_xday_range', 'recurring_xday_range')
                ->addFieldMap('recurring_xday_time_to', 'recurring_xday_time_to')
                ->addFieldMap('recurring_defined_day', 'recurring_defined_day')
                ->addFieldMap('recurring_defined_time_to', 'recurring_defined_time_to')
                ->addFieldDependence('recurring_type', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_everyday_time_from', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_everyday_time_to', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_everyday_weekdays', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_xday_range', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_xday_time_to', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_defined_day', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_defined_time_to', 'recurring_enabled', '1')
                ->addFieldDependence('recurring_everyday_time_from', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_DAY_VALUE)
                ->addFieldDependence('recurring_everyday_time_to', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_DAY_VALUE)
                ->addFieldDependence('recurring_everyday_weekdays', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_DAY_VALUE)
                ->addFieldDependence('recurring_xday_range', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_X_DAYS_VALUE)
                ->addFieldDependence('recurring_xday_time_to', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_X_DAYS_VALUE)
                ->addFieldDependence('recurring_defined_day', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_DEFINED_DAY_VALUE)
                ->addFieldDependence('recurring_defined_time_to', 'recurring_type', AW_Countdown_Model_Source_Recurring_Type::EVERY_DEFINED_DAY_VALUE)
        );
        return $_result;
    }
}