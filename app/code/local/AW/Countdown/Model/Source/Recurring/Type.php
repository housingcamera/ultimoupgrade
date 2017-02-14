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


class AW_Countdown_Model_Source_Recurring_Type
{
    const EVERY_DAY_LABEL         = 'Every day';
    const EVERY_X_DAYS_LABEL      = 'Every X day(s)';
    const EVERY_DEFINED_DAY_LABEL = 'Every defined day';

    const EVERY_DAY_VALUE         = 1;
    const EVERY_X_DAYS_VALUE      = 2;
    const EVERY_DEFINED_DAY_VALUE = 3;

    public function toOptionArray()
    {
        return array(
            ''                             => Mage::helper('awcountdown')->__('Please choose the recurring type...'),
            self::EVERY_DAY_VALUE          => Mage::helper('awcountdown')->__(self::EVERY_DAY_LABEL),
            self::EVERY_X_DAYS_VALUE       => Mage::helper('awcountdown')->__(self::EVERY_X_DAYS_LABEL),
            self::EVERY_DEFINED_DAY_VALUE  => Mage::helper('awcountdown')->__(self::EVERY_DEFINED_DAY_LABEL),
        );
    }
}