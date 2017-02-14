<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */ 
class Amasty_Label_Block_Adminhtml_Label_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('labelGrid');
        $this->setDefaultSort('label_id');
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('amlabel/label')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $hlp =  Mage::helper('amlabel'); 
        $this->addColumn('label_id', array(
          'header'    => $hlp->__('ID'),
          'align'     => 'right',
          'width'     => '50px',
          'index'     => 'label_id',
        ));
        
        $this->addColumn('name', array(
            'header'    => $hlp->__('Name'),
            'index'     => 'name',
        ));

        $this->addColumn('is_active', array(
            'header'    => $hlp->__('Status'),
            'index'     => 'is_active',
            'align'        => 'center',
            'width'     => '130px',
            'renderer'  => 'amlabel/adminhtml_label_grid_renderer_status',
        ));
        
        $url = Mage::getBaseUrl('media') . 'amlabel/';
        $this->addColumn('prod_img', array(
            'header'    => $hlp->__('Product Page Label'),
            'index'     => 'prod_img',
            'align'     => 'center',
            'renderer'  => 'amlabel/adminhtml_label_grid_renderer_image_product',
        ));
        $this->addColumn('prod_txt', array(
            'header'    => $hlp->__('Product Page Text'),
            'index'     => 'prod_txt',
        ));        

        $this->addColumn('cat_img', array(
            'header'    => $hlp->__('Category Page Label'),
            'index'     => 'cat_img',
            'align'     => 'center',
            'renderer'  => 'amlabel/adminhtml_label_grid_renderer_image_category',
        )); 
        $this->addColumn('cat_txt', array(
            'header'    => $hlp->__('Category Page Text'),
            'index'     => 'cat_txt',
        ));                      
    
        return parent::_prepareColumns();
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('label_id');
        $this->getMassactionBlock()->setFormFieldName('labels');
        
        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('amlabel')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('amlabel')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('change_status', array(
             'label'      => Mage::helper('amlabel')->__('Change Status'),
             'url'        => $this->getUrl('*/*/masschangestatus'),
             'confirm'    => Mage::helper('amlabel')->__('Are you sure you want to Change Status?'),
             'additional' => $this->_getValueField(),
        ));
        
        return $this; 
    }

    /**
     * Returns value field options for the mass actions block
     *
     * @param string $title field title
     * @return array
     */
    protected function _getValueField()
    {
        $hlp = Mage::helper('amlabel');
        $yesno = array();
        $yesno[] = array('value' => 0, 'label' => $hlp->__('Inactive'));
        $yesno[] = array('value' => 1, 'label' => $hlp->__('Active'));

        $field = array('amlabel_value' => array(
            'name'   => 'amlabel_value',
            'type'   => 'select',
            'class'  => 'required-entry',
            'label'  => $hlp->__('Status'),
            'values' => $yesno
        ));

        return $field;
    }
}
