<?php

class StitchLabs_ChannelIntegration_Model_Observer {

    /**
     * This will hook into the 'sales_order_creditmemo_save_after' event which is defined in the 
     * config.xml of this module. Because magento is not keeping track of if a credit memo line item was returned
     * to stock, we will loop over all credit memo line items and check if the line item will be returned to stock
     * or not.
     * When importing Magento credit memos into Stitch, the 'StitchLabs_ChannelIntegration_return_to_stock' which 
     * we create on installation of this module in the sales_flat_creditmemo_item table, will be used to determine
     * if we have to reconcile stock for this product variant.
     * 
     * @param $observer
     */
    public function logCreditMemoStockReturns($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $items      = $creditmemo->getAllItems();

        foreach ($items as $item)
        {
            $return = false;
            
            if ($item->hasBackToStock())
            {
                if ($item->getBackToStock() && $item->getQty())
                {
                    $return = true;
                }
            }
            elseif (Mage::helper('cataloginventory')->isAutoReturnEnabled())
            {
                $return = true;
            }
            
            if ($return)
            {
                $item->setData('StitchLabs_ChannelIntegration_return_to_stock', 1);
                $item->save();
            }
        }
    }
}