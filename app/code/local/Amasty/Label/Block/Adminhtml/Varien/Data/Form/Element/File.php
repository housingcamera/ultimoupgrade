<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Block_Adminhtml_Varien_Data_Form_Element_File  extends Varien_Data_Form_Element_File
{
    public function getElementHtml()
    {
        $html = '<div class="amlabel-choose-container" id="amlabel-choose-' . $this->getHtmlId() . '">';
        list($shareChecked, $domnloadChecked) = $this->_getChecked();
        $html .=  '<input ' . $shareChecked . '
                          type="radio"
                          name="label_type' . $this->getHtmlId() . '"
                          id="shape_' . $this->getHtmlId() . '"
                          value="shape' . $this->getHtmlId() . '">
                   <label for="shape_' . $this->getHtmlId() . '">'
                        . Mage::helper('amlabel')->__('Select The Label Shape') .
                   '</label><br>
                   <input ' . $domnloadChecked . '
                          type="radio"
                          name="label_type' . $this->getHtmlId() . '"
                          id="upload_radio' . $this->getHtmlId() . '"
                          value="download' . $this->getHtmlId() . '">
                   <label for="upload_radio' . $this->getHtmlId() . '">' .
                        Mage::helper('amlabel')->__('Upload Custom Label Image') .
                   '</label>';
        $html .= '</div>';
        $html .= $this->_getDownloadHtml();
        $html .= $this->_getShapeHtml();

        return $html;
    }

    protected function _getShapeHtml()
    {
        $html = '<div id="amlabel-shape' . $this->getHtmlId() . '">';
            $html .= '<div class="amlabel-shapes-container">';
                $value = $this->getValue();
                $shapes = Amasty_Label_Model_Shape::getShapes();
                foreach ($shapes as $shape => $shapeName) {
                    $checked = ($value && strpos($value, $shape) !== FALSE)? 'checked' : '';
                    $html .= Amasty_Label_Model_Shape::generateShape($shape, $this->getHtmlId(), $checked);
                }
            $html .= '</div>';
            $html .= '<div class="amlabel-shapes-container-clone"></div>';
        $html .= '</div>';

        return $html;
    }

    protected function _getDownloadHtml()
    {
        $html = '<div id="amlabel-download' . $this->getHtmlId() . '">';

        $img = $this->getValue();
        if ($img) {
            $html .= '<div class="amlabel-image-preview">';
            $html .= '<img id="image_preview' . $this->getHtmlId() . '" src="' . Mage::getBaseUrl('media') . 'amlabel/' . $img . '" />';
            $html .= '</div><div class="amlabel-image-upload">';
                $html .= '<input style="margin-bottom: 3px;" id="' . $this->getHtmlId() . '"
                            name="' . $this->getName() . '"
                            value="' . $this->getEscapedValue() . '"
                            ' . $this->serialize($this->getHtmlAttributes())
                    . '/><br/>';
                $html .= '<input type="checkbox" value="1" name="remove_' . $this->getHtmlId() . '"/> ' . Mage::helper('amlabel')->__('Remove');
                $html .= '    <input type="hidden" value="' . $img . '" name="old_' . $this->getHtmlId() . '"/>';
            $html .= '</div>';
        }
        else {
            $html .= '<input style="margin-bottom: 3px;" id="' . $this->getHtmlId() . '"
                        name="' . $this->getName() . '"
                        value="' . $this->getEscapedValue() . '"
                        ' . $this->serialize($this->getHtmlAttributes())
                . '/>';
        }

        $html .= '<p class="note" id="note_prod_img"><span>' .
                    Mage::helper('amlabel')->__('Click <a href="%s">here</a> to download the packs of label images.',
                        'https://amasty.com/media/downloads/labels/labels-images.zip') .
                   '</span></p>';
        $html .= '</div>';

        return $html;
    }

    protected function _getChecked(){
        if($this->getValue()) {
            return array('', 'checked');
        }
        else {
            return array('checked', '');
        }
    }
}
