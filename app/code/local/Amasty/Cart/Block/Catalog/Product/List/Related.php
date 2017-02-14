<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2013 Amasty (http://www.amasty.com)
 * @package Amasty_Cart
 */
class Amasty_Cart_Block_Catalog_Product_List_Related extends Mage_Catalog_Block_Product_List_Related
{
    const maxItemsCount = 3;

    protected function _prepareData()
    {
        $product = Mage::registry('product');
        /* @var $product Mage_Catalog_Model_Product */

        $this->_itemCollection = $product->getRelatedProductCollection()
            ->addAttributeToSelect('required_options')
            ->setPositionOrder()
            ->addStoreFilter()
        ;

        if (Mage::helper('catalog')->isModuleEnabled('Mage_Checkout')) {
            Mage::getResourceSingleton('checkout/cart')->addExcludeProductFilter($this->_itemCollection,
                Mage::getSingleton('checkout/session')->getQuoteId()
            );
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);

        /* Amasty code start add limit and stock filter*/
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_itemCollection);
        $this->_itemCollection->getSelect()->limit(self::maxItemsCount);
        /*Amasty code end*/

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }
}