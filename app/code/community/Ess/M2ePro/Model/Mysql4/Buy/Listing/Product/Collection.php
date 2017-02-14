<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Buy_Listing_Product_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Component_Child_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Product');
    }

    //########################################
}