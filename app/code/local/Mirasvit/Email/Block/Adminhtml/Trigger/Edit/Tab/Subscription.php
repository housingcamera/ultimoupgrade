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
 * @package   Follow Up Email
 * @version   1.0.14
 * @build     630
 * @copyright Copyright (C) 2016 Mirasvit (http://mirasvit.com/)
 */



class Mirasvit_Email_Block_Adminhtml_Trigger_Edit_Tab_Subscription extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('email_trigger_subscription_grid');
        $this->setDefaultSort('unsubscription_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::registry('current_model')->getUnsubscriptionCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('unsubscription_id', array(
                'header' => $this->__('ID'),
                'index' => 'unsubscription_id',
            ))
            ->addColumn('email', array(
                'header' => $this->__('Email Address'),
                'type' => 'text',
                'index' => 'email',
            ))
            ->addColumn('created_at', array(
                'header' => $this->__('Unsubscription Date'),
                'type' => 'datetime',
                'index' => 'created_at',
            ))
            ->addColumn('action', array(
                'header' => Mage::helper('email')->__('Action'),
                'width' => '100',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('email')->__('Subscribe'),
                        'url' => array('base' => '*/*/subscribe/'),
                        'field' => 'id',
                        'confirm' => $this->__('Are you sure? This record will be removed.'),
                    ),
                ),
                'filter' => false,
                'sortable' => false,
            ));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('unsubscription_id');
        $this->getMassactionBlock()->setFormFieldName('unsubscription_id')
            ->addItem('delete', array(
                'label' => $this->__('Subscribe'),
                'url' => $this->getUrl('*/*/massSubscribe'),
                'confirm' => $this->__('Are you sure? Selected records will be removed.'),
            ));

        return $this;
    }

    public function getRowUrl($row)
    {
        $customer = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('email', $row->getEmail())
            ->getFirstItem();

        if (!$customer || !$customer->getId()) {
            return;
        }

        return $this->getUrl('*/customer/edit', array('id' => $customer->getId()));
    }

    // Used for AJAX loading
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
