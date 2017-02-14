<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_General
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if ($this->getAmazonListingProduct()->isAfnChannel()) {

            // M2ePro_TRANSLATIONS
            // List Action for FBA Items is impossible as their Quantity is unknown. You can run Revise Action for such Items, but the Quantity value will be ignored.
            $this->addMessage('List Action for FBA Items is impossible as their Quantity is unknown. You can run Revise
            Action for such Items, but the Quantity value will be ignored.');

            return false;
        }

        if (!$this->getListingProduct()->isNotListed() || !$this->getListingProduct()->isListable()) {

            // M2ePro_TRANSLATIONS
            // Item is already on Amazon, or not available.
            $this->addMessage('Item is already on Amazon, or not available.');

            return false;
        }

        if ($this->getVariationManager()->isLogicalUnit()) {
            return true;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        $condition = $this->getAmazonListingProduct()->getListingSource()->getCondition();
        if (empty($condition)) {
            // M2ePro_TRANSLATIONS
            // You cannot list this Product because the Item Condition is not specified. You can set the Condition in the Selling Settings of the Listing.
            $this->addMessage('You cannot list this Product because the Item Condition is not specified.
                               You can set the Condition in the Selling Settings of the Listing.');
            return false;
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationChildType() && !$this->validateChildRequirements()) {
            return false;
        }

        return true;
    }

    //########################################

    private function validateChildRequirements()
    {
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $parentAmazonListingProduct */
        $parentAmazonListingProduct = $this->getVariationManager()
            ->getTypeModel()
            ->getParentListingProduct()
            ->getChildObject();

        if (!$parentAmazonListingProduct->getGeneralId()) {

// M2ePro_TRANSLATIONS
// You cannot list this Product because for managing Child Products, the respective Parent Product needs to be connected to Amazon Parent Product. Please link your Magento Parent Product to Amazon Parent Product and try again.
            $this->addMessage('You cannot list this Product because for managing Child Products,
                              the respective Parent Product needs to be connected to Amazon Parent Product.
                              Please link your Magento Parent Product to Amazon Parent Product and try again.');
            return false;
        }

        if (!$this->getAmazonListingProduct()->isGeneralIdOwner() &&
            !$this->getAmazonListingProduct()->getGeneralId()
        ) {
// M2ePro_TRANSLATIONS
// You cannot list this Product because it has to be whether linked to existing Amazon Product or to be ready for creation of the new ASIN.
            $this->addMessage('You cannot list this Product because it has to be whether linked to
                              existing Amazon Product or to be ready for creation of the new ASIN/ISBN.');
            return false;
        }

        return true;
    }

    //########################################
}