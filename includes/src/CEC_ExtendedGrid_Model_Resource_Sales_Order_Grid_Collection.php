<?php

/**
 * CEC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 * @category    CEC
 * @package     CEC_ExtendedGrid
 * @author      CEC Core Team
 * @copyright   Copyright (c) 2014 CEC
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CEC_ExtendedGrid_Model_Resource_Sales_Order_Grid_Collection extends Mage_Sales_Model_Resource_Order_Grid_Collection
{

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if (Mage::app()->getRequest()->getControllerName() == 'sales_order') {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->columns("COUNT(DISTINCT main_table.entity_id)");

            $havingCondition = $countSelect->getPart(Zend_Db_Select::HAVING);
            if (count($havingCondition)) {
                $countSelect->where(
                    str_replace("group_concat(`sales_flat_order_item`.sku SEPARATOR ', ')", 'sales_flat_order_item.sku', $havingCondition[0])
                );
                $countSelect->reset(Zend_Db_Select::HAVING);
            }
        }

        return $countSelect;
    }
}
