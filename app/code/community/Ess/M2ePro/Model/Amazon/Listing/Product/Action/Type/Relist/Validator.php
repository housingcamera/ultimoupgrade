<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Relist_Validator
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        if (!$this->validateBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if ($this->getAmazonListingProduct()->isAfnChannel()) {

            // M2ePro_TRANSLATIONS
            // Relist Action for FBA Items is impossible as their Quantity is unknown. You can run Revise Action for such Items, but the Quantity value will be ignored.
            $this->addMessage('Relist Action for FBA Items is impossible as their Quantity is unknown. You can run
            Revise Action for such Items, but the Quantity value will be ignored.');

            return false;
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->getListingProduct()->isStopped() || !$this->getListingProduct()->isRelistable()) {

            // M2ePro_TRANSLATIONS
            // The Item either is Listed, or not Listed yet or not available
            $this->addMessage(
                'The Item either is Listed, or not Listed yet or not available'
            );

            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }

    //########################################
}