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

class AW_Autorelated_Block_Blocks_Category extends AW_Autorelated_Block_Blocks_Abstract
{
    const CACHE_TIME = 300;

    protected $_canShow = null;
    protected $_currentCategory = null;

    public function canShow()
    {
        if ($this->_canShow !== null) {
            return $this->_canShow;
        }

        $this->_canShow = false;
        $currentlyViewed = $this->_getCurrentlyViewed();
        $currentCategory = $this->_getCurrentCategory();

        if ($currentlyViewed && $currentlyViewed instanceof Varien_Object
            && (
                ($currentCategory && Mage::registry('current_product') === null)
                || $this->getBlockPosition() == AW_Autorelated_Model_Source_Position::CUSTOM
            )
        ) {
            if ($currentlyViewed->getData('area') == 1) {
                // Categories = ALL
                $this->_canShow = true;
                return $this->_canShow;
            }

            if (!$currentCategory || !$categoryIds = $currentlyViewed->getData('category_ids')) {
                return $this->_canShow;
            }

            // Block has category IDs
            if (is_string($categoryIds)) {
                $categoryIds = explode(',', $categoryIds);
            }

            if (is_array($categoryIds) && in_array($currentCategory->getId(), $categoryIds)) {
                $this->_canShow = true;
            }
        }
        return $this->_canShow;
    }

    protected function _getCurrentCategory()
    {
        if ($this->_currentCategory === null) {
            if ($this->getParent() && $this->getParent()->getData('category_id')) {
                if (preg_match("/^category\/([0-9]*)$/", $this->getParent()->getData('category_id'), $matches)) {
                    $categoryId = isset($matches[1]) ? intval($matches[1]) : null;
                    if ($categoryId) {
                        $category = Mage::getModel('catalog/category')->load($categoryId);
                        if ($category->getData()) {
                            $this->_currentCategory = $category;
                        }
                    }
                }
            } else {
                $this->_currentCategory = Mage::registry('current_category');
            }
        }
        return $this->_currentCategory;
    }

    protected function _setTemplate()
    {
        if (!$this->getTemplate()) {
            if (Mage::helper('awautorelated')->checkVersion('1.4')) {
                $this->setTemplate('aw_autorelated/blocks/category/category.phtml');
            } else {
                // Magento 1.3.x
                $this->setTemplate('aw_autorelated/blocks/category/category_13.phtml');
            }
        }
        return $this;
    }

    public function getMatchingIds()
    {
        return AW_Autorelated_Model_Cache::getCategoryBlockMatchedIds($this->getId());
    }

    protected function _getCacheKey()
    {
        $cacheKey = AW_Autorelated_Model_Cache::CACHE_KEY_CATEGORY
            . '-' . $this->getId() . '-' . Mage::app()->getStore()->getId()
        ;
        $relatedProducts = $this->_getRelatedProducts();
        if ($relatedProducts
            && $relatedProducts->getData('include') != AW_Autorelated_Model_Source_Block_Category_Include::ALL
            && $this->_getCurrentCategory()
        ) {
            $cacheKey .= '-' . $this->_getCurrentCategory()->getId();
        }
        return $cacheKey;
    }

    protected function _getRelatedIds()
    {
        $relatedIds = @unserialize(Mage::app()->loadCache($this->_getCacheKey()));
        if (is_array($relatedIds)) {
            return $relatedIds;
        }

        $filteredIds = $this->getMatchingIds();
        $intersectedArray = $this->_collection->getAllIds();

        if (null !== $filteredIds) {
            $intersectedArray = array_intersect($intersectedArray, $filteredIds);
        }

        $relatedProducts = $this->_getRelatedProducts();
        $currentCategory = $this->_getCurrentCategory();
        if ($intersectedArray) {
            $this->_initCollectionForIds($intersectedArray, false);

            // Setting include filter
            if ($relatedProducts->getData('include') != AW_Autorelated_Model_Source_Block_Category_Include::ALL
                && $currentCategory
            ) {
                $include = true;
                if ($relatedProducts->getData('include')
                    == AW_Autorelated_Model_Source_Block_Category_Include::CURRENT_CATEGORY
                ) {
                    $include = false;
                }
                $this->_collection->addCategoriesFilter($currentCategory->getId(), $include);
            }

            $relatedIds = array();
            if ($relatedProducts->getData('include') == AW_Autorelated_Model_Source_Block_Category_Include::ALL
                || $currentCategory
            ) {
                $relatedIds = $this->_collection->getAllIds();
                $relatedIds = array_diff($relatedIds, Mage::helper('awautorelated')->getWishlistProductsIds());
                $relatedIds = array_diff($relatedIds, Mage::getSingleton('checkout/cart')->getProductIds());
            }

            if (AW_Autorelated_Model_Cache::CACHE_ENABLED) {
                Mage::app()->saveCache(serialize($relatedIds), $this->_getCacheKey(), array(), self::CACHE_TIME);
            }
        }
        return $relatedIds;
    }

    protected function _renderRelatedProductsFilters()
    {
        $limit = $this->_getRelatedProducts()->getData('count');
        $relatedIds = $this->_getRelatedIds();
        if ($relatedIds) {
            $this->_initCollectionForIds($relatedIds, true);
            $this->_collection->setPageSize($limit);
            $this->_collection->setCurPage(1);
        } else {
            $this->_collection = null;
        }
        return $this;
    }

    public function getRelatedProductsLimit()
    {
        return $this->_getRelatedProducts()->getData('count');
    }
}