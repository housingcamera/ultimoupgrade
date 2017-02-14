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


class AW_Countdown_Model_Countdown_Mysql4_Countdown_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function _construct()
    {
        parent::_construct();
        $this->_init('awcountdown/countdown');
    }

    public function addIdFilter($id)
    {
        $this->getSelect()->where('countdownid = ?', $id);
        return $this;
    }

    /**
     * Filters collection by store ids
     *
     * @param $stores= null
     * @param $breakOnAllStores = false
     *
     * @return AW_Countdown_Model_Countdown_Mysql4_Countdown_Collection
     */
    public function addStoreIdsFilter($stores = null, $breakOnAllStores = false)
    {
        $_stores = array(Mage::app()->getStore()->getId());
        if (is_string($stores)) {
            $_stores = explode(',', $stores);
        }
        if (is_array($stores)) {
            $_stores = $stores;
        }
        if (!in_array('0', $_stores)) {
            array_push($_stores, '0');
        }
        if ($breakOnAllStores && $_stores == array(0)) {
            return $this;
        }
        $_sqlString = '(';
        $i = 0;
        foreach ($_stores as $_store) {
            $_sqlString .= sprintf('find_in_set(%s, store_ids)', $this->getConnection()->quote($_store));
            if (++$i < count($_stores)) {
                $_sqlString .= ' OR ';
            }
        }
        $_sqlString .= ')';
        $this->getSelect()->where($_sqlString);
        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()->where('status = ?', $status);
        return $this;
    }

    public function addStatusesFilter($statuses)
    {
        $this->getSelect()->where('status IN(?)', $statuses);
        return $this;
    }

    public function addIsEnabledFilter($status)
    {
        $this->getSelect()->where('is_enabled = ?', $status);
        return $this;
    }

    public function addDateFromFilter($date = null)
    {
        if ($date === null) {
            $date = now(true);
        }
        $this->getSelect()->where('date_from IS NULL OR date_from <= ?', $date);
        return $this;
    }

    public function addDateToFilter($date = null)
    {
        if ($date === null) {
            $date = now(true);
        }
        $this->getSelect()->where('date_to <= ?', $date);
        return $this;
    }

    public function addAutomDisplayFilter($pos)
    {
        $this->getSelect()->where('autom_display = ?', $pos);
        return $this;
    }

    public function addActualDateFilter()
    {
        $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);
        $now = date(self::MYSQL_DATETIME_FORMAT, time() + $timeShift);
        $this->getSelect()->where('date_to > ?', $now);
        return $this;
    }

    public function orderByDateTo($direction)
    {
        $this->getSelect()->order('date_to', $direction);
        return $this;
    }

    public function addStoreFilter($storeIds)
    {
        $query = null;
        sort($storeIds);
        foreach ($storeIds as $key => $storeId) {
            $query .= "FIND_IN_SET({$storeId},main_table.store_ids)";
            if ($key != count($storeIds) - 1) {
                $query .= " OR ";
            }
        }
        $this->getSelect()->where($query);
        return $this;
    }

    /**
     * Covers bug in Magento function
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        return $countSelect->reset()->from($this->getSelect(), array())->columns('COUNT(*)');
    }

    public function addIndexTable($productId, $storeId)
    {
        $this->getSelect()
            ->join(
                 array(
                      'index' => $this->getTable('awcountdown/countdown_index')
                 ),
                'main_table.countdownid = index.countdown_id AND index.product_id = '
                    . $productId . ' AND index.store_id = ' . $storeId
            )
        ;
        return $this;
    }

    public function addIsRecurringFilter()
    {
        $this->getSelect()->where('recurring_enabled = ?', 1);
        return $this;
    }

    public function sortByPriority($direction = Zend_Db_Select::SQL_DESC)
    {
        $this->getSelect()->order('priority', $direction);
        return $this;
    }
}
