<?php

/**
 * StitchLabs_ChannelIntegration extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category       StitchLabs
 * @package        StitchLabs_ChannelIntegration
 * @copyright      Copyright (c) 2014
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
class StitchLabs_ChannelIntegration_Model_Product_Api
    extends Mage_Catalog_Model_Product_Api
{

    /**
     * Retrieve list of products with basic info (id, sku, type, set, name)
     *
     * @param null|object|array $filters
     * @param string|int $store
     * @return array
     */
    public function items($filters = null, $store = null)
    {
        echo '';
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($this->_getStoreId($store))
            ->addAttributeToSelect('*');

        /** @var $apiHelper Mage_Api_Helper_Data */
        $apiHelper = Mage::helper('api');
        $filters = $apiHelper->parseFilters($filters, $this->_filtersMap);
        try {
            $page = 1;
            $page_size = 50;

            foreach ($filters as $field => $value) {
                if ($field == 'page') {
                    $page = (int) $value;
                } elseif ($field == 'size') {
                    $page_size = (int) $value;
                } else {
                    $collection->addFieldToFilter($field, $value);
                }
            }

            $offset = 0;

            if ($page > 0) {
                $offset = $page - 1;
            }

            $offset = $offset * $page_size;

            $collection->getSelect()->limit($page_size, $offset);
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        $result = array();

        Mage::getSingleton('cataloginventory/stock_item')->addCatalogInventoryToProductCollection($collection);

        foreach ($collection as $product) {
            $temp_result = array(
                'product_id' => $product->getId(),
                'sku' => $product->getSku(),
                'name' => $product->getName(),
                'set' => $product->getAttributeSetId(),
                'type' => $product->getTypeId(),
                'category_ids' => $product->getCategoryIds(),
                'image_url' => $product->getImageUrl(),
                'configurable_parent_product_ids' => array(),
                'grouped_parent_product_ids' => array(),
                'configurable_child_product_ids' => array(),
                'grouped_child_product_ids' => array(),
            );

            if ($product->isConfigurable()) {
                $child_product_ids = $product->getTypeInstance()->getUsedProductIds();
                $temp_result['configurable_child_product_ids'] = $child_product_ids;
            } elseif ($product->isGrouped()) {
                $child_product_ids = $product->getTypeInstance()->getAssociatedProductIds();
                $temp_result['grouped_child_product_ids'] = $child_product_ids;
            } else {
                $grouped_parent_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
                $temp_result['grouped_parent_product_ids'] = $grouped_parent_ids;

                $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                $temp_result['configurable_parent_product_ids'] = $parent_ids;
            }

            $result[] = $this->infoResult($temp_result, $product);;
        }
        return $result;
    }

    /**
     * Retrieve product info
     *
     * @param int|string $productId
     * @param string|int $store
     * @param array $attributes
     * @return array
     */
    public function info($productId, $store = null, $attributes = null, $identifierType = null)
    {
        $product = $this->_getProduct($productId, $store, $identifierType);

        $result = array(
            'product_id' => $product->getId(),
            'sku' => $product->getSku(),
            'name' => $product->getName(),
            'set' => $product->getAttributeSetId(),
            'type' => $product->getTypeId(),
            'category_ids' => $product->getCategoryIds(),
            'image_url' => $product->getImageUrl(),
            'configurable_parent_product_ids' => array(),
            'grouped_parent_product_ids' => array(),
            'configurable_child_product_ids' => array(),
            'grouped_child_product_ids' => array(),
        );

        if ($product->isConfigurable()) {
            $child_product_ids = $product->getTypeInstance()->getUsedProductIds();
            $result['configurable_child_product_ids'] = $child_product_ids;
        } elseif ($product->isGrouped()) {
            $child_product_ids = $product->getTypeInstance()->getAssociatedProductIds();
            $result['grouped_child_product_ids'] = $child_product_ids;
        } else {
            $grouped_parent_ids = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
            $result['grouped_parent_product_ids'] = $grouped_parent_ids;

            $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            $result['configurable_parent_product_ids'] = $parent_ids;
        }

        return $this->infoResult($result, $product);
    }

    public function infoResult($result, $product, $attributes = array(), $store = null, $all_attributes = true)
    {
        $productId = $product->getId();
        if (in_array('url_complete', $attributes) || $all_attributes) {
            $result['url_complete'] = $product->setStoreId($store)->getProductUrl();
        }

        if (in_array('description', $attributes) || $all_attributes) {
            $result['description'] = $product->setStoreId($store)->getDescription();
        }

        if (in_array('price', $attributes) || $all_attributes) {
            $result['price'] = $product->setStoreId($store)->getPrice();
        }

        if (in_array('weight', $attributes) || $all_attributes) {
            $result['weight'] = $product->setStoreId($store)->getWeight();
        }

        if (in_array('status', $attributes) || $all_attributes) {
            $result['status'] = $product->setStoreId($store)->getStatus();
        }

        if (in_array('stock_data', $attributes) || $all_attributes) {
            $result['stock_data'] = Mage::getSingleton('Mage_CatalogInventory_Model_Stock_Item_Api')->items($productId);
        }

        if (in_array('images', $attributes) || $all_attributes) {
            $result['images'] = Mage::getSingleton('Mage_Catalog_Model_Product_Attribute_Media_Api')->items(
                $productId,
                $store
            );
        }

        if (!$product->isSuper() && (in_array('parent_sku', $attributes) || $all_attributes)) {
            $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            if (!$parentIds) {
                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
            }
            if (isset($parentIds[0])) {
                $parent = Mage::getModel('catalog/product')->load($parentIds[0]);
                $result['parent_sku'] = $parent->getSku();
            }
        } elseif ($product->isConfigurable()) {
            $attributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray();
            // configurable_options
            if (in_array('configurable_attributes_data', $attributes) || $all_attributes) {
                $options = array();
                $k = 0;
                foreach ($attributesData as $attribute) {
                    $options[$k]['code'] = $attribute['attribute_code'];
                    foreach ($attribute['values'] as $value) {
                        $value['attribute_code'] = $attribute['attribute_code'];
                        $options[$k]['options'][] = $value;
                    }
                    $k++;
                }
                $result['configurable_attributes_data'] = $options;
                // children
                // @todo use $childProducts = $product->getTypeInstance()->getUsedProducts();
                $childProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getUsedProducts(null, $product);
                $skus = array();
                $i = 0;
                foreach ($childProducts as $childProduct) {
                    $skus[$i]['product_id'] = $childProduct->getId();
                    $skus[$i]['sku'] = $childProduct->getSku();
                    $j = 0;
                    foreach ($attributesData as $attribute) {
                        $skus[$i]['options'][$j]['label'] = $attribute['label'];
                        $skus[$i]['options'][$j]['attribute_code'] = $attribute['attribute_code'];
                        $skus[$i]['options'][$j]['value_index'] = $childProduct[$attribute['attribute_code']];
                        $skus[$i]['options'][$j]['value_text'] = $childProduct->getAttributeText($attribute['attribute_code']);
                        $j++;
                    }
                    $i++;
                }
                $result['configurable_products_data'] = $skus;
            }
        }

        return $result;
    }
}
