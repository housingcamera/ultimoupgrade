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


class AW_Countdown_Helper_Recurring extends Mage_Core_Helper_Abstract
{
    public function getDateTo(AW_Countdown_Model_Countdown $countdown)
    {
        $date = Mage::app()->getLocale()->date();
        switch ($countdown->getRecurringType()) {
            case AW_Countdown_Model_Source_Recurring_Type::EVERY_DAY_VALUE :
                $recurringTimeTo = explode(',', $countdown->getRecurringEverydayTimeTo());
                $date->setHour($recurringTimeTo[0]);
                $date->setMinute($recurringTimeTo[1]);
                $date->setSecond($recurringTimeTo[2]);
                break;
            case AW_Countdown_Model_Source_Recurring_Type::EVERY_X_DAYS_VALUE :
                $recurringTimeTo = explode(',', $countdown->getRecurringXdayTimeTo());
                $range = (int)$countdown->getRecurringXdayRange();
                $dateFrom = $countdown->getDateFrom();
                if (empty($dateFrom)) {
                    $dateFrom = null;
                }
                $startDay = new Zend_Date(strtotime($dateFrom), Zend_Date::TIMESTAMP);
                $nowDate = clone $date;
                $diff = $nowDate->toString(Zend_Date::TIMESTAMP) - $startDay->toString(Zend_Date::TIMESTAMP);
                $diffDays = floor($diff/60/60/24);
                if ($diffDays == 0) {
                    $diffDays = $range;
                }
                if ($diffDays > 0 && $diffDays < $range) {
                    $diffDays = $range - $diffDays;
                }
                while ($diffDays > $range) {
                    $diffDays = $diffDays - $range;
                }
                $date->addDay($diffDays);
                $date->setHour($recurringTimeTo[0]);
                $date->setMinute($recurringTimeTo[1]);
                $date->setSecond($recurringTimeTo[2]);
                break;
            case AW_Countdown_Model_Source_Recurring_Type::EVERY_DEFINED_DAY_VALUE :
                $endDate = clone $date;
                $diffDays = -1;
                while ($diffDays <= -1) {
                    $recurringDefinedDay = (int)$countdown->getRecurringDefinedDay();
                    if ($recurringDefinedDay > (int)$endDate->toString(Zend_Date::MONTH_DAYS)) {
                        $recurringDefinedDay = (int)$endDate->toString(Zend_Date::MONTH_DAYS);
                    }
                    $endDate->setDay($recurringDefinedDay);
                    if ((int)$date->toString(Zend_Date::DAY) == $recurringDefinedDay) {
                        $diffDays = 0;
                    } else {
                        $checkDate = clone $endDate;
                        $diff = $checkDate->sub($date);
                        if ($diff instanceof Zend_Date) {
                            $diff = $diff->toValue();
                        }
                        $diffDays = floor($diff/60/60/24);
                        if ($diffDays <= -1) {
                            $endDate->addMonth(1);
                        }
                    }
                }
                $date->addDay($diffDays);
                $recurringTimeTo = explode(',', $countdown->getRecurringDefinedTimeTo());
                $date->setHour($recurringTimeTo[0]);
                $date->setMinute($recurringTimeTo[1]);
                $date->setSecond($recurringTimeTo[2]);
                break;
        }
        return $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
    }

    public function canShow(AW_Countdown_Model_Countdown $countdown)
    {
        if ($countdown->getRecurringEnabled()
            && $countdown->getRecurringType() == AW_Countdown_Model_Source_Recurring_Type::EVERY_DAY_VALUE
        ) {
            $date = Mage::app()->getLocale()->date();
            $time = $date->toString(Zend_Date::HOUR) * 3600 + $date->toString(Zend_Date::MINUTE) * 60 + $date->toString(Zend_Date::SECOND);
            $recurringTimeFrom = explode(',', $countdown->getRecurringEverydayTimeFrom());
            $recurringTimeFrom = $recurringTimeFrom[0] * 3600 + $recurringTimeFrom[1] * 60 + $recurringTimeFrom[2];
            $recurringTimeTo = explode(',', $countdown->getRecurringEverydayTimeTo());
            $recurringTimeTo = $recurringTimeTo[0] * 3600 + $recurringTimeTo[1] * 60 + $recurringTimeTo[2];
            if( is_array($countdown->getRecurringEverydayWeekdays())
                && in_array($date->get(Zend_Date::WEEKDAY_DIGIT), $countdown->getRecurringEverydayWeekdays())
                && $time >= $recurringTimeFrom && $time <= $recurringTimeTo
            ) {
                return true;
            }
            return false;
        }
        return true;
    }
}