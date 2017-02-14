<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
class Amasty_Label_Model_Shape
{
    protected static $_shapeTypes = array(
        'circle'        => 'Circle',
        'rquarter'      => 'Right Quarter',
        'rbquarter'      => 'Right Bottom Quarter',
        'lquarter'      => 'Left Quarter',
        'lbquarter'      => 'Left Bottom Quarter',
        'list'          => 'List',
        'note'          => 'Note',
        'flag'          => 'Flag',
        'banner'        => 'Banner',
        'tag'           => 'Tag',
    );

    public static function getShapes(){
        return self::$_shapeTypes;
    }

    public static function generateNewLabel($shape, $color){
        $fileName =  $shape . '_' . $color . '.svg';
        $svg =  Mage::getBaseDir('media') . '/amlabel/' . $fileName;
        if (file_exists($svg)) {
            return $fileName;
        }
        else{
            $svg =  Mage::getBaseDir('media') . '/amlabel/' . $shape . '.svg';
            if (file_exists($svg)) {
                $fileContents = self::_changeColorImage($svg, $color);
                if ($fileContents) {
                    $newName = Mage::getBaseDir('media') . '/amlabel/' . $fileName;
                    if( self::_copyAndRenameImage($fileContents, $newName)) {
                        return $fileName;
                    }
                }
            }
        }

        return false;
    }

    public static function generateShape($shape, $type, $checked){
        $html = '<div class="amlabel-shape">';
            $html .= '<input ' . $checked . ' type="radio" value="' . $shape . '" name="shape_type' .
                $type . '" id="shape_' . $shape . $type . '">';
        $svg =  Mage::getBaseDir('media') . '/amlabel/' . $shape . '.svg';
        if (file_exists($svg)) {
            $svg =  Mage::getBaseUrl('media') . 'amlabel/' . $shape . '.svg';
            $html .=   '<label for="shape_' . $shape . $type . '">';
                $html .= '<img src="' . $svg . '" class="amlabel-shape-image">';
            $html .= '</label>';
        }

        $html .= '</div>';
        return $html;
    }

    protected static function _changeColorImage($imageSvgFile, $color)
    {
        $fileContents = file_get_contents($imageSvgFile);
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        if( $document->loadXML($fileContents)) {
            $allTags = $document->getElementsByTagName("path");
            foreach ($allTags as $tag) {
                $vectorColor = $tag->getAttribute('fill');
                if (strtoupper($vectorColor) != '#FFFFFF') {
                    $tag->setAttribute('fill', '#' . $color);
                    $fileContents = $document->saveXML($document);
                    return $fileContents;
                }
            }
        }
        else{
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('amlabel')->__('Failed to load SVG file ' . $imageSvgFile . ' as XML.  It probably contains malformed data.')
            );
            return false;
        }

        return $fileContents;
    }

    protected static function _copyAndRenameImage($fileContents, $newName)
    {
        try {
            file_put_contents($newName, $fileContents);
            return true;
        }
        catch(Exception $exc){
            Mage::getSingleton('adminhtml/session')->addError($exc->getMessage());
            return false;
        }
    }
}
