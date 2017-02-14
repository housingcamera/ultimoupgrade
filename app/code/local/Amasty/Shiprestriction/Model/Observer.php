<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */
class Amasty_Shiprestriction_Model_Observer
{
    protected $_allRules = null;
    
    public function restrictRates($observer) 
    {
        $request = $observer->getRequest();
        $result  = $observer->getResult();

        $rates = $result->getAllRates();
        if (!count($rates)){
            return $this;
        }
            
        $rules = $this->_getRestrictionRules($request);
        if (!count($rules)){
             return $this;
        }
        
        $result->reset();
        
        $isEmptyResult = true;
        $lastError     = Mage::helper('amshiprestriction')->__('Sorry, no shipping quotes are available for the selected products and destination');
        $lastRate      = null;
        $isRestrict    = false;

        foreach ($rates as $rate){
            $isValid = true;
            foreach ($rules as $rule){
                if ($rule->restrict($rate)){
                    $lastRate  = $rate;
                    $lastError = $rule->getMessage();
                    $isValid   = false;
                    $isRestrict= true;
                    break;
                }
            }
            if ($isValid){
                $result->append($rate);
                $isEmptyResult = false;                    
            }
        }

        $isShowMessage = Mage::getStoreConfig('amshiprestriction/general/error_message');
        if ($isEmptyResult || ($isShowMessage && $isRestrict)){
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($lastRate->getCarrier());
            $error->setCarrierTitle($lastRate->getMethodTitle());
            $error->setErrorMessage($lastError);

            $result->append($error);
        }
        
        return $this;
    }
   
    protected function _getRestrictionRules($request)
    {
        $all = $request->getAllItems();
        if (!$all){
            return array();
        }
        $firstItem = current($all);
        $address = $firstItem->getAddress();
        if (!$address){
            $quote = $firstItem->getQuote();     
            if (!$quote) { return array(); } // we need it for true order editor

            $address = $quote->getShippingAddress(); 
        }
        $address->setItemsToValidateRestrictions($request->getAllItems());
        
       
        //multishipping optimization
        if (is_null($this->_allRules)){
            $this->_allRules = Mage::getModel('amshiprestriction/rule')
                ->getCollection()
                ->addAddressFilter($address)
            ;
            if ($this->_isAdmin()){
                $this->_allRules->addFieldToFilter('for_admin', 1);
            }                
            
            $this->_allRules->load();
            foreach ($this->_allRules as $rule){
                $rule->afterLoad(); 
            }                
        }
        
        $hasBackOrders = false;
        foreach ($request->getAllItems() as $item){
            if ($item->getBackorders() > 0 ){
                $hasBackOrders = true;
                break;
            }
        }

	// remember old                 
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();
        // set new
        $this->_modifySubtotal($address);

        /** @var Amasty_Shiprestriction_Helper_Data $hlp */
        $hlp =  Mage::helper('amshiprestriction');

        $validRules = array();
        foreach ($this->_allRules as $rule) {
            $hlp->clearProducts();

            $validBackOrder = true;
            switch ($rule->getOutOfStock()) {
                case Amasty_Shiprestriction_Model_Rule::BACKORDERS_ONLY:
                    $validBackOrder = $hasBackOrders ? true : false;
                    break;
                case Amasty_Shiprestriction_Model_Rule::NON_BACKORDERS:
                    $validBackOrder = $hasBackOrders ? false : true;
                    break;
            }

            if ($validBackOrder
                && $rule->validate($address)
                && $this->isCouponValid($request, $rule)
                && !$this->isCouponValid($request, $rule, true)
            ){
                // remember used products
                $newMessage = $hlp->parseMessage($rule->getMessage(), $hlp->getProducts());
                $rule->setMessage($newMessage);

                $validRules[] = $rule;
            }
        }

        // restore
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);
        
