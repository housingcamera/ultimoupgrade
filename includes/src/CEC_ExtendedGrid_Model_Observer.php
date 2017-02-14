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
 *
 * @category    CEC
 * @package     CEC_ExtendedGrid
 * @author      CEC Core Team
 * @copyright   Copyright (c) 2014 CEC
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class CEC_ExtendedGrid_Model_Observer
{
    /**
     * Joins extra tables for adding custom columns to Mage_Adminhtml_Block_Sales_Order_Grid
     * @param Varien_Object $observer
     * @return CEC_Exgrid_Model_Observer
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
		//$collection->addAttributeToFilter('status', array('neq' => 'Canceled'));
        $select = $collection->getSelect();
		
        $select->joinLeft(array('payment' => $collection->getTable('sales/order_payment')), 'payment.parent_id=main_table.entity_id', array('payment_method' => 'method'));
        $select->join('sales_flat_order_item', '`sales_flat_order_item`.order_id=`main_table`.entity_id',
		array(
			'skus' => new Zend_Db_Expr('group_concat(`sales_flat_order_item`.sku SEPARATOR ", ")')
		));
		$select->join('sales_flat_order', '`sales_flat_order`.entity_id=`main_table`.entity_id',
		array(
			'active' => new Zend_Db_Expr("CASE `sales_flat_order`.status WHEN 'canceled' THEN 'No' WHEN 'closed' THEN 'No' ELSE 'Yes' END")		
		));
        $select->group('main_table.entity_id');
    }

    /**
     * callback function used to filter collection
     * @param $collection
     * @param $column
     * @return $this
     */
    public function filterSkus($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->getSelect()->having(
            "group_concat(`sales_flat_order_item`.sku SEPARATOR ', ') like ?", "%$value%");

        return $this;
    }
	 /**
     * callback function used to filter collection
     * @param $collection
     * @param $column
     * @return $this
    */
    public function filterActive($collection, $column)
    {
		
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }else if ($value=="yes" || $value=="Yes"){
			$collection->getSelect()->where("`sales_flat_order`.status <> 'canceled' AND `sales_flat_order`.status <> 'complete' AND `sales_flat_order`.status <> 'closed'");
		}else if ($value=="no" || $value=="No"){
			$collection->getSelect()->where("`sales_flat_order`.status = 'canceled' OR `sales_flat_order`.status = 'complete' OR `sales_flat_order`.status = 'closed'");
		}

        return $this;
    } 
}
