<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Regions
 */
class Amasty_Regions_Block_Adminhtml_Region_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('regionGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('region_title');

    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }


    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amregions/region')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        /* @var $_helper Amasty_Regions_Helper_Data */
        $_helper = Mage::helper('amregions');
        $this->addColumn('region_id', array(
            'header'    => $this->__('ID'),
            'align'     => 'left',
            'index'     => 'region_id',
        ));

        $this->addColumn('region_title', array(
            'header'    => $this->__('Shipping Area Title'),
            'align'     => 'left',
            'index'     => 'region_title',
        ));

        $this->addColumn('countries', array(
            'header'    => $this->__('Countries'),
            'align'     => 'left',
            'getter'    => 'getCountriesText',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('action',array(
            'header'    => $this->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'     => 'getId',
            'actions'   => array(
                array(
                    'caption' => $this->__('Edit'),
                    'url'     => array(
                        'base'=>'*/*/edit',
                    ),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
        ));

        parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('region_id');
        $this->getMassactionBlock()->setFormFieldName('region_ids');

        $actions = array(
            'massDelete'     => 'Delete',
        );
        foreach ($actions as $code => $label){
            $this->getMassactionBlock()->addItem($code, array(
                'label'    => $this->__($label),
                'url'      => $this->getUrl('*/*/' . $code),
                'confirm'  => ($code == 'massDelete' ? $this->__('Are you sure?') : null),
            ));
        }
        parent::_prepareMassaction();
        return $this;
    }
}