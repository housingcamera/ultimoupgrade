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


class AW_Countdown_Model_Countdown extends Mage_Rule_Model_Rule
{
    const ENTITY         = 'awcountdown';
    const STATUS_PENDING = '0';
    const STATUS_STARTED = '1';
    const STATUS_ENDED   = '2';
    const STATUS_PAUSED  = '3';

    protected $_triggers = null;

    public function _construct()
    {
        $this->_init('awcountdown/countdown');
    }

    /**
     *
     */
    public function clearMyTriggers()
    {
        $collection = Mage::getModel('awcountdown/trigger')
            ->getResourceCollection()
            ->addTimerIdFilter($this->getCountdownid())
        ;
        foreach ($collection as $trigger) {
            $trigger->delete();
        }
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('awcountdown/countdown_condition_combine');
    }

    /**
     * @param Varien_Object $product
     *
     * @return Varien_Object|bool
     */
    public function validateProductAttributes(Varien_Object $product)
    {
        $customerGroupId = Mage::helper('awcountdown')->getCustomerGroupId();
        $countdownCollection = Mage::getModel('awcountdown/countdown')->getCollection()
            ->addStoreIdsFilter(Mage::app()->getStore()->getId())
            ->addAutomDisplayFilter(AW_Countdown_Model_Source_Automation::INSIDE_PRODUCT_PAGE)
            ->addIsEnabledFilter(AW_Countdown_Model_Source_Status::ENABLED)
            ->addStatusFilter(self::STATUS_STARTED)
            ->addFieldToFilter('customer_group_ids', array("finset" => $customerGroupId))
            ->addActualDateFilter()
            ->sortByPriority(Zend_Db_Select::SQL_ASC)
            ->addIndexTable($product->getId(), Mage::app()->getStore()->getId())
        ;
        return Mage::getModel('awcountdown/countdown')->load($countdownCollection->getFirstItem()->getId());
    }

    /**
     * @param Mage_Catalog_Model_Product|null $product
     *
     * @return bool
     */
    public function validateProductAttributesWidget($product = null)
    {
        $customerGroupId = Mage::helper('awcountdown')->getCustomerGroupId();
        $countdownCollection = Mage::getModel('awcountdown/countdown')->getCollection()
            ->addStoreIdsFilter(Mage::app()->getStore()->getId())
            ->addAutomDisplayFilter(AW_Countdown_Model_Source_Automation::NO)
            ->addIsEnabledFilter(AW_Countdown_Model_Source_Status::ENABLED)
            ->addStatusFilter(self::STATUS_STARTED)
            ->addFieldToFilter('customer_group_ids', array("finset" => $customerGroupId))
            ->addActualDateFilter()
            ->sortByPriority(Zend_Db_Select::SQL_ASC)
            ->addIdFilter($this->getId())
        ;
        if (null !== $product) {
            $countdownCollection->addIndexTable($product->getId(), Mage::app()->getStore()->getId());
        }
        if ($countdownCollection->getSize() > 0) {
            return true;
        }
        return false;
    }

    public function awSaveBefore()
    {
        return true;
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
        }
        if (is_array($this->getStoreIds())) {
            $this->setStoreIds(join(',', $this->getStoreIds()));
        }
        $recurringData = array(
            'recurring_type'               => $this->getRecurringType(),
            'recurring_everyday_time_from' =>
                is_array($this->getRecurringEverydayTimeFrom()) ? @implode(',', $this->getRecurringEverydayTimeFrom())
                    : $this->getRecurringEverydayTimeFrom(),
            'recurring_everyday_time_to'   =>
                is_array($this->getRecurringEverydayTimeTo()) ? @implode(',', $this->getRecurringEverydayTimeTo())
                    : $this->getRecurringEverydayTimeTo(),
            'recurring_everyday_weekdays'  => $this->getRecurringEverydayWeekdays(),
            'recurring_xday_range'         => $this->getRecurringXdayRange(),
            'recurring_xday_time_to'       =>
                is_array($this->getRecurringXdayTimeTo()) ? @implode(',', $this->getRecurringXdayTimeTo())
                    : $this->getRecurringXdayTimeTo(),
            'recurring_defined_day'        => $this->getRecurringDefinedDay(),
            'recurring_defined_time_to'    =>
                is_array($this->getRecurringDefinedTimeTo()) ? @implode(',', $this->getRecurringDefinedTimeTo())
                    : $this->getRecurringDefinedTimeTo()
        );
        $this->setRecurringData($recurringData);
    }

    public function getValidatedProductIds($productIds, $storeId)
    {
        return $this->getConditions()->getValidatedProductIds($productIds, $storeId);
    }

    protected function _afterSave()
    {
        $result = parent::_afterSave();

        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        if ($this->getOrigData('status') != $this->getData('status')) {
            $storeIds = explode(',', $this->getStoreIds());
            if ($this->getData('status') == self::STATUS_STARTED && $this->getProductActionEnable()) {
                $productIds = $this->getResource()->getIndexesProductsIds($this->getId());
                foreach ($storeIds as $storeId) {
                    $this
                        ->getResource()
                        ->changeProductsStatus($productIds, $storeId, Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                    ;
                }
            }
            if (($this->getData('status') == self::STATUS_ENDED || $this->getData('status') == self::STATUS_PAUSED)
                && $this->getProductActionDisable()
            ) {
                $productIds = $this->getResource()->getIndexesProductsIds($this->getId());
                foreach ($storeIds as $storeId) {
                    $this
                        ->getResource()
                        ->changeProductsStatus($productIds, $storeId, Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                    ;
                }
            }
        }
        return $result;
    }

    protected function _beforeDelete()
    {
        Mage::getSingleton('index/indexer')->logEvent(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
        );
        return parent::_beforeDelete();
    }

    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();
        Mage::getSingleton('index/indexer')->indexEvents(
            self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE
        );
    }

    public function getDateTo()
    {
        if ($this->getRecurringEnabled()) {
            return Mage::helper('awcountdown/recurring')->getDateTo($this);
        }
        return $this->getData('date_to');
    }

    public function getTriggerCollection()
    {
        if (null === $this->_triggers) {
            $collection = Mage::getModel('awcountdown/trigger')
                ->getResourceCollection()
                ->addTimerIdFilter($this->getId())
                ->load()
            ;
            $this->_triggers = $collection;
        }
        return $this->_triggers;
    }

    public function addTriggersFromArray(array $triggers)
    {
        $collection = $this->getTriggerCollection();
        $increment = 1;
        if (count($collection->getItems()) > 0) {
            $increment = max($collection->getAllIds());
        }
        foreach ($triggers as $id => $trigger) {
            $item = Mage::getModel('awcountdown/trigger')->load($id);
            if (null !== $item->getId()) {
                continue;
            }
            $item->addData($trigger);
            $item->setId(++$increment);
            $collection->addItem($item);
        }
        $this->_triggers = $collection;
        return $this;
    }
}
