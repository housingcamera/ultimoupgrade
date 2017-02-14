<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Label_Edit_Tab_Category extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        /* @var $hlp Amasty_Label_Helper_Data */
        $hlp   = Mage::helper('amlabel');
        $model = Mage::registry('amlabel_label');

        $fldCat = $form->addFieldset('category_page', array('legend'=> $hlp->__('Category Page')));
        $fldCat->addType('preview', 'Amasty_Label_Block_Adminhtml_Varien_Data_Form_Element_Preview');
        $fldCat->addType('custom_file', 'Amasty_Label_Block_Adminhtml_Varien_Data_Form_Element_File');

        $fldCat->addField('cat_img', 'custom_file', array(
            'label'     => $hlp->__('Label Type'),
            'name'      => 'cat_img'
        ));
        $fldCat->addField('cat_label_color', 'text', array(
            'label'     => $hlp->__('Label Color'),
            'name'      => 'cat_label_color',
            'after_element_html' => "<span id='cat_label_color_wheel' class='icon i-color-wheel'></span>",
        ));

        $fldCat->addField('cat_pos', 'select', array(
            'label'     => $hlp->__('Label Position'),
            'name'      => 'cat_pos',
            'values'    => $model->getAvailablePositions(),
            'after_element_html' => $this->getPositionHtml('cat_pos')
        ));

        $fldCat->addField('cat_image_width', 'text', array(
            'label'     => $hlp->__('Label Size'),
            'name'      => 'cat_image_width',
            'note'      => $hlp->__('Percent of the product image;'),
        ));

        $fldCat->addField('cat_txt', 'text', array(
            'label'     => $hlp->__('Label Text'),
            'name'      => 'cat_txt',
            'note'      => $hlp->__($this->_getTextNote()),
        ));

        $fldCat->addField('cat_color', 'text', array(
            'label'     => $hlp->__('Text Color'),
            'name'      => 'cat_color',
            'after_element_html' => "<span id='cat_color_wheel' class='icon i-color-wheel'></span>",
        ));

        $fldCat->addField('cat_size', 'text', array(
            'label'     => $hlp->__('Text Size'),
            'name'      => 'cat_size',
            'note'      => $hlp->__('Example: 12px;'),
        ));

        $fldCat->addField('cat_style', 'textarea', array(
            'label'     => $hlp->__('Label & Text Style'),
            'name'      => 'cat_style',
            'note'      => $hlp->__('Ex.: text-align: center; line-height: 50px; <br/>For more CSS properties click <a href="%s">here</a>',
                'http://www.w3schools.com/css/css_text.asp' )
        ));

        $fldCat->addField('cat_preview', 'preview', array(
            'label'     => $hlp->__(''),
            'name'      => 'cat_preview'
        ));

        //set form values
        $data = $model->getData();
        $data = $this->_restoreSizeColor($data);
        $data = $this->_addDefaultValues($data);

        $form->setValues($data);

        return $this->_prepareParentForm();
    }

    protected function _prepareParentForm(){
        return parent::_prepareForm();
    }
    protected function _getTextNote(){
        // {ATTR:code} - attribute value, {STOCK_QTY} - quantity in stock
        return 'Variables: {PRICE} - regular price; {BR} - new line;<br/>
                    {SAVE_PERCENT} - save percent;<br/>
                    {SAVE_AMOUNT} - save amount;<br/>
                    {SPECIAL_PRICE} - special price;<br/>
                    {ATTR:code} - attribute value, e.g. {ATTR:color};<br/>
                    {SPDL} - days left for special price;<br/>
                    {SPHL} - hours left for special price;<br/>
                    {NEW_FOR} - days ago the product was added;<br/>
                    {SKU} - product SKU; {STOCK} - product qty.';
    }

    protected function getPositionHtml($field)
    {
        $html = '<table id="amlabel-table-' . $field . '" class="amlabel-table-position">
            <tr><td></td><td></td><td></td></tr>
            <tr><td></td><td></td><td></td></tr>
            <tr><td></td><td></td><td></td></tr>
            </table>';
        $html .= '<script>
                var amLabelPositionObject_' . $field . ' = new amLabelPosition("' . $field . '");
            </script>';
        return $html;
    }

    protected function _addDefaultValues($data){
        if(!array_key_exists('prod_style', $data)) {
            $data['prod_style'] = 'text-align: center; line-height: 60px;';
        }
        if(!array_key_exists('cat_style', $data)) {
            $data['cat_style'] = 'text-align: center; line-height: 40px;';
        }
        return $data;
    }

    protected function _restoreSizeColor($data){
        if(array_key_exists('prod_style', $data) && $data['prod_style']) {
            $prodStyles = $data['prod_style'];

            $template = '@font-size: (.*?);@s';
            preg_match_all($template, $prodStyles, $res);
            if (isset($res[1]) && isset($res[1][0])) {
                $data['prod_size'] = $res[1][0];
            }

            $template = '@color: (.*?);@s';
            preg_match_all($template, $prodStyles, $res);
            if (isset($res[1]) && isset($res[1][0])) {
                $data['prod_color'] = str_replace("#", '', $res[1][0]);
            }
        }

        if(array_key_exists('cat_style', $data) && $data['cat_style']) {
            $catStyles = $data['cat_style'];

            $template = '@font-size: (.*?);@s';
            preg_match_all($template, $catStyles, $res);
            if (isset($res[1]) && isset($res[1][0])) {
                $data['cat_size'] = $res[1][0];
            }

            $template = '@color: (.*?);@s';
            preg_match_all($template, $catStyles, $res);
            if (isset($res[1]) && isset($res[1][0])) {
                $data['cat_color'] = str_replace("#", '', $res[1][0]);
            }
        }

        return $data;
    }
}
