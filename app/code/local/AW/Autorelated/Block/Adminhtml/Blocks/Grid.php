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

class AW_Autorelated_Block_Adminhtml_Blocks_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('autorelatedBlocoksGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var AW_Autorelated_Model_Mysql4_Blocks_Collection $collection */
        $collection = Mage::getModel('awautorelated/blocks')->getCollection();
        $collection->getSelect()->reset(Zend_Db_Select::ORDER);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '5',
            'index' => 'id'
        ));

        $this->addColumn('name', array(
            'header' => $this->__('Block Name'),
            'align' => 'left',
            'index' => 'name'
        ));

        $this->addColumn('type', array(
            'header'  => $this->__('Type'),
            'align'   => 'center',
            'width'   => '100',
            'index'   => 'type',
            'type'    => 'options',
            'options' => Mage::getModel('awautorelated/source_type')->toArray()
         ));

        $positions = new AW_Autorelated_Model_Source_Position();
        $this->addColumn('position', array(
            'header' => $this->__('Position'),
            'align' => 'center',
            'width' => '120',
            'index' => 'position',
            'type' => 'options',
            'options' => $positions->getOptionArray()
        ));

        $this->addColumn('priority', array(
            'header' => $this->__('Priority'),
            'align' => 'right',
            'index' => 'priority',
            'width' => '50'
        ));

        $this->addColumn('date_from', array(
            'header' => $this->__('Date Start'),
            'align' => 'center',
            'width' => '120',
            'index' => 'date_from',
            'type' => 'date',
            'renderer' => 'AW_Autorelated_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('date_to', array(
            'header' => $this->__('Date Expire'),
            'align' => 'center',
            'width' => '120',
            'index' => 'date_to',
            'type' => 'date',
            'renderer' => 'AW_Autorelated_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'align' => 'center',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('awautorelated/source_status')->toArray()
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store',
                array(
                   'header'                    => $this->__('Store View'),
                   'width'                     => '200',
                   'index'                     => 'store',
                   'sortable'                  => FALSE,
                   'type'                      => 'store',
                   'store_all'                 => TRUE,
                   'store_view'                => TRUE,
                   'renderer'                  => 'Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store',
                   'filter_condition_callback' => array($this, '_filterStoreCondition')
                )
            );
        }

        if (Mage::helper('awautorelated')->isEditAllowed()) {
            $this->addColumn('action', array(
                'header' => $this->__('Action'),
                'width' => '100px',
                'align' => 'center',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url'     => array('base' => '*/*/delete'),
                        'field'   => 'id',
                        'confirm' => $this->__('Are you sure that you want to delete this block?')
                    ),
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));
        }
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        if (!Mage::helper('awautorelated')->isEditAllowed()) {
            return $this;
        }

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->__('Delete'),
            'url'     => $this->getUrl('*/*/delete'),
            'confirm' => $this->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('awautorelated/source_status')->toOptionArray();

        array_unshift($statuses, array('label' => '', 'value' => ''));
        $this->getMassactionBlock()->addItem('status', array(
            'label' => $this->__('Change status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name'   => 'status',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $this->__('Status'),
                    'values' => $statuses
                )
            )
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit/', array('id' => $row->getId()));
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }
        $collection->addStoreFilter($value);
    }
}