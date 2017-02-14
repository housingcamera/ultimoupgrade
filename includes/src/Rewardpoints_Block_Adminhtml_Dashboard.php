<?php

class Rewardpoints_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template
{
    protected $_locale;

    /**
     * Location of the "Enable Chart" config param
     */
    const XML_PATH_ENABLE_CHARTS = 'admin/dashboard/enable_charts';

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('rewardpoints/dashboard.phtml');

    }

    protected function _prepareLayout()
    {
        if (Mage::getStoreConfig(self::XML_PATH_ENABLE_CHARTS)) {
            $block = $this->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_diagrams');
        } else {
            $block = $this->getLayout()->createBlock('adminhtml/template')
                ->setTemplate('dashboard/graph/disabled.phtml')
                ->setConfigUrl($this->getUrl('adminhtml/system_config/edit', array('section'=>'admin')));
        }
        $this->setChild('diagrams', $block);
        
        $this->setChild('totals',
            $this->getLayout()->createBlock('rewardpoints/adminhtml_dashboard_totals')
        );

        parent::_prepareLayout();
    }

    public function getSwitchUrl()
    {
        if ($url = $this->getData('switch_url')) {
            return $url;
        }
        return $this->getUrl('*/*/*', array('_current'=>true, 'period'=>null));
    }
}
