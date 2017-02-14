<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $shippingCalculatedTemplateModel Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    private $shippingCalculatedTemplateModel = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return $this
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance
     * @return $this
     */
    public function setShippingCalculatedTemplate(Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated $instance)
    {
        $this->shippingCalculatedTemplateModel = $instance;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated
     */
    public function getShippingCalculatedTemplate()
    {
        return $this->shippingCalculatedTemplateModel;
    }

    //########################################

    /**
     * @return string
     */
    public function getPackageSize()
    {
        $src = $this->getShippingCalculatedTemplate()->getPackageSizeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::PACKAGE_SIZE_CUSTOM_ATTRIBUTE) {
            return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return $src['value'];
    }

    /**
     * @return array
     */
    public function getDimension()
    {
        $src = $this->getShippingCalculatedTemplate()->getDimensionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::DIMENSION_NONE) {
            return array();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::DIMENSION_CUSTOM_ATTRIBUTE) {

            $widthValue = str_replace(',','.',$this->getMagentoProduct()->getAttributeValue($src['width_attribute']));
            $lengthValue = str_replace(',','.',$this->getMagentoProduct()->getAttributeValue($src['length_attribute']));
            $depthValue = str_replace(',','.',$this->getMagentoProduct()->getAttributeValue($src['depth_attribute']));

            return array(
                'width' => $widthValue,
                'length' => $lengthValue,
                'depth' => $depthValue
            );
        }

        return array(
            'width'  => $src['width_value'],
            'length' => $src['length_value'],
            'depth'  => $src['depth_value']
        );
    }

    /**
     * @return array
     */
    public function getWeight()
    {
        $src = $this->getShippingCalculatedTemplate()->getWeightSource();

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_CUSTOM_ATTRIBUTE) {

            $weightValue = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $weightValue = str_replace(',', '.', $weightValue);
            $weightArray = explode('.', $weightValue);

            $minor = $major = 0;
            if (count($weightArray) >= 2) {
                list($major, $minor) = $weightArray;

                if ($minor > 0 && $this->getShippingCalculatedTemplate()->isMeasurementSystemEnglish()) {
                    $minor = ($minor / pow(10, strlen($minor))) * 16;
                    $minor = ceil($minor);
                    if ($minor == 16) {
                        $major += 1;
                        $minor = 0;
                    }
                }

                if ($minor > 0 && $this->getShippingCalculatedTemplate()->isMeasurementSystemMetric()) {
                    $minor = ($minor / pow(10, strlen($minor))) * 1000;
                    $minor = ceil($minor);
                    if ($minor == 1000) {
                        $major += 1;
                        $minor = 0;
                    }
                }

                $minor < 0 && $minor = 0;
            } else {
                $major = (int)$weightValue;
            }

            return array(
                'minor' => (float)$minor,
                'major' => (int)$major
            );
        }

        if ($src['mode'] == Ess_M2ePro_Model_Ebay_Template_Shipping_Calculated::WEIGHT_NONE) {
            return array(
                'minor' => 0,
                'major' => 0
            );
        }

        return array(
            'minor' => (float)$src['minor'],
            'major' => (int)$src['major']
        );
    }

    //########################################
}