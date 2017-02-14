<?php

class WP_AjaxShippingPriceCalculator_Model_Config
{
    const XML_PATH_ENABLED              = 'wp_ajaxshippingpricecalculator/general/enabled';
    const XML_PATH_POPUP_LINK           = 'wp_ajaxshippingpricecalculator/general/popup_link';
    const XML_PATH_USE_COUNTRY          = 'wp_ajaxshippingpricecalculator/general/use_country';
    const XML_PATH_USE_REGION           = 'wp_ajaxshippingpricecalculator/general/use_region';
    const XML_PATH_USE_CITY             = 'wp_ajaxshippingpricecalculator/general/use_city';
    const XML_PATH_USE_POSTCODE         = 'wp_ajaxshippingpricecalculator/general/use_postcode';
    const XML_PATH_USE_COUPON_CODE      = 'wp_ajaxshippingpricecalculator/general/use_coupon_code';
    const XML_PATH_USE_CART             = 'wp_ajaxshippingpricecalculator/general/use_cart';
    const XML_PATH_USE_CART_DEFAULT     = 'wp_ajaxshippingpricecalculator/general/use_cart_default';
    const XML_PATH_DEFAULT_COUNTRY      = 'shipping/origin/country_id';
    const XML_PATH_CONTROLLER_ACTIONS   = 'wp/ajaxshippingpricecalculator/controller_actions';
    const XML_PATH_BLOCK_POSITION       = 'wp_ajaxshippingpricecalculator/general/block_position';

    const BLOCK_POSITION_RIGHT              = 'right';
    const BLOCK_POSITION_LEFT               = 'left';
    const BLOCK_POSITION_SHORT_DESCRIPTION  = 'short_description';
    const BLOCK_POSITION_DESCRIPTION        = 'description';
    const BLOCK_POSITION_CUSTOM             = 'custom';

    const LAYOUT_HANDLE_LEFT                = 'wp_ajaxshippingpricecalculator_left';
    const LAYOUT_HANDLE_RIGHT               = 'wp_ajaxshippingpricecalculator_right';
    const LAYOUT_HANDLE_SHORT_DESCRIPTION   = 'wp_ajaxshippingpricecalculator_short_description';
    const LAYOUT_HANDLE_DESCRIPTION         = 'wp_ajaxshippingpricecalculator_description';
    const LAYOUT_HANDLE_CUSTOM_POSITION     = 'wp_ajaxshippingpricecalculator_view';

    public function useCountry()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_COUNTRY);
    }

    public function useRegion()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_REGION);
    }

    public function useCity()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CITY);
    }

    public function usePostcode()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_POSTCODE);
    }

    public function useCouponCode()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_COUPON_CODE);
    }

    public function useCart()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CART);
    }

    public function useCartDefault()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_USE_CART_DEFAULT);
    }

    public function getDefaultCountry()
    {
        return Mage::getStoreConfig(self::XML_PATH_DEFAULT_COUNTRY);
    }

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    public function isPopupLink()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_POPUP_LINK);
    }

    public function getBlockPosition()
    {
        return Mage::getStoreConfig(self::XML_PATH_BLOCK_POSITION);
    }

    public function getControllerActions()
    {
        $actions = array();
        foreach (Mage::getConfig()->getNode(self::XML_PATH_CONTROLLER_ACTIONS)->children() as $action => $node) {
            $actions[] = $action;
        }
        return $actions;
    }
}
