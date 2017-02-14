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

class AW_Autorelated_Block_Adminhtml_Blocks_Category_Edit_Tabs_Currentlyviewed extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);

        $_data = Mage::registry('categoryblock_data');

        $_fieldset = $_form->addFieldset('currently_viewed', array(
            'legend' => $this->__('Currently Viewed Category')
        ));

        $_fieldset->addField('currently_viewed_categories_area', 'select', array(
            'label'  => $this->__('Categories'),
            'title'  => $this->__('Categories'),
            'name'   => 'currently_viewed[area]',
            'values' => array(
                array('value' => 1, 'label' => $this->__('All')),
                array('value' => 2, 'label' => $this->__('Custom'))
            )
        ));

        $categoriesGridBlock = Mage::getSingleton('core/layout')
            ->createBlock('awautorelated/adminhtml_blocks_category_edit_tabs_currentlyviewed_categoriesgrid')
        ;
        $_fieldset->addField('gridcontainer_categories', 'note', array(
            'label' => $this->__('Select Categories'),
            'text'  => $categoriesGridBlock->toHtml()
        ));
        $_form->setValues($_data);
    }
}