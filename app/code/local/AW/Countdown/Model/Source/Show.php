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


class AW_Countdown_Model_Source_Show extends AW_Countdown_Model_Source_Abstract
{
    const DAYS = 'D';
    const HOURS = 'DH';
    const MINUTES = 'DHM';
    const SECONDS = 'DHMS';

    const DAYS_LABEL = 'Days only';
    const HOURS_LABEL = 'Days & Hours';
    const MINUTES_LABEL = 'Days, Hours, Minutes';
    const SECONDS_LABEL = 'Complete';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::DAYS, 'label' => $_helper->__(self::DAYS_LABEL)),
            array('value' => self::HOURS, 'label' => $_helper->__(self::HOURS_LABEL)),
            array('value' => self::MINUTES, 'label' => $_helper->__(self::MINUTES_LABEL)),
            array('value' => self::SECONDS, 'label' => $_helper->__(self::SECONDS_LABEL))
        );
    }

}
