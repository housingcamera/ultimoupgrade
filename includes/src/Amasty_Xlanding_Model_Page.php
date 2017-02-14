<?php

class Amasty_Xlanding_Model_Page extends Mage_Core_Model_Abstract
{
    /**
     * Page's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const ON_SALE_YES = 2;
    const ON_SALE_NO = 1;

    const IS_NEW_YES = 2;
    const IS_NEW_NO = 1;

    const IS_INSTOCK_YES = 2;

    protected $_attributeCache;

    const FILTER_CONDITION_AND = 1;
    const FILTER_CONDITION_OR = 0;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('amlanding/page');
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    public function getAttributesAsArray()
    {
    	$array = array();
    	$attributes = $this->getData('attributes');
    	if (!empty($attributes)) {
    		$array = unserialize($attributes);
    	}
    	return $array;
    }

    public function applyPageRules()
    {
    	$layer = Mage::getSingleton('catalog/layer');

        $collection = $layer->getProductCollection();  
        
        if ($this->getCategory()) {
          $category = Mage::getModel('catalog/category')->load($this->getCategory());
	   if ($category) {
              $layer->setCurrentCategory($category);
              $collection = $category->getProductCollection();
              $layer->prepareProductCollection($collection);
          }
   	}
        
        $this->prepareCollection($layer->getProductCollection());

        if (isset($_GET['xlanding_debug_page'])) {
//            var_dump(get_class($layer->getProductCollection()));
            echo $layer->getProductCollection()->getSelect();
//            exit(1);
        }
    }
    
    public function prepareCollection($collection){
        $collection->distinct(true);
                
        $collection->addStoreFilter();
        
//        $this->applyCategoryFilter($collection);

        $this->applyAttributesFilter($collection);

        $this->applyStockStatusFilter($collection);

        $this->applyNewCriteriaFilter($collection);

        $this->applyIsSaleFilter($collection);
    }

    function applyCategoryFilter(&$collection){
        if ($this->getCategory()) {
            $fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);
            if (isset($fromPart["cat_index"]) && isset($fromPart["cat_index"]['joinCondition'])){
                $fromPart["cat_index"]['joinCondition'] = 'cat_index.product_id=e.entity_id AND cat_index.store_id=' . Mage::app()->getStore()->getId() . ' AND cat_index.visibility IN(2, 4) AND cat_index.category_id = ' . $this->getCategory();
                $collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
            }            
        }
    }

    function getModifedAttributes(){
        $attributes = $this->getAttributesAsArray();
        $filters = array();
        if ($attributes){
            foreach ($attributes as $value) {
                $filter = $this->getAttributeFilter($value);
                
                if ($filter) {
                    
                    if ($value['cond'] == 'like' && count($filter['like']) > 1){
                        
                        foreach($filter['like'] as $like){
                            $filters[] = array_merge($filter, array(
                                "like" => array($like)
                            ));
                        }
                        
                    } else if ($value['cond'] == 'in' || $value['cond'] == 'nin'){
                        
                        $found = false;
                        foreach ($filters as $ind => $exist){
                            if ($exist['attribute'] == $filter['attribute']){
                                
                                $filters[$ind][$value['cond']][] = $value['value'];
                                
                                $found = true;
                                break;
                            }
                        }
                        if (!$found){
                            $filters[] = array(
                                'attribute' => $filter['attribute'],
                                'cond' => $value['cond'],
                                $value['cond'] => array(
                                    $value['value']
                                )
                            );
                        }
                    } else {
                        $filters[] = $filter;
                    }
                }
                
            }
        }
        
        return $filters;
    }

    function applyAttributesFilter(&$collection){
        
        $filters = $this->getModifedAttributes();
        
        if (count($filters) >  0) {
            if ((int)$this->getAdvancedFilterCondition() === self::FILTER_CONDITION_OR) {
                $collection->addAttributeToFilter($filters, null, 'inner');

            } else {

                foreach($filters as $filter){
                    $collection->addAttributeToFilter(array($filter), null, 'inner');
                }
            }
        }
    }

    function applyNewCriteriaFilter(&$collection){
        $newCriteriaDays = Mage::getStoreConfig('amlanding/advanced/new_criteria');
        if ($isNew = $this->getIsNew()) {
            if ($isNew == self::IS_NEW_YES) {
                if ($newCriteriaDays) {
                    $threshold = Mage::getStoreConfig('amlanding/advanced/new_threshold');
                    $collection->getSelect()->where('datediff(now(), created_at) < ?', $threshold);
                } else {
                    $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
                    $collection
                            ->addAttributeToFilter('news_from_date',
                                    array(

                                    'or' => array(
                            0 => array('date' => false, 'to' => $todayDate),
                                            1 => array('is' => new Zend_Db_Expr('null'))
                                    )), 'left')
                            ->addAttributeToFilter('news_to_date',
                                    array(
                                    'or'=> array(
                            0 => array('date' => false, 'from' => $todayDate),
                            1 => array('is' => new Zend_Db_Expr('null'))
                                    )), 'left');

                    $from = "e.news_from_date";
                    $to = "e.news_to_date ";

                    if (!Mage::helper('catalog/product_flat')->isEnabled()){
                       $from = "IF(at_news_from_date.value_id > 0, at_news_from_date.value, at_news_from_date_default.value)";
                       
                       $to = "IF(at_news_to_date.value_id > 0, at_news_to_date.value, at_news_to_date_default.value)";
                    }
                    
                    $collection->getSelect()->where('NOT ('. $from .' IS NULL AND ' . $to . ' IS NULL)');

//                    $collection->getSelect()->where('NOT (IF(at_news_from_date.value_id > 0, at_news_from_date.value, at_news_from_date_default.value) IS NULL AND IF(at_news_to_date.value_id > 0, at_news_to_date.value, at_news_to_date_default.value)IS NULL)');

                }
            }

            if ($isNew == self::IS_NEW_NO) {
                if ($newCriteriaDays) {
                    $threshold = Mage::getStoreConfig('amlanding/advanced/new_threshold');
                    $collection->getSelect()->where('datediff(now(), created_at) > ?', $threshold);
                } else {
                    $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
                    $collection
                            ->addAttributeToFilter('news_from_date',
                                    array(
                                    'or' => array(
                            0 => array('date' => false, 'from' => $todayDate),
                                            1 => array('is' => new Zend_Db_Expr('null'))
                                    )), 'left')
                            ->addAttributeToFilter('news_to_date',
                                    array('or'=> array(
                            0 => array('date' => false, 'to' => $todayDate),
                            1 => array('is' => new Zend_Db_Expr('null'))
                                    )), 'left');

                }
            }
        }
    }

    function applyIsSaleFilter(&$collection){
        if ($sale = $this->getIsSale()){
            if ($sale == self::ON_SALE_YES) {
                $collection
                    ->addAttributeToFilter('special_price', array('gt' => 0));
                            $todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATE_INTERNAL_FORMAT);
                    $collection
                            ->addAttributeToFilter('special_from_date', array('null' => true))
                            ->addAttributeToFilter('special_to_date', array('null' => true))
                            ->addAttributeToFilter('special_from_date',
                                    array(
                                    'or' => array(
                                        0 => array('date' => false, 'to' => $todayDate),
                                        1 => array('is' => new Zend_Db_Expr('null'))
                                    )), 'left')
                            ->addAttributeToFilter('special_to_date',
                                    array('or'=> array(
                                        0 => array('date' => false, 'from' => $todayDate),
                                        1 => array('is' => new Zend_Db_Expr('null'))
                                    )), 'left')
                            ;
                    
                $where = $collection->getSelect()->getPart(Zend_Db_Select::WHERE);

                $withoutDatesCond = 'NOT (' . str_replace('AND ', '', $where[0]) . ' AND ' . str_replace('AND ', '', $where[1]) . ')';
                $where[0] = $withoutDatesCond;
                $where[1] = '';
                $collection->getSelect()->setPart(Zend_Db_Select::WHERE, $where);

            }

            if ($sale == self::ON_SALE_NO) {
                $collection->addAttributeToFilter('special_price', array('null'=>'special_price'), 'left');
            }
        }
    }

    function applyStockStatusFilter(&$collection){
        if ($stock = $this->getStockStatus()){
            if ($stock == self::IS_INSTOCK_YES) {
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
            }
        }
    }
        
    protected function _prepareValue($param){
        $value = $param['value'];
    	$cond  = $param['cond'];
        
        if ($cond == "like"){
            if (is_array($value)){
                foreach($value as $index => $el){
                    if (strpos($el, "%") === FALSE){
                        $value[$index] = "%" . $el . "%";
                    }
                    
                }
            }
        }
        
        return $value;
    }
        
    function getAttributeFilter($param)
    {
    	$code  = $param['code'];
    	$value = $this->_prepareValue($param);
    	$cond  = $param['cond'];
    	
    	if (!isset($this->_attributeCache[$code])) {
    		$attribute = Mage::getModel('catalog/product')->getResource()->getAttribute($code);
    		$this->_attributeCache[$code] = $attribute;
    	}

    	$attribute = $this->_attributeCache[$code];
    	/*$filterable = $attribute->getIsFilterable();
        
        $ret = null;

    	if ($filterable && !Mage::helper('amlanding')->isPrice($code)) {
	    	$alias = $code . $cond . '_idx';
			$connection = $this->_getResource()->getReadConnection();
			$conditions = array(
				"{$alias}.entity_id = e.entity_id",
			    $connection->quoteInto("{$alias}.attribute_id = ?", $attribute->getAttributeId()),
			    $connection->quoteInto("{$alias}.store_id = ?",     $collection->getStoreId()),
			);

			$condSql = array(
				'eq' => ' = ?',
				'in' => ' in (?)',
				'nin' => ' not in (?)',
			);

			$conditions[] = $connection->quoteInto("{$alias}.value " . $condSql[$cond], $value);

			$collection->getSelect()->join(
				array($alias => Mage::getResourceModel('catalog/layer_filter_attribute')->getMainTable()),
				join(' AND ', $conditions),
			    array()
	        );
    	} 
    	else {*/
            if ($attribute->getFrontendInput() == "multiselect"){
                if ($cond == "in" || $cond == "eq"){
                    $filter = array();
                    if (is_array($value)){
                        foreach($value as $v){
                            $filter[] = array(
                                'like' => '%' . $v . '%'
                            );
                        }
                        
                        $ret = array(
                            "attribute" => $attribute,
                            'cond' => 'like',
                            'like' => '%' . $filter . '%'
                        );
                    } else {
                        $ret = array(
                            "attribute" => $code,
                            'cond' => 'like',
                            'like' => '%' . $value . '%'
                        );
                    }
                    
                }
            }
            else {
                $code = $attribute->getAttributeCode();
                
                $ret = array(
                    "attribute" => $code,
                    'cond' => $cond,
                    $cond => $value
                );

                
            }
//    	}
        
