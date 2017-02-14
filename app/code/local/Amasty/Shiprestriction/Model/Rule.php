<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */ 
class Amasty_Shiprestriction_Model_Rule extends Mage_Rule_Model_Rule
{
    const ALL_ORDERS = 0;
    const BACKORDERS_ONLY = 1;
    const NON_BACKORDERS = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('amshiprestriction/rule');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('amshiprestriction/rule_condition_combine');
    }
    
    public function restrict($rate)
    {
        if (false !== strpos($this->getCarriers(), ',' . $rate->getCarrier(). ','))
            return true;
        
        $m = $this->getMethods();    
        $m = str_replace("\r\n", "\n", $m);
        $m = str_replace("\r", "\n", $m);
        $m = str_replace("(", "\(", $m);
        $m = str_replace(")", "\)", $m);
        $m = trim($m);
        if (!$m){
            return false;
        }
        
        $m = array_unique(explode("\n", $m));
        foreach ($m as $pattern){
            $pattern = explode('::', $pattern);
            $patternCount = count($pattern);

            $rateCarrier = $rate->getCarrier();
            $rateMethod = $rate->getMethodTitle();

            if ($patternCount == 1) { //method compare
                $method = trim($pattern[0]);
                $posMethod = stripos($rateMethod, $method);
                if ($posMethod !== false) {
                    return true;
                }
            } elseif ($patternCount >= 3) { //carrier::regular_expression::regex
                if ($pattern[2] == 'regex') {
                    $carrier = $pattern[0];
                    $carrierPattern = '/' . preg_quote(trim($carrier)) . '/i';
                    $pattern = $pattern[1];
                    if (preg_match($pattern, $rateMethod) && preg_match($carrierPattern, $rateCarrier)){
                        return true;
                    }
                } elseif ($pattern[2] == 'eval') {

                } else {
                    $patternCount = 2;
                }
            }

            if ($patternCount == 2) { //carrier::method
                $carrier = $pattern[0];
                $posCarrier = stripos($rateCarrier, $carrier);
                $method = $pattern[1];
                $posMethod = stripos($rateMethod, $method);
                if ($posCarrier !== false && $posMethod !== false){
                    return true;
                }
            }

        }
        return false;
    }
    
    public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }
    
    protected function _afterSave()
    {
        //Saving attributes used in rule
        $ruleProductAttributes = array_merge(
            $this->_getUsedAttributes($this->getConditionsSerialized()),
            $this->_getUsedAttributes($this->getActionsSerialized())
        );
        if (count($ruleProductAttributes)) {
            $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
        } 
        
        return parent::_afterSave(); 
    } 
    
    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = array();
        
        $pattern = '~s:32:"salesrule/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = array();
        if (preg_match_all($pattern, $serializedString, $matches)){
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        
        return $result;
    }

    protected function _setWebsiteIds(){
        $websites = array();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $websites[$website->getId()] = $website->getId();
                }
            }
        }

        $this->setOrigData('website_ids', $websites);
    }

    protected function _beforeSave(){
        $this->_setWebsiteIds();
        return parent::_beforeSave();
    }

    protected function _beforeDelete(){
        $this->_setWebsiteIds();
        return parent::_beforeDelete();
    }
     
}