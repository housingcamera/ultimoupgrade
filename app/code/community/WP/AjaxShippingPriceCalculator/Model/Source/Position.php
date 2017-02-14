<?php

class WP_AjaxShippingPriceCalculator_Model_Source_Position
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_LEFT,
                'label' => Mage::helper('wp_ajaxshippingpricecalculator')->__('Left Column')
            ),
            array(
                'value' => WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_RIGHT,
                'label' => Mage::helper('wp_ajaxshippingpricecalculator')->__('Right Column')
            ),
            array(
                'value' => WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_SHORT_DESCRIPTION,
                'label' => Mage::helper('wp_ajaxshippingpricecalculator')->__('After Product Short Description')
            ),
            array(
                'value' => WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_DESCRIPTION,
                'label' => Mage::helper('wp_ajaxshippingpricecalculator')->__('Before Product Description')
            ),
            array(
                'value' => WP_AjaxShippingPriceCalculator_Model_Config::BLOCK_POSITION_CUSTOM,
                'label' => Mage::helper('wp_ajaxshippingpricecalculator')->__('Custom Position')
            ),
        );
    }
}
