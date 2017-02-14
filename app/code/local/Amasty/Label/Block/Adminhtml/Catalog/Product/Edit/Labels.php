<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Catalog_Product_Edit_Labels extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        /* @var $hlp Amasty_Label_Helper_Data */
        $hlp   = Mage::helper('amlabel');
        $fldInfo = $form->addFieldset('general', array('legend'=> $hlp->__('Associated Labels')));
        $fldInfo->addField('amlabels', 'hidden', array(
            'name'  => 'amlabels',
            'value' => 1,
        ));        
          
        $product = Mage::registry('current_product'); 
        
        $collection = Mage::getModel('amlabel/label')->getCollection()
            ->addFieldToFilter('include_type', array('neq'=>1));

        if (0 < $collection->getSize()) {
            foreach ($collection as $label) {
                $name = 'amlabel_' . $label->getId();
                $el = $fldInfo->addField($name, 'checkbox', array(
                    'label'              => $label->getName(),
                    'name'               => $name,
                    'value'              => 1,
                    'after_element_html' => $this->getImageHtml($label->getProdImg()),
                ));
                if ($product->hasData($name)) {
                    $el->setIsChecked($product->getData($name));
                }
                elseif ($product->getData('sku')) {
                    $skus = explode(',', $label->getIncludeSku());
                    $el->setIsChecked(in_array($product->getData('sku'), $skus));
                }
            }
        }
        
        return parent::_prepareForm();
    }
    
    protected function getImageHtml($img)
    {
        $html = '';
        if ($img) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<img style="max-width:300px" src="' . Mage::getBaseUrl('media') . 'amlabel/' . $img . '" />';
            $html .= '</p>';
        } 
        return $html;     
    }
      
    
}