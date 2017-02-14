<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */

/**
 * @method string getCmsBlock()
 * @method string getCmsBlockBottom()
 * @method string getDescr()
 * @method string getImgBig()
 * @method string getMetaDescr()
 * @method string getMetaKw()
 * @method string getMetaTitle()
 * @method int getOptionId()
 * @method boolean getShowOnList()
 * @method boolean setShowOnList()
 * @method string getTitle()
 * @method string getUrlAlias()
 */
class Amasty_Shopby_Model_Value extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        $this->_init('amshopby/value');
    }
}