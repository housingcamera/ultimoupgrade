<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Orders_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Buy_Responser
{
    //########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['orders'])) {
            return false;
        }

        return true;
    }

    protected function prepareResponseData($response)
    {
        return $response['orders'];
    }

    //########################################
}