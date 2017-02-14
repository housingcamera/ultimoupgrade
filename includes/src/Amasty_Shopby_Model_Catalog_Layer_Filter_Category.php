<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */
class Amasty_Shopby_Model_Catalog_Layer_Filter_Category extends Amasty_Shopby_Model_Catalog_Layer_Filter_Category_Adapter
{
    /**
     * Display Types
     */
    const DT_DEFAULT    = 0;
    const DT_DROPDOWN   = 1;
    const DT_WSUBCAT    = 2;
    const DT_STATIC2LVL = 3;
    const DT_ADVANCED   = 4;

    protected $_facetedData;

    protected $includedIds;
    protected $excludedIds;

    protected static $_appliedState = FALSE;
    
    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        if ('catalogsearch' == Mage::app()->getRequest()->getModuleName()) {
            $items = parent::_getItemsData();

            // exclude
            foreach ($items as $key => $item) {
                if ($this->isExcluded($item['value'])) {
                    unset($items[$key]);
                }
            }

            // Hide one value
            if (Mage::getStoreConfig('amshopby/general/hide_one_value') && count ($items) == 1) {
                $items = array();
            }

            return $items;
        }

        $_displayType = Mage::getStoreConfig('amshopby/general/categories_type');
        $isStatic2LevelTree = (self::DT_STATIC2LVL == $_displayType);
        $isShowSubCats      = (self::DT_WSUBCAT    == $_displayType);
        $isAdvanced         = (self::DT_ADVANCED   == $_displayType);

        $isCountEnabled     = Mage::getStoreConfig('catalog/layered_navigation/display_product_count');
        if (is_null($isCountEnabled)) {
            // Magento 1.4 has no option
            $isCountEnabled = true;
        }


        if ($isAdvanced) {
            return array(
                0 => 1
            );
        }

        // alwaus use root category
        $currentCategory = $this->getCategory();

        $root = Mage::getModel('catalog/category')
                ->load($this->getLayer()->getCurrentStore()->getRootCategoryId()) ;

        $categories = $isStatic2LevelTree ? $root->getChildrenCategories() : $currentCategory->getChildrenCategories();


        if ($isStatic2LevelTree)
            $this->getLayer()->setCurrentCategory($root);

        if ($isCountEnabled) {
            $this->getLayer()->getProductCollection()->addCountToCategories($categories);
        }


        $data = array();

        $startLevel = 0;
        if ($isShowSubCats) {

            $isNotRoot = ($root->getId() != $currentCategory->getId());
            //Get parent category of the current category
            if ($isNotRoot) {
                $parent = $currentCategory->getParentCategory();
                if ($parent->getId() != $root->getId() && !$this->isExcluded($parent->getId())){
                    $data[] = $this->_prepareItemData($parent, false, 0, false, false);
                }
            }

            //Add current category
            if ($isNotRoot) {
                $startLevel = count($data) > 0 ? 2 : 1;
                $data[] = $this->_prepareItemData($currentCategory, true, $startLevel, false, false);
            }
        }
        foreach ($categories as $category) {
            $id = $category->getId();
            if ($this->isExcluded($id))
            {
                continue;
            }

            $data[] = $this->_prepareItemData($category, $id == $currentCategory->getId(), $startLevel + 1, false, $isCountEnabled);
            if ($isShowSubCats || $isStatic2LevelTree) {
                $children = $category->getChildrenCategories();
                if ($children && count($children)){

                    //remember that category has children
                    $last = count($data)-1;
                    if ($data[$last])
                        $data[$last]['has_children'] = true;

                    $this->getLayer()->getProductCollection()->addCountToCategories($children);
                    foreach ($children as $child){ // we shoul have all categories in the top navigation cache, so no additional queries
                        if ($this->isExcluded($child->getId()))
                        {
                            continue;
                        }
                        $isFolded   = ($currentCategory->getParentId() != $child->getParentId());
                        $isSelected = ($currentCategory->getId() == $child->getId());
                        if ($isSelected && $data[$last]){
                            $data[$last]['is_child_selected'] = true;
                        }

                        $row = $this->_prepareItemData($child, $isSelected, $startLevel + 2, $isFolded, $isCountEnabled);
                        $data[] = $row;
                    }
                }
            } //if add sub-categories
        }

        //restore category
        if ($isStatic2LevelTree)
            $this->getLayer()->setCurrentCategory($currentCategory);

        return $data;
    }

    protected function isExcluded($id)
    {
        if (is_null($this->excludedIds)) {
            $exclude = Mage::getStoreConfig('amshopby/general/exclude_cat');
            if ($exclude){
                $this->excludedIds = explode(',', preg_replace('/[^\d,]+/','', $exclude));
            }
            else {
                $this->excludedIds = array();
            }
        }
        if (in_array($id, $this->excludedIds)) {
            return true;
        }

        if (is_null($this->includedIds)) {
            $include = Mage::getStoreConfig('amshopby/general/include_cat');
            if ($include){
                $this->includedIds = explode(',', preg_replace('/[^\d,]+/','', $include));
            }
            else {
                $this->includedIds = array();
            }
        }
        if ($this->includedIds && !in_array($id, $this->includedIds)) {
            return true;
        }

        return false;
    }

    protected function _initItems()
    {
        if ('catalogsearch' == Mage::app()->getRequest()->getModuleName())
            return parent::_initItems();

        $data  = $this->_getItemsData();
        $items = array();
        foreach ($data as $itemData) {
            if (!$itemData)
                continue;

            $obj = new Varien_Object();
            $obj->setData($itemData);
            $obj->setUrl($itemData['value']);

            $items[] = $obj;
        }
        $this->_items = $items;
        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param $isSelected
     * @param int $level
     * @param bool $isFolded
     * @param bool $addCount
     * @return array|null
     */
    protected function _prepareItemData($category, $isSelected, $level = 1, $isFolded = false, $addCount = false)
    {
        $row = null;

        /*
         * Display only active category and having products or being parents
         */
        if ($category->getIsActive() && (!$addCount || $category->getProductCount())) {
            $row = array(
                'label'       => Mage::helper('core')->htmlEscape($category->getName()),
                'value'       => Mage::helper('amshopby/url')->getCategoryUrl($category),
                'count'       => $addCount ? $this->_getProductCount($category) : 0,
                'level'       => $level,
                'id'          => $category->getId(),
                'parent_id'   => $category->getParentId(),
                'is_folded'   => $isFolded,
                'is_selected' => $isSelected,
            );
        }
        return $row;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @return mixed
     */
    protected function _getProductCount($category)
    {
        /** @var Amasty_Shopby_Helper_Data $helper */
        $helper = Mage::helper('amshopby');
        if ($helper->useSolr()) {
            // not implemented yet
            return null;
        } else {
            return $category->getProductCount();
        }
    }

    public function addFacetCondition()
    {
        parent::addFacetCondition();
    }
    
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $filter = (int) $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }
        $this->_categoryId = $filter;

        Mage::register('current_category_filter', $this->getCategory(), true);

        $this->_appliedCategory = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($filter);

        if ($this->_isValidCategory($this->_appliedCategory)) {
            $this->getLayer()->getProductCollection()
                ->addCategoryFilter($this->_appliedCategory);

            if (!self::$_appliedState){
                $this->getLayer()->getState()->addFilter(
                    $this->_createItem($this->_appliedCategory->getName(), $filter)
                );
            } 
            self::$_appliedState = TRUE;
        }

        return $this;
    }
}