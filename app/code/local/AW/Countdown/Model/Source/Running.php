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


class AW_Countdown_Model_Source_Running extends AW_Countdown_Model_Source_Abstract
{
    const PENDING_LABEL = 'Pending';
    const STARTED_LABEL = 'Started';
    const ENDED_LABEL = 'Ended';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            AW_Countdown_Model_Countdown::STATUS_PENDING => $_helper->__(self::PENDING_LABEL),
            AW_Countdown_Model_Countdown::STATUS_STARTED => $_helper->__(self::STARTED_LABEL),
            AW_Countdown_Model_Countdown::STATUS_ENDED   => $_helper->__(self::ENDED_LABEL)
        );
    }

}
