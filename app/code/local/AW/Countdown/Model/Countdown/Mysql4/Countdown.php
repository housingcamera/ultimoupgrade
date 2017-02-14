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


class AW_Countdown_Model_Countdown_Mysql4_Countdown extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields   = array(
        'recurring_data' => array(null, array())
    );

    public function _construct()
    {
        $this->_init('awcountdown/countdown', 'countdownid');
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $groupIds = $object->getCustomerGroupIds();
        if (is_string($groupIds)) {
            $object->setCustomerGroupIds(explode(',', $groupIds));
        }
        $storeIds = $object->getStoreIds();
        if (!is_array($storeIds)) {
            $object->setStoreIds(explode(',', $storeIds));
        }
        if (is_array($object->getRecurringData()) && count($object->getRecurringData()) > 0) {
            $object->addData($object->getRecurringData());
        }
        return parent::_afterLoad($object);
    }

    public function getIndexesProductsIds($countDownModelId, $storeId = null)
    {
        $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readAdapter->select();
        $select
            ->from(array('main_table' => $this->getTable('awcountdown/countdown_index')),
                array(
                     'product_id' => 'main_table.product_id'
                )
            )
            ->where('main_table.countdown_id =?', $countDownModelId)
            ->group('main_table.product_id')
        ;
        if (null !== $storeId) {
            $select->where('main_table.store_id =?', $storeId);
        }
        return $readAdapter->fetchCol($select);
    }

    public function changeProductsStatus(array $productIds, $storeId, $status)
    {
        if (count($productIds) == 0) {
            return $this;
        }
        $statusAttribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'status');
        $statusAttributeId = $statusAttribute->getId();
        $statusAttributeCode = $statusAttribute->getAttributeCode();
        $writeAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        foreach ($productIds as $productId) {
            $sql = "INSERT INTO {$this->getValueTable('catalog/product', 'int')} "
                . "(entity_type_id, entity_id, attribute_id, store_id, value) VALUES "
                . "((SELECT entity_type_id FROM {$this->getTable('catalog/product')} "
                . "WHERE entity_id = {$productId}), {$productId}, {$statusAttributeId}, {$storeId}, {$status}) "
                . "ON DUPLICATE KEY UPDATE value = {$status}"
            ;
            $writeAdapter->query($sql);
        }

        $attrData = array($statusAttributeCode => $status);
        $massActionData = new Varien_Object;
        $massActionData->addData(
            array(
                'product_ids'     => $productIds,
                'attributes_data' => $attrData,
                'store_id'        => $storeId
            )
        );
        Mage::register('aw_countdown_skip_indexer', 1, true);
        Mage::getSingleton('index/indexer')->processEntityAction(
            $massActionData, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
        );
        return $this;
    }
}
