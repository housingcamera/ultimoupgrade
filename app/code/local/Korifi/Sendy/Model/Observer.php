<?php
class Korifi_Sendy_Model_Observer{	
    public function addMassAction($observer)
    {
        if ('subscriberGrid' == $observer->getEvent()->getBlock()->getId()) {
            $massBlock = $observer->getEvent()->getBlock()->getMassactionBlock();
            if ($massBlock) {
                $massBlock->addItem('sendy_add_option_migration', array(
                    'label'=> Mage::helper('core')->__('Migration Subscribers to Sendy'),
                    'url'  => Mage::getUrl('adminsendy/adminhook/migrationsubscriberstosendy', array('_secure'=>true)),
                ));
            }
        }
    }	
}

?>