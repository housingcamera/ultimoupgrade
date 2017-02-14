<?php
/**
 * Magento
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Catalog_Block_Product_List_Filterby extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
	/**
	This function takes the 2 params below from layout xml and creates a filtered category on a CMS page. See format below.
	**/
		$filter_attribute = $this->getData('filter_attribute');
		$filter_value = $this->getData('filter_value');
		
		
		if (is_null($this->_productCollection)) {
			
			$collection = Mage::getResourceModel('catalog/product_collection');
			Mage::getModel('catalog/layer')->prepareProductCollection($collection);
			
			if (strpos($filter_value,'*') !== false) {
				// Star in Value indicates a date or in the future other special attribute
				$filter_value = str_replace("*","",$filter_value);
				if ($filter_value == "today") {
					$filter_value = strval(date("m/d/Y"));
					$date1 = "09/10/2014";
				}
				$collection->addAttributeToFilter($filter_attribute, array('from' => $filter_value, 'date' => true))
					->addStoreFilter();
			}else{
				// standard attribute filter 
				$collection->addAttributeToFilter($filter_attribute, $filter_value)
					->addStoreFilter();
			
			}
			$this->_productCollection = $collection;
		}
		
       
        return $this->_productCollection;
    }
}
/**
<reference name="left"> 
	<block type=" amshopby/catalog_layer_view " name="amshopby.navleft" template="catalog/layer/view.phtml"/> 
</reference>

<reference name="content">
        <block type="catalog/product_list_filterby" name="home" template="catalog/product/list.phtml">
			<action method="setData">
				<name>filter_attribute</name>
				<value>manufacturer</value>
			</action>
			<action method="setData">
				<name>filter_value</name>
				<value>16</value>
			</action>
			<!-- Product List View -->
			<action method="setCategoryId"><category_id>3</category_id></action> <!-- Lists the root category -->
			<block type="catalog/product_list_toolbar" name="product_list_toolbar" template="catalog/product/list/toolbar.phtml">
					<block type="page/html_pager" name="product_list_toolbar_pager"/>
			</block>
			<action method="setToolbarBlockName"><name>product_list_toolbar</name></action>
        </block>
</reference> 
**/

