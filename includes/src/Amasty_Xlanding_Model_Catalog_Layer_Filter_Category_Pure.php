<?php
if ('true' == (string)Mage::getConfig()->getNode('modules/Amasty_Shopby/active')){
    class Amasty_Xlanding_Model_Catalog_Layer_Filter_Category_Pure extends Amasty_Shopby_Model_Catalog_Layer_Filter_Category {}
} else {
    class Amasty_Xlanding_Model_Catalog_Layer_Filter_Category_Pure extends Mage_Catalog_Model_Layer_Filter_Category {}
}
