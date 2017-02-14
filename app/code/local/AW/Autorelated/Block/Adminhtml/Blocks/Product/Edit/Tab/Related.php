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
 * @package    AW_Autorelated
 * @version    2.4.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Autorelated_Block_Adminhtml_Blocks_Product_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::getModel('awautorelated/blocks')->load((int)$this->getRequest()->getParam('id'));
        $form = new Varien_Data_Form();

        $genearalFieldset = $form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General')
        ));

        if ($model->getData('related_products')) {
            $generalOptions = $model->getData('related_products')->getData('general');
        } else {
            $generalOptions = array();
        }

        $genearalFieldset
            ->addField('general_options', 'text',
                array(
                    'name'  => 'general_options',
                    'label' => $this->__('Number of products'),
                    'title' => $this->__('Number of products')
                )
            )
            ->setRenderer(
                $this->getLayout()
                ->createBlock('adminhtml/widget_form_renderer_fieldset')
                ->setTemplate('aw_autorelated/render/attfield.phtml')
                ->setValues($generalOptions)
            )
        ;

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl('*/*/newConditionHtml',
                    array(
                        'form'   => 'related_conditions_fieldset',
                        'prefix' => 'related',
                        'rule'   => base64_encode('awautorelated/blocks_product_rulerelated')
                    )
                )
            )
        ;

        $fieldset = $form
            ->addFieldset('related_conditions_fieldset',
                array(
                    'legend' => $this->__('Conditions (leave blank for all products)')
                )
            )
            ->setRenderer($renderer)
        ;

        $rule = Mage::getModel('awautorelated/blocks_product_rulerelated');
        $rule->getConditions()->setJsFormObject('related_conditions_fieldset');
        $rule->getConditions()->setId('related_conditions_fieldset');
        $rule->setForm($fieldset);

        if ($model->getData('related_products')) {
            if (is_array($model->getData('related_products')->getData('related'))) {
                $conditions = $model->getData('related_products')->getData('related');
                $conditions = $conditions['conditions'];
                $rule->getConditions()->loadArray($conditions, 'related');
                $rule->getConditions()->setJsFormObject('related_conditions_fieldset');
            }
            $model = $this->_prepareRelatedTabData($model);
        }

        $fieldset
            ->addField('related_conditions', 'text',
                array(
                    'name'     => 'related_conditions',
                    'label'    => $this->__('Apply To'),
                    'title'    => $this->__('Apply To'),
                    'required' => true
                )
            )
            ->setRule($rule)
            ->setRenderer(Mage::getBlockSingleton('rule/conditions'))
        ;

        $other = $form->addFieldset('other', array(
            'legend' => $this->__('Other')
        ));

        $other->addField('product_qty', 'text', array(
            'name'     => 'product_qty',
            'label'    => $this->__('Number of products'),
            'title'    => $this->__('Number of products'),
            'class'    => 'validate-digits validate-greater-than-zero',
            'required' => true
        ));

        $other->addField('order', 'select', array(
            'name'   => 'related_products[order][type]',
            'label'  => $this->__('Order Products'),
            'title'  => $this->__('Order Products'),
            'values' => Mage::getModel('awautorelated/source_block_common_order')->toOptionArray()
        ));

        $other->addField('order_attribute', 'select', array(
            'name'   => 'related_products[order][attribute]',
            'values' => Mage::getModel('awautorelated/source_catalog_product_attributes')->toOptionArray(),
            'note'   => $this->__('Select Attribute')
        ));

        $other->addField('order_direction', 'select', array(
            'name'   => 'related_products[order][direction]',
            'values' => Mage::getModel('awautorelated/source_resource_collection_order')->toOptionArray(),
            'note'   => $this->__('Sort Direction')
        ));

        $other->addField('show_out_of_stock', 'select', array(
            'name'   => 'related_products[show_out_of_stock]',
            'label'  => $this->__('Show "Out of stock" Products'),
            'title'  => $this->__('Show "Out of stock" Products'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $form->setValues($model->toArray());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareRelatedTabData(Varien_Object $object)
    {
        $object->setData('product_qty', Mage::helper('awautorelated/config')->getNumberOfProducts());

        if ($relatedProduct = $object->getData('related_products')) {
            $object->setData('order', $relatedProduct->getData('order/type'));
            $object->setData('order_attribute', $relatedProduct->getData('order/attribute'));
            $object->setData('order_direction', $relatedProduct->getData('order/direction'));
            $object->setData('show_out_of_stock', $relatedProduct->getData('show_out_of_stock'));
            $object->setData('product_qty', $object->getData('related_products')->getData('product_qty'));
        }
        return $object;
    }
}