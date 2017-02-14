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

class AW_Autorelated_Adminhtml_CategoryblockController extends AW_Autorelated_Adminhtml_AbstractblockController
{
    protected function _initCategoryBlock()
    {
        $blockModel = Mage::getModel('awautorelated/blocks');
        $blockId  = (int) $this->getRequest()->getParam('id');
        if ($blockId) {
            try {
                $blockModel->load($blockId);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        if (null !== Mage::getSingleton('adminhtml/session')->getCategoryBlockData()) {
            $blockModel->addData(Mage::getSingleton('adminhtml/session')->getCategoryBlockData());
            Mage::getSingleton('adminhtml/session')->setCategoryBlockData(null);
        }

        /**
         * render chosen categories in
         * AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_Currentlyviewed_Categoriesgrid
         */
        $currentlyViewed = $blockModel->getCurrentlyViewed();
        $categoryIds = array();
        if (is_object($currentlyViewed)) {
            if (null !== $currentlyViewed->getCategoryIds()) {
                $categoryIds = explode(',', $currentlyViewed->getCategoryIds());
            }
            $blockModel->setCurrentlyViewedCategoriesArea($currentlyViewed->getArea());
        }
        $blockModel->setCategoryIds($categoryIds);

        Mage::register('categoryblock_data', $blockModel);
        return $blockModel;
    }
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
        $this->_initAction();
        $this->_initCategoryBlock();

        $this
            ->_addContent($this->getLayout()
                ->createBlock('awautorelated/adminhtml_blocks_category_edit')
            )
            ->_addLeft($this->getLayout()
                    ->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs')
            )
        ;
        $this->_setTitle($this->getRequest()->getParam('id') ? 'Edit Category Block' : 'Add Category Block');
        $this->renderLayout();

        return $this;
    }

    public function categoriesJsonAction()
    {
        $this->_initCategoryBlock();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs_currentlyviewed_categoriesgrid')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function saveAction()
    {
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getParams();
            if (!$data['name']) {
                $this->_getSession()->addError($this->__("Name couldn't be empty"));
            }

            if (is_array($data['customer_groups'])
                && in_array(Mage_Customer_Model_Group::CUST_GROUP_ALL, $data['customer_groups'])
            ) {
                $data['customer_groups'] = array(Mage_Customer_Model_Group::CUST_GROUP_ALL);
            }

            $data = $this->_filterDates($data, array('date_from', 'date_to'));

            $data['currently_viewed']['category_ids'] = Mage::helper('awautorelated')->prepareArray(
                $data['category_ids']
            );

            if (!isset($data['currently_viewed']['area'])
                || (($data['currently_viewed']['area'] == 2) && (!$data['currently_viewed']['category_ids']))
            ) {
                $this->_getSession()->addError($this->__("Categories are not specified"));
            }

            if (!isset($data['related_products']['count']) || intval($data['related_products']['count']) < 1) {
                $this->_getSession()->addError($this->__('Count of products should be an integer and greater than 0'));
            }

            $data = $this->_prepareRelatedTabData($data);

            $data['type'] = AW_Autorelated_Model_Source_Type::CATEGORY_PAGE_BLOCK;

            $model = $this->_initCategoryBlock();
            $model->addData($data);

            if (array_key_exists('saveasnew', $data)) {
                $model->setId(null);
            }

            if ($this->_hasErrors()) {
                Mage::getSingleton('adminhtml/session')->setCategoryBlockData($model->humanizeData()->getData());
                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }

            try {
                $model->save();
                Mage::getSingleton('adminhtml/session')->setCategoryBlockData(null);
                $this->_getSession()->addSuccess($this->__('Block has been succesfully saved'));
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__($e->getMessage()));
            }

            if ($this->getRequest()->getParam('continue') || $this->getRequest()->getParam('saveasnew')) {
                return $this->_redirect('*/*/edit',
                    array(
                        'id'           => $model->getId(),
                        'continue_tab' => $data['continue_tab']
                    )
                );
            }
        }
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function _prepareRelatedTabData(array $data)
    {
        $conditions = $data['rule'];
        if (is_array($conditions) && isset($conditions['related'])) {
            $conditionsRelated = $conditions['related'];
            $conditionsRelated = Mage::helper('awautorelated')->updateChild(
                $conditionsRelated,
                'catalogrule/rule_condition_combine',
                'awautorelated/catalogrule_rule_condition_combine'
            );
            $conditions['related'] = $conditionsRelated;
        }

        $conditions = Mage::helper('awautorelated')->convertFlatToRecursive($conditions, array('related'));
        $data['related_products']['conditions'] = $conditions['related']['related_conditions'];
        return $data;
    }

    protected function deleteAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/delete', array(
            'id' => $this->getRequest()->getParam('id')
        ));
    }

    protected function indexAction()
    {
        return $this->_redirect('*/adminhtml_blocksgrid/list');
    }

    protected function _isAllowed()
    {
        $helper = Mage::helper('awautorelated');
        switch ($this->getRequest()->getActionName()) {
            case 'delete':
            case 'new':
            case 'save':
                return $helper->isEditAllowed();
                break;
            case 'categoriesJson':
            case 'edit':
            case 'index':
            case 'newConditionHtml':
                return $helper->isViewAllowed() || $helper->isEditAllowed();
                break;
            default:
                return false;
        }
    }
}