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

abstract class AW_Autorelated_Block_Blocks_Abstract extends Mage_Core_Block_Template
{
    /** @var $_collection AW_Autorelated_Model_Product_Collection */
    protected $_collection = null;
    abstract public function getRelatedProductsLimit();

    protected function _getStoreId()
    {
        return Mage::app()->getStore()->getId();
    }

    protected function _initCollection()
    {
        if ($this->_collection === null) {
            $this->_collection = Mage::getModel('awautorelated/product_collection');
            $this->_collection->addAttributeToSelect('*');

            $_visibility = array(
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
            );

            $this->_collection
                ->addAttributeToFilter('visibility', $_visibility)
                ->addAttributeToFilter('status',
                    array(
                         'in' => Mage::getSingleton("catalog/product_status")->getVisibleStatusIds()
                    )
                )
            ;

            if (!$this->_getShowOutOfStock()) {
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_collection);
                $this->_collection
                    ->getSelect()
                    ->join(
                        array(
                             'inv_stock_status' => $this->_collection->getTable('cataloginventory/stock_status')
                        ),
                        'inv_stock_status.product_id = e.entity_id AND inv_stock_status.stock_status = 1',
                        array()
                    )
                ;
            }

            $this->_collection
                ->addStoreFilter($this->_getStoreId())
                ->joinCategoriesByProduct()
                ->groupByAttribute('entity_id')
            ;
        }
        return $this->_collection;
    }

    protected function _initCollectionForIds(array $ids, $sort = true)
    {
        unset($this->_collection);
        $this->_collection = Mage::getModel('awautorelated/product_collection');

        //init sort by
        if (true === $sort) {
            $ids = array_unique($ids);
            $orderSettings = $this->_getRelatedProductsOrder();
            switch ($orderSettings->getData('type')) {
                case AW_Autorelated_Model_Source_Block_Common_Order::RANDOM:
                    shuffle($ids);
                    $limit = $this->getRelatedProductsLimit();
                    if (count($ids) > $limit) {
                        array_splice($ids, $limit);
                    }
                    $this->_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                    break;
                case AW_Autorelated_Model_Source_Block_Common_Order::BY_ATTRIBUTE:
                    $this->_collection->addAttributeToSort(
                        $orderSettings->getData('attribute'),
                        $orderSettings->getData('direction')
                    );
                    break;
                case AW_Autorelated_Model_Source_Block_Common_Order::NONE:
                    $limit = $this->getRelatedProductsLimit();
                    if (count($ids) > $limit) {
                        array_splice($ids, $limit);
                    }
                    break;
            }
        }

        $this->_collection
            ->addAttributeToSelect('*')
            ->addFilterByIds($ids)
            ->setStoreId($this->_getStoreId())
        ;
        return $this->_collection;
    }

    protected function _getShowOutOfStock()
    {
        return $this->getData('related_products') instanceof Varien_Object
            && $this->getData('related_products')->getData('show_out_of_stock');
    }

    /**
     * @return AW_Autorelated_Model_Product_Collection
     */
    public function getCollection()
    {
        if ($this->canShow()) {
            if ($this->_collection === null) {
                $this->_initCollection();
                $this->_renderRelatedProductsFilters();
                $this->_postProcessCollection();
            }

            return $this->_collection;
        }
        return null;
    }

    protected function _postProcessCollection()
    {
        if ($this->_collection instanceof AW_Autorelated_Model_Product_Collection) {
            $this->_collection->setStoreId($this->_getStoreId())
                ->addMinimalPrice()
                ->groupByAttribute('entity_id');

            if (Mage::helper('awautorelated')->checkVersion('1.13.0.0', '!=')) {
                $this->_collection->addUrlRewrites();
            }

            if ($this->_getShowOutOfStock() && !Mage::helper('cataloginventory')->isShowOutOfStock()) {
                $fromPart = $this->_collection->getSelect()->getPart(Zend_Db_Select::FROM);
                if (isset($fromPart['price_index'])
                    && is_array($fromPart['price_index'])
                    && isset($fromPart['price_index']['joinType'])
                    && $fromPart['price_index']['joinType'] === Zend_Db_Select::INNER_JOIN
                ) {
                    $fromPart['price_index']['joinType'] = Zend_Db_Select::LEFT_JOIN;
                    $this->_collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
                }
            }
        }
        return $this;
    }

    public function getBlockPosition()
    {
        $position = null;
        if ($this->getParent() && $this->getParent()->getBlockPosition()) {
            $position = $this->getParent()->getBlockPosition();
        }
        return $position;
    }

    protected function _getCurrentlyViewed()
    {
        return $this->getData('currently_viewed') ? $this->getData('currently_viewed') : null;
    }

    protected function _getRelatedProducts()
    {
        return $this->getData('related_products') ? $this->getData('related_products') : null;
    }

    protected function _getRelatedProductsOrder()
    {
        if (!$this->_getData('_rp_order')) {
            $rpOrder = array(
                'type' => AW_Autorelated_Model_Source_Block_Common_Order::NONE
            );
            if (($relatedProducts = $this->_getRelatedProducts())
                && is_array($order = $relatedProducts->getData('order'))
            ) {
                $rpOrder = $order;
            }
            $this->setData('_rp_order', new Varien_Object($rpOrder));
        }
        return $this->_getData('_rp_order');
    }

    protected function _beforeToHtml()
    {
        $this->_setTemplate();
        return parent::_beforeToHtml();
    }

    abstract protected function _setTemplate();

    abstract protected function _renderRelatedProductsFilters();

    abstract public function canShow();
}