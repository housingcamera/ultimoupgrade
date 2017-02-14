<?php

abstract class WP_AjaxShippingPriceCalculator_Block_Abstract extends Mage_Catalog_Block_Product_Abstract
{
    protected static $_isCustomOutside = false;

    protected $_calculator = null;
    protected $_config = null;
    protected $_session = null;
    protected $_carriers = null;

    public function getCalculator()
    {
        if ($this->_calculator === null)
            $this->_calculator = Mage::getSingleton('wp_ajaxshippingpricecalculator/calculator');
        return $this->_calculator;
    }

    public function getConfig()
    {
        if ($this->_config === null)
            $this->_config = Mage::getSingleton('wp_ajaxshippingpricecalculator/config');
        return $this->_config;
    }

    public function getSession()
    {
        if ($this->_session === null)
            $this->_session = Mage::getSingleton('wp_ajaxshippingpricecalculator/session');
        return $this->_session;
    }

    public function isEnabled()
    {
        return $this->getConfig()->isEnabled() && !$this->getProduct()->isVirtual();
    }

    public function isPopupLink()
    {
        return $this->getConfig()->isPopupLink();
    }

    public function isOutsideBlock()
    {
        $blockPosition = $this->getConfig()->getBlockPosition();
        switch ($blockPosition) {
            case WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_LEFT:
            case WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_RIGHT:
                return true;
                break;
            case WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_CUSTOM:
                return self::$_isCustomOutside;
                break;
        }
        return;
    }
}
