<?php

class WP_AjaxShippingPriceCalculator_Block_Form_Customposition extends WP_AjaxShippingPriceCalculator_Block_Form
{
    public function setOutside($value = false)
    {
        self::$_isCustomOutside = $value;
        return $this;
    }

    protected function _toHtml()
    {
        if (Mage::getStoreConfig('wp_ajaxshippingpricecalculator/general/block_position') == WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_CUSTOM) {

            if (!$this->getTemplate()) {
                $this->setTemplate('webandpeople/ajaxshippingpricecalculator/view.phtml');
            }

            if (!$this->getId()) {
                $this->setId('wp_ajax_shipping_price_calculator');
            }

            return parent::_toHtml();
        }
        return '';
    }
}
