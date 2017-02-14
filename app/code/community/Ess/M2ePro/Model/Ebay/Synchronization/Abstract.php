<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_Abstract
    extends Ess_M2ePro_Model_Synchronization_Task
{
    //########################################

    /**
     * @return string
     */
    protected function getComponent()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    //########################################
}