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



class Mirasvit_SearchIndex_Block_Adminhtml_Report_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        $this->setId('searchindex_report_view_grid');
        $this->setSaveParametersInSession(false);
        $this->setPagerVisibility(true);
        parent::__construct();
    }

    protected function _prepareCollection()
    {
        $model = Mage::registry('current_model');

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(
            $model->getStoreId(),
            Mage_Core_Model_App_Area::AREA_FRONTEND
        );

        $this->setCollection($this->getSearchResultsCollection());
        parent::_prepareCollection();

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $this;
    }

    private function getSearchResultsCollection()
    {
        $model = Mage::registry('current_model');

        Mage::app()->getRequest()->setParam('q', $model->getQueryText());
        Mage::helper('searchindex/index')->getIndex('mage_catalog_product')->setQuery($model);

        $searchResultCollection = Mage::getModel('catalogsearch/layer')->getProductCollection();
        $searchResultCollection->getSelect()->group('e.entity_id');

        return $searchResultCollection;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header' => Mage::helper('searchindex')->__('Product Name'),
            'index' => 'name',
            'width' => '200',
            'sortable' => false,
        ));

        $this->addColumn('sku', array(
            'header' => Mage::helper('searchindex')->__('SKU'),
            'index' => 'sku',
            'width' => '120',
            'align' => 'center',
            'sortable' => false,
        ));

        $this->addColumn('relevance', array(
            'header' => Mage::helper('searchindex')->__('Relevance'),
            'index' => 'relevance',
            'align' => 'center',
            'width' => '50',
            'sortable' => false,
            'filter' => false,
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('searchindex')->__('Action'),
            'width' => '15px',
            'sortable' => false,
            'filter' => false,
            'type' => 'action',
            'getter' => 'getId',
            'align' => 'center',
            'header_css_class' => 'a-center',
            'actions' => array(
                array(
                    'url' => array('base' => 'adminhtml/catalog_product/edit/'),
                    'caption' => $this->helper('catalog')->__('View'),
                    'field' => 'id',
                ),
            ),
        ));

        return parent::_prepareColumns();
    }
}
