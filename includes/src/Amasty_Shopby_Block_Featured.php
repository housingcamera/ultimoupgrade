<?php
/**
* @copyright Amasty.
*/  
class Amasty_Shopby_Block_Featured extends Mage_Core_Block_Template
{
    public function getItems()
    {
        $items = array();
        // get filter ID by attribute code 
        $id = Mage::getResourceModel('amshopby/filter')
            ->getIdByCode($this->getAttributeCode());
        if ($id){
            $items = Mage::getResourceModel('amshopby/value_collection')
                ->addFieldToFilter('is_featured', 1)
                ->addFieldToFilter('filter_id', $id)
                ->addValue();  
                
            if ($this->getRandom()){
                $items->setOrder('rand()');
            } 
            else {
            	$items->setOrder('featured_order', 'asc');
                $items->setOrder('value', 'asc');    
                $items->setOrder('title', 'asc');    
            }  
             
            if ($this->getLimit()){
                $items->setPageSize(intVal($this->getLimit()));
            }   

            /** @var Amasty_Shopby_Helper_Url $hlp */
            $hlp = Mage::helper('amshopby/url');
            $base = Mage::getBaseUrl('media') . 'amshopby/';
            foreach ($items as $item){
                if ($item->getImgBig())
                    $item->setImgBig($base . $item->getImgBig());   

                $query = array(
                    $this->getAttributeCode() => $item->getOptionId(),
                );
                $item->setUrl($hlp->getFullUrl($query, true));
            }
        }
        return $items;
    }
}