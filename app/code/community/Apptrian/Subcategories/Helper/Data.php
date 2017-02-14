<?php
/**
 * @category   Apptrian
 * @package    Apptrian_Subcategories
 * @author     Apptrian
 * @copyright  Copyright (c) 2015 Apptrian (http://www.apptrian.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Apptrian_Subcategories_Helper_Data extends Mage_Core_Helper_Abstract
{
	
	/**
	 * Returns extension version.
	 *
	 * @return string
	 */
	public function getExtensionVersion()
	{
		return (string) Mage::getConfig()->getNode()->modules->Apptrian_Subcategories->version;
	}
	
	/**
	 * Finds and returns array of subcategories.
	 * 
	 * @param array $params
	 * @return array
	 */
    public function getSubcategories($params)
    {
    	
    	$categoryId = $params['category_id'];
    	$isLayered  = $params['is_layered'];
    	$pageType   = $params['page_type'];
    	
    	$categories = array();
    	
    	$attributesToSelect = array('name', 'url_key', 'url_path', 'image', 'thumbnail', 'description', 'meta_description');
    	
        // Get sort attribute and sort direction from config
        // Attribute options: "name", "position", and "created_at"
        // Direction options: "asc" and "desc"
        $sortAttribute = Mage::getStoreConfig('apptrian_subcategories/' . $pageType . '/sort_attribute');
        $sortDirection = Mage::getStoreConfig('apptrian_subcategories/' . $pageType . '/sort_direction');
        
        // Get category IDs from config
        $categoryIds = trim(Mage::getStoreConfig('apptrian_subcategories/home_page/category_ids'), ',');
        
        // For home page when category_ids is provided
        if ($pageType == 'home_page' && $categoryIds != '') {
        	
        	// "Random" mode
        	if (Mage::getStoreConfig('apptrian_subcategories/home_page/mode') == 'random') {
        		
        		// Get random parent ID
        		$id = $this->getRandomId($categoryIds);
        		
        		$category = Mage::getModel('catalog/category')->load($id);

                $childrenIds = $category->getChildren();
                
        		$collection = Mage::getResourceModel('catalog/category_collection')
        		    ->addAttributeToSelect($attributesToSelect)
        		    ->addAttributeToFilter('is_active', 1)
        		    ->addAttributeToSort($sortAttribute, $sortDirection)
        		    ->addIdFilter($childrenIds)
                    ->load();
        		 
        		// Get categories array from collection
        		$categories = $this->getCategoriesFromCollection($collection);
        		
        	// "Specific" mode
        	} else {
        		
        		$collection = Mage::getResourceModel('catalog/category_collection')
                    ->addAttributeToSelect($attributesToSelect)
                    ->addAttributeToFilter('is_active', 1)
                    ->addIdFilter($categoryIds);
        		 
        		// In this context "position" is different and must be done programmatically
        		// so there is no need to sort it
        		if ($sortAttribute != 'position') {
        		
        			$collection->addAttributeToSort($sortAttribute, $sortDirection)->load();
        		
        			// Get categories array from collection
        			$categories = $this->getCategoriesFromCollection($collection);
        		
        		} else {
                    
                    $collection->load();
                    
        			// Get categories array from collection sorted by $categoryIDs
        			$categories = $this->getCategoriesFromCollection($collection, $categoryIds);
        		
        		}
        		
        	}
        	
        // For layered category pages
        } elseif ($isLayered == true && $categoryId > 0) {
        	
        	$category = Mage::getModel('catalog/category')->load($categoryId);
        	 
        	$childrenIds = $category->getChildren();
        	 
        	$collection = Mage::getResourceModel('catalog/category_collection')
	        	->addAttributeToSelect($attributesToSelect)
	        	->addAttributeToFilter('is_active', 1)
	        	->addAttributeToSort($sortAttribute, $sortDirection)
	        	->addIdFilter($childrenIds)
	        	->load();
        	
        	// Get categories array from collection
        	$categories = $this->getCategoriesFromCollection($collection);
        	
        // For category pages and home page when category_ids field is empty
        } else {
            
            $childrenIds = Mage::getSingleton('catalog/layer')->getCurrentCategory()->getChildren();
            
            $collection = Mage::getResourceModel('catalog/category_collection')
        	    ->addAttributeToSelect($attributesToSelect)
        	    ->addAttributeToFilter('is_active', 1)
        	    ->addAttributeToSort($sortAttribute, $sortDirection)
                ->addIdFilter($childrenIds)
                ->load();
	            
        	// Get categories array from collection
        	$categories = $this->getCategoriesFromCollection($collection);
        	
        }
        
        return $categories;
        
    }
    
    /**
     * This method checks for page type and if it is layered category page
     * filters and validates category id
     * 
     * @param string $pageType
     * @return array
     */
    public function checkCategoryPage($pageType)
    {
    	// Initialize result array
    	$r = array(
    		'category_id' => 0,
    		'is_layered'  => false,
    		'page_type'   => $pageType
    	);
    	
    	if ($pageType == 'category_page_layered') {
    		
    		$r['category_id'] = $this->filterAndValidateCategoryId(Mage::app()->getRequest()->getParam('cat'));
    		$r['is_layered']  = true;
    		$r['page_type']   = 'category_page';
    		
    	}
    	
    	return $r;
    	
    }
    
    /**
     * Filters and validates "cat" url query param for layered category pages
     * 
     * @param string $id
     * @return int
     */
    public function filterAndValidateCategoryId($id)
    {
    	
    	$filterChain = new Zend_Filter();
    	$filterChain->addFilter(new Zend_Filter_StripTags())
    				->addFilter(new Zend_Filter_StringTrim());
    	
    	$idFiltered = $filterChain->filter($id);
    	
    	if ($idFiltered != ''
    		&& Zend_Validate::is($idFiltered, 'Digits')
    		&& Zend_Validate::is($idFiltered, 'GreaterThan', array(1))
    	) {
    			
    		return (int) $idFiltered;
    			
    	} else {
    			
    		return 0;
    			
    	}
    	
    }
    
    /**
     * Based on provided comma separated list of Ids, returns one random id.
     * 
     * @param string $categoryIds
     * @return string
     */
    public function getRandomId($categoryIds)
    {
    	
    	$pool = explode(',', $categoryIds);
    	
    	$index = array_rand($pool, 1);
    	
    	return $pool[$index];
    	
    }
    
    /**
     * Based on provided Collection and optionally sort order, returns sorted array of categories.
     * 
     * @param Mage_Catalog_Model_Resource_Category_Collection $collection
     * @param string $sortOrder
     * @return array
     */
    public function getCategoriesFromCollection($collection, $sortOrder = '')
    {
        
        $categories = array();
        
        if ($sortOrder != '') {
        	
        	$sort = explode(',', $sortOrder);
        	
        	foreach ($sort as $id) {
        		
        		$c = $collection->getItemById($id);
        		
        		if ($c != null) {
        			
        			$categories[$id] = $this->categoryToArray($c);
        			
        		}
        		
        	}
        	
        } else {
        	
        	foreach ($collection as $c) {
        		
        		$id = $c->getId();
        		
        		$categories[$id] = $this->categoryToArray($c);
        		
        	}
        	
        }
        
        return $categories;
        
    }
    
    /**
     * Based on provided category object returns small category array with necessary data.
     * 
     * @param Mage_Catalog_Model_Category $c
     * @return array
     */
    public function categoryToArray($c)
    {
    	
    	$category = array();
    	
    	$category['url']              = $c->getUrl();
    	$category['name']             = Mage::helper('core')->htmlEscape($c->getName());
    	$category['image']            = $c->getImage();
    	$category['thumbnail']        = $c->getThumbnail();
    	$category['description']      = Mage::helper('catalog/output')->categoryAttribute($c, $c->getDescription(), 'description');
    	$category['meta_description'] = Mage::helper('core')->htmlEscape($c->getMetaDescription());
    	
    	return $category;
    	
    }
    
    /**
     * Generates image url based on provided data.
     * 
     * @param array $category
     * @param string $showImage
     * @param string $placeholderImageUrl
     * @return string
     */
    public function getImageUrl($category, $showImage, $placeholderImageUrl)
    {
        
        if ($showImage == 'image') {
            
            if ($category['image'] != null) {
                $url = Mage::getBaseUrl('media') . 'catalog/category/' . $category['image'];
            } else {
                $url = $placeholderImageUrl;
            }
            
        } elseif ($showImage == 'thumbnail') {
            
            if ($category['thumbnail'] != null) {
                $url = Mage::getBaseUrl('media') . 'catalog/category/' . $category['thumbnail'];
            } else {
                $url = $placeholderImageUrl;
            }
            
        } else {
            
            $url = '';
            
        }
        
        return $url;
        
    }
    
    /**
     * Returns proper description text based on provided data.
     * 
     * @param array $category
     * @param string $showDescription
     * @return string
     */
    public function getDescription($category, $showDescription)
    {
        
        // description field should be used
        if ($showDescription == 'description') {
            
            $text = $category['description'];
            
            if ($text != '') {
                $categoryDescription = '<div class="apptrian-subcategories-category-description">' . $text . '</div>';
            } else {
                $categoryDescription = '';
            }
            
        // meta_description field should be used
        } elseif ($showDescription == 'meta_description') {
            
            $text = $category['meta_description'];
            
            if ($text != '') {
                $categoryDescription = '<div class="apptrian-subcategories-category-description"><p>' . $text . '</p></div>';
            } else {
                $categoryDescription = '';
            }
            
        // none (do not show category description)
        } else {
        
            $categoryDescription = '';
            
        }
        
        return $categoryDescription;
        
    }
    
    /**
     * Returns array of exclude Ids from config.
     * 
     * @return array
     */
    public function getExcludedIds()
    {
    	$excludeIds = trim(Mage::getStoreConfig('apptrian_subcategories/category_page/exclude_ids'), ',');
    	
    	return explode(',', $excludeIds);
    	
    }
    
    /**
     * Checks if category is in excluded list.
     * 
     * @param array $params
     * @return boolean
     */
    public function isExcluded($params)
    {
    	$categoryId = $params['category_id'];
    	$isLayered  = $params['is_layered'];
    	
    	if (!($isLayered == true && $categoryId > 0)) {
    		
    		$c = Mage::registry('current_category');
    		
    		if ($c !== null) {
    			$categoryId = $c->getId();
    		} else {
    			$categoryId = 0;
    		}
    		
    	}
    	
    	if ($categoryId > 0) {
    		
    		$excluded = $this->getExcludedIds();
    		
    		if (count($excluded) > 0 && in_array($categoryId, $excluded)) {
    			
    			return true;
    			
    		// Exclude list is empty
    		} else {
    			
    			return false;
    			
    		}
    		
    	// Not a category page
    	} else {
    		
    		return false;
    		
    	}
    	
    }
    
}