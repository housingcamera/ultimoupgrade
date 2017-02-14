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


class AW_Countdown_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEditAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('awcountdown/countdown/new');
    }

    public function isViewAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('awcountdown/countdown/list');
    }

    public function getExtDisabled()
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_Countdown');
    }

    public function getCustomerGroupId()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return 0;
    }

    public static function isNewRules()
    {
        if ((version_compare(Mage::getVersion(), '1.7.0.0', '>=')
                && Mage::helper('awall/versions')->getPlatform() == AW_ALL_Helper_Versions::CE_PLATFORM)
            || (version_compare(Mage::getVersion(), '1.12.0.0', '>=')
                && Mage::helper('awall/versions')->getPlatform() == AW_ALL_Helper_Versions::EE_PLATFORM)
        ) {
            return true;
        }
        return false;
    }

    public function getCountDownForProduct($product)
    {
        $validationModel = Mage::getModel('awcountdown/countdown');
        $countDownModel = $validationModel->validateProductAttributes($product);
        if (null !== $countDownModel->getId() && Mage::helper('awcountdown/recurring')->canShow($countDownModel)) {
            return $countDownModel;
        }
        return null;
    }
}
