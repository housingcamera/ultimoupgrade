<?php
if(Mage::getConfig()->getModuleConfig('IWD_Productvideo')->is('active', 'true') && class_exists("IWD_Productvideo_Block_Frontend_Media")){
    class Infortis_CloudZoom_Block_Product_View_Media_Rewrite extends IWD_Productvideo_Block_Frontend_Media {}
} else {
    class Infortis_CloudZoom_Block_Product_View_Media_Rewrite extends Mage_Catalog_Block_Product_View_Media {}
}

class Infortis_CloudZoom_Block_Product_View_Media extends Infortis_CloudZoom_Block_Product_View_Media_Rewrite
{
}
