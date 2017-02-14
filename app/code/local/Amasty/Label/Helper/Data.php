<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_labels = null;
    protected $_sizes  = array();

    public function getLabels($product, $mode = 'category', $useJs = false)
    {
        $html = '';

        $applied = false;
        $labelCollection = $this->_getCollection();
        if (0 < $labelCollection->getSize()) {
            foreach ($labelCollection as $label) {
                if ($label->getIsSingle() && $applied) {
                    continue;
                }
                $label->init($product, $mode);
                if ($label->isApplicable()) {
                    $applied = true;
                    $html .= $this->_generateHtml($label);
                } elseif ($label->getUseForParent() && ($product->isConfigurable() || $product->isGrouped())) {
                    $usedProds = $this->getUsedProducts($product);
                    foreach ($usedProds as $child) {
                        $label->init($child, $mode, $product);
                        if ($label->isApplicable()) {
                            $applied = true;
                            $html .= $this->_generateHtml($label);
                        }
                    }
                }
            }
        }

        return $html;
    }

    protected function _getCollection()
    {
        if (is_null($this->_labels)) {
            $id            = Mage::app()->getStore()->getId();
            $this->_labels = Mage::getModel('amlabel/label')->getCollection()
                                 ->addFieldToFilter('stores', array('like' => "%,$id,%"))
                                 ->addFieldToFilter('is_active', 1)
                                 ->setOrder('pos', 'asc')
                                 ->load();
        }

        return $this->_labels;
    }

    protected function _generateHtml($label)
    {
        $imgUrl = $label->getImageUrl();

        if (empty($this->_sizes[$imgUrl])) {
            $this->_sizes[$imgUrl] = $label->getImageInfo();
        }

        $positionClass = $label->getCssClass();
        $customStyle = $label->getStyle();

        if ($label->getMode() == 'cat') {
            $textStyle = $label->getCatTextStyle();
            $imgWidth  = $label->getCatImageWidth();
        } else {
            $textStyle = $label->getProdTextStyle();
            $imgWidth  = $label->getProdImageWidth();
        }
        $imgWidth  = ($imgWidth)? $imgWidth . '%': '';
        if(!$imgWidth && array_key_exists('w', $this->_sizes[$imgUrl])) {
            $imgWidth = $this->_sizes[$imgUrl]['w'];
        }
        $imgWidth  = ($imgWidth)? $imgWidth : 'auto';
        
        if(array_key_exists('h', $this->_sizes[$imgUrl]) && $this->_sizes[$imgUrl]['h']) {
            $customStyle .= ' max-height: '. $this->_sizes[$imgUrl]['h'] . ';';
        }

        $customStyle .= ' max-width: 100%;';
        if ($textStyle) {
            $textStyle = 'style="' . $textStyle . '"';
        }

        $textBlockStyle = 'style="width:' . $imgWidth . '; background: url(' . $imgUrl . ') no-repeat 0 0; ' . $customStyle . '"';
        $html  = '<div class="amlabel-table2 top-left" ' . $label->getJs() . ' >';
        $html .= '  <div class="amlabel-txt2 ' . $positionClass . '" ' . $textBlockStyle . ' ><div class="amlabel-txt" ' . $textStyle . '>' . $label->getText() . '</div></div>';
        $html .= '</div>';

        return $html;
    }

    public function getUsedProducts($product)
    {
        if ($product->isConfigurable()) {
            return $product->getTypeInstance(true)->getUsedProducts(null, $product);
        } else { // product is grouped
            return $product->getTypeInstance(true)->getAssociatedProducts($product);
        }
    }
}
