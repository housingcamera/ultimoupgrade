<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Varien_Data_Form_Element_Preview  extends Varien_Data_Form_Element_Note
{
    public function getElementHtml()
    {
        $html  = '<div class="preview" id="' . $this->getHtmlId() . '">';
            $html .= '<div class="preview-image">';
                $html .= '<img src="' .  Mage::getDesign()->getSkinBaseUrl(array('_area'=>'adminhtml')) . '/amasty/amlabel/images/example.jpg">';
                $html .= '<div class="amlabel-table2 top-left">';
                $html .= '  <div class="amlabel-txt2"><div class="amlabel-txt"></div></div>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '<p class="note" id="note_preview"><span>' .
                Mage::helper('amlabel')->__('Please click <a onclick="saveAndContinueEdit()" class="update-preview">here</a> to update the preview.') .
            '</span></p>';
        $html .= '</div>';

        $html.= $this->getAfterElementHtml();
        return $html;
    }
}