       return $ret; 
    }

  	public function massChangeStatus($ids, $status)
    {
        return $this->getResource()->massChangeStatus($ids, $status);
    }
    
    public function getUploadPath()
    {
        return  'amasty' . DS .'amxlanding';
    }
    
    public function getLayoutFileUrl(){
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $this->getLayoutFile();
    }
    
    protected function _beforeSave(){
        
        if(isset($_FILES['layout_file']) &&
                $_FILES['layout_file']['name'] != '') {
        
            try{
                $uploader = new Varien_File_Uploader('layout_file');
                $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $this->setLayoutFileName($uploader->getCorrectFileName($_FILES['layout_file']['name']));
                $ext = pathinfo($_FILES['layout_file']['name'], PATHINFO_EXTENSION);

                $result = $uploader->save(
                    Mage::getBaseDir('media') . DS . $this->getUploadPath(), uniqid().".".$ext
                );

                $this->setLayoutFile($this->getUploadPath() . $result['file']); 
            } catch (Exception $e){
                Mage::throwException($this->__('Invalid image format'));
            }
            
        } else {
            
            $layoutFile = $this->getLayoutFile();
            
            if (isset($layoutFile['delete']) &&
                    $layoutFile['delete'] == 1
            ) {
                $this->setLayoutFile(NULL);
            } else {
                $this->setLayoutFile($this->layout_file["value"]);
            }
            
        }
        return parent::_beforeSave(); 
    }
    
    public function getAvailableSortBy()
    {
        $available = $this->getData('available_sort_by');
        if (empty($available)) {
            return array();
        }
        if ($available && !is_array($available)) {
            $available = explode(',', $available);
        }
        return $available;
    }

    public function getAvailableSortByOptions() {
        $availableSortBy = array();
        $defaultSortBy   = Mage::getSingleton('catalog/config')
            ->getAttributeUsedForSortByArray();
        if ($this->getAvailableSortBy()) {
            foreach ($this->getAvailableSortBy() as $sortBy) {
                if (isset($defaultSortBy[$sortBy])) {
                    $availableSortBy[$sortBy] = $defaultSortBy[$sortBy];
                }
            }
        }

        if (!$availableSortBy) {
            $availableSortBy = $defaultSortBy;
        }

        return $availableSortBy;
    }

    public function getDefaultSortBy() {
        if (!$sortBy = $this->getData('default_sort_by')) {
            $sortBy = Mage::getSingleton('catalog/config')
                ->getProductListDefaultSortBy();
        }
        
        $available = $this->getAvailableSortByOptions();
        if (!isset($available[$sortBy])) {
            $sortBy = array_keys($available);
            $sortBy = $sortBy[0];
        }

        return $sortBy;
    }
}
