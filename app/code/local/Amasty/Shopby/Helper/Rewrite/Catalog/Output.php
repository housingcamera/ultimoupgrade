<?php

class Amasty_Shopby_Helper_Rewrite_Catalog_Output extends Mage_Catalog_Helper_Output
{
    /**
     * Prepare category attribute html output
     *
     * @param   Mage_Catalog_Model_Category $category
     * @param   string $attributeHtml
     * @param   string $attributeName
     * @return  string
     */
    public function categoryAttribute($category, $attributeHtml, $attributeName)
    {
        $isDefaultCategory = $category->getId() == Mage::app()->getStore()->getRootCategoryId();
        if ($attributeName == 'name' && $isDefaultCategory) {
            /** @var Amasty_Shopby_Helper_Attributes $helper */
            $helper = Mage::helper('amshopby/attributes');
            $setting = $helper->getRequestedBrandOption();
            if (is_array($setting)) {
                return $this->escapeHtml($setting['title']);
            }
        }

        return parent::categoryAttribute($category, $attributeHtml, $attributeName);
    }
}