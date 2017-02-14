<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2010-2012 Amasty (http://www.amasty.com)
 */
if (Mage::helper('amshopby')->useSolr()) {
    class Amasty_Shopby_Model_Catalog_Layer_Filter_Decimal_Adapter extends Enterprise_Search_Model_Catalog_Layer_Filter_Decimal {}
} else {
    class Amasty_Shopby_Model_Catalog_Layer_Filter_Decimal_Adapter extends Mage_Catalog_Model_Layer_Filter_Decimal {}
}