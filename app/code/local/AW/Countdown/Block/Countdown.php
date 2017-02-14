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


class AW_Countdown_Block_Countdown extends Mage_Core_Block_Template
{
    protected $_appliedCountdown = null;
    protected $_currentProduct = null;
    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @return AW_Countdown_Model_Countdown|null
     */
    public function getAppliedCountdown()
    {
        return $this->_appliedCountdown;
    }

    public function getProduct()
    {
        $this->setData('product', Mage::registry('current_product'));
        if (!$this->getData('product')) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            if (!$product->getId()) {
                return null;
            }
            $this->setData('product', $product);
        }
        return $this->getData('product');
    }

    protected function _beforeToHtml()
    {
        if (null === $this->getProduct() && null === $this->getCountdownid()) {
            return $this;
        }

        if ($this->getCountdownid() != null) {
            $countDownModel = $this->getCountdownModel()->load($this->getCountdownid());
            if (null !== $countDownModel->getId()
                && $countDownModel->getAutomDisplay() == AW_Countdown_Model_Source_Automation::NO
                && $countDownModel->validateProductAttributesWidget($this->getProduct())
                && $this->helper('awcountdown/recurring')->canShow($countDownModel)
            ) {
                $this->_appliedCountdown = $countDownModel;
            }
        } else {
            $this->_appliedCountdown = $this->validateProduct();
        }

        if (null !== $this->_appliedCountdown) {
            return parent::_beforeToHtml();
        }
        return $this;
    }

    public function getDefaultTemplate()
    {
        return 'aw_countdown/blocks.phtml';
    }

    public function getTemplate()
    {
        if (null === $this->getData('template')) {
            $this->setData('template', $this->getDefaultTemplate());
        }
        return $this->getData('template');
    }

    public function getCountdownModel()
    {
        return Mage::getModel('awcountdown/countdown');
    }

    public function validateProduct()
    {
        $countDownModel = $this->helper('awcountdown')->getCountDownForProduct($this->getProduct());
        if (null !== $countDownModel) {
            return $countDownModel;
        }
        return null;
    }

    public function getFormat($block)
    {
        return $block->getShowFormat();
    }

    public function getDesign($block)
    {
        return $block->getDesign();
    }

    public function getTimeLeft(AW_Countdown_Model_Countdown $cowndown)
    {
        $date = $cowndown->getDateTo();
        $now = Mage::app()->getLocale()->date();
        $timeDiff = strtotime($date) - strtotime($now->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        return $timeDiff;
    }

    public function getCountdownHtml(AW_Countdown_Model_Countdown $countdown)
    {
        $template = $countdown->getTemplate();
        $title = $countdown->getBlockTitle();
        $timerHTML = $this->getLayout()->createBlock('awcountdown/timer')->toHtml();
        $content = str_replace('{{title}}', $title, $template);
        $content = str_replace('{{timer}}', $timerHTML . '<div class="clearer"></div>', $content);
        return $content;
    }

    public function getCacheKeyInfo() {
        $info = parent::getCacheKeyInfo();
        $info['countdown_id'] = $this->getCountdownid();
        return $info;
    }
}