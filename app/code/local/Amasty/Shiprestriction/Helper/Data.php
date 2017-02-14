<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */ 
class Amasty_Shiprestriction_Helper_Data extends Mage_Core_Helper_Abstract
{

    const STORAGE_KEY = 'amshiprestriction_products';

    public function getAllGroups()
    {
        $customerGroups = Mage::getResourceModel('customer/group_collection')
            ->load()->toOptionArray();

        $found = false;
        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array('value'=>0, 'label'=>Mage::helper('salesrule')->__('NOT LOGGED IN')));
        } 
        
        return $customerGroups;
    }
    
    public function getAllCarriers()
    {
        $carriers = array();
        foreach (Mage::getStoreConfig('carriers') as $code=>$config){
            if (!empty($config['title'])){
                $carriers[] = array('value'=>$code, 'label'=>$config['title'] . ' [' . $code . ']');
            }
        }  
        return $carriers;      
    }
    
    public function getStatuses()
    {
        return array(
                '1' => Mage::helper('salesrule')->__('Active'),
                '0' => Mage::helper('salesrule')->__('Inactive'),
            );       
    }
    
    public function getAllDays()
    {
        return array(
            array('value'=>'7', 'label' => $this->__('Sunday')),
            array('value'=>'1', 'label' => $this->__('Monday')),
            array('value'=>'2', 'label' => $this->__('Tuesday')),
            array('value'=>'3', 'label' => $this->__('Wednesday')),
            array('value'=>'4', 'label' => $this->__('Thursday')),
            array('value'=>'5', 'label' => $this->__('Friday')),
            array('value'=>'6', 'label' => $this->__('Saturday')),
        );             
    }

    public function getAllRules()
    {
        $rules =  array(
            array('value'=>'0', 'label' => $this->__('')));

        $rulesCollection = Mage::getResourceModel('salesrule/rule_collection')->load();

        foreach ($rulesCollection as $rule){
            $rules[] = array('value'=>$rule->getRuleId(), 'label' => $rule->getName());
        }

        return $rules;
    }

    public function parseMessage($message, $products)
    {
        $allProducts = implode(', ', $products);
        $lastProduct = end($products);
        $newMessage = str_replace('{all-products}', $allProducts, $message);
        $newMessage = str_replace('{last-product}', $lastProduct, $newMessage);

        return $newMessage;
    }

    public function clearProducts()
    {
        Mage::unregister(self::STORAGE_KEY);
    }

    /**
     * @param $name string product name
     */
    public function addProduct($name)
    {
        $oldNames = $this->getProducts();
        if (!in_array($name, $oldNames)) {
            $oldNames[] = $name;
        }
        $this->_saveProducts($oldNames);

        return $this;
    }

    public function getProducts()
    {
        $names = Mage::registry(self::STORAGE_KEY);
        if (empty($names)) {
            $names = array();
        }

        return $names;
    }

    protected function _saveProducts($names)
    {
        Mage::unregister(self::STORAGE_KEY);
        Mage::register(self::STORAGE_KEY, $names);

        return $this;
    }

    public function getAllTimes()
    {
        $timeArray = array();
        $timeArray[0] = 'Please select...';

        for($i = 0 ; $i < 24 ; $i++){
            for($j = 0; $j < 60 ; $j=$j+15){
                $timeStamp = $i.':'.$j;
                $timeFormat = date ('H:i',strtotime($timeStamp));
                $timeArray[$i * 100 + $j + 1] = $timeFormat;
            }
        }
        return $timeArray;
    }

}