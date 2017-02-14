<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



class CEC_ExtendedGrid_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    public function setCollection($collection)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */

        $store = $this->_getStore();

        if ($store->getId() && !isset($this->_joinAttributes['url_path'])) {
            $collection->joinAttribute(
                'url_path',
                'catalog_product/url_path',
                'entity_id',
                null,
                'left',
                $store->getId()
            );
        }
        else {
            $collection->addAttributeToSelect('url_path');
        }

        parent::setCollection($collection);
    }

    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        
        $this->addColumnAfter('View',
            array(
                'header'    => Mage::helper('catalog')->__('View'),
                'width'     => '50px',
                'index'     => 'url_path',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'CEC_ExtendedGrid_Block_Adminhtml_Catalog_Product_Renderer_Link',// THIS IS WHAT THIS POST IS ALL ABOUT
        ),'action');
        
        $this->addColumnAfter('Last Updated',
            array(
                'header'    => Mage::helper('catalog')->__('Last Updated'),
                'width'     => '50px',
                'type'     => 'date',
                'index'     => 'updated_at',
                'filter'    => false,
        ),'action');
        
        $this->sortColumnsByOrder();

        return parent::_prepareColumns();
    }
}
