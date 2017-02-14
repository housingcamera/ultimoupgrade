<?php
class Amasty_Shopby_Helper_Layer_View_Strategy_Decimal extends Amasty_Shopby_Helper_Layer_View_Strategy_Modeled
{
    protected function setTemplate()
    {
        return 'amasty/amshopby/price.phtml';
    }

    protected function setHasSelection()
    {
        return Mage::app()->getRequest()->getParam($this->attribute->getAttributeCode());
    }

    protected function getTransferableFields()
    {
        return array('hide_counts', 'display_type', 'seo_rel', 'depend_on', 'depend_on_attribute', 'comment', 'from_to_widget', 'slider_type', 'value_label', 'value_placement', 'slider_decimal');
    }
}
