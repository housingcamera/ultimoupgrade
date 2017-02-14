<?php

class stamped_core_Model_Mysql4_Richsnippet extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct()
    {
        $this->_init('stamped/richsnippet', 'rich_snippet_id');
    }   
}