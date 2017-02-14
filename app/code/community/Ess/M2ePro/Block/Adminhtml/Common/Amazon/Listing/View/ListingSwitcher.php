<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_View_ListingSwitcher
    extends Ess_M2ePro_Block_Adminhtml_Listing_View_ListingSwitcher_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block

        $this->setAddListingUrl('*/adminhtml_common_listing_create/index/step/1/component/amazon/');
    }

    //########################################
}