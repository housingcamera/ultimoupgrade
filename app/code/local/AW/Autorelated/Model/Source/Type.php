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

class AW_Autorelated_Model_Source_Type extends AW_Autorelated_Model_Source_Abstract
{
    const PRODUCT_PAGE_BLOCK = 1;
    const CATEGORY_PAGE_BLOCK = 2;
    const SHOPPING_CART_BLOCK = 3;

    const PRODUCT_PAGE_BLOCK_LABEL = 'Product Page Block';
    const CATEGORY_PAGE_BLOCK_LABEL = 'Category Page Block';
    const SHOPPING_CART_BLOCK_LABEL = 'Shopping Cart Block';

    const PRODUCT_PAGE_BLOCK_SHORT_LABEL = 'Product';
    const CATEGORY_PAGE_BLOCK_SHORT_LABEL = 'Category';
    const SHOPPING_CART_BLOCK_SHORT_LABEL = 'Shopping Cart';

    public function toOptionArray()
    {
        $_helper = $this->_getHelper();
        return array(
            array('value' => self::PRODUCT_PAGE_BLOCK, 'label' => $_helper->__(self::PRODUCT_PAGE_BLOCK_LABEL)),
            array('value' => self::CATEGORY_PAGE_BLOCK, 'label' => $_helper->__(self::CATEGORY_PAGE_BLOCK_LABEL)),
            array('value' => self::SHOPPING_CART_BLOCK, 'label' => $_helper->__(self::SHOPPING_CART_BLOCK_LABEL))
        );
    }

    public function toArray()
    {
        $_helper = $this->_getHelper();
        return array(
            self::PRODUCT_PAGE_BLOCK  => $_helper->__(self::PRODUCT_PAGE_BLOCK_SHORT_LABEL),
            self::CATEGORY_PAGE_BLOCK => $_helper->__(self::CATEGORY_PAGE_BLOCK_SHORT_LABEL),
            self::SHOPPING_CART_BLOCK => $_helper->__(self::SHOPPING_CART_BLOCK_SHORT_LABEL)
        );
    }
}