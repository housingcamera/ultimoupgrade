<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Quote Address Total  abstract model
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Total Code name
     *
     * @var string
     */
    protected $_code;
    protected $_address = null;

    /**
     * Various abstract abilities
     * @var bool
     */
    protected $_canAddAmountToAddress = true;
    protected $_canSetAddressAmount   = true;

    /**
     * Key for item row total getting
     *
     * @var string
     */
    protected $_itemRowTotalKey = null;

    /**
     * Set total code code name
     *
     * @param string $code
     * @return Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function setCode($code)
    {
        $this->_code = $code;
        return $this;
    }

    /**
     * Retrieve total code name
     *
     * @return unknown
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Label getter
     *
     * @return string
     */
    public function getLabel()
    {
        return '';
    }

    /**
     * Collect totals process.
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_setAddress($address);
        /**
         * Reset amounts
         */
        $this->_setAmount(0);
        $this->_setBaseAmount(0);
        return $this;
    }

    /**
     * Fetch (Retrieve data as array)
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_setAddress($address);
        return array();
    }

    /**
     * Set address shich can be used inside totals calculation
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _setAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * Get quote address object
     *
     * @throw   Mage_Core_Exception if address not declared
     * @return  Mage_Sales_Model_Quote_Address
     */
    protected function _getAddress()
    {
        if ($this->_address === null) {
            Mage::throwException(
                Mage::helper('sales')->__('Address model is not defined.')
            );
        }
        return $this->_address;
    }

    /**
     * Set total model amount value to address
     *
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _setAmount($amount)
    {
        if ($this->_canSetAddressAmount) {
            $this->_getAddress()->setTotalAmount($this->getCode(), $amount);
        }
        return $this;
    }

    /**
     * Set total model base amount value to address
     *
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _setBaseAmount($baseAmount)
    {
        if ($this->_canSetAddressAmount) {
            $this->_getAddress()->setBaseTotalAmount($this->getCode(), $baseAmount);
        }
        return $this;
    }

    /**
     * Add total model amount value to address
     *
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _addAmount($amount)
    {
        if ($this->_canAddAmountToAddress) {
            $this->_getAddress()->addTotalAmount($this->getCode(),$amount);
        }
        return $this;
    }

    /**
     * Add total model base amount value to address
     *
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _addBaseAmount($baseAmount)
    {
        if ($this->_canAddAmountToAddress) {
            $this->_getAddress()->addBaseTotalAmount($this->getCode(), $baseAmount);
        }
        return $this;
    }

    /**
     * Get all items except nominals
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return array
     */
    protected function _getAddressItems(Mage_Sales_Model_Quote_Address $address)
    {
        return $address->getAllNonNominalItems();
    }

    /**
     * Getter for row default total
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    public function getItemRowTotal(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if (!$this->_itemRowTotalKey) {
            return 0;
        }
        return $item->getDataUsingMethod($this->_itemRowTotalKey);
    }

    /**
     * Getter for row default base total
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    public function getItemBaseRowTotal(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if (!$this->_itemRowTotalKey) {
            return 0;
        }
        return $item->getDataUsingMethod('base_' . $this->_itemRowTotalKey);
    }

    /**
     * Whether the item row total may be compouded with others
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return bool
     */
    public function getIsItemRowTotalCompoundable(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        if ($item->getData("skip_compound_{$this->_itemRowTotalKey}")) {
            return false;
        }
        return true;
    }

    /**
     * Process model configuration array.
     * This method can be used for changing models apply sort order
     *
     * @param   array $config
     * @param   store $store
     * @return  array
     */
    public function processConfigArray($config, $store)
    {
        return $config;
    }
}
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Discount calculation model
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Quote_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Discount calculation object
     *
     * @var Mage_SalesRule_Model_Validator
     */
    protected $_calculator;

    /**
     * Initialize discount collector
     */
    public function __construct()
    {
        $this->setCode('discount');
        $this->_calculator = Mage::getSingleton('salesrule/validator');
    }

    /**
     * Collect address discount amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());
        $this->_calculator->reset($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $eventArgs = array(
            'website_id'        => $store->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code'       => $quote->getCouponCode(),
        );

        $this->_calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->_calculator->initTotals($items, $address);

        $address->setDiscountDescription(array());
        $items = $this->_calculator->sortItemsByPriority($items);
        foreach ($items as $item) {
            if ($item->getNoDiscount()) {
                $item->setDiscountAmount(0);
                $item->setBaseDiscountAmount(0);
            }
            else {
                /**
                 * Child item discount we calculate for parent
                 */
                if ($item->getParentItemId()) {
                    continue;
                }

                $eventArgs['item'] = $item;
                Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $this->_calculator->process($child);
                        $eventArgs['item'] = $child;
                        Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                        $this->_aggregateItemDiscount($child);
                    }
                } else {
                    $this->_calculator->process($item);
                    $this->_aggregateItemDiscount($item);
                }
            }
        }

        /**
         * process weee amount
         */
        if (Mage::helper('weee')->isEnabled() && Mage::helper('weee')->isDiscounted($store)) {
            $this->_calculator->processWeeeAmount($address, $items);
        }

        /**
         * Process shipping amount discount
         */
        $address->setShippingDiscountAmount(0);
        $address->setBaseShippingDiscountAmount(0);
        if ($address->getShippingAmount()) {
            $this->_calculator->processShippingAmount($address);
            $this->_addAmount(-$address->getShippingDiscountAmount());
            $this->_addBaseAmount(-$address->getBaseShippingDiscountAmount());
        }

        $this->_calculator->prepareDescription($address);
        return $this;
    }

    /**
     * Aggregate item discount information to address data and related properties
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    protected function _aggregateItemDiscount($item)
    {
        $this->_addAmount(-$item->getDiscountAmount());
        $this->_addBaseAmount(-$item->getBaseDiscountAmount());
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getDiscountAmount();

        if ($amount != 0) {
            $description = $address->getDiscountDescription();
            if (strlen($description)) {
                $title = Mage::helper('sales')->__('Discount (%s)', $description);
            } else {
                $title = Mage::helper('sales')->__('Discount');
            }
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }
}
/**
 * J2T RewardsPoint2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@j2t-design.com so we can send you a copy immediately.
 *
 * @category   Magento extension
 * @package    RewardsPoint2
 * @copyright  Copyright (c) 2012 J2T DESIGN. (http://www.j2t-design.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


if (Mage::getConfig()->getModuleConfig('J2t_Multicoupon')->is('active', 'true')){
    // >>in case of tax calculation issue redefine extends Mage_Sales_Model_Quote_Address_Total_Discount in app/code/community/J2t/Multicoupon/Model/Quote/Discount.php
    class Rewardpoints_Model_Total_Points_Abstract extends J2t_Multicoupon_Model_Quote_Discount //magento 1.4.x and greater
    {
        
    }
} else {
    // >>in case of tax calculation issue, uncomment the appropriate line
    //class Rewardpoints_Model_Total_Points_Abstract extends Mage_Sales_Model_Quote_Address_Total_Discount //magento 1.3.x
    if (version_compare(Mage::getVersion(), '1.4.0', '<')){
        class Rewardpoints_Model_Total_Points_Abstract extends Mage_Sales_Model_Quote_Address_Total_Abstract
        {

        }
    } else {
        class Rewardpoints_Model_Total_Points_Abstract extends Mage_SalesRule_Model_Quote_Discount //magento 1.4.x and greater
        {

        }
    }
}

// >>in case of tax calculation issue, uncomment the appropriate line
//class Rewardpoints_Model_Total_Points extends Mage_Sales_Model_Quote_Address_Total_Discount //magento 1.3.x
//DEFAULT class declaration
//class Rewardpoints_Model_Total_Points extends Mage_SalesRule_Model_Quote_Discount //magento 1.4.x and greater
//When using J2T Multicoupon:
//class Rewardpoints_Model_Total_Points extends J2t_Multicoupon_Model_Quote_Discount
// ... and comment the following line
//class Rewardpoints_Model_Total_Points extends Mage_Sales_Model_Quote_Address_Total_Abstract
class Rewardpoints_Model_Total_Points extends Rewardpoints_Model_Total_Points_Abstract
{
    /*public function __construct()
    {
        //parent::__construct();
        $this->setCode('rewardpoints');
    }*/
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        
        if (version_compare(Mage::getVersion(), '1.4.0', '>=') && method_exists($this, '_getAddressItems')){
            $items = $this->_getAddressItems($address);
        } else {
            $items = $address->getAllItems();
        }
        if (!count($items)) {
            return $this;
        }
        
        $totalPPrice = 0;
        $totalPBasePrice = 0;
        
        $this->checkAutoUse($address->getQuote());
        $creditPoints = $this->getCreditPoints($address->getQuote());
        
        $subtotalWithDiscount = 0;
        $baseSubtotalWithDiscount = 0;
        
        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;
        
        // verify max percent usage
        $creditPoints = $this->percentPointMax($address->getQuote(), $creditPoints);
        
        if ($userId = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
            $address->getQuote()->setRewardpointsReferrer($userId);
        }
        
        //verify if dont process rule
        //$to_validate = $address->getQuote();
        //$to_validate->setQuote($address->getQuote());
        if ($creditPoints > 0 && Mage::getModel('rewardpoints/pointrules')->getRulePointsGathered($address->getQuote(), $address->getQuote()->getCustomerGroupId(), true) === false){
            $creditPoints = 0;
            Mage::getSingleton('checkout/session')->addNotice(Mage::helper('rewardpoints')->__('Your current cart configuration does not allow point usage.'));
        }
        
        if ($creditPoints > 0 && $this->checkMinUse($address->getQuote())){
            //coupon code restriction 
	    if (Mage::getStoreConfig('rewardpoints/default/coupon_codes', $address->getQuote()->getStoreId()) && $address->getCouponCode()){
	   	$address->setCouponCode(''); 
	    } 
	    if ($address->getCustomerId()){
                $pointsAmount = Mage::helper('rewardpoints/data')->convertPointsToMoney($creditPoints, $address->getCustomerId(), $address->getQuote(), true);
            } elseif ($address->getQuote()->getCustomerId()) {
                $pointsAmount = Mage::helper('rewardpoints/data')->convertPointsToMoney($creditPoints, $address->getQuote()->getCustomerId(), $address->getQuote(), true);
            } else {
                $pointsAmount = 0;//continue;
            }
            
            $no_discount = array();
            foreach ($items as $item) {
                /*if ($item->getProduct()->isVirtual()) {
                    continue;
                }*/
                //echo $item->getProduct()->getData('reward_no_discount');
                //die;
                
                //get price to be removed from discount
                $remove_price = 0;
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        //$i = $i + $child->getQty();
                        if ($product = $child->getProduct()) {
                            if (!$product->getData('reward_no_discount')) {
				if (Mage::getStoreConfig('rewardpoints/default/process_tax', $address->getQuote()->getStoreId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $address->getQuote()->getStoreId()) == 0){
                                    $tax = ($child->getTaxBeforeDiscount() ? $child->getTaxBeforeDiscount() : $child->getTaxAmount());
                                    $row_base_total = $child->getBaseRowTotal() + $tax;
                                } else {
                                    $row_base_total = $child->getBaseRowTotal();
                                }
                                $remove_price += $row_base_total;
                            } else {
                                $no_discount[$product->getId()] = '"'.$product->getName().'"';
                            }
                        }
                    }
                }
                if ($product = $item->getProduct()) {
                    if ($product->getData('reward_no_discount')) {
                        $no_discount[$product->getId()] = '"'.$product->getName().'"';
                        continue;
                    }
                }
                if (Mage::getStoreConfig('rewardpoints/default/process_tax', $address->getQuote()->getStoreId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $address->getQuote()->getStoreId()) == 0){
                    $tax = ($item->getTaxBeforeDiscount() ? $item->getTaxBeforeDiscount() : $item->getTaxAmount());
                    $row_base_total = $item->getBaseRowTotal() + $tax;
                } else {
                    $row_base_total = $item->getBaseRowTotal();
                }
                
                $row_base_total -= $remove_price;
                $baseDiscountAmount = min($row_base_total - $item->getBaseDiscountAmount(), $pointsAmount);
                
                if ($baseDiscountAmount > 0){
                    $rewardpoints_used = Mage::helper('rewardpoints/data')->convertMoneyToPoints(abs($baseDiscountAmount), false, $address->getQuote(), true);
                    $item->setRewardpointsUsed($rewardpoints_used);
                    
                    $points = -$baseDiscountAmount;
                    $totalPBasePrice += $points;
                    $discountAmount = $address->getQuote()->getStore()->convertPrice($points, false);
                    $totalPPrice += $discountAmount;
                    
                    if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                        $item->setDiscountAmount(abs($discountAmount)+$item->getDiscountAmount());
                        $item->setBaseDiscountAmount(abs($baseDiscountAmount)+$item->getBaseDiscountAmount());
                    } else {
                        $item->setDiscountAmount(abs($discountAmount)+$item->getDiscountAmount());
                        $item->setBaseDiscountAmount(abs($baseDiscountAmount)+$item->getBaseDiscountAmount());
                        
                        
                        $item->setRowTotalWithDiscount($item->getRowTotal()-$item->getDiscountAmount());
                        $item->setBaseRowTotalWithDiscount($item->getBaseRowTotal()-$item->getBaseDiscountAmount());

                        $subtotalWithDiscount += $item->getRowTotalWithDiscount();
                        $baseSubtotalWithDiscount += $item->getBaseRowTotalWithDiscount();
                    }
                    $totalDiscountAmount += abs($discountAmount);
                    $baseTotalDiscountAmount += abs($baseDiscountAmount);
                    
                    
                } else {
                    $item->setRewardpointsUsed(0);
                }
                
                $pointsAmount -= $baseDiscountAmount;
            }
            
            //J2T process shipping address
            $shipping_process = Mage::getStoreConfig('rewardpoints/default/process_shipping', $address->getQuote()->getStoreId());
            if (version_compare(Mage::getVersion(), '1.4.0', '>=') && $shipping_process){
                $shipping_tax = 0;
                if (Mage::getStoreConfig('rewardpoints/default/process_tax', $address->getQuote()->getStoreId()) == 1 && Mage::getStoreConfig('tax/calculation/apply_after_discount', $address->getQuote()->getStoreId()) == 0){
                    $shipping_tax = $address->getBaseShippingTaxAmount();
                }
                
                $baseShippingDiscountAmount = min(( ($address->getBaseShippingAmount() + $shipping_tax - $address->getBaseShippingDiscountAmount())), $pointsAmount);
                $points = -$baseShippingDiscountAmount;
                $totalPBasePrice += $points;
                $totalPPrice += $address->getQuote()->getStore()->convertPrice($points, false);
                $pointsAmount -= $baseShippingDiscountAmount;
                
                $address->setShippingDiscountAmount($address->getQuote()->getStore()->convertPrice($baseShippingDiscountAmount, false) + $address->getShippingDiscountAmount());
                $address->setBaseShippingDiscountAmount($baseShippingDiscountAmount + $address->getBaseShippingDiscountAmount());
                
            }
            //J2T end process shipping address
           
            if (sizeof($no_discount) && Mage::app()->getRequest()->getRouteName() == 'checkout'){
                Mage::getSingleton('checkout/session')->addNotice(Mage::helper('rewardpoints')->__('Points are not usable on the following product(s): %s.', implode(", ", $no_discount)));
            }
            if ($pts = Mage::helper('rewardpoints/event')->getCreditPoints($address->getQuote())){
                $address->getQuote()
                        ->setRewardpointsQuantity($pts)
                        ->setBaseRewardpoints(-$totalPBasePrice)
                        ->setRewardpoints(-$totalPPrice);
                        //->save();
            }
            
            
            if (abs($totalPBasePrice) > 0){
                $points_used = Mage::helper('rewardpoints/data')->convertMoneyToPoints(abs($totalPBasePrice), false, $address->getQuote(), true);
                $points_session = Mage::helper('rewardpoints/event')->getCreditPoints($address->getQuote());
                if ($points_used < $points_session){
                    Mage::helper('rewardpoints/event')->setCreditPoints($points_used);
                    
                    $address->getQuote()
                            ->setRewardpointsQuantity($points_used)
                            ->setBaseRewardpoints(-$totalPBasePrice)
                            ->setRewardpoints(-$totalPPrice);
                                //->save();
                    
                }
            } else {
                //remove all reward points within this cart
                if ($referrer_id = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
                    Mage::getSingleton('rewardpoints/session')->unsetAll();
                    Mage::getSingleton('rewardpoints/session')->setReferralUser($referrer_id);
                } else {
                    Mage::getSingleton('rewardpoints/session')->unsetAll();
                }
                Mage::helper('rewardpoints/event')->removeCreditPoints($address->getQuote(), true);
            }

            
            if ($pts = Mage::helper('rewardpoints/event')->getCreditPoints($address->getQuote())){
                $title = Mage::helper('rewardpoints')->__('%s points used', $pts);
                //echo $pts;
                //die;
                
                $address->getQuote()->setRewardpointsDescription($title);
                //$title_base = $title;
                
                $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', $address->getQuote()->getStoreId());
                $remove_link = Mage::getStoreConfig('rewardpoints/default/remove_link', $address->getQuote()->getStoreId());
                if (!$auto_use && $remove_link && !Mage::getSingleton('admin/session')->isLoggedIn()){
                    //$title .= ' <a href="javascript:$(\'discountFormPoints2\').submit();" title="'.Mage::helper('rewardpoints')->__('Remove Points').'"><img src="'.Mage::getDesign()->getSkinUrl('images/j2t_delete.gif').'" alt="'.Mage::helper('rewardpoints')->__('Remove Points').'" /></a>';
                    //$title .= '<span id="link_j2t_rewards"></span>';
                }
                
                if ($address->getDiscountDescription() != ''){
                    $desc_array = $address->getDiscountDescriptionArray();
                    $desc_array[] = $title;
                    $address->setDiscountDescriptionArray($desc_array);
                    //$address->setDiscountDescriptionArray($couponCode);
                    $address->setDiscountDescription($address->getDiscountDescription().', '.$title);
                } else {
                    $address->setDiscountDescription($title);
                    $address->setDiscountDescriptionArray(array($title));
                }
                
                
                //if (version_compare(Mage::getVersion(), '1.6.0', '>=')){
                //if (version_compare(Mage::getVersion(), '1.4.0', '>=')){
                if (version_compare(Mage::getVersion(), '1.4.0.1', '>=')){
                    
                    $address->setDiscountAmount($address->getDiscountAmount()+$totalPPrice);                
                    $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+$totalPBasePrice);
                    
                    $this->_addAmount($totalPPrice);
                    $this->_addBaseAmount($totalPBasePrice);
                } else {
                    $address->setDiscountAmount($address->getDiscountAmount()+$totalDiscountAmount);
                    $address->setSubtotalWithDiscount($subtotalWithDiscount);
                    $address->setBaseDiscountAmount($address->getBaseDiscountAmount()+$baseTotalDiscountAmount);
                    $address->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
                    if ($coupon = $address->getCouponCode()){
                        $address->setCouponCode($address->getCouponCode().', '.$title);
                    } else {
                        $address->setCouponCode($title);
                    }
                    $address->setGrandTotal($address->getGrandTotal() - $totalDiscountAmount);
                    $address->setBaseGrandTotal($address->getBaseGrandTotal()-$baseTotalDiscountAmount);
                }
                
                //if ($address->getQuote()->getRewardpointsQuantity() != $pts && $pts > 0){
            }
            
        } else {
            //remove all reward points within this cart
            if ($referrer_id = Mage::getSingleton('rewardpoints/session')->getReferralUser()){
                Mage::getSingleton('rewardpoints/session')->unsetAll();
                Mage::getSingleton('rewardpoints/session')->setReferralUser($referrer_id);
            } else {
                Mage::getSingleton('rewardpoints/session')->unsetAll();
            }
            Mage::helper('rewardpoints/event')->removeCreditPoints($address->getQuote());
            
            //set all item points usage to 
            foreach ($items as $item) {
                $item->setRewardpointsUsed(0);
            }
        }
        $address->getQuote()->setRewardpointsGathered(Mage::helper('rewardpoints/data')->getPointsOnOrder());
        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (Mage::getConfig()->getModuleConfig('Amasty_Rules')->is('active', 'true')){
            if (!Mage::getStoreConfig('amrules/general/breakdown'))
            return parent::fetch($address);
        
            $amount = $address->getDiscountAmount();
            if ($amount != 0) {
                $address->addTotal(array(
                    'code'      => $this->getCode(),
                    'title'     => Mage::helper('sales')->__('Discount'),
                    'value'     => $amount,
                    'full_info' => $address->getFullDescr(),
                ));
            }
            return $this;
        }
        return parent::fetch($address);
    }
 
    /*public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $pts = $this->getCreditPoints();
        $amount = $address->getRewardpointsAmount();
        
        if ($amount != 0 && $address->getAddressType() == 'shipping') {
            $title = Mage::helper('rewardpoints')->__('%s points used', $pts);
            //skin/frontend/default/default/images/j2t_delete.gif
            $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', Mage::app()->getStore()->getId());
            if (!$auto_use){
                $title .= ' <a href="javascript:$(\'discountFormPoints2\').submit();" title="'.Mage::helper('rewardpoints')->__('Remove Points').'"><img src="'.Mage::getDesign()->getSkinUrl('images/j2t_delete.gif').'" alt="'.Mage::helper('rewardpoints')->__('Remove Points').'" /></a>';
            }
            
            $address->addTotal(array(
                'code' => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }*/

    
    /*public function getLabel()
    {
        return Mage::helper('rewardpoints')->__('Points');
    }*/
    
    protected function getCreditPoints($quote)
    {
        return Mage::helper('rewardpoints/event')->getCreditPoints($quote);
    }
    
    protected function getCurrentCurrencyRate($quote = null)
    {
        if ($quote == null) {
            $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        } else {
            $currentCode = $quote->getStore()->getCurrentCurrency()->getCurrencyCode();
        }
        if ($currentCode == ""){
            $currentCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        }
        
        $baseCode = Mage::app()->getBaseCurrencyCode();      
        $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies(); 
        $rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCode, array_values($allowedCurrencies));
        
        $current_rate = (isset($rates[$currentCode])) ? $rates[$currentCode] : 1;
        return $current_rate;
    }
    
    
    protected function percentPointMax($quote, $current_points_usage)
    {
        $return_value = $current_points_usage;
        //max_point_percent_order
        $store_id = $quote->getStoreId();
        $percent_use = (int)Mage::getStoreConfig('rewardpoints/default/max_point_percent_order', $store_id);
        $percent_use = ($percent_use > 100 || $percent_use <= 0) ? 100 : $percent_use;
        
        //todo use base total
        $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($quote);
        $currency_rate = $this->getCurrentCurrencyRate($quote);
        
        $cart_amount = $cart_amount / $currency_rate;
        //TODO - check if we need to use multiply for higher rate (CHF for example)
        $cart_amount = ( $cart_amount * $percent_use ) / 100;
        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($cart_amount);
        $points_value = Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount, false, $quote, true);
        if ($points_value < $current_points_usage){
            $return_value = $points_value;
        }
        return $return_value;
    }
    
    protected function checkMinUse($quote)
    {
        $store_id = $quote->getStoreId();
        if ($quote->getCustomerId()){
            $customerId = $quote->getCustomerId();
        } else {
            $customerId = Mage::getModel('customer/session')->getCustomerId();
        }
        $min_use = Mage::getStoreConfig('rewardpoints/default/min_use', $store_id);
        if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
            $reward_model = Mage::getModel('rewardpoints/flatstats');
            $customer_points = $reward_model->collectPointsCurrent($customerId, $store_id);
        } else {
            $reward_model = Mage::getModel('rewardpoints/stats');
            $customer_points = $reward_model->getPointsCurrent($customerId, $store_id);
        }
        if ($min_use > $customer_points){
            return false;
        }
        return true;
    }
    
    protected function checkAutoUse($quote){
        $customer = Mage::getSingleton('customer/session');
        $store_id = $quote->getStoreId();
        if ($customer->isLoggedIn()){
            
            if ($quote->getCustomerId()){
                $customerId = $quote->getCustomerId();
            } else {
                $customerId = Mage::getModel('customer/session')->getCustomerId();
            }
            $auto_use = Mage::getStoreConfig('rewardpoints/default/auto_use', $store_id);
            if ($auto_use){
                //MODIFICATION SPENT = COLLECT
                $order = Mage::getModel('sales/order')->loadByAttribute('quote_id', $quote->getId());
                if (!$order->getId()){
                    if (Mage::getStoreConfig('rewardpoints/default/flatstats', $store_id)){
                        $reward_model = Mage::getModel('rewardpoints/flatstats');
                        $customer_points = $reward_model->collectPointsCurrent($customerId, $store_id);
                    } else {
                        $reward_model = Mage::getModel('rewardpoints/stats');
                        $customer_points = $reward_model->getPointsCurrent($customerId, $store_id);
                    }
                    if ($customer_points && $customer_points > Mage::helper('rewardpoints/event')->getCreditPoints($quote)){
                        $cart_amount = Mage::getModel('rewardpoints/discount')->getCartAmount($quote);
                        //todo use base total
                        $cart_amount = $cart_amount / $this->getCurrentCurrencyRate($quote);
                        $cart_amount = Mage::helper('rewardpoints/data')->processMathValue($cart_amount);
                        $points_value = min(Mage::helper('rewardpoints/data')->convertMoneyToPoints($cart_amount), (int)$customer_points, $quote, true);

                        Mage::getSingleton('customer/session')->setProductChecked(0);
                        Mage::helper('rewardpoints/event')->setCreditPoints($points_value);

                        $quote->setRewardpointsQuantity($points_value);
                        //->save();
                    }
                }
            }
        }
    }
}