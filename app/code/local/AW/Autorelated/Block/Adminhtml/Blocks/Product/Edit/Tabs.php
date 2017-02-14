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

class AW_Autorelated_Block_Adminhtml_Blocks_Product_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productblock_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('awautorelated')->__('Product block'));
    }

    protected function _beforeToHtml()
    {
        $generalBlock = $this->getLayout()->createBlock('awautorelated/adminhtml_blocks_product_edit_tab_general');
        $this->addTab('general', array(
            'label' => Mage::helper('awautorelated')->__('General'),
            'title' => Mage::helper('awautorelated')->__('General'),
            'content' => $generalBlock->toHtml()
        ));
        $viewedBlock = $this->getLayout()->createBlock('awautorelated/adminhtml_blocks_product_edit_tab_viewed');
        $this->addTab('viewed', array(
            'label' => Mage::helper('awautorelated')->__('Currently Viewed Product'),
            'title' => Mage::helper('awautorelated')->__('Currently Viewed Product'),
            'content' => $viewedBlock->toHtml()
        ));
        $relatedBlock = $this->getLayout()->createBlock('awautorelated/adminhtml_blocks_product_edit_tab_related');
        $this->addTab('related', array(
            'label' => Mage::helper('awautorelated')->__('Related Products'),
            'title' => Mage::helper('awautorelated')->__('Related Products'),
            'content' => $relatedBlock->toHtml()
        ));

        return parent::_beforeToHtml();
    }
}