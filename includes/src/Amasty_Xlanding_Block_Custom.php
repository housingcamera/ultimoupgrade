<?php
class Amasty_Xlanding_Block_Custom extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("amasty/amlanding/custom.phtml");
    }
    
    protected function _getPage(){
        return Mage::registry('amlanding_page');
    }
    
    protected function getHeading(){
        return $this->_getPage()->getLayoutHeading();
    }
    
    protected function getDescription(){
        return $this->_getPage()->getLayoutDescription();
    }
    
    protected function getFile(){
        return $this->_getPage()->getLayoutFileUrl();
    }
    
    protected function getFileName(){
        return $this->_getPage()->getLayoutFileName();
    }
    
    protected function getFileAlt(){
        return $this->_getPage()->getLayoutFileAlt();
    }
}
?>