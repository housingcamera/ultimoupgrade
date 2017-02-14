<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_PhysicalUnit
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $parentListingProduct = NULL;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getParentListingProduct()
    {
        if (is_null($this->parentListingProduct)) {
            $parentListingProductId = $this->getVariationManager()->getVariationParentId();
            $this->parentListingProduct = Mage::helper('M2ePro/Component_Amazon')
                                                    ->getObject('Listing_Product',$parentListingProductId);
        }

        return $this->parentListingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonParentListingProduct()
    {
        return $this->getParentListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent
     */
    public function getParentTypeModel()
    {
        return $this->getAmazonParentListingProduct()->getVariationManager()->getTypeModel();
    }

    //########################################

    /**
     * @return array|mixed|null
     */
    public function getRealProductOptions()
    {
        $productOptions = $this->getProductOptions();

        $virtualProductAttributes = $this->getParentTypeModel()->getVirtualProductAttributes();
        if (empty($virtualProductAttributes)) {
            return $productOptions;
        }

        $realProductOptions = array();
        foreach ($productOptions as $attribute => $value) {
            if (isset($virtualProductAttributes[$attribute])) {
                continue;
            }

            $realProductOptions[$attribute] = $value;
        }

        return $realProductOptions;
    }

    //########################################

    /**
     * @return bool
     */
    public function isVariationChannelMatched()
    {
        return (bool)$this->getListingProduct()->getData('is_variation_channel_matched');
    }

    //########################################

    /**
     * @param array $options
     */
    public function setChannelVariation(array $options)
    {
        $this->unsetChannelVariation();

        $this->setChannelOptions($options, false);
        $this->getListingProduct()->setData('is_variation_channel_matched', 1);

        $this->getListingProduct()->save();
    }

    public function unsetChannelVariation()
    {
        if (!$this->isVariationChannelMatched()) {
            return;
        }

        $this->setChannelOptions(array(), false);
        $this->getListingProduct()->setData('is_variation_channel_matched', 0);

        $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @return mixed|null
     */
    public function getChannelOptions()
    {
        return $this->getListingProduct()->getSetting('additional_data', 'variation_channel_options', array());
    }

    /**
     * @return array|mixed|null
     */
    public function getRealChannelOptions()
    {
        $channelOptions = $this->getChannelOptions();

        $virtualChannelAttributes = $this->getParentTypeModel()->getVirtualChannelAttributes();
        if (empty($virtualChannelAttributes)) {
            return $channelOptions;
        }

        $realChannelOptions = array();
        foreach ($channelOptions as $attribute => $value) {
            if (isset($virtualChannelAttributes[$attribute])) {
                continue;
            }

            $realChannelOptions[$attribute] = $value;
        }

        return $realChannelOptions;
    }

    // ---------------------------------------

    private function setChannelOptions(array $options, $save = true)
    {
        $this->getListingProduct()->setSetting('additional_data', 'variation_channel_options', $options);
        $save && $this->getListingProduct()->save();
    }

    //########################################

    /**
     * @param array $matchedAttributes
     * @param bool
     */
    public function setCorrectMatchedAttributes(array $matchedAttributes, $save = true)
    {
        $this->getListingProduct()->setSetting(
            'additional_data', 'variation_correct_matched_attributes', $matchedAttributes
        );
        $save && $this->getListingProduct()->save();
    }

    /**
     * @return mixed
     */
    public function getCorrectMatchedAttributes()
    {
        return $this->getListingProduct()->getSetting(
            'additional_data', 'variation_correct_matched_attributes', array()
        );
    }

    // ---------------------------------------

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     */
    public function isActualMatchedAttributes()
    {
        $correctMatchedAttributes = $this->getCorrectMatchedAttributes();
        if (empty($correctMatchedAttributes)) {
            return true;
        }

        $parentTypeModel = $this->getAmazonParentListingProduct()->getVariationManager()->getTypeModel();
        $currentMatchedAttributes = $parentTypeModel->getMatchedAttributes();
        if (empty($currentMatchedAttributes)) {
            return false;
        }

        return count(array_diff_assoc($correctMatchedAttributes, $currentMatchedAttributes)) <= 0;
    }

    //########################################

    public function clearTypeData()
    {
        parent::clearTypeData();

        $this->unsetChannelVariation();

        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_channel_options']);
        unset($additionalData['variation_correct_matched_attributes']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    //########################################
}