        return $validRules;                
    }

    public function isCouponValid($request, $rule, $isDisable = false)
    {
        if (!$isDisable) {
            $code = $rule->getCoupon();
            $discountId = $rule->getDiscountId();
        } else {
            $code = $rule->getCouponDisable();
            $discountId = $rule->getDiscountIdDisable();
        }
        $actualCouponCode  = trim(strtolower($code));
        $actualDiscountId  = intVal($discountId);

        if (!$actualCouponCode && !$actualDiscountId) {
            if (!$isDisable) {
                return true;
            } else {
                return false;
            }
        }

        $providedCouponCodes = $this->getCouponCodes($request);

        if ($actualCouponCode){
            return (in_array($actualCouponCode, $providedCouponCodes));
        }

        if ($actualDiscountId){
            foreach ($providedCouponCodes as $code){
                $couponModel         = Mage::getModel('salesrule/coupon')->load($code, 'code');
                $providedDiscountId  = $couponModel->getRuleId();

                if ($providedDiscountId == $actualDiscountId){
                    return true;
                }
                $couponModel = null;
            }

        }

        return false;
    }

    public function getCouponCodes($request)
    {
        if (!count($request->getAllItems()))
            return array();

        $firstItem = current($request->getAllItems());
        $codes = trim(strtolower($firstItem->getQuote()->getCouponCode()));

        if (!$codes)
            return array();

        $providedCouponCodes = explode(",",$codes);

        foreach ($providedCouponCodes as $key => $code){
            $providedCouponCodes[$key] = trim($code);
        }

        return $providedCouponCodes;

    }
    
    protected function _modifySubtotal($address)
    {
        $subtotal = $address->getSubtotal();
        $baseSubtotal = $address->getBaseSubtotal();

        $includeTax = Mage::getStoreConfig('amshiprestriction/general/tax');
        if ($includeTax){
           $subtotal += $address->getTaxAmount();
           $baseSubtotal += $address->getBaseTaxAmount(); 
        }
        
        $includeDiscount = Mage::getStoreConfig('amshiprestriction/general/discount');
        if ($includeDiscount){
           $subtotal += $address->getDiscountAmount();
           $baseSubtotal += $address->getBaseDiscountAmount(); 
        } 
                 
        $address->setSubtotal($subtotal);
        $address->setBaseSubtotal($baseSubtotal);

	return true;
    }
 
    
    protected function _isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return true;
        // for some reason isAdmin does not work here
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create')
            return true;
            
        return false;
    }        

    
    /**
     * Append rule product attributes to select by quote item collection
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_SalesRule_Model_Observer
     */
    public function addProductAttributes(Varien_Event_Observer $observer)
    {
        // @var Varien_Object
        $attributesTransfer = $observer->getEvent()->getAttributes();

        $attributes = Mage::getResourceModel('amshiprestriction/rule')->getAttributes();
        
        $result = array();
        foreach ($attributes as $code) {
            $result[$code] = true;
        }
        $attributesTransfer->addData($result);
        
        return $this;
    }
     
     /**
     * Adds new conditions
     * @param   Varien_Event_Observer $observer
     */
    public function handleNewConditions($observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)){
            $cond = array();
        }
        
        $types = array(
            'customer' => 'Customer attributes',
        );
        foreach ($types as $typeCode => $typeLabel){
            $condition           = Mage::getModel('amshiprestriction/rule_condition_' . $typeCode);
            $conditionAttributes = $condition->loadAttributeOptions()->getAttributeOption();
            
            $attributes = array();
            foreach ($conditionAttributes as $code=>$label) {
                $attributes[] = array(
                    'value' => 'amshiprestriction/rule_condition_'.$typeCode.'|' . $code, 
                    'label' => $label,
                );
            }         
            $cond[] = array(
                'value' => $attributes, 
                'label' => Mage::helper('amshiprestriction')->__($typeLabel), 
            );            
        }

        $transport->setConditions($cond);
        
        return $this; 
    }             
    
}