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


class AW_Countdown_Model_Countdown_Mysql4_Indexer extends Mage_Index_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('awcountdown/countdown_index', 'countdown_id');
    }

    public function reindexAll()
    {
        $this->useIdxTable(true);
        $this->beginTransaction();
        try {
            $this->clearTemporaryIndexTable();
            $countDownCollection = Mage::getModel('awcountdown/countdown')->getCollection();
            foreach ($countDownCollection as $countDownModel) {
                $countDownModel = $countDownModel->load($countDownModel->getId());
                $this->_prepareIndex($countDownModel);
            }
            $this->syncData();
            $this->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    protected function _prepareIndex($countDownModel, $productIds = array())
    {
        $write    = $this->_getWriteAdapter();
        $idxTable = $this->getIdxTable();
        $storeCollection = Mage::getModel('core/store')->getCollection();
        foreach ($storeCollection as $storeModel) {
            $storeIds = $countDownModel->getStoreIds();
            if (!is_array($storeIds)) {
                $storeIds = explode(',', $storeIds);
            }
            if ((!in_array($storeModel->getId(), $storeIds)
                && !in_array(0, $storeIds)) == TRUE
            ) {
                continue;
            }

            $_ids = $productIds;
            if (count($_ids) == 0) {
                $productCollection = Mage::getModel('catalog/product')->getCollection();
                $productCollection->addStoreFilter($storeModel);
                $_ids = $productCollection->getAllIds();
            }

            $validatedProductIds = $countDownModel->getValidatedProductIds($_ids, $storeModel->getId());
            foreach ($validatedProductIds as $productId) {
                $write->insert($idxTable,
                    array(
                        'countdown_id' => $countDownModel->getId(),
                        'product_id'   => $productId,
                        'store_id'     => $storeModel->getId()
                    )
                );
            }
        }
        return $this;
    }

    public function awcountdownSave(Mage_Index_Model_Event $event)
    {
        $countDownModel = $event->getDataObject();
        if ($countDownModel instanceof AW_Countdown_Model_Countdown) {
            $this->useIdxTable(true);
            $this->clearTemporaryIndexTable();
            $this->_prepareIndex($countDownModel);
            $this->_synchronizeIndexData($countDownModel);
        }
    }

    public function awcountdownDelete(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (empty($data['delete_countdown_id'])) {
            return $this;
        }
        $countDownModel = Mage::getModel('awcountdown/countdown')->setId($data['delete_countdown_id']);
        $this->_deleteIndexData($countDownModel);
    }

    protected function _deleteIndexData($countDownModel)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $where = $adapter->quoteInto('countdown_id = ?', $countDownModel->getId());
            $adapter->delete($this->getMainTable(), $where);
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        return $this;
    }

    protected function _synchronizeIndexData($countDownModel, $productIds = array())
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $where = $adapter->quoteInto('countdown_id = ?', $countDownModel->getId());
            if (count($productIds) > 0) {
                $where .= ' AND ' .  $adapter->quoteInto('product_id IN (?)', $productIds);
            }
            $adapter->delete($this->getMainTable(), $where);
            $this->insertFromTable($this->getIdxTable(), $this->getMainTable());
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        return $this;
    }

    public function catalogProductSave(Mage_Index_Model_Event $event)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $event->getDataObject();
        if (null !== $product->getId()) {
            return $this->_updateByProductIds(array($product->getId()));
        }
        return $this;
    }

    public function catalogProductMassAction(Mage_Index_Model_Event $event)
    {
        if (Mage::registry('aw_countdown_skip_indexer')) {
            return $this;
        }
        /* @var $actionObject Varien_Object */
        $actionObject = $event->getDataObject();
        if (!$actionObject instanceof Varien_Object) {
            return $this;
        }
        $productIds = $actionObject->getProductIds();
        if (count($productIds) > 0) {
            return $this->_updateByProductIds($productIds);
        }
        return $this;
    }

    protected function _updateByProductIds(array $productIds)
    {
        $countDownCollection = Mage::getModel('awcountdown/countdown')->getCollection();
        foreach ($countDownCollection as $countDownModel) {
            $countDownModel = $countDownModel->load($countDownModel->getId());
            $this->useIdxTable(true);
            $this->clearTemporaryIndexTable();
            $this->_prepareIndex($countDownModel, $productIds);
            $this->_synchronizeIndexData($countDownModel, $productIds);
        }
        return $this;
    }

    public function catalogProductDelete(Mage_Index_Model_Event $event)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $data = $event->getNewData();
        if (empty($data['delete_product_id'])) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();
        try {
            $where = $adapter->quoteInto('product_id = ?', $data['delete_product_id']);
            $adapter->delete($this->getMainTable(), $where);
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        return $this;
    }
}