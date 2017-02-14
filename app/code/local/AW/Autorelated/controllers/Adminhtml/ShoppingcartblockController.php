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

require_once 'AbstractblockController.php';

class AW_Autorelated_Adminhtml_ShoppingcartblockController extends AW_Autorelated_Adminhtml_AbstractblockController
{
    const BLOCK_REGISTRY_KEY = 'aw_arp2_scb';

    protected function _initAction()
    {
        return $this->loadLayout()->_setActiveMenu('catalog/awautorelated');
    }

    protected function newAction()
    {
        return $this->_redirect('*/*/edit');
    }

    protected function editAction()
    {
        /** @var $helperForms AW_Autorelated_Helper_Forms */
        $helperForms = Mage::helper('awautorelated/forms');
        $this->_initAction();
        $blockId = $this->getRequest()->getParam('id');
        if (!($block = $helperForms->getFormData($blockId))) {
            $block = Mage::getModel('awautorelated/blocks')->load($blockId);
            if (!$block->getId() && $blockId) {
                $this->_getSession()->addError($this->__("Couldn't load block by given ID"));
                return $this->_redirect('*/adminhtml_blocksgrid/list');
            }
        }
        Mage::register(self::BLOCK_REGISTRY_KEY, $block);
        $this->_setTitle($this->getRequest()->getParam('id') ? 'Edit Shopping Cart Block' : 'Add Shopping Cart Block');
        $this->_addContent($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit'))
            ->_addLeft($this->getLayout()->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit_tabs'));
        $this->renderLayout();

        return $this;
    }

    protected function saveAction()
    {
        /** @var $helperForms AW_Autorelated_Helper_Forms */
        $helperForms = Mage::helper('awautorelated/forms');
        $data = $this->getRequest()->getParams();
        unset($data['id']);

        if (!isset($data['name'])) {
            $this->_getSession()->addError($this->__("Name couldn't be empty"));
        }

        if (is_array($data['customer_groups'])
            && in_array(Mage_Customer_Model_Group::CUST_GROUP_ALL, $data['customer_groups'])
        ) {
            $data['customer_groups'] = array(Mage_Customer_Model_Group::CUST_GROUP_ALL);
        }

        $data = $this->_filterDates($data, array('date_from', 'date_to'));
        $data = $this->_prepareRelatedAndViewedTabsData($data);
        $data['type'] = AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK;

        $id = ($this->getRequest()->getParam('saveasnew')) ? null : (int)$this->getRequest()->getParam('id');
        $model = Mage::getModel('awautorelated/blocks')->load($id);
        $model->addData($data);

        if ($this->_hasErrors()) {
            $helperForms->setFormData($model->humanizeData());
            return $this->_redirect('*/*/edit', array('id' => $id));
        }

        try {
            $model->save();
            $helperForms->unsetFormData($model->getId());
            $this->_getSession()->addSuccess($this->__('Block has been succesfully saved'));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
        }

        if ($this->getRequest()->getParam('continue') || $this->getRequest()->getParam('saveasnew')) {
            return $this->_redirect('*/*/edit',
                array(
                    'id'           => $model->getId(),
                    'continue_tab' => $this->getRequest()->getParam('continue_tab')
                )
            );
        }
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function _prepareRelatedAndViewedTabsData($data)
    {
        $conditions = $data['rule'];
        if (is_array($conditions)) {
            if (isset($conditions['viewed'])) {
                $viewedConditions = $conditions['viewed'];
                $viewedConditions = Mage::helper('awautorelated')->updateChild(
                    $viewedConditions,
                    'salesrule/rule_condition_combine',
                    'awautorelated/salesrule_rule_condition_combine'
                );
                $conditions['viewed'] = $viewedConditions;
                unset($viewedConditions);
            }
            if (isset($conditions['related'])) {
                $relatedConditions = $conditions['related'];
                $relatedConditions = Mage::helper('awautorelated')->updateChild(
                    $relatedConditions,
                    'catalogrule/rule_condition_combine',
                    'awautorelated/catalogrule_rule_condition_combine'
                );
                $conditions['related'] = $relatedConditions;
                unset($relatedConditions);
            }
            $conditions = Mage::helper('awautorelated')->convertFlatToRecursive(
                $conditions,
                array('viewed', 'related')
            );
            $data['currently_viewed']['conditions'] = $conditions['viewed']['viewed_conditions'];
            $data['related_products']['conditions'] = $conditions['related']['related_conditions'];
        }

        if (isset($data['related_products'])
            && isset($data['related_products']['options'])
            && is_array($data['related_products']['options'])
        ) {
            $_options = array();
            foreach ($data['related_products']['options'] as $_option) {
                if (!isset($_option['delete']) || !$_option['delete']) {
                    unset($_option['delete']);
                    $_options[] = $_option;
                }
            }
            $data['related_products']['options'] = $_options;
            unset($_options);
        }
        return $data;
    }

    protected function indexAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function deleteAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/delete', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }
}