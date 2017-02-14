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


class AW_Countdown_Block_Adminhtml_Countdown_Edit_Tab_Design extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('countdown_data');
        $form = new Varien_Data_Form();

        $designFieldset = $form->addFieldset('design_design', array('legend' => $this->__('Design')));
        $designFieldset->addField(
            'block_title', 'text',
            array(
                 'name'  => 'block_title',
                 'label' => $this->__('Title'),
                 'title' => $this->__('Title')
            )
        );
        $designFieldset->addField(
            'design', 'select',
            array(
                 'name'   => 'design',
                 'label'  => $this->__('Design Package'),
                 'title'  => $this->__('Design Package'),
                 'values' => Mage::getModel('awcountdown/source_design')->toOptionArray()
            )
        );
        $designFieldset->addField(
            'show_format', 'select',
            array(
                 'name'   => 'show_format',
                 'label'  => $this->__('Show'),
                 'title'  => $this->__('Show'),
                 'values' => Mage::getModel('awcountdown/source_show')->toOptionArray()
            )
        );

        $templateFieldset = $form->addFieldset('template_fieldset', array('legend' => $this->__('Template')));
        $addTriggerButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                     'label'   => Mage::helper('awcountdown')->__('Add Trigger'),
                     'onclick' => 'trigger.add(this)',
                     'class'   => 'add'
                )
            );
        $this->setChild('addButton', $addTriggerButton);
        $templateFieldset->addField(
            'addTimer', 'button',
            array(
                 'title'     => $this->__('Insert timer'),
                 'class'     => 'button add form-button',
                 'name'      => 'addTimer',
                 'value'     => $this->__('Insert timer'),
                 'onclick'   => 'insertText($(\'template\'),\'{{timer}}\')',
                 'style'     => 'min-width: 150px;',
                 'read-only' => true
            )
        );
        $templateFieldset->addField(
            'addTitle', 'button',
            array(
                 'title'     => $this->__('Insert block title'),
                 'class'     => 'button add form-button',
                 'name'      => 'addTitle',
                 'value'     => $this->__('Insert block title'),
                 'onclick'   => 'insertText($(\'template\'),\'{{title}}\')',
                 'style'     => 'min-width: 150px;',
                 'read-only' => true
            )
        );
        $templateFieldset->addField(
            'template', 'textarea',
            array(
                 'name'  => 'template',
                 'label' => $this->__('Template'),
                 'note'  => $this->__('Use the timer variable only once. HTML code is accepted.')
            )
        );
        $model->setData('addTitle', $this->__('Insert block title'));
        $model->setData('addTimer', $this->__('Insert timer'));
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}