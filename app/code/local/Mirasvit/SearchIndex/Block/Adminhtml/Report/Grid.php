<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at http://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   Sphinx Search Ultimate
 * @version   2.3.4
 * @build     1364
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_SearchIndex_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        $this->setId('searchindex_report_grid');
        $this->setSaveParametersInSession(false);
        $this->setDefaultSort('popularity');
        $this->setDefaultDir('desc');
        parent::__construct();
    }

    protected function _prepareCollection()
    {
        $store = (!is_null($this->getParam('store'))) ? $this->getParam('store')
            : Mage::app()->getDefaultStoreView()->getId();

        $collection = Mage::getResourceModel('catalogsearch/query_collection')
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('store_id', $store);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('query_text', array(
            'header' => Mage::helper('searchindex')->__('Search Query'),
            'index' => 'query_text',
            'width' => '200',
            'frame_callback' => array($this, 'formQueryText'),
            'sortable' => true,
        ));

        $this->addColumn('popularity', array(
            'header' => Mage::helper('searchindex')->__('Popularity'),
            'index' => 'popularity',
            'width' => '50',
            'sortable' => true,
            'type' => 'range',
            'column_css_class' => 'a-center',
        ));

        $this->addColumn('num_results', array(
            'header' => Mage::helper('searchindex')->__('Number of results'),
            'index' => 'num_results',
            'width' => '50',
            'type' => 'range',
            'column_css_class' => 'a-center',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('searchindex')->__('View search results (frontend)'),
            'width' => '50',
            'sortable' => false,
            'filter' => false,
            'type' => 'action',
            'align' => 'center',
            'header_css_class' => 'a-center',
            'frame_callback' => array($this, 'getSearchResultsUrl'),
        ));

        $this->addColumn('view', array(
            'header' => Mage::helper('searchindex')->__('Details'),
            'width' => '50',
            'sortable' => false,
            'filter' => false,
            'type' => 'action',
            'getter' => 'getId',
            'align' => 'center',
            'header_css_class' => 'a-center',
            'actions' => array(
                array(
                    'url' => array('base' => 'adminhtml/searchindex_report/view/'),
                    'caption' => $this->helper('searchindex')->__('View'),
                    'field' => 'id',
                ),
            ),
        ));

        return parent::_prepareColumns();
    }

    public function formQueryText($value, $row, $column, $isExport)
    {
        return '<b>'.strip_tags($value).'</b>';
    }

    public function getSearchResultsUrl($value, $row, $column, $isExport)
    {
        $url = Mage::app()->getStore($row->getStoreId())->getUrl('catalogsearch/result', array(
                '_query' => array('q' => $row->getQueryText()),
            )
        );
        $value = '<a href="'.$url.'" target="_blank">View</a>';

        return $value;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/view', array('id' => $row->getId()));
    }
}
