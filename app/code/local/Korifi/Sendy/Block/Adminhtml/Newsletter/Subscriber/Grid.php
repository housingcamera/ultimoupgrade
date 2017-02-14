<?php
class Korifi_Sendy_Block_Adminhtml_Newsletter_Subscriber_Grid extends Mage_Adminhtml_Block_Newsletter_Subscriber_Grid
{
    /**
     * Constructor
     *
     * Set main configuration of grid
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('subscriberGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('subscriber_id', 'desc');
    }

    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        return parent::_prepareColumns();
    }


    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscriber_id');
        $this->getMassactionBlock()->setFormFieldName('subscriber');

        $this->getMassactionBlock()->addItem('unsubscribe', array(
             'label'        => Mage::helper('newsletter')->__('Unsubscribe on Magento and Sendy'),
             'url'          => $this->getUrl('adminsendy/adminhook/massUnsubscribe')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
             'label'        => Mage::helper('newsletter')->__('Delete'),
             'url'          => $this->getUrl('*/*/massDelete')
        ));

        return $this;
    }
}
