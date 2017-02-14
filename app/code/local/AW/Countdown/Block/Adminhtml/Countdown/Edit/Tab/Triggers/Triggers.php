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


class AW_Countdown_Block_Adminhtml_Countdown_Edit_Tab_Triggers_Triggers extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('aw_countdown/triggers.phtml');
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $collection = Mage::registry('countdown_data')->getTriggerCollection();
        $this->assign('triggers', false);
        if (count($collection->getItems()) > 0) {
            $this->assign('triggers', $collection);
        }
        return parent::_toHtml();
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $deleteButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => Mage::helper('awcountdown')->__('Remove'),
                    'onclick' => 'trigger.del(this)',
                    'class'   => 'delete',
                    'style'   => 'float:right;'
                )
            )
        ;
        $this->setChild('deleteButton', $deleteButton);

        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                    'label'   => Mage::helper('awcountdown')->__('Add Trigger'),
                    'onclick' => 'trigger.add(this)',
                    'class'   => 'add'
                )
            )
        ;
        $this->setChild('addButton', $addButton);
        return parent::_prepareLayout();
    }

    /**
     * @return bool|object
     */
    public function getSaleRules()
    {
        $salesRules = Mage::getModel('salesrule/rule')->getCollection();
        if ($salesRules->getSize() == 0) {
            return false;
        }
        return $salesRules;
    }

    /**
     * @return bool|object
     */
    public function getCatalogRules()
    {
        $catalogRules = Mage::getModel('catalogrule/rule')->getCollection();
        if ($catalogRules->getSize() == 0) {
            return false;
        }
        return $catalogRules;
    }

    /**
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('deleteButton');
    }

    /**
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('addButton');
    }

    public function getRuleName($ruleId, $type)
    {
        //get Sales Rule Name
        if ($type === '0') {
            return Mage::getModel('salesrule/rule')->load($ruleId)->getName();
        } else {
            return Mage::getModel('catalogrule/rule')->load($ruleId)->getName();
        }
    }
}