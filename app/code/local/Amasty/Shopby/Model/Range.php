<?php
/**
 * @author Amasty
 */ 
class Amasty_Shopby_Model_Range extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('amshopby/range');
    }
}