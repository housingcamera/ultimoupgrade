<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Model_Region extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('amregions/region');
    }

    public function getCountries($regionId = null)
    {
        if(is_null($regionId)) {
            $regionId = $this->getId();
        }
        return $this->getResource()->getCountries($regionId);
    }

    public function setCountries($countriesIds, $regionId = null)
    {
        if(is_null($regionId)) {
            $regionId = $this->getId();
        }
        return $this->getResource()->setCountries($countriesIds, $regionId);
    }

    public function getCountriesText($maxLen = 200)
    {
        $countries = $this->getCountries();
        $countriesList = Mage::helper('amregions')->getCountriesAsHash();
        $text = array();
        $allLength = 0;
        foreach($countries as $countryCode) {
            if(!empty($countriesList[$countryCode])) {
                if($maxLen && $allLength+3 > $maxLen) {
                    $text[] = '...';
                    break;
                }
                $text[] = $countriesList[$countryCode];
                $allLength += strlen(utf8_decode($countriesList[$countryCode]))+2;
            }
        }

        return implode(', ', $text);
    }
}