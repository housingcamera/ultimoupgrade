<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_listCountriesHash = null;

    public function getCountries()
    {
        $list =  Mage::getModel('adminhtml/system_config_source_country')
            ->toOptionArray();

        unset($list[0]);
        return $list;
    }

    public function getCountriesAsHash()
    {
        if(is_null($this->_listCountriesHash)) {
            foreach($this->getCountries() as $country) {
                $this->_listCountriesHash[$country['value']] = $country['label'];
            }
        }

        return $this->_listCountriesHash;
    }
}