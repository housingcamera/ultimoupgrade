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


class AW_Countdown_Model_Countdown_Condition_Combine_Product
    extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    public function getValidatedProductIds($productIds, $storeId)
    {
        $_stringConditions = '';
        $_categoryCondition = null;
        $_attributeValueCondition = 'IF('
            . 'IF(cpev.value IS NULL,'
            . 'IF(cpei.value IS NULL,'
            . 'IF(cpet.value IS NULL,'
            . 'IF(cped.value IS NULL, NULL, cped.value), cpet.value), cpei.value), cpev.value) IS NULL,'
            . 'IF(cpev_def.value IS NULL,'
            . 'IF(cpei_def.value IS NULL,'
            . 'IF(cpet_def.value IS NULL,'
            . 'IF(cped_def.value IS NULL, NULL, cped_def.value), cped_def.value), cpei_def.value), cpev_def.value),'
            . 'IF(cpev.value IS NULL,'
            . 'IF(cpei.value IS NULL,'
            . 'IF(cpet.value IS NULL,'
            . 'IF(cped.value IS NULL, NULL, cped.value), cpet.value), cpei.value), cpev.value)'
            .')'
        ;

        $readAdapter = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $readAdapter->select();
        $productEntityIdColumnName = 'cpe.entity_id';
        $select
            ->from(array('cpe' => $this->_getTableName('catalog_product_entity')),
                array(
                    'product_id' => $productEntityIdColumnName
                )
            )
        ;

        switch ($this->getAttribute()) {
            case 'category_ids':
                $productEntityIdColumnName = 'ccpi.product_id';
                $select = $readAdapter->select();
                $select
                    ->from(array('ccpi' => $this->_getTableName('catalog_category_product')),
                        array(
                             'product_id' => $productEntityIdColumnName
                        )
                    )
                ;
                $select->having($this->_getSqlCondition('GROUP_CONCAT(ccpi.category_id)'));
                break;
            case 'sku':
                $select
                    ->where($this->_getSqlCondition('cpe.sku'))
                ;
                break;
            default:
                $_stringConditions .= $this->_getSqlConditionWithAttributeFilter($_attributeValueCondition);
                $select
                    ->joinLeft(array('ea' => $this->_getTableName('eav_attribute')),
                        'ea.entity_type_id = cpe.entity_type_id',
                        array('attribute_code' => 'ea.attribute_code')
                    )
                    ->joinLeft(array('cpev' => $this->_getTableName('catalog_product_entity_varchar')),
                        'cpev.entity_id = ' . $productEntityIdColumnName . ' AND cpev.attribute_id = ea.attribute_id AND cpev.store_id = ' . $storeId,
                        array()
                    )
                    ->joinLeft(array('cpei' => $this->_getTableName('catalog_product_entity_int')),
                        'cpei.entity_id = ' . $productEntityIdColumnName . ' AND cpei.attribute_id = ea.attribute_id AND cpei.store_id = ' . $storeId,
                        array()
                    )
                    ->joinLeft(array('cpet' => $this->_getTableName('catalog_product_entity_text')),
                        'cpet.entity_id = ' . $productEntityIdColumnName . ' AND cpet.attribute_id = ea.attribute_id AND cpet.store_id = ' . $storeId,
                        array()
                    )
                    ->joinLeft(array('cped' => $this->_getTableName('catalog_product_entity_decimal')),
                        'cped.entity_id = ' . $productEntityIdColumnName . ' AND cped.attribute_id = ea.attribute_id AND cped.store_id = ' . $storeId,
                        array()
                    )
                    ->joinLeft(array('cpev_def' => $this->_getTableName('catalog_product_entity_varchar')),
                        'cpev_def.entity_id = ' . $productEntityIdColumnName . ' AND cpev_def.attribute_id = ea.attribute_id AND cpev_def.store_id = 0',
                        array()
                    )
                    ->joinLeft(array('cpei_def' => $this->_getTableName('catalog_product_entity_int')),
                        'cpei_def.entity_id = ' . $productEntityIdColumnName . ' AND cpei_def.attribute_id = ea.attribute_id AND cpei_def.store_id = 0',
                        array()
                    )
                    ->joinLeft(array('cpet_def' => $this->_getTableName('catalog_product_entity_text')),
                        'cpet_def.entity_id = ' . $productEntityIdColumnName . ' AND cpet_def.attribute_id = ea.attribute_id AND cpet_def.store_id = 0',
                        array()
                    )
                    ->joinLeft(array('cped_def' => $this->_getTableName('catalog_product_entity_decimal')),
                        'cped_def.entity_id = ' . $productEntityIdColumnName . ' AND cped_def.attribute_id = ea.attribute_id AND cped_def.store_id = 0',
                        array()
                    )
                ;
                if (!empty($_stringConditions)) {
                    $select
                        ->where($_stringConditions)
                    ;
                }
            break;
        }

        $select
            ->where($productEntityIdColumnName . ' IN(?)', $productIds)
            ->group($productEntityIdColumnName)
        ;
        return $readAdapter->fetchCol($select);
    }

    protected function _getTableName($tableName)
    {
        return Mage::getSingleton('core/resource')->getTableName($tableName);
    }

    protected function _getSqlConditionWithAttributeFilter($_attributeValueCondition)
    {
        $_sqlOperator = '(attribute_code = "' . $this->getAttribute()
            . '" AND ' .  $this->_getSqlCondition($_attributeValueCondition)  . ')'
        ;
        return $_sqlOperator;
    }

    protected function _getSqlCondition($columnName)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY,
            $this->getAttribute()
        );
        $_quote = '"';
        if ($attribute->getBackendType() == 'decimal') {
            $_quote = '';
        }
        $value = $this->getValue();
        switch ($this->getOperator()) {
            case '=='  :
                $_sqlOperator = $columnName . ' = ' . $_quote . $value . $_quote;
                break;
            case '{}'  :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = $columnName . ' LIKE("%' . trim($value[0]) . '%")';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' OR ' . $columnName . ' LIKE("%' . trim($_conditionValue) . '%")';
                }
                break;
            case '!{}' :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = $columnName . 'NOT LIKE("%' . trim($value[0]) . '%)';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' AND ' . $columnName . ' NOT LIKE("%' . trim($_conditionValue) . '%")';
                }
                break;
            case '()'  :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = 'FIND_IN_SET("' . trim($value[0]) . '",' . $columnName . ')';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' OR FIND_IN_SET(' . $_quote . trim($_conditionValue) . $_quote . ',' . $columnName . ')';
                }
                break;
            case '!()' :
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $_sqlOperator = '!FIND_IN_SET(' . $_quote . trim($value[0]) .  $_quote . ',' . $columnName . ')';
                unset($value[0]);
                foreach ($value as $_conditionValue) {
                    $_sqlOperator .= ' AND !FIND_IN_SET(' . $_quote . trim($_conditionValue) . $_quote . ',' . $columnName . ')';
                }
                break;
        }
        if (!isset($_sqlOperator)) {
            $_sqlOperator = $columnName . $this->getOperator() . $_quote . $value . $_quote;
        }
        return $_sqlOperator;
    }
}