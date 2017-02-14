<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Countdown
 * @version    1.1.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Countdown_Model_Countdown_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('awcountdown/countdown_condition_combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('awcountdown/countdown_condition_combine_product');
        $productAttributes = $productCondition->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($productAttributes as $code => $label) {
            $attributes[] = array('value' => 'awcountdown/countdown_condition_combine_product|' . $code, 'label' => $label);
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                 array(
                     'value' => 'catalogrule/rule_condition_combine',
                     'label' => Mage::helper('catalogrule')->__('Conditions Combination')
                 ),
                 array(
                     'label' => Mage::helper('catalogrule')->__('Product Attribute'),
                     'value' => $attributes
                 ),
            )
        );
        return $conditions;
    }

    /**
     * @param $productCollection
     *
     * @return AW_Countdown_Model_Countdown_Condition_Combine
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() .
            Mage::helper('awcountdown')->__(
                "If %s of these order conditions are %s",
                $this->getAggregatorElement()->getHtml(),
                $this->getValueElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    public function getValidatedProductIds($productIds, $storeId)
    {
        if (!$this->getConditions()) {
            return $productIds;
        }

        $all    = $this->getAggregator() === 'all';
        $true   = (bool)$this->getValue();
        $validatedIds = null;
        foreach ($this->getConditions() as $cond) {
            $conditionValidatedIds = $cond->getValidatedProductIds($productIds, $storeId);
            $allIds = $productIds;
            if (!$true) {
                $conditionValidatedIds = array_diff($allIds, $conditionValidatedIds);
            }

            if (null === $validatedIds) {
                $validatedIds = $all ? $allIds : array();
            }

            if (!$all) {
                $validatedIds = array_unique(array_merge($validatedIds, $conditionValidatedIds), SORT_NUMERIC);
            } else {
                $validatedIds = array_intersect($validatedIds, $conditionValidatedIds);
            }
        }
        return $validatedIds;
    }
}