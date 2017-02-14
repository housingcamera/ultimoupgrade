<?php

class WP_AjaxShippingPriceCalculator_Model_Observer
{
    protected $_config = null;

    public function getConfig()
    {
        if ($this->_config === null)
            $this->_config = Mage::getSingleton('wp_ajaxshippingpricecalculator/config');
        return $this->_config;
    }

    public function observeLayoutHandleInitialization(Varien_Event_Observer $observer)
    {
        /* @var $controllerAction Mage_Core_Controller_Varien_Action */
        $controllerAction = $observer->getEvent()->getAction();
        $fullActionName = $controllerAction->getFullActionName();
        if ($this->getConfig()->isEnabled() && in_array($fullActionName, $this->getConfig()->getControllerActions())) {
            if ($this->getConfig()->getBlockPosition() === WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_LEFT) {
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    WP_AjaxShippingPriceCalculator_Model_Config::LAYOUT_HANDLE_LEFT
                );
            } elseif ($this->getConfig()->getBlockPosition() === WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_RIGHT) {
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    WP_AjaxShippingPriceCalculator_Model_Config::LAYOUT_HANDLE_RIGHT
                );
            } elseif ($this->getConfig()->getBlockPosition() === WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_SHORT_DESCRIPTION) {
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    WP_AjaxShippingPriceCalculator_Model_Config::LAYOUT_HANDLE_SHORT_DESCRIPTION
                );
            } elseif ($this->getConfig()->getBlockPosition() === WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_DESCRIPTION) {
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    WP_AjaxShippingPriceCalculator_Model_Config::LAYOUT_HANDLE_DESCRIPTION
                );
            } elseif ($this->getConfig()->getBlockPosition() === WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_CUSTOM) {
                $controllerAction->getLayout()->getUpdate()->addHandle(
                    WP_AjaxShippingPriceCalculator_Model_Config::LAYOUT_HANDLE_CUSTOM_POSITION
                );
            }
        }
    }
}
