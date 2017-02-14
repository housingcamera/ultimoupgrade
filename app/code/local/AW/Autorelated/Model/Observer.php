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

class AW_Autorelated_Model_Observer
{
    protected function _getShoppingCartCrosssellsBlocks()
    {
        /** @var $collection AW_Autorelated_Model_Mysql4_Blocks_Collection */
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->addStoreFilter()
            ->addTypeFilter(AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK)
            ->addPositionFilter(AW_Autorelated_Model_Source_Position::REPLACE_CROSSSELS_BLOCK);
        return $collection->getSize() > 0;
    }

    public function replaceCrossselsBlock($observer)
    {
        if (!$this->_getShoppingCartCrosssellsBlocks() || !$observer->getBlock() instanceof Mage_Checkout_Block_Cart) {
            return;
        }
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::app()->getLayout();
        /** @var $helper AW_Autorelated_Helper_Data */
        $helper = Mage::helper('awautorelated');
        if (!$helper->getExtDisabled()) {
            /** @var $shoppingCartBlock Mage_Checkout_Block_Cart */
            $shoppingCartBlock = $observer->getBlock();
            /** @var $arpBlock AW_Autorelated_Block_Blocks */
            $arpBlock = $layout->createBlock('awautorelated/blocks', 'aw.arp2.shc.crosssells');

            $crosssellBlock = $shoppingCartBlock->getChild('crosssell');
            if($crosssellBlock instanceof AW_Relatedproducts_Block_Relatedproducts) {
                $crosssellBlock->setData('_aw_arp2_cs_block', $arpBlock);
            } else {
                $shoppingCartBlock->setChild('crosssell', $arpBlock);
            }
        }
        return $this;
    }
}