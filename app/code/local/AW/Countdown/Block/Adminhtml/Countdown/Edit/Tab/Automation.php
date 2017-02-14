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


class AW_Countdown_Block_Adminhtml_Countdown_Edit_Tab_Automation extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('countdown_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('auto_');
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_countdown/newConditionHtml/form/auto_conditions_fieldset'))
        ;

        $conditionsFieldset = $form->addFieldset('conditions_fieldset', array('legend' => $this->__('Products subselection')))
            ->setRenderer($renderer)
        ;

        $conditionsFieldset->addField(
            'conditions', 'text',
            array(
                 'name'     => 'conditions',
                 'label'    => $this->__('Products subselection'),
                 'title'    => $this->__('Products subselection'),
                 'required' => false,
            )
        )->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $automationFieldset = $form->addFieldset('design_design', array('legend' => $this->__('Automation')));
        $automationFieldset->addField(
            'autom_display', 'select',
            array(
                'name'     => 'autom_display',
                'label'    => $this->__('Show for selected products'),
                'title'    => $this->__('Show for selected products'),
                'required' => true,
                'values'   => Mage::getModel('awcountdown/source_automation')->getOptionArray()
            )
        );
        $automationFieldset->addField(
            'product_action_disable', 'select',
            array(
                'name'   => 'product_action_disable',
                'label'  => $this->__('Disable selected products when countdown is over:'),
                'title'  => $this->__('Disable selected products when countdown is over:'),
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            )
        );
        $automationFieldset->addField(
            'product_action_enable', 'select',
            array(
                'name'   => 'product_action_enable',
                'label'  => $this->__('Enable selected products on countdown start:'),
                'title'  => $this->__('Enable selected products on countdown start:'),
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
                'after_element_html' => '<p><b>'
                    . $this->__("Be careful with enable and disable options. Note,"
                    . " if you haven't specified any conditions for the timer, turning these options"
                    . " on will enable or disable ALL the products at your store")
                    . '</b></p>'
            )
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

}