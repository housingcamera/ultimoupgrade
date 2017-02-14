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

class AW_Autorelated_Block_Adminhtml_Blocks_Shoppingcart_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awautorelated_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Shopping Cart Block'));
    }

    protected function _beforeToHtml()
    {
        $generalBlock = $this->getLayout()
            ->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit_tabs_general')
        ;
        $this->addTab('general', array(
            'label'   => $this->__('General'),
            'title'   => $this->__('General'),
            'content' => $generalBlock->toHtml()
        ));
        $orderConditionsBlock = $this->getLayout()
            ->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit_tabs_orderconditions')
        ;
        $this->addTab('currently_viewed', array(
            'label'   => $this->__('Shopping Cart Conditions'),
            'title'   => $this->__('Shopping Cart Conditions'),
            'content' => $orderConditionsBlock->toHtml()
        ));
        $relatedProductsBlock = $this->getLayout()
            ->createBlock('awautorelated/adminhtml_blocks_shoppingcart_edit_tabs_relatedproducts')
        ;
        $this->addTab('related_products', array(
            'label'   => $this->__('Related Products'),
            'title'   => $this->__('Related Products'),
            'content' => $relatedProductsBlock->toHtml()
        ));

        if ($this->getRequest()->getParam('continue_tab')) {
            $this->setActiveTab($this->getRequest()->getParam('continue_tab'));
        }
        return parent::_beforeToHtml();
    }
}