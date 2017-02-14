<?php
class Amasty_Shopby_Helper_Layer_View_Strategy_Category extends Amasty_Shopby_Helper_Layer_View_Strategy_Abstract
{
    public function prepare()
    {
        parent::prepare();

        $this->filter->setDisplayType(Mage::getStoreConfig('amshopby/general/categories_type'));
    }

    protected function setTemplate()
    {
        return 'amasty/amshopby/category.phtml';
    }

    protected function setPosition()
    {
        return Mage::getStoreConfig('amshopby/general/categories_order');
    }

    protected function setHasSelection()
    {
        return false;
    }

    protected function setCollapsed()
    {
        return $this->isCollapseEnabled() && Mage::getStoreConfig('amshopby/general/categories_collapsed');
    }

    public function getIsExcluded()
    {
        return $this->setPosition() == -1;
    }
}
