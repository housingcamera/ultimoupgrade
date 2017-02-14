<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */



class Amasty_Shiprestriction_Block_Adminhtml_Rule_Edit_Tab_Apply extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /* @var $hlp Amasty_Shiprestriction_Helper_Data */
        $hlp = Mage::helper('amshiprestriction');

        $fldInfo = $form->addFieldset('apply_restriction', array('legend'=> $hlp->__('Apply Restrictions Only With')));

        $promoShippingRulesUrl = $this->getUrl('adminhtml/promo_quote');

        $fldInfo->addField('coupon', 'text', array(
            'label'     => Mage::helper('salesrule')->__('Coupon Code'),
            'name'      => 'coupon',
            'note'      => $hlp->__('Apply this restriction with coupon only. You should configure coupon in <a href="'. $promoShippingRulesUrl . '"><span>Promotions / Shopping Cart Rules</span></a> area first.'),
        ));

        $fldInfo->addField('discount_id', 'select', array(
            'label'     => $hlp->__('Shopping Cart Rule (discount)'),
            'name'      => 'discount_id',
            'values'    => $hlp->getAllRules(),
            'note'      => $hlp->__('Apply this restriction with ANY coupon from specified discount rule. You should configure the rule in <a href="'. $promoShippingRulesUrl . '"><span>Promotions / Shopping Cart Price Rules</span></a> area first. Useful when you have MULTIPLE coupons in one rule.'),
        ));

        $fldInfo = $form->addFieldset('not_apply_restriction', array('legend'=> $hlp->__('Do NOT Apply Restrictions With')));

        $fldInfo->addField('coupon_disable', 'text', array(
            'label'     => Mage::helper('salesrule')->__('Coupon code'),
            'name'      => 'coupon_disable',
            'note'      => $hlp->__('Not apply this restriction with coupon. You should configure coupon in <a href="'. $promoShippingRulesUrl . '"><span>Promotions / Shopping Cart Rules</span></a> area first.'),
        ));

        $fldInfo->addField('discount_id_disable', 'select', array(
            'label'     => $hlp->__('Shopping Cart Rule (discount)'),
            'name'      => 'discount_id_disable',
            'values'    => $hlp->getAllRules(),
            'note'      => $hlp->__('Not apply this restriction with ANY coupon from specified discount rule. You should configure the rule in <a href="'. $promoShippingRulesUrl . '"><span>Promotions / Shopping Cart Price Rules</span></a> area first. Useful when you have MULTIPLE coupons in one rule.'),
        ));

        //set form values
        $form->setValues(Mage::registry('amshiprestriction_rule')->getData());

        return parent::_prepareForm();
    }
}