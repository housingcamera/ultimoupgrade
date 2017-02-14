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


class AW_Countdown_Model_Cron
{
    const CACHE_ENABLED = false;
    const LOCK = 'awcountdowncronlock';
    const LOCKTIME = 300; // 5 minutes
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public static function run()
    {
        $timeShift = Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS);
        $now = date(self::MYSQL_DATETIME_FORMAT, time() + $timeShift);
        if (self::checkLock()) {
            $catalogRulesCounter = 0;
            //**************************
            //Get Timers for onStart event
            //**************************
            $countdownsOnStart = Mage::getModel('awcountdown/countdown')->getCollection()
                ->addStatusFilter(AW_Countdown_Model_Countdown::STATUS_PENDING)
                ->addDateFromFilter($now)
                ->addIsEnabledFilter('1')->load();

            //process timers onstart triggers
            if ($countdownsOnStart->getSize() != 0) {
                foreach ($countdownsOnStart as $timer) {
                    $countdownModel = Mage::getModel('awcountdown/countdown')->load($timer->getId());
                    $onStartTriggers = Mage::getModel('awtrigger/trigger')->getCollection()
                        ->addTimerIdFilter($countdownModel->getId())
                        ->addActionTypeFilter(AW_Countdown_Model_Trigger::ON_START)
                        ->load()
                    ;
                    if ($onStartTriggers->getSize() != 0) {
                        //Run triggers
                        foreach ($onStartTriggers as $trigger) {
                            try {
                                if ($trigger->getRuleType() == '0') {
                                    //ShoppingCart rules
                                    $rule = Mage::getModel('salesrule/rule')->load($trigger->getRuleId());
                                } else {
                                    //CatalogRule
                                    $rule = Mage::getModel('catalogrule/rule')->load($trigger->getRuleId());
                                    $catalogRulesCounter++;
                                }
                                if ($rule->getId()) {
                                    $rule->setData('is_active', $trigger->getAction())->save();
                                }
                            } catch (Exception $e) {

                            }
                        }
                    }
                    Mage::dispatchEvent(
                        'awcountdown_execute_onstart_triggers',
                        array('triggers' => $onStartTriggers, 'countdowns' => $countdownsOnStart)
                    );
                    Mage::helper('awcore/logger')->log(
                        $countdownModel, 'Countdown #' . $countdownModel->getId() . ' started.', null,
                        'Executed triggers count: ' . $onStartTriggers->getSize()
                    );
                    $countdownModel->setData('status', AW_Countdown_Model_Countdown::STATUS_STARTED)->save();
                }
            }
            //**************************
            //Get Timers for onEnd event
            //**************************
            $countdownsOnEnd = Mage::getModel('awcountdown/countdown')->getCollection()
                ->addStatusFilter(AW_Countdown_Model_Countdown::STATUS_STARTED)
                ->addDateToFilter($now)
                ->addIsEnabledFilter('1')
                ->load()
            ;
            //process timers onstart triggers
            if ($countdownsOnEnd->getSize() != 0) {
                foreach ($countdownsOnEnd as $timer) {
                    $countdownModel = Mage::getModel('awcountdown/countdown')->load($timer->getId());
                    $onEndTriggers = Mage::getModel('awtrigger/trigger')->getCollection()
                        ->addTimerIdFilter($countdownModel->getId())
                        ->addActionTypeFilter(AW_Countdown_Model_Trigger::ON_END)
                        ->load();
                    if ($onEndTriggers->getSize() != 0) {
                        //Run triggers
                        foreach ($onEndTriggers as $trigger) {
                            try {
                                if ($trigger->getRuleType() == '0') {
                                    //ShoppingCart rules
                                    $rule = Mage::getModel('salesrule/rule')->load($trigger->getRuleId());
                                } else {
                                    //CatalogRule
                                    $rule = Mage::getModel('catalogrule/rule')->load($trigger->getRuleId());
                                    $catalogRulesCounter++;
                                }
                                if ($rule->getId()) {
                                    $rule->setData('is_active', $trigger->getAction())->save();
                                }
                            } catch (Exception $e) {

                            }
                        }
                    }
                    Mage::dispatchEvent(
                        'awcountdown_execute_onend_triggers',
                        array('triggers' => $onEndTriggers, 'countdowns' => $countdownsOnEnd)
                    );
                    Mage::helper('awcore/logger')->log(
                        $countdownModel, 'Countdown #' . $countdownModel->getId() . ' ended.', null,
                        'Executed triggers count: ' . $onEndTriggers->getSize()
                    );
                    $countdownModel->setData('status', AW_Countdown_Model_Countdown::STATUS_ENDED)->save();
                }
            }
            if ($catalogRulesCounter > 0) {
                try {
                    Mage::getModel('catalogrule/rule')->applyAll();
                    Mage::app()->removeCache('catalog_rules_dirty');
                } catch (Exception $e) {

                }
            }
            Mage::app()->removeCache(self::LOCK);
        }
    }

    public static function checkLock()
    {
        if ($time = Mage::app()->loadCache(self::LOCK)) {
            if ((time() - $time) < self::LOCKTIME) {
                return false;
            }
        }
        Mage::app()->saveCache(time(), self::LOCK, array(), self::LOCKTIME);
        return true;
    }

    public function recurring()
    {
        $countdownsCollection = Mage::getModel('awcountdown/countdown')->getCollection()
            ->addStatusesFilter(array(AW_Countdown_Model_Countdown::STATUS_STARTED, AW_Countdown_Model_Countdown::STATUS_PAUSED))
            ->addIsEnabledFilter('1')
            ->addIsRecurringFilter()
        ;
        foreach ($countdownsCollection as $countdown) {
            $countdown->load($countdown->getId());
            if(Mage::helper('awcountdown/recurring')->canShow($countdown)) {
                if ($countdown->getStatus() == AW_Countdown_Model_Countdown::STATUS_PAUSED) {
                    $countdown
                        ->setStatus(AW_Countdown_Model_Countdown::STATUS_STARTED)
                        ->save()
                    ;
                }
            } elseif ($countdown->getStatus() != AW_Countdown_Model_Countdown::STATUS_PAUSED) {
                $countdown
                    ->setStatus(AW_Countdown_Model_Countdown::STATUS_PAUSED)
                    ->save()
                ;
            }
        }
        return true;
    }
}
