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


class AW_Countdown_Block_Adminhtml_Countdown_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('countdown_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('awcountdown')->__('Countdown Timer'));
    }

    protected function _beforeToHtml()
    {
        $generalContent = $this->getLayout()->createBlock('awcountdown/adminhtml_countdown_edit_tab_general')->toHtml();
        $this->addTab(
            'general',
            array(
                 'label'   => Mage::helper('awcountdown')->__('General'),
                 'title'   => Mage::helper('awcountdown')->__('General'),
                 'content' => $generalContent,
                 'active'  => ($this->getRequest()->getParam('tab') == 'countdown_tabs_general') ? true : false,
            )
        );

        $designContent = $this->getLayout()->createBlock('awcountdown/adminhtml_countdown_edit_tab_design')->toHtml();
        $this->addTab(
            'design',
            array(
                 'label'   => Mage::helper('awcountdown')->__('Design'),
                 'title'   => Mage::helper('awcountdown')->__('Design'),
                 'content' => $designContent,
                 'active'  => ($this->getRequest()->getParam('tab') == 'countdown_tabs_design') ? true : false,
            )
        );

        $automationContent = $this->getLayout()->createBlock('awcountdown/adminhtml_countdown_edit_tab_automation')
            ->toHtml()
        ;
        $this->addTab(
            'automation',
            array(
                 'label'   => Mage::helper('awcountdown')->__('Automation'),
                 'title'   => Mage::helper('awcountdown')->__('Automation'),
                 'content' => $automationContent,
                 'active'  => ($this->getRequest()->getParam('tab') == 'countdown_tabs_automation') ? true : false,
            )
        );

        $triggersContent = $this->getLayout()->createBlock('awcountdown/adminhtml_countdown_edit_tab_triggers')
            ->append($this->getLayout()->createBlock('awcountdown/adminhtml_countdown_edit_tab_triggers_triggers'))
            ->toHtml()
        ;
        $this->addTab(
            'triggers',
            array(
                 'label'   => Mage::helper('awcountdown')->__('Triggers'),
                 'title'   => Mage::helper('awcountdown')->__('Triggers'),
                 'content' => $triggersContent,
                 'active'  => ($this->getRequest()->getParam('tab') == 'countdown_tabs_triggers') ? true : false,
            )
        );
        $recurringContent = $this->getLayout()->createBlock('awcountdown/adminhtml_countdown_edit_tab_recurring')
            ->toHtml()
        ;
        $this->addTab(
            'recurring',
            array(
                 'label'   => Mage::helper('awcountdown')->__('Recurring'),
                 'title'   => Mage::helper('awcountdown')->__('Recurring'),
                 'content' => $recurringContent,
                 'active'  => ($this->getRequest()->getParam('tab') == 'countdown_tabs_recurring') ? true : false,
            )
        );
        return parent::_beforeToHtml();
    }

}