<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Follow Up Email
 * @version   1.0.14
 * @build     630
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Model_Rule_Condition_Customer extends Mirasvit_Email_Model_Rule_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'group_id' => Mage::helper('email')->__('Customer: Group'),
            'lifetime_sales' => Mage::helper('email')->__('Customer: Lifetime Sales'),
            'number_of_orders' => Mage::helper('email')->__('Customer: Number of Orders'),
            'is_subscriber' => Mage::helper('email')->__('Customer: Is subscriber of newsletter'),
            'reviews_count' => Mage::helper('email')->__('Customer: Number of reviews'),
            'last_order_date' => Mage::helper('email')->__('Customer: Last order date'),
            'subscription_status' => Mage::helper('email')->__('Customer: Newsletter subscription status'),
        );

        $customerAttributes = Mage::getModel('customer/customer')->getAttributes();
        foreach ($customerAttributes as $attr) {
            if ($attr->getStoreLabel()
                && $attr->getAttributeCode()) {
                $attributes[$attr->getAttributeCode()] = Mage::helper('email')->__('Customer: ').$attr->getStoreLabel();
            }
        }

        if (Mage::helper('mstcore')->isModuleInstalled('AW_Marketsuite')) {
            $attributes['mss_rule'] = Mage::helper('email')->__('Customer: AheadWorks MSS rule');
        }

        // asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        $type = 'string';

        switch ($this->getAttribute()) {
            case 'group_id':
                $type = 'multiselect';
            break;

            case 'is_subscriber':
            case 'mss_rule':
            case 'store_id':
            case 'website_id':
                $type = 'select';
            break;

            case 'last_order_date':
            case 'created_in':
            case 'dob':
                $type = 'date';
            break;
        }

        return $type;
    }

    public function getValueElementType()
    {
        $type = 'text';

        switch ($this->getAttribute()) {
            case 'group_id':
                $type = 'multiselect';
            break;

            case 'is_subscriber':
            case 'subscription_status':
            case 'mss_rule':
            case 'store_id':
            case 'website_id':
                $type = 'select';
            break;

            case 'last_order_date':
            case 'created_in':
            case 'dob':
                $type = 'date';
            break;
        }

        return $type;
    }

    public function getValueElement()
    {
        $element = parent::getValueElement();
        switch ($this->getAttribute()) {
            case 'last_order_date':
            case 'created_in':
            case 'dob':
                $element->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
                break;
        }

        return $element;
    }

    public function getExplicitApply()
    {
        $result = parent::getExplicitApply();
        switch ($this->getAttribute()) {
            case 'last_order_date':
            case 'created_in':
            case 'dob':
                $result = true;
                break;
        }

        return $result;
    }

    protected function _prepareValueOptions()
    {
        $hashedOptions = array();
        $selectOptions = $this->getAttributeOptions();

        $this->setData('value_select_options', $selectOptions);
        foreach ($selectOptions as $o) {
            $hashedOptions[$o['value']] = $o['label'];
        }

        $this->setData('value_option', $hashedOptions);

        return $this;
    }

    public function getAttributeOptions()
    {
        $options = parent::getAttributeOptions();

        switch ($this->getAttribute()) {
            case 'is_subscriber':
                $options = array(
                    array('value' => 0, 'label' => Mage::helper('email')->__('No')),
                    array('value' => 1, 'label' => Mage::helper('email')->__('Yes')),
                );
                break;

            case 'subscription_status':
                $options = Mage::getModel('email/system_source_subscriptionStatus')->toOptionArray();
                break;

            case 'group_id':
                $options = Mage::helper('customer')->getGroups()->toOptionArray();
                array_unshift($options, array('value' => 0, 'label' => Mage::helper('email')->__('Not registered')));
                break;

            case 'website_id':
            case 'store_id':
                $options = Mage::getResourceModel('core/'.substr($this->getAttribute(), 0, -3).'_collection')->toOptionArray();
                break;

            case 'mss_rule':
                if (Mage::helper('mstcore')->isModuleInstalled('AW_Marketsuite')) {
                    $ruleCollection = Mage::getModel('marketsuite/filter')->getActiveRuleCollection();
                    foreach ($ruleCollection as $rule) {
                        $options[] = array(
                            'value' => $rule->getId(),
                            'label' => $rule->getName(),
                        );
                    }
                }
                break;
        }

        return $options;
    }

    public function validate(Varien_Object $object)
    {
        return $this->validateAttribute($this->getObjectValue($object));
    }

    private function getObjectValue($object)
    {
        $value = null;
        $attrCode = $this->getAttribute();

        $subscriber = Mage::getModel('newsletter/subscriber');
        $customer = Mage::getModel('customer/customer');
        if ($customerId = $object->getData('customer_id')) {
            $customer->load($customerId);
            $subscriber->loadByEmail($customer->getEmail());
        } else {
            $customer->setWebsiteId(Mage::app()->getStore($object->getStoreId())->getWebsiteId());
            $customer->loadByEmail($object->getData('customer_email'));
            $subscriber->loadByEmail($object->getData('customer_email'));
        }

        switch ($attrCode) {
            case 'group_id':
                $value = $customer->getId() ? $customer->getGroupId() : Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
                break;

            case 'lifetime_sales':
                $totals = Mage::getResourceModel('sales/sale_collection')
                    ->setOrderStateFilter(Mage_Sales_Model_Order::STATE_CANCELED, true);
                if ($customer->getId()) {
                    $customerTotals = $totals->setCustomerFilter($customer)->load()->getTotals();
                } else {
                    $customerTotals = $totals->addFieldToFilter('customer_email', $object->getCustomerEmail())->load()
                        ->getTotals();
                }

                $value = floatval($customerTotals['lifetime']);
                break;

            case 'number_of_orders':
                $totals = Mage::getResourceModel('sales/sale_collection');
                if ($customer->getId()) {
                    $customerTotals = $totals->setCustomerFilter($customer)->load()->getTotals();
                } else {
                    $customerTotals = $totals->addFieldToFilter('customer_email', $object->getCustomerEmail())->load()
                        ->getTotals();
                }

                $value = intval($customerTotals['num_orders']);
                break;

            case 'is_subscriber':
                $value = $subscriber->getId() && $subscriber->getSubscriberStatus() == Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED ? 1 : 0;
                break;

            case 'reviews_count':
                $value = 0;
                if ($customer->getId()) {
                    $reviews = Mage::getModel('review/review')->getCollection()
                        ->addFieldToFilter('customer_id', $customer->getId());
                    $value = $reviews->count();
                }
                break;

            case 'last_order_date':
                $orders = Mage::getModel('sales/order')->getCollection()
                    ->setOrder('created_at', Varien_Data_Collection_Db::SORT_ORDER_DESC);
                if ($customer->getId()) {
                    $orders->addFieldToFilter('customer_id', $customer->getId())->count();
                } else {
                    $orders->addFieldToFilter('customer_email', $object->getCustomerEmail());
                }

                $value = ($orders->count() > 0) ? $orders->getFirstItem()->getCreatedAt() : 0;
                break;

            case 'subscription_status':
                $value = $subscriber->getSubscriberStatus();
                break;

            case 'mss_rule':
                if (Mage::helper('mstcore')->isModuleInstalled('AW_Marketsuite')) {
                    $mssApi = Mage::getModel('marketsuite/api');
                    if ($mssApi->checkRule($customer, (int) $this->getValue())) {
                        $value = $this->getValue();
                    }
                }
                break;

            default:
                if ($customer->getId()) {
                    $object->addData($customer->getData());
                } elseif ($subscriber->getId()) {
                    $object->setStoreId($subscriber->getStoreId())
                        ->setWebsiteId(Mage::app()->getStore($object->getStoreId())->getWebsiteId());
                }

                $value = $object->getData($attrCode);
                break;
        }

        return $value;
    }
}
