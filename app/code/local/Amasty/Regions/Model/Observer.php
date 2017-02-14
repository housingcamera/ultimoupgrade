<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Model_Observer
{
    public function handleNewConditions($observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)) {
            $cond = array();
        }

        $cond[] = array(
            'value' => 'amregions/rule_condition_region',
            'label' => Mage::helper('amregions')->__('Shipping Area')
        );

        $transport->setConditions($cond);

        return $this;
    }
}