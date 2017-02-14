<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
* @package Amasty_Paction
*/
class Amasty_Paction_Model_Observer
{
	protected $_irregularGridClasses = array('Aitoc_Aitpermissions_Block_Rewrite_AdminCatalogProductGrid',
		'TBT_Enhancedgrid_Block_Catalog_Product_Grid',
        'Magentomasters_Supplier_Block_Adminhtml_Catalog_Product_Grid'
    );
	
    public function onCoreBlockAbstractToHtmlBefore($observer) 
    {
        $block = $observer->getBlock();
        $massactionClass  = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $productGridClass = Mage::getConfig()->getBlockClassName('adminhtml/catalog_product_grid');
        if ($massactionClass == get_class($block)
			&& ($productGridClass == get_class($block->getParentBlock())
			|| in_array(get_class($block->getParentBlock()), $this->_irregularGridClasses))) {
		/*if ($massactionClass == get_class($block) // other way for checking
    		&& 'catalog_product' == $block->getRequest()->getControllerName()) {*/
            $types = Mage::getStoreConfig('ampaction/general/commands');
            if (!$types)
                return $this;
            
            $types = explode(',', $types);
            foreach ($types as $i => $type) {
                if (strlen($type) > 2) {
                    $command = Amasty_Paction_Model_Command_Abstract::factory($type);
                    $command->addAction($block);
                } else { // separator
						$block->addItem('ampaction_separator' . $i, array(
							'label'=> '---------------------',
							'url'  => '' 
						));
                }
            }
        }
        
        return $this;
    }
}
