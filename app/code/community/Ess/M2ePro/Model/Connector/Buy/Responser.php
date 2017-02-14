<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Connector_Buy_Responser extends Ess_M2ePro_Model_Connector_Responser
{
    private $cachedParamsObjects = array();

    //########################################

    protected function getObjectByParam($model, $idKey)
    {
        if (isset($this->cachedParamsObjects[$idKey])) {
            return $this->cachedParamsObjects[$idKey];
        }

        if (!isset($this->params[$idKey])) {
            return NULL;
        }

        $this->cachedParamsObjects[$idKey] = Mage::helper('M2ePro/Component_Buy')
                    ->getObject($model,$this->params[$idKey]);

        return $this->cachedParamsObjects[$idKey];
    }

    //########################################
}