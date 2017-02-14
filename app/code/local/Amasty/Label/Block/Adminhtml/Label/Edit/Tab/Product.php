<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Label_Edit_Tab_Product extends Amasty_Label_Block_Adminhtml_Label_Edit_Tab_Category
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /* @var $hlp Amasty_Label_Helper_Data */
        $hlp = Mage::helper('amlabel');
        $model = Mage::registry('amlabel_label');

        $fldProduct = $form->addFieldset('product_page', array('legend' => $hlp->__('Product Page')));
        $fldProduct->addType('preview', 'Amasty_Label_Block_Adminhtml_Varien_Data_Form_Element_Preview');
        $fldProduct->addType('custom_file', 'Amasty_Label_Block_Adminhtml_Varien_Data_Form_Element_File');

        $fldProduct->addField('prod_img', 'custom_file', array(
            'label' => $hlp->__('Label Type'),
            'name' => 'prod_img',
        ));

        $fldProduct->addField('prod_label_color', 'text', array(
            'label' => $hlp->__('Label Color'),
            'name' => 'prod_label_color',
            'after_element_html' => "<span id='prod_label_color_wheel' class='icon i-color-wheel'></span>",
        ));

        $fldProduct->addField('prod_pos', 'select', array(
            'label' => $hlp->__('Label Position'),
            'name' => 'prod_pos',
            'values' => $model->getAvailablePositions(),
            'after_element_html' => $this->getPositionHtml('prod_pos')
        ));

        $fldProduct->addField('prod_image_width', 'text', array(
            'label'     => $hlp->__('Label Size'),
            'name'      => 'prod_image_width',
            'note'      => $hlp->__('Percent of the product image;'),
        ));

        $fldProduct->addField('prod_txt', 'text', array(
            'label' => $hlp->__('Label Text'),
            'name' => 'prod_txt',
            'note' => $hlp->__($this->_getTextNote()),
        ));

        $fldProduct->addField('prod_color', 'text', array(
            'label' => $hlp->__('Text Color'),
            'name' => 'prod_color',
            'after_element_html' => "<span id='prod_color_wheel' class='icon i-color-wheel'></span>",
        ));

        $fldProduct->addField('prod_size', 'text', array(
            'label' => $hlp->__('Text Size'),
            'name' => 'prod_size',
            'note' => $hlp->__('Example: 12px;'),
        ));

        $fldProduct->addField('prod_style', 'textarea', array(
            'label' => $hlp->__('Label & Text Style'),
            'name' => 'prod_style',
            'note' => $hlp->__('Ex.: text-align: center; line-height: 50px; <br/>For more CSS properties click <a href="%s" target="_blank">here</a>',
                'http://www.w3schools.com/css/css_text.asp')
        ));

        $fldProduct->addField('prod_preview', 'preview', array(
            'label' => $hlp->__(''),
            'name' => 'prod_preview'
        ));

        $data = $model->getData();
        $data = $this->_restoreSizeColor($data);
        $data = $this->_addDefaultValues($data);

        $form->setValues($data);

        return $this->_prepareParentForm();
    }
}